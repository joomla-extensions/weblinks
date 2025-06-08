// type definitions for Cypress object "cy"
// <reference types="cypress" />

describe('Install Joomla and Weblinks package', () => {
  it('Install Joomla and Weblinks package', function () {
    // Disable compat plugin
    cy.db_enableExtension(0, 'plg_behaviour_compat');
    // Disable stats plugin
    cy.db_enableExtension(0, 'plg_system_stats');
    cy.doAdministratorLogin(Cypress.env('username'), Cypress.env('password'), false)
    cy.cancelTour();
    // cy.disableStatistics()
    cy.setErrorReportingToDevelopment()
    cy.doAdministratorLogout()
    // Update to the correct secret for the API tests because of the bearer token
    cy.config_setParameter('secret', 'tEstValue');
  })
})
