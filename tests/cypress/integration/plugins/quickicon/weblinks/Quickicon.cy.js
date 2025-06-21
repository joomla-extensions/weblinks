
describe('Test in backend that the Quickicon', () => {
  const quickIconAnchorId = 'PLG_QUICKICON_WEBLINKS';
  const moduleData = {
    title: 'Test Weblinks Quick Icons Module',
    module: 'mod_quickicon',
    position: 'cpanel',
    published: 1,
    client_id: 1,
    showtitle: 0,
  };

  beforeEach(() => {
    cy.doAdministratorLogin();
    // Enable the Quick Icon - Weblinks plugin
    cy.db_enableExtension('1', 'plg_quickicon_weblinks');
  });

  afterEach(() => {
    // Disable the plugin
    cy.db_enableExtension('0', 'plg_quickicon_weblinks');
  });

  it('shows the weblinks icon with add and without counter if disabled', () => {
    // Set the show_count parameter is set to 0
    cy.db_updateExtensionParameter('show_count', '0', 'plg_quickicon_weblinks');

    // Create a new Quick Icons module
    cy.db_createModule(moduleData).then(() => {
      // Go to Home Dashboard
      cy.visit('/administrator/index.php');

      // Check for the Weblinks Quick Icon
      cy.get(`a#${quickIconAnchorId}`).should('be.visible')

      // Check for the "add" link associated with the quick icon group
      cy.get(`a#${quickIconAnchorId}`)
        .parents('.quickicon-group')
        .find('.quickicon-linkadd a[href*="option=com_weblinks&task=weblink.add"]')
        .should('be.visible');

      // Check that the amount element should not exist
      cy.get(`a#${quickIconAnchorId} .quickicon-amount[data-url]`)
        .should('not.exist');
    });
  });

  it('shows the weblinks icon with add and counter if enabled', () => {
    // Set the show_count parameter is set to 1
    cy.db_updateExtensionParameter('show_count', '1', 'plg_quickicon_weblinks');

    // Create a new Quick Icons module
    cy.db_createModule(moduleData).then((moduleId) => {
      // Go to Home Dashboard
      cy.visit('/administrator/index.php');

      // Check for the Weblinks Quick Icon
      cy.get(`a#${quickIconAnchorId}`).should('be.visible');

      // Check for the "add" link associated with the quick icon group
      cy.get(`a#${quickIconAnchorId}`)
        .parents('.quickicon-group')
        .find('.quickicon-linkadd a[href*="option=com_weblinks&task=weblink.add"]')
        .should('be.visible');

      // Check that the amount element exists and shows a number
      cy.get(`a#${quickIconAnchorId} .quickicon-amount[data-url]`)
        .should('exist')
        .and('be.visible')
        .should(($el) => {
          // Get the text and remove all LRM characters (U+200E) for a cleaner check
          const text = $el.text().replace(/\u200E/g, '').trim();
          expect(text).not.to.be.empty;
          // Spinner should be gone, replaced by the count
          expect($el.find('span.icon-spinner').length).to.equal(0);
          // Check if the cleaned text consists only of digits
          expect(text).to.match(/^\d+$/);
        });
    });
  });
});
