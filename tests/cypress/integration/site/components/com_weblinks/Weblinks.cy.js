describe('Test in frontend that the weblinks category view', () => {
  it('can display a list of weblinks without a menu item', () => {
    cy.db_createWeblink({ title: 'automated test weblink 1' })
      .then((weblink) => cy.db_createWeblink({ title: 'automated test weblink 2', catid: weblink.catid }))
      .then((weblink) => cy.db_createWeblink({ title: 'automated test weblink 3', catid: weblink.catid }))
      .then((weblink) => cy.db_createWeblink({ title: 'automated test weblink 4', catid: weblink.catid }))
      .then((weblink) => {
        cy.visit(`/index.php?option=com_weblinks&view=category&id=${weblink.catid}`);

        cy.contains('automated test weblink 1');
        cy.contains('automated test weblink 2');
        cy.contains('automated test weblink 3');
        cy.contains('automated test weblink 4');
      });
  });

});
