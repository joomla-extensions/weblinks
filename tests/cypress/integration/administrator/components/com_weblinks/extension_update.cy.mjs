describe('Extension Upgrade Test (Latest Stable -> PR Candidate)', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
  });

  it('updates successfully from the latest published release to the current PR candidate', () => {
    // ------------------------------------------------------------------
    // STEP 1: Dynamically fetch the latest stable release (n-1) from GitHub
    // ------------------------------------------------------------------
    cy.request('https://api.github.com/repos/joomla-extensions/weblinks/releases/latest').then((response) => {
      const zipAsset = response.body.assets.find((asset) => asset.name.endsWith('.zip'));
      expect(zipAsset, 'Latest release zip asset found').to.exist;

      const latestStableUrl = zipAsset.browser_download_url;

      // Install latest published stable version over the current installation
      cy.installExtensionFromUrl(latestStableUrl);
      cy.get('#system-message-container').should('contain', 'Installation of the package was successful');
    });

    // ------------------------------------------------------------------
    // STEP 2: Create sample data in version n-1 to test data retention
    // ------------------------------------------------------------------
    cy.visit('administrator/index.php?option=com_weblinks&view=weblink&layout=edit');
    cy.get('#jform_title').type('Pre-Upgrade Test Link');
    cy.get('#jform_url').type('https://joomla.org');
    cy.clickToolbarButton('save');
    cy.get('#system-message-container').should('contain', 'Weblink saved');

    // ------------------------------------------------------------------
    // STEP 3: Update to current PR candidate (n) using local web server URL
    // ------------------------------------------------------------------
    const prCandidateUrl = `${Cypress.config('baseUrl')}/pkg-weblinks-current.zip`;

    cy.installExtensionFromUrl(prCandidateUrl);
    cy.get('#system-message-container').should('contain', 'Installation of the package was successful');

    // ------------------------------------------------------------------
    // STEP 4: Verify data integrity and component health post-upgrade
    // ------------------------------------------------------------------
    cy.visit('administrator/index.php?option=com_weblinks');
    cy.contains('a', 'Pre-Upgrade Test Link').should('exist');
  });
});
