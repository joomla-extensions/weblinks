// type definitions for Cypress object "cy"
// <reference types="cypress" />

describe('Install Joomla and Weblinks package', () => {
  it('Install Joomla and Weblinks package', function () {
    cy.doAdministratorLogin(Cypress.env('username'), Cypress.env('password'), false)
    cy.cancelTour();
    cy.disableStatistics()
    cy.setErrorReportingToDevelopment()
    cy.doAdministratorLogout()
  })
})
