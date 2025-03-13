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
});

