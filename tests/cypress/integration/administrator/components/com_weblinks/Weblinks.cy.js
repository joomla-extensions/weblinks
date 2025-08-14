describe('Test in backend that the weblinks component', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.task('queryDB', "DELETE FROM #__weblinks WHERE title LIKE '%Test weblink%'");
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

  it('can archive the test weblink', () => {
    cy.db_createWeblink({ title: 'Test weblink', state: 0 }).then(() => {
      cy.reload();
      cy.searchForItem('Test weblink');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Archive').click();

      cy.checkForSystemMessage('Web Link archived');
    });
  });

  it('can unarchive the test weblink', () => {
    cy.db_createWeblink({ title: 'Test weblink', state: 2 }).then(() => {

      cy.reload();
      cy.searchForItem('Test weblink');
      cy.get('button.js-stools-btn-filter').click();
      cy.get('#filter_published').select('2')
      cy.get('.js-grid-item-action').click();

      cy.checkForSystemMessage('Web Link unpublished');
    });
  });

  it('can batch move weblinks to a new category', () => {
    cy.db_createCategory({ title: 'Source Category', extension: 'com_weblinks' }).then((sourceCategory) => {
      cy.db_createCategory({ title: 'Destination Category', extension: 'com_weblinks' }).then((destinationCategory) => {
        cy.db_createWeblink({ title: 'Test Weblink 1', alias: 'test-weblink-1', catid: sourceCategory });
        cy.db_createWeblink({ title: 'Test Weblink 2', alias: 'test-weblink-2', catid: sourceCategory });

        cy.reload();
        cy.checkAllResults();
        cy.clickToolbarButton('Action');
        cy.contains('Batch').click();
        cy.get('#batch-category-id').select(destinationCategory.toString());
        cy.get('#batch-submit-button-id').click();

        cy.checkForSystemMessage('Batch process completed.');

        cy.get('button.js-stools-btn-filter').click();
        cy.get('#filter_category_id').select(destinationCategory.toString());
        cy.contains('Test Weblink 1').should('exist');
        cy.contains('Test Weblink 2').should('exist');

        cy.reload();
        cy.get('button.js-stools-btn-filter').click();
        cy.get('#filter_category_id').select(sourceCategory.toString());
        cy.contains('Test Weblink 1').should('not.exist');
        cy.contains('Test Weblink 2').should('not.exist');
      });
    });
  });

  it('can batch change the access level of weblinks', () => {
      cy.db_createWeblink({ title: 'Test Weblink 1', alias: 'test-weblink-1', access: 1 });
      cy.db_createWeblink({ title: 'Test Weblink 2', alias: 'test-weblink-2', access: 1 });

      cy.reload();
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Batch').click();
      cy.get('#batch-access').select('Registered');
      cy.get('#batch-submit-button-id').click();

      cy.checkForSystemMessage('Batch process completed.');

      cy.get('button.js-stools-btn-filter').click();
      cy.get('#filter_access').select('Registered');
      cy.contains('Test Weblink 1').should('exist');
      cy.contains('Test Weblink 2').should('exist');
  });

  it('can batch add tags to weblinks', () => {
    cy.db_createTag({ title: 'Test Tag' }).then((tagId) => {
      cy.db_createWeblink({ title: 'Test Weblink' }).then((weblink) => {
        cy.reload();
        cy.checkAllResults();
        cy.clickToolbarButton('Action');
        cy.contains('Batch').click();
        cy.get('#batch-tag-id').select(tagId.toString());
        cy.get('#batch-submit-button-id').click();

        cy.checkForSystemMessage('Batch process completed.');

        cy.contains('a', 'Test Weblink').click({force:true});
        cy.contains('Test Tag').should('exist');
      });
    });
  });

  it('Verifies all web link tabs are present and functional', () => {
    // Visit the category edit page
    cy.visit('/administrator/index.php?option=com_weblinks&view=weblink&layout=edit');

    // Define the expected tabs
    const expectedTabs = [
      'New Web Link',
      'Images',
      'Publishing',
      'Options'
    ];

    // Verify tab structure and visibility
    cy.get('#myTab div[role="tablist"] > button[role="tab"]:visible')
      .should('have.length', expectedTabs.length)
      .each(($tab, index) => {
        // Check tab text and visibility
        cy.wrap($tab)
          .should('be.visible')
          .and('contain.text', expectedTabs[index])
          .and('have.attr', 'role', 'tab');
      });

    // Verify initial active tab (New Web Link)
    cy.get('#myTab div[role="tablist"] > button[role="tab"]:visible:nth-child(1)')
      .should('have.attr', 'aria-selected', 'true')
      .and('contain.text', 'New Web Link');

    // Verify tab panels exist
    const tabPanels = [
      'details',
      'images',
      'publishing',
      'attrib-jbasic'
    ];

    tabPanels.forEach(panelId => {
      cy.get(`#${panelId}`)
        .should('exist')
        .and('have.attr', 'role', 'tabpanel');
    });

    // Optional: Test tab switching
    expectedTabs.forEach((tabText, index) => {
      if (index > 0) { // Skip first tab (already active)
        cy.contains('#myTab div[role="tablist"] > button[role="tab"]:visible', tabText)
          .click()
          .should('have.attr', 'aria-selected', 'true');

        cy.get(`#${tabPanels[index]}`)
          .should('be.visible');
      }
    });
  });
});
