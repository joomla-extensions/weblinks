describe('Test in backend that the weblinks component', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.task('queryDB', "DELETE FROM #__weblinks WHERE title = 'Test weblink'");
    cy.visit('/administrator/index.php?option=com_weblinks&view=weblinks&filter=');
  });

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', 'Web Links');
  });

  it('can display a list of weblinks', () => {
    cy.db_createWeblink({ title: 'Test weblink' }).then(() => {
      cy.reload();

      cy.contains('Test weblink');
    });
  });

  it('can open the weblink form', () => {
    cy.clickToolbarButton('New');

    cy.contains('Web Link: New');
  });

  it('can create a weblink', () => {
    cy.visit('/administrator/index.php?option=com_weblinks&view=weblink&layout=edit');
    cy.get('#jform_title').clear().type('Test weblink');
    cy.get('#jform_url').clear().type('www.example.com');
    cy.clickToolbarButton('Save & Close');

    cy.checkForSystemMessage('Web link saved');
    cy.contains('Test weblink');
  });

  it('cannot create a weblink without title', () => {
    cy.visit('/administrator/index.php?option=com_weblinks&view=weblink&layout=edit');
    cy.get('#jform_url').clear().type('www.example.com');
    cy.clickToolbarButton('Save & Close');
    cy.checkForSystemMessage("The form cannot be submitted as it's missing required data");
    cy.contains('Test weblink').should('not.exist');
  });

  it('can publish the test weblink', () => {
    cy.db_createWeblink({ title: 'Test weblink', state: 0 }).then(() => {
      cy.reload();
      cy.searchForItem('Test weblink');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Publish').click();

      cy.checkForSystemMessage('Web Link published');
    });
  });

  it('can unpublish the test weblink', () => {
    cy.db_createWeblink({ title: 'Test weblink', state: 1 }).then(() => {
      cy.reload();
      cy.searchForItem('Test weblink');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Unpublish').click();

      cy.checkForSystemMessage('Web Link unpublished');
    });
  });

  it('can trash the test weblink', () => {
    cy.db_createWeblink({ title: 'Test weblink' }).then(() => {
      cy.reload();
      cy.searchForItem('Test weblink');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Trash').click();

      cy.checkForSystemMessage('Web Link trashed');
    });
  });

  it('can delete the test weblink', () => {
    cy.db_createWeblink({ title: 'Test weblink', state: -2 }).then(() => {
      cy.reload();
      cy.setFilter('published', 'Trashed');
      cy.searchForItem('Test weblink');
      cy.checkAllResults();
      cy.clickToolbarButton('empty trash');
      cy.clickDialogConfirm(true);

      cy.checkForSystemMessage('Web Link deleted');
    });
  });
});
