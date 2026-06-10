describe('Test weblinks task plugin', () => {
  beforeEach(() => {
    cy.task('clearEmails');
    cy.db_enableExtension('1', 'plg_task_weblinks');
    cy.doAdministratorLogin();
  });
  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__weblinks WHERE title LIKE '%automated test weblink%'");
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
    cy.db_createWeblink({ title: 'automated test weblink', url: 'http://example.com/broken-link', state: 1 });
    cy.db_createSchedulerTask({
      title: 'Check Weblinks with Notification',
      type: 'check.weblinks',
      execution_rules: { 'rule-type': 'manual' },
      cron_rules: { type: 'manual', exp: '' },
      params: {
        notifications: { success_mail: 1 },
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
        // Ensure we actually got an array and it's not empty
        expect(mails).to.be.an('array').and.not.be.empty;

        const latestMail = mails[0];

        // Assert against the properties directly using standard Chai
        expect(latestMail.headers.subject).to.include('Weblinks check results');
  
        const expectedFrom = `"${Cypress.expose('sitename')}" <${Cypress.expose('email')}>`;
        expect(latestMail.headers.from).to.equal(expectedFrom);
      });
    });
  });
});
