// type definitions for Cypress object "cy"
// <reference types="cypress" />

describe('Install Joomla and Weblinks package', () => {
  it('Install Joomla and Weblinks package', function () {
    cy.doAdministratorLogin(Cypress.env('username'), Cypress.env('password'), false)
    cy.cancelTour();
    cy.disableStatistics()
    cy.setErrorReportingToDevelopment()
    cy.doAdministratorLogout()
    // Update to the correct secret for the API tests because of the bearer token
    cy.config_setParameter('secret', 'tEstValue');
  })
})
