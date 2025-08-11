describe('Test in backend that the System - Weblinks plugin', () => {
    beforeEach(() => {
        cy.doAdministratorLogin();
        cy.task('queryDB', "DELETE FROM #__weblinks WHERE title LIKE '%Test weblink%'");
        cy.db_createModule({
            title: 'Test Weblinks Statisitcs Module',
            module: 'mod_stats_admin',
            position: 'cpanel',
            published: 1,
            client_id: 1
        });
    });

    it('should display the weblinks count in the statistics module when the plugin is enabled', () => {
        cy.db_enableExtension('1', 'plg_system_weblinks');
        cy.db_createWeblink({ title: 'Test weblink 1' });
        cy.db_createWeblink({ title: 'Test weblink 2' });

        cy.visit('/administrator/index.php');

        cy.contains('li.list-group-item', 'Web Links').should('contain.text', '2');
    });

    it('should not display the weblinks count in the statistics module when the plugin is disabled', () => {
        cy.db_enableExtension('0', 'plg_system_weblinks');
        cy.db_createWeblink({ title: 'Test weblink 1' });

        cy.visit('/administrator/index.php');

        cy.contains('li.list-group-item', 'Web Links').should('not.exist');
    });

    it('should not display the weblinks count when there are no weblinks', () => {
        cy.db_enableExtension('1', 'plg_system_weblinks');

        cy.visit('/administrator/index.php');

        cy.contains('li.list-group-item', 'Web Links').should('not.exist');
    });

    it('should not count unpublished weblinks', () => {
        cy.db_enableExtension('1', 'plg_system_weblinks');
        cy.db_createWeblink({ title: 'Test weblink 1', state: 1 });
        cy.db_createWeblink({ title: 'Test weblink 2', state: 0 });

        cy.visit('/administrator/index.php');

        cy.contains('li.list-group-item', 'Web Links').should('contain.text', '1');
    });
});

describe('Test in frontend that the System - Weblinks plugin', () => {
    beforeEach(() => {
        cy.doAdministratorLogin();
        cy.task('queryDB', "DELETE FROM #__weblinks WHERE title LIKE '%Test weblink%'");
        cy.db_createModule({
            title: 'Test Weblinks Statisitcs Module',
            module: 'mod_stats',
            position: 'sidebar-right',
            published: 1
        });
    });

    it('should display the weblinks count in the statistics module when the plugin is enabled', () => {
        cy.db_enableExtension('1', 'plg_system_weblinks');
        cy.db_createWeblink({ title: 'Test weblink 1' });
        cy.db_createWeblink({ title: 'Test weblink 2' });

        cy.visit('/');

        cy.contains('li.list-group-item', 'Web Links').find('span.badge').should('contain.text', '2');
    });

    it('should not display the weblinks count in the statistics module when the plugin is disabled', () => {
        cy.db_enableExtension('0', 'plg_system_weblinks');
        cy.db_createWeblink({ title: 'Test weblink 1' });

        cy.visit('/');

        cy.contains('li.list-group-item', 'Web Links').should('not.exist');
    });

    it('should not display the weblinks count when there are no weblinks', () => {
        cy.db_enableExtension('1', 'plg_system_weblinks');

        cy.visit('/');

        cy.contains('li.list-group-item', 'Web Links').should('not.exist');
    });

    it('should not count unpublished weblinks', () => {
        cy.db_enableExtension('1', 'plg_system_weblinks');
        cy.db_createWeblink({ title: 'Test weblink 1', state: 1 });
        cy.db_createWeblink({ title: 'Test weblink 2', state: 0 });

        cy.visit('/');

        cy.contains('li.list-group-item', 'Web Links').find('span.badge').should('contain.text', '1');
    });
});
