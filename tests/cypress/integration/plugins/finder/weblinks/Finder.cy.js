describe('Test in backend that the Smart Search', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
  });

  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__weblinks WHERE title = 'Test weblink'");
    cy.task('queryDB', "DELETE FROM #__categories WHERE title = 'Test weblink category'")
  });

  it('can index a new weblink', () => {
    // Enable the smart search weblinks plugin
    cy.db_enableExtension('1', 'plg_finder_weblinks');
    cy.visit('/administrator/index.php?option=com_weblinks&view=weblink&layout=edit');
    cy.get('#jform_title').clear().type('Test weblink');
    cy.get('#jform_url').clear().type('www.example.com');
    cy.clickToolbarButton('Save & Close');
    // Visit the smart search page
    cy.visit('/administrator/index.php?option=com_finder&view=index');
    cy.contains('Test weblink').should('exist');

    cy.db_enableExtension('0', 'plg_finder_weblinks');
  });

  it('can index a new weblink category', () => {
    // Enable the smart search weblinks plugin
    cy.db_enableExtension('1', 'plg_finder_weblinks');
    cy.visit('/administrator/index.php?option=com_categories&task=category.add&extension=com_weblinks');
    cy.get('#jform_title').type('Test weblink category');
    cy.clickToolbarButton('Save & Close');
    // Visit the smart search page
    cy.visit('/administrator/index.php?option=com_finder&view=index');
    cy.contains('Test weblink category').should('exist');

    cy.db_enableExtension('0', 'plg_finder_weblinks');
  });

  it('can delete the indexed weblink items', () => {
    // Visit the smart search page
    cy.visit('/administrator/index.php?option=com_finder&view=index');
    cy.searchForItem('Test weblink');
    cy.checkAllResults();
    cy.clickToolbarButton('Action');
    cy.contains('Delete').click();
    cy.clickDialogConfirm(true);
    cy.checkForSystemMessage('items deleted.');
    cy.contains('Test weblink').should('not.exist');
    cy.contains('Test weblink category').should('not.exist');
  });
});
