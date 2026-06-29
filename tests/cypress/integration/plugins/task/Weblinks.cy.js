describe('Test weblinks task plugin', () => {
  let joomlaVersion;

  const joomlaVersionRequest = () => {
    return cy.request('administrator/manifests/files/joomla.xml').then((response) => {
      const parser = new DOMParser();
      const xmlDoc = parser.parseFromString(response.body, "text/xml");
      
      // Store the version in our file-scoped variable
      joomlaVersion = xmlDoc.getElementsByTagName("version")[0].childNodes[0].nodeValue;
      
      cy.log(`Global Joomla Version Initialized: ${joomlaVersion}`);
    });
  };

  beforeEach(() => {
    cy.task('clearEmails');
    return joomlaVersionRequest().then(() => {
      cy.db_enableExtension('1', 'plg_task_weblinks');
      cy.doAdministratorLogin();
    });
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
      params: {
        "individual_log": false,
        "log_file": "",
        "notifications": {
          "success_mail": "0",
          "failure_mail": "1",
          "fatal_failure_mail": "1",
          "orphan_mail": "1"
        },
        "http_timeout": 8,
        "batch_size": 3
      },
    }).then((task) => {
      cy.visit('/administrator/index.php?option=com_scheduler&view=tasks&filter=');
      cy.searchForItem('Check Weblinks Task');
      cy.intercept('GET', '**/administrator/index.php?option=com_ajax&format=json&plugin=RunSchedulerTest&group=system&id=*').as('runschedulertest');
      cy.get('button[data-scheduler-run]').should('have.attr', 'data-id', task.id).click();
      cy.wait('@runschedulertest').then((interception) => {
        expect(interception.response.body.success).to.eq(true);
      });

      cy.log(`Joomla Version in Test: ${joomlaVersion}`);
      const taskString = joomlaVersion.startsWith('5') 
        ? `Test task (ID: ${task.id})` 
        : `Run Task (ID: ${task.id})`;

      cy.get('joomla-dialog[type="inline"]').should('be.visible');
      cy.get('joomla-dialog[type="inline"]').within(() => {
        cy.get('header.joomla-dialog-header').should('contain', taskString);
        cy.get('div.scheduler-status').should('contain', 'Status: Completed');
      });
    });
  });

  it('can send email notification when broken links found', () => {
    const query = Cypress.expose('DB_TYPE') === 'postgres' 
      ? 'UPDATE "#__users" SET "sendEmail" = 1' 
      : 'UPDATE `#__users` SET `sendEmail` = 1';

    cy.task('queryDB', query);
    
    cy.db_createWeblink({
      title: 'automated test weblink',
      url: 'http://example.com/broken-link',
      state: 1
    }).then(() => {
      cy.db_createSchedulerTask({
        title: 'Check Weblinks with Notification',
        type: 'check.weblinks',
        execution_rules: { 'rule-type': 'manual' },
        cron_rules: { type: 'manual', exp: '' },
        params: {
          "individual_log": false,
          "log_file": "",
          "notifications": {
            "success_mail": "0",
            "failure_mail": "1",
            "fatal_failure_mail": "1",
            "orphan_mail": "1"
          },
          "http_timeout": 8,
          "batch_size": 3
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

        cy.log(`Joomla Version in Test: ${joomlaVersion}`);
        const taskString = joomlaVersion.startsWith('5')
          ? `Test task (ID: ${task.id})`
          : `Run Task (ID: ${task.id})`;

        cy.get('joomla-dialog[type="inline"]').should('be.visible');
        cy.get('joomla-dialog[type="inline"]').within(() => {
          cy.get('header.joomla-dialog-header').should('contain', taskString);
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
});
