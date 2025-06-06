import('./commands.mjs');
import('joomla-cypress');

before(() => {
  cy.task('startMailServer');
  cy.task('clearLogs');
});

afterEach(() => {
  cy.checkForPhpNoticesOrWarnings();
  // Check for logs
  cy.task('checkForLogs');
  cy.task('cleanupDB');
});
