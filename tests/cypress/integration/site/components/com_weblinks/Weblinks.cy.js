describe('Test in frontend that the weblinks', () => {
  beforeEach(() => cy.task('queryDB', "DELETE FROM #__weblinks WHERE title like '%test weblink%'"));

  it('can display a list of weblinks in a category', () => {
    cy.db_createWeblink({ title: 'automated test weblink 1' })
      .then((weblink) => cy.db_createWeblink({ title: 'automated test weblink 2', catid: weblink.catid }))
      .then((weblink) => cy.db_createWeblink({ title: 'automated test weblink 3', catid: weblink.catid }))
      .then((weblink) => cy.db_createWeblink({ title: 'automated test weblink 4', catid: weblink.catid }))
      .then((weblink) => {
        cy.visit(`/index.php?option=com_weblinks&view=category&id=${weblink.catid}`);

        cy.contains('automated test weblink 1');
        cy.contains('automated test weblink 2');
        cy.contains('automated test weblink 3');
        cy.contains('automated test weblink 4');
      });
  });

  it('can display a single weblink', () => {
    // Create a single weblink
    cy.db_createWeblink({ title: 'Single test weblink', url: 'http://example.com' })
      .then((weblink) => {
        // Visit the weblink view page
        cy.visit(`/index.php?option=com_weblinks&view=weblink&id=${weblink.id}`);

        // Check if the weblink is displayed
        cy.contains('Single test weblink');
        cy.get('a[href="http://example.com"]').should('exist');
      });
  });

  it('can create a weblink through the form', () => {
    cy.doFrontendLogin();
    // Visit the weblink creation form
    cy.visit('/index.php?option=com_weblinks&view=form&layout=edit');

    // Fill out the form
    cy.get('#jform_title').type('Form test weblink');
    cy.get('#jform_url').type('http://example.com');
    // Click the save button
    cy.get('button.btn.btn-primary[onclick="Joomla.submitbutton(\'weblink.save\')"]').click();

    // Check if the weblink is successfully saved
    cy.checkForSystemMessage('Web Link successfully submitted.')
  });

    it('Visit a weblink and check the hits is incremented by one', () => {
    cy.db_updateExtensionParameter('count_clicks', '1', 'com_weblinks');
    cy.db_createWeblink({ title: 'automated test weblink', url: Cypress.config('baseUrl') })
      .then((weblink) => {
        cy.visit(`/index.php?option=com_weblinks&view=category&id=${weblink.catid}`);

        // Get the hits before clicking the link
        cy.get('div.list-hits.badge.bg-info.float-end').invoke('text').then((text) => {
          const hitsBefore = parseInt(text.match(/\d+/)[0], 10);

          // Click the link with the specific text
          cy.contains('a', 'automated test weblink').invoke('removeAttr', 'target').click();

          // Go back to the list page
          cy.go('back');

          // Get the hits after clicking the link
          cy.get('div.list-hits.badge.bg-info.float-end').invoke('text').then((text) => {
            const hitsAfter = parseInt(text.match(/\d+/)[0], 10);

            // Verify that the hits have increased by 1
            expect(hitsAfter).to.equal(hitsBefore + 1);
          });
        });
      });
  });

  it('Visit a weblink and check the hits is not incremented', () => {
    cy.db_updateExtensionParameter('count_clicks', '0', 'com_weblinks');
    cy.db_createWeblink({ title: 'automated test weblink', url: Cypress.config('baseUrl') })
      .then((weblink) => {
        cy.visit(`/index.php?option=com_weblinks&view=category&id=${weblink.catid}`);

        // Get the hits before clicking the link
        cy.get('div.list-hits.badge.bg-info.float-end').invoke('text').then((text) => {
          const hitsBefore = parseInt(text.match(/\d+/)[0], 10);

          // Click the link with the specific text
          cy.contains('a', 'automated test weblink').invoke('removeAttr', 'target').click();

          // Go back to the list page
          cy.go('back');

          // Get the hits after clicking the link
          cy.get('div.list-hits.badge.bg-info.float-end').invoke('text').then((text) => {
            const hitsAfter = parseInt(text.match(/\d+/)[0], 10);

            // Verify that the hits have not increased
            expect(hitsAfter).to.equal(hitsBefore);
          });
        });
      });
  });
});

