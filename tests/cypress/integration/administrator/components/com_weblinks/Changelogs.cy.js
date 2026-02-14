describe('Extension Changelogs for Weblinks', () => {
    beforeEach(() => {
        cy.doAdministratorLogin();
        cy.visit('/administrator/index.php?option=com_installer&view=manage&filter=');
        cy.setFilter('core', 'Non-core Extensions');
        cy.searchForItem('Web Links');
    });

    it('should show a changelog link for all weblinks extensions', () => {
        // Check each row in the filtered result, and make sure all extensions have a changelog link
        cy.get('table#manageList > tbody > tr').each(($row) => {
            cy.wrap($row).within(() => {
                cy.get('button[data-joomla-dialog*="task=manage.loadChangelogRaw"]').should('exist');
            });
        });
    });
});
