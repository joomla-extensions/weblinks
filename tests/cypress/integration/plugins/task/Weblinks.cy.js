describe('Test weblinks task plugin', () => {
  beforeEach(() => {
    cy.task('clearEmails');
    cy.db_enableExtension('1', 'plg_task_weblinks');
    cy.doAdministratorLogin();
  });
  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__weblinks WHERE title LIKE '%automated test weblink%'");
    cy.task('queryDB', "DELETE FROM #__scheduler_tasks WHERE title LIKE '%Check Weblinks%'");
    cy.db_enableExtension('0', 'plg_task_weblinks');
  });
  it('can create and run weblinks check task', () => {
    cy.db_createSchedulerTask({
      title: 'Check Weblinks Task',
      type: 'check.weblinks',
      execution_rules: { 'rule-type': 'manual' },
      cron_rules: { type: 'manual', exp: '' },
      params: {},
    }).then((task) => {
      cy.visit('/administrator/index.php?option=com_scheduler&view=tasks&filter=');
      cy.searchForItem('Check Weblinks Task');
      cy.intercept('GET', '**/administrator/index.php?option=com_ajax&format=json&plugin=RunSchedulerTest&group=system&id=*').as('runschedulertest');
      cy.get('button[data-scheduler-run]').should('have.attr', 'data-id', task.id).click();
      cy.wait('@runschedulertest').then((interception) => {
        expect(interception.response.body.message).to.eq(null);
        expect(interception.response.body.success).to.eq(true);
      });
      cy.get('joomla-dialog[type="inline"]').should('be.visible');
      cy.get('joomla-dialog[type="inline"]').within(() => {
        cy.get('header.joomla-dialog-header').should('contain', `Test task (ID: ${task.id})`);
        cy.get('div.scheduler-status').should('contain', 'Status: Completed');
      });
    });
  });

  it('can send email notification when broken links found', () => {
    const query = Cypress.expose('DB_TYPE') === 'postgres' 
      ? 'UPDATE "#__users" SET "sendEmail" = 1' 
      : 'UPDATE `#__users` SET `sendEmail` = 1';
    cy.task('queryDB', query);
    cy.db_createWeblink({ title: 'automated test weblink', url: 'http://example.com/broken-link', state: 1 });
    cy.db_createSchedulerTask({
      title: 'Check Weblinks with Notification',
      type: 'check.weblinks',
      execution_rules: { 'rule-type': 'manual' },
      cron_rules: { type: 'manual', exp: '' },
      params: {
        notifications: { success_mail: 0 },
      },
    }).then((task) => {
      cy.visit('/administrator/index.php?option=com_scheduler&view=tasks&filter=');
      cy.searchForItem('Check Weblinks with Notification');
      cy.intercept('GET', '**/administrator/index.php?option=com_ajax&format=json&plugin=RunSchedulerTest&group=system&id=*').as('runschedulertest');
      cy.get('button[data-scheduler-run]').should('have.attr', 'data-id', task.id).click();
      cy.wait('@runschedulertest').then((interception) => {
        expect(interception.response.body.message).to.eq(null);
        expect(interception.response.body.success).to.eq(true);
      });
      cy.get('joomla-dialog[type="inline"]').should('be.visible');
      cy.get('joomla-dialog[type="inline"]').within(() => {
        cy.get('header.joomla-dialog-header').should('contain', `Test task (ID: ${task.id})`);
        cy.get('div.scheduler-status').should('contain', 'Status: Completed');
      });
      cy.task('getMails').then((mails) => {
        if (mails.length > 0) {
          cy.wrap(mails[0].headers.subject).should('include', 'Weblinks check results');
          cy.wrap(mails[0].headers.from).should('equal', `"${Cypress.expose('sitename')}" <${Cypress.expose('email')}>`);
        }
      });
    });
  });
});
