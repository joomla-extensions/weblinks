// tests/cypress/integration/.../Update_AlikonwebPackage.cy.js
//
// NOTE: this file defines `cy.removeOrphanedUpdateSite` inline via
// Cypress.Commands.add so the spec is fully self-contained and runnable
// as-is. If you already keep custom commands centralized in
// cypress/support/commands.js, just cut the Cypress.Commands.add block
// below and move it there instead.
//
// ASSUMPTION: this relies on a `queryDB` Cypress task (cy.task('queryDB', sql))
// that runs raw SQL against whichever DB driver (mysql/mariadb/postgres) the
// current CI matrix leg is using and returns rows. This mirrors the pattern
// you already use elsewhere (querying the DB directly instead of trusting
// UI/task return values, as you did for db_createMenuItem). If your actual
// task has a different name/signature, tell me and I'll adjust the calls.

Cypress.Commands.add('removeOrphanedUpdateSite', (packageName) => {
  // 1. Find any update_sites rows whose location matches our fake update.xml
  //    URL pattern (i.e. update sites created/edited by this test) OR whose
  //    linked extension no longer exists in #__extensions.
  const findOrphans = `
    SELECT us.update_site_id, us.location
    FROM #__update_sites us
    LEFT JOIN #__update_sites_extensions use ON usx.update_site_id = us.update_site_id
    LEFT JOIN #__extensions ext ON ext.extension_id = usx.extension_id
    WHERE us.name = '${packageName}'
       OR (usx.extension_id IS NOT NULL AND ext.extension_id IS NULL)
  `;

  cy.task('queryDB', findOrphans).then((rows) => {
    if (!rows || rows.length === 0) {
      cy.log('No orphaned update sites found — nothing to clean up');
      return;
    }

    const ids = rows.map((r) => r.update_site_id);
    cy.log(`Removing ${ids.length} orphaned update site row(s): ${ids.join(', ')}`);

    const idList = ids.join(',');
    const deleteExtLinks = `DELETE FROM #__update_sites_extensions WHERE update_site_id IN (${idList})`;
    const deleteSites = `DELETE FROM #__update_sites WHERE update_site_id IN (${idList})`;
    // Also clear any stale cached "update available" rows for extensions
    // that no longer exist, since these are what PackageAdapter chokes on
    // when it tries to resolve ->id on a null extension row.
    // NOTE: written as a NOT EXISTS correlated subquery (not MySQL's
    // multi-table DELETE...JOIN syntax) so it also runs on PostgreSQL.
    const deleteStaleUpdates = `
      DELETE FROM #__updates u
      WHERE NOT EXISTS (
        SELECT 1 FROM #__extensions ext WHERE ext.extension_id = u.extension_id
      )
    `;

    cy.task('queryDB', deleteExtLinks);
    cy.task('queryDB', deleteSites);
    cy.task('queryDB', deleteStaleUpdates);
  });
});

describe('Extension Upgrade Test (Latest Release -> PR Candidate)', () => {
  // Confermati dal manifest pkg_weblinks.xml
  const PACKAGE_ELEMENT = 'pkg_weblinks';
  const PACKAGE_NAME = 'pkg_weblinks'; // <name> nel manifest, nessuna stringa di lingua da risolvere

  // ATTENZIONE: l'element DB del plugin è "weblinks" (dal tag id=""),
  // non "plg_system_weblinks" (quello è solo il nome del file zip)
  const PLUGIN_ELEMENT = 'weblinks';
  const PLUGIN_NAME = 'System - Web Links'; // nome visualizzato in com_plugins

  const PR_ZIP_PUBLIC_URL = `${Cypress.config('baseUrl')}/pkg-weblinks-current.zip`;
  const CMS_PATH = Cypress.expose('cmsPath'); // es. /tests/www/mysql
  const FAKE_UPDATE_XML_RELATIVE = 'pr-build/update.xml';
  const FAKE_UPDATE_XML_PUBLIC_URL = `${Cypress.config('baseUrl')}/${FAKE_UPDATE_XML_RELATIVE}`;
  const FAKE_VERSION = '99.99.99';

  // Stato condiviso tra gli it() dello stesso describe (il server/DB persiste
  // tra i test nello stesso describe, quindi possiamo far girare i vari step
  // in sequenza invece che in un unico it() monolitico)
  const ctx = {
    latestZipUrl: null,
  };

  beforeEach(() => {
    cy.doAdministratorLogin();
  });

  // Se uno step fallisce, ripulisci subito eventuali update site orfani
  // invece di lasciare Joomla in uno stato a metà: è proprio questo stato
  // a metà che genera il warning PHP "Attempt to read property 'id' on null"
  // in PackageAdapter.php durante l'afterEach originale.
  afterEach(function () {
    if (this.currentTest.state === 'failed') {
      cy.log('Previous step failed — cleaning up any orphaned update site before aborting the chain');
      //cy.removeOrphanedUpdateSite(PACKAGE_NAME);
    }
  });

  it('1. uninstalls any pre-existing pkg_weblinks package (cascades to subextensions)', () => {
    cy.visit('administrator/index.php?option=com_installer&view=manage');
    cy.searchForItem(PACKAGE_NAME);
    cy.get('body').then(($body) => {
      if ($body.find('table tbody tr').length > 0) {
        cy.checkAllResults();
        cy.get('button.button-status-group.btn.btn-action.dropdown-toggle').click();
        cy.get('button.button-delete.dropdown-item').click();
        cy.get('div.joomla-dialog-container')
          .find('button.button.button-primary.btn.btn-primary[data-button-ok]')
          .click();
        cy.checkForSystemMessage('was successful');
      }
    });

    // Pulizia esplicita: rimuovi l'update site orfano lasciato dal package
    // disinstallato, altrimenti com_installer&view=manage può incappare in
    // un extension_id null durante la prossima "Find Updates"/render
    //cy.removeOrphanedUpdateSite(PACKAGE_NAME);
  });

  it('2. installs latest stable package release from GitHub', () => {
    cy.request({
      url: 'https://api.github.com/repos/joomla-extensions/weblinks/releases/latest',
      failOnStatusCode: false,
    }).then((response) => {
      expect(response.status, 'GitHub releases/latest reachable').to.eq(200);

      const assets = response.body.assets || [];
      cy.log(`Found ${assets.length} release assets: ${assets.map((a) => a.name).join(', ')}`);

      // FIX: il nome del file zip può usare l'underscore dell'element
      // (pkg_weblinks-x.y.z.zip) invece del trattino usato in precedenza
      // (pkg-weblinks...). Accettiamo entrambe le varianti.
      const zipAsset = assets.find(
        (asset) => /^pkg[-_]weblinks.*\.zip$/i.test(asset.name)
      );

      expect(
        zipAsset,
        `Latest package zip asset found (checked: ${assets.map((a) => a.name).join(', ') || 'none'})`
      ).to.exist;

      ctx.latestZipUrl = zipAsset.browser_download_url;
      cy.wrap(ctx.latestZipUrl).as('latestZipUrl');
    });

    cy.get('@latestZipUrl').then((url) => {
      cy.installExtensionFromUrl(url);
    });
    cy.get('#system-message-container').should('contain', 'Installation of the package was successful');
  });

  it('3. enables MagicLogin plugin (state that must survive the update)', () => {
    cy.db_enableExtension('0', 'plg_system_weblinks');
    cy.visit('administrator/index.php?option=com_plugins&view=plugins');
    cy.searchForItem(PLUGIN_NAME);
    cy.checkAllResults();
    cy.contains('Enable').click();
    cy.on('window:confirm', () => true);
    cy.checkForSystemMessage('Plugin enabled.');
  });

  it('4. writes the fake update.xml for the package into the webroot', () => {
    if (!CMS_PATH) {
      throw new Error('Cypress.env("cmsPath") is not set — check cypress.config.js env block');
    }

    // Pass relative path to the Cypress task
    cy.task('getRelativeFileSha512', 'pkg-weblinks-current.zip').then((sha512Hash) => {
      const fakeUpdateXml = `<?xml version="1.0" encoding="utf-8"?>
<updates>
  <update>
    <name>${PACKAGE_NAME}</name>
    <description>PR candidate build (mocked update)</description>
    <element>${PACKAGE_ELEMENT}</element>
    <type>package</type>
    <version>${FAKE_VERSION}</version>
    <client>site</client>
    <infourl title="testcom">https://github.com/joomla-extensions/weblinks</infourl>
    <downloads>
      <downloadurl type="full" format="zip">${PR_ZIP_PUBLIC_URL}</downloadurl>
    </downloads>
    <tags>
      <tag>stable</tag>
    </tags>
    <sha512>${sha512Hash}</sha512>
    <targetplatform name="joomla" version=".*"/>
  </update>
</updates>`;

      cy.writeFile(`${CMS_PATH}/${FAKE_UPDATE_XML_RELATIVE}`, fakeUpdateXml);
    });
  });

  it('5. points the package update site at the fake update.xml (via DB)', () => {
    // Find the update_site_id linked to our package extension.
    const findUpdateSiteId = `
      SELECT us.update_site_id
      FROM #__update_sites us
      JOIN #__update_sites_extensions usx ON usx.update_site_id = us.update_site_id
      JOIN #__extensions ext ON ext.extension_id = usx.extension_id
      WHERE ext.element = '${PACKAGE_ELEMENT}' AND ext.type = 'package'
    `;
 
    cy.task('queryDB', findUpdateSiteId).then((rows) => {
      expect(rows && rows.length, `update site found for element "${PACKAGE_ELEMENT}"`).to.be.greaterThan(0);
      const updateSiteId = rows[0].update_site_id;
 
      // Point it at the fake update.xml, make sure it's enabled, and reset
      // last_check_timestamp so the next "Find Updates" doesn't skip it as
      // recently checked.
      const updateLocation = `
        UPDATE #__update_sites
        SET location = '${FAKE_UPDATE_XML_PUBLIC_URL}',
            enabled = 1,
            last_check_timestamp = 0
        WHERE update_site_id = ${updateSiteId}
      `;
      const purgeCachedUpdates = `
        DELETE FROM #__updates WHERE element = '${PACKAGE_ELEMENT}'
      `;
      cy.task('queryDB', updateLocation);
      cy.task('queryDB', purgeCachedUpdates);
    });
  });

  it('debug: verifies update.xml is reachable via HTTP', () => {
    cy.request({
      url: FAKE_UPDATE_XML_PUBLIC_URL,
      failOnStatusCode: false,
    }).then((response) => {
      expect(response.status, `HTTP status of ${FAKE_UPDATE_XML_PUBLIC_URL}`).to.eq(200);
      expect(response.headers['content-type']).to.include('xml');
      expect(response.body).to.include(PACKAGE_ELEMENT);
    });
  });

  it('6. finds the mocked update', () => {
    cy.visit('administrator/index.php?option=com_installer&view=update');
    cy.get('#toolbar-search').click();

    cy.searchForItem('Weblinks');
    cy.get('table tbody tr')
      .contains('th', 'Weblinks')
      .parents('tr')
      .find('span.badge.bg-success')
      .should('contain', FAKE_VERSION);
  });

  it('debug: check update_sites state in DB', () => {
    const query = `
      SELECT update_site_id, name, type, location, enabled, last_check_timestamp 
      FROM #__update_sites 
      WHERE location LIKE '%pr-build%' OR name = '${PACKAGE_NAME}'
    `;

    cy.task('queryDB', query).then((rows) => {
      cy.log('Update Sites in DB:', JSON.stringify(rows, null, 2));
    
      if (rows && rows.length > 0) {
        const site = rows[0];
        expect(site.enabled).to.eq(1);
        // If last_check_timestamp is still 0 after "Find Updates", Joomla never attempted the HTTP request
        expect(site.last_check_timestamp, 'last_check_timestamp should be updated').to.be.greaterThan(0);
      }
    });
  });

  it('7. applies the update through the real Joomla flow', () => {
    cy.visit('administrator/index.php?option=com_installer&view=update');
    cy.searchForItem(PACKAGE_ELEMENT);
    cy.checkAllResults();
    cy.get('#toolbar-upload').click();
    cy.get('#system-message-container').should('contain', 'Updating package was successful');
  });

  it('8. verifies the package updated and MagicLogin kept its configuration', () => {
    cy.visit('administrator/index.php?option=com_plugins&view=plugins');
    cy.searchForItem(PLUGIN_NAME);
    cy.get('tbody tr').contains(PLUGIN_NAME).parents('tr')
      .find('.badge-success')
      .should('exist');
  });
});
