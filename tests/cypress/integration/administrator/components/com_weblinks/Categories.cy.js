
describe('Test in backend that the categories list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_categories&view=categories&extension=com_weblinks&filter=');
  });

  it('has a title', () => {
    cy.contains('h1', 'Web Links: Categories').should('exist');
  });

  it('can display a list of weblink categories', () => {
    cy.db_createCategory({ title: 'Test weblink category', extension: 'com_weblinks' }).then(() => {
      cy.reload();

      cy.contains('Test weblink category').should('exist');
    });
  });

  it('can open the weblink category form', () => {
    cy.clickToolbarButton('New');

    cy.contains('Web Links: New Category');
  });

  it('can publish the test weblink category', () => {
    cy.db_createCategory({ title: 'Test weblink category', published: 0, extension: 'com_weblinks' }).then(() => {
      cy.reload();
      cy.searchForItem('Test weblink category');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Publish').click();

      cy.checkForSystemMessage('Category published.');
    });
  });

  it('can unpublish the test weblink category', () => {
    cy.db_createCategory({ title: 'Test weblink category', published: 1, extension: 'com_weblinks' }).then(() => {
      cy.reload();
      cy.searchForItem('Test weblink category');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Unpublish').click();

      cy.checkForSystemMessage('Category unpublished.');
    });
  });

  it('can archive the test weblink category', () => {
    cy.db_createCategory({ title: 'Test weblink category', published: 1, extension: 'com_weblinks' }).then(() => {
      cy.reload();
      cy.searchForItem('Test weblink category');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Archive').click();

      cy.checkForSystemMessage('Category archived.');
    });
  });

  it('can trash the test weblink category', () => {
    cy.db_createCategory({ title: 'Test weblink category', extension: 'com_weblinks' }).then(() => {
      cy.reload();
      cy.searchForItem('Test weblink category');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Trash').click();

      cy.checkForSystemMessage('Category trashed.');
    });
  });

  it('can delete the test weblink category', () => {
    // The category needs to be created through the form so proper assets are created
    cy.visit('/administrator/index.php?option=com_categories&task=category.add&extension=com_weblinks');
    cy.get('#jform_title').type('Test weblink category');
    cy.get('#jform_published').select('Trashed');
    cy.clickToolbarButton('Save & Close');
    cy.setFilter('published', 'Trashed');
    cy.searchForItem('Test weblink category');
    cy.checkAllResults();
    cy.clickToolbarButton('empty trash');
    cy.clickDialogConfirm(true);

    cy.checkForSystemMessage('Category deleted.');
  });

  it('Verifies all category tabs are present and functional', () => {
    // Visit the category edit page
    cy.visit('/administrator/index.php?option=com_categories&task=category.add&extension=com_weblinks');

    // Define the expected tabs
    const expectedTabs = [
      'Category',
      'Options',
      'Publishing',
      //'Associations',
      'Permissions'
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

    // Verify initial active tab (Category)
    cy.get('#myTab div[role="tablist"] > button[role="tab"]:visible:nth-child(1)')
      .should('have.attr', 'aria-expanded', 'true')
      .and('contain.text', 'Category');

    // Verify tab panels exist
    const tabPanels = [
      'general',
      'attrib-options',
      'publishing',
    //  'associations',
      'rules'
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
          .should('have.attr', 'aria-expanded', 'true');

        cy.get(`#${tabPanels[index]}`)
          .should('be.visible');
      }
    });
  });
});
