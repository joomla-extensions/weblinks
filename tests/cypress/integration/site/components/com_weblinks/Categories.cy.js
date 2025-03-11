describe('Test in frontend that the weblink categories view', () => {
  it('can display a list of weblink categories without a menu item', () => {
    cy.db_createCategory({ title: 'automated test weblink category 1', extension: 'com_weblinks' })
      .then((id) => cy.db_createWeblink({ title: 'automated test weblink 1', catid: id }))
      .then(() => cy.db_createCategory({ title: 'automated test weblink category 2', extension: 'com_weblinks' }))
      .then(async (id) => {
        await cy.db_createCategory({ title: 'automated test weblink category 2', extension: 'com_weblinks' })
        await cy.db_createWeblink({ title: 'automated test weblink 2', catid: id });
        await cy.db_createWeblink({ title: 'automated test weblink 3', catid: id });
      })
      .then(() => {
        cy.visit('/index.php?option=com_weblinks&view=categories');

        cy.contains('automated test weblink category 1');
        cy.contains('automated test weblink category 2');
      });
  });

  it('can display a list of weblink categories in a menu item', () => {
    cy.db_createCategory({ title: 'automated test weblink category 1', extension: 'com_weblinks' })
      .then((id) => cy.db_createWeblink({ title: 'automated test weblink 1', catid: id }))
      .then(() => cy.db_createCategory({ title: 'automated test weblink category 2', extension: 'com_weblinks' }))
      .then(async (id) => {
        await cy.db_createWeblink({ title: 'automated test weblink 2', catid: id });
        await cy.db_createWeblink({ title: 'automated test weblink 3', catid: id });
      })
      .then(() => cy.db_createMenuItem({ title: 'automated test weblink categories', link: 'index.php?option=com_weblinks&view=categories' }))
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(automated test weblink categories)').click();

        cy.contains('automated test weblink category 1');
        cy.contains('automated test weblink category 2');
      });
  });
});