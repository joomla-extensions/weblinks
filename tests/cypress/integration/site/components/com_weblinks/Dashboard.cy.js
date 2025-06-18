describe('Test in frontend that the weblinks dashboard', () => {
  beforeEach(() => {
    cy.doFrontendLogin();
    cy.task('queryDB', "DELETE FROM #__weblinks WHERE title LIKE '%Test weblink%'");
    cy.visit('/index.php?option=com_weblinks&view=weblinks');
  });

  it('can display a list of weblinks', () => {
    cy.db_createWeblink({ title: 'Test weblink 1' })
      .then(() => cy.db_createWeblink({ title: 'Test weblink 2' }))
      .then(() => {
        cy.reload();

        cy.contains('Test weblink 1');
        cy.contains('Test weblink 2');
      });
  });

  it('can open the weblink form', () => {
    cy.task('queryDB', 'DELETE FROM #__weblinks');
    cy.reload();

    cy.contains('No Web Links have been created yet.');
    cy.contains('Add your first Web Link').click();
    cy.contains('Title');
    cy.contains('Alias');
    cy.contains('URL');
  });

  it('can create a weblink', () => {
    cy.visit('/index.php?option=com_weblinks&task=weblink.add');
    cy.get('#jform_title').clear().type('Test weblink');
    cy.get('#jform_url').clear().type('www.example.com');
    cy.contains('Save').click();

    cy.checkForSystemMessage('Web Link successfully submitted.');
  });

  it('cannot create a weblink without title', () => {
    cy.visit('/index.php?option=com_weblinks&task=weblink.add');
    cy.get('#jform_url').clear().type('www.example.com');
    cy.contains('Save').click();
    
    cy.checkForSystemMessage("The form cannot be submitted as it's missing required data.");
  });

  it('can publish the test weblink', () => {
    cy.db_createWeblink({ title: 'Test weblink', state: 0 }).then(() => {
      cy.reload();
      cy.get('#filter_search').clear().type('Test weblink');
      cy.get('.filter-search-bar__button').click();
      cy.contains('Test weblink').should('be.visible');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Publish').click();

      cy.checkForSystemMessage('1 weblinks published');
    });
  });

  it('can unpublish the test weblink', () => {
    cy.db_createWeblink({ title: 'Test weblink', state: 1 }).then(() => {
      cy.reload();
      cy.get('#filter_search').clear().type('Test weblink');
      cy.get('.filter-search-bar__button').click();
      cy.contains('Test weblink').should('be.visible');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Unpublish').click();

      cy.checkForSystemMessage('1 weblinks unpublished');
    });
  });

  it('can trash the test weblink', () => {
    cy.db_createWeblink({ title: 'Test weblink' }).then(() => {
      cy.reload();
      cy.get('#filter_search').clear().type('Test weblink');
      cy.get('.filter-search-bar__button').click();
      cy.contains('Test weblink').should('be.visible');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Trash').click();

      cy.checkForSystemMessage('1 weblinks trashed');
      cy.contains('Clear').click();
    });
  });

  it('can delete the test weblink', () => {
    cy.db_createWeblink({ title: 'Test weblink', state: -2 }).then(() => {
      cy.reload();

      cy.get('#adminForm .js-stools-container-filters').then($container => {
        if ($container.is(':not(:visible)')) {
          cy.get('button.js-stools-btn-filter').click();
        }
      });
      cy.get('#filter_published').select('Trashed');
      cy.contains('#weblinkList', 'Test weblink').should('be.visible');
      cy.get('#filter_search').type('Test weblink');
      cy.get('.filter-search-bar__button').click();
      cy.contains('#weblinkList', 'Test weblink').should('be.visible');

      cy.checkAllResults();
      cy.contains('Delete').click();
      cy.clickDialogConfirm(true);

      cy.checkForSystemMessage('1 weblinks deleted');
    });
  });

  it('can batch change the access level', () => {
    cy.db_createWeblink({ title: 'Test weblink 1', alias: 'test-weblink-1' }).then(() => {
      cy.db_createWeblink({ title: 'Test weblink 2', alias: 'test-weblink-2' }).then(() => {
        cy.reload();
        cy.contains('Clear').click();
        cy.checkAllResults();
        cy.clickToolbarButton('Action');
        cy.contains('Batch').click();
        cy.get('#batch-access').select('Registered');
        cy.contains('Process').click();

        cy.checkForSystemMessage('Batch process completed.');
      });
    });
  });

  it('can batch add tags to weblinks', () => {
    cy.db_createTag({ title: 'Test Tag' }).then(() => {
      cy.db_createWeblink({ title: 'Test weblink 1', alias: 'test-weblink-1' }).then(() => {
        cy.db_createWeblink({ title: 'Test weblink 2', alias: 'test-weblink-2' }).then(() => {
          cy.reload();
          cy.checkAllResults();
          cy.clickToolbarButton('Action');
          cy.contains('Batch').click();
          cy.get('#batch-tag-id').select('Test Tag');
          cy.contains('Process').click();

          cy.checkForSystemMessage('Batch process completed.');
        });
      });
    });
  });
});
