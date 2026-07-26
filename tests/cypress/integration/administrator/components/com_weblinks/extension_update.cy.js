describe('Extension Upgrade Test (Latest Release -> PR Candidate)', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
  });

  it('uninstalls PR candidate, installs latest stable release, creates data, and updates to PR candidate', () => {
    // ------------------------------------------------------------------
    // 1. Uninstall current PR version installed during environment setup
    // ------------------------------------------------------------------
    cy.visit('administrator/index.php?option=com_installer&view=manage');
    cy.searchForItem('pkg_weblinks');

    cy.get('body').then(($body) => {
      if ($body.find('table tbody tr').length > 0) {
        cy.checkAllResults();
        cy.clickToolbarButton('delete');
        cy.get('#system-message-container').should('contain', 'Uninstalling the package was successful');
      }
    });

    // ------------------------------------------------------------------
    // 2. Fetch and install latest stable release (n-1) from GitHub
    // ------------------------------------------------------------------
    cy.request('https://api.github.com/repos/joomla-extensions/weblinks/releases/latest').then((response) => {
      const zipAsset = response.body.assets.find((asset) => asset.name.endsWith('.zip'));
      expect(zipAsset, 'Latest release zip asset found').to.exist;

      cy.installExtensionFromUrl(zipAsset.browser_download_url);
      cy.get('#system-message-container').should('contain', 'Installation of the package was successful');
    });

    // ------------------------------------------------------------------
    // 3. Create sample data in version n-1
    // ------------------------------------------------------------------
    cy.visit('administrator/index.php?option=com_weblinks&view=weblink&layout=edit');
    cy.get('#jform_title').type('Pre-Upgrade Test Link');
    cy.get('#jform_url').type('https://joomla.org');
    cy.clickToolbarButton('save');
    cy.get('#system-message-container').should('contain', 'Web link saved');

    // ------------------------------------------------------------------
    // 4. Upgrade using current PR Candidate package (n)
    // ------------------------------------------------------------------
    const prCandidateUrl = `${Cypress.config('baseUrl')}/pkg-weblinks-current.zip`; 
    cy.installExtensionFromUrl(prCandidateUrl);
    cy.get('#system-message-container').should('contain', 'Installation of the package was successful');

    // ------------------------------------------------------------------
    // 5. Verify sample data survived the update
    // ------------------------------------------------------------------
    cy.visit('administrator/index.php?option=com_weblinks');
    cy.contains('a', 'Pre-Upgrade Test Link').should('exist');
  });
});
