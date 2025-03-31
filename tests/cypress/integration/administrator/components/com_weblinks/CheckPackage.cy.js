describe('Test that the weblinks extension package', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_installer&view=manage&filter=');
    cy.setFilter('core', 'Non-core Extensions');
    cy.searchForItem('Web Links');
  });

  it('has Smart search plugin installed', () => {
    // Check if the row with "Smart Search - Web Links" exists
    cy.get('#manageList tbody tr') // Target the table rows
      .contains('th', 'Smart Search - Web Links') // Check the <th> in the row
      .parents('tr') // Navigate to the parent row
      .should('exist') // Confirm the row exists
      // Verify other cells in the same row
      .within(() => {
        cy.get('td').eq(2).should('contain', 'Site'); // Location column
        cy.get('td').eq(3).should('contain', 'Plugin'); // Type column
        cy.get('td').eq(7).should('contain', 'finder'); // Folder column
      });
  });

  it('has Web links module installed', () => {
    // Check if the row with "Module - Web Links" exists
    cy.get('#manageList tbody tr') // Target the table rows
      .contains('div', 'This modules displays Web Links') // Check the <div> in the row
      .parents('tr') // Navigate to the parent row
      .should('exist') // Confirm the row exists
      // Verify other cells in the same row
      .within(() => {
        cy.get('td').eq(2).should('contain', 'Site'); // Location column
        cy.get('td').eq(3).should('contain', 'Module'); // Type column
        cy.get('td').eq(7).should('contain', 'N/A'); // Folder column
      });
  });

  it('has System plugin installed', () => {
    // Check if the row with "System - Web Links" exists
    cy.get('#manageList tbody tr') // Target the table rows
      .contains('th', 'System - Web Links') // Check the <th> in the row
      .parents('tr') // Navigate to the parent row
      .should('exist') // Confirm the row exists
      // Verify other cells in the same row
      .within(() => {
        cy.get('td').eq(2).should('contain', 'Site'); // Location column
        cy.get('td').eq(3).should('contain', 'Plugin'); // Type column
        cy.get('td').eq(7).should('contain', 'system'); // Folder column
      });
  });

  it('has Web links component installed', () => {
    // Check if the row with "Web Links Component" exists
    cy.get('#manageList tbody tr') // Target the table rows
      .contains('div', 'Component for web links management') // Check the <div> in the row
      .parents('tr') // Navigate to the parent row
      .should('exist') // Confirm the row exists
      // Verify other cells in the same row
      .within(() => {
        cy.get('td').eq(2).should('contain', 'Administrator'); // Location column
        cy.get('td').eq(3).should('contain', 'Component'); // Type column
        cy.get('td').eq(4).should('contain', '5.0.0-dev'); // Version column
        cy.get('td').eq(7).should('contain', 'N/A'); // Folder column
      });
  });

  it('has Web Links Package installed', () => {
    // Check if the row with "Web Links Package" exists
    cy.get('#manageList tbody tr') // Target the table rows
      .contains('th', 'Web Links Extension Package') // Check the <th> in the row
      .parents('tr') // Navigate to the parent row
      .should('exist') // Confirm the row exists
      // Verify other cells in the same row
      .within(() => {
        cy.get('td').eq(2).should('contain', 'Site'); // Location column
        cy.get('td').eq(3).should('contain', 'Package'); // Type column
        cy.get('td').eq(7).should('contain', 'N/A'); // Folder column
      });
  });
})
