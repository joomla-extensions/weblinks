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

  it('should render content plugins in weblink description when prepare_content is enabled', () => {
    cy.db_updateExtensionParameter('prepare_content', '1', 'com_weblinks');
    cy.db_createCategory({ title: 'automated test weblink category 1', extension: 'com_weblinks', rgt: 2 })
      .then((id) => cy.db_createWeblink({ title: 'automated test weblink 1', catid: id, description: '<p>You can contact us at myemail@example.com.</p>' }))
      .then((weblink) => cy.db_createMenuItem({
        title: 'automated test weblink categories',
        link: `index.php?option=com_weblinks&view=category&id=${weblink.catid}`,
        params: JSON.stringify({
          show_link_description: '1',
        }),
      }))
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(automated test weblink categories)').click();
        cy.get('joomla-hidden-mail').should('exist');
      });
  });

  it('should not render content plugins in weblink description when prepare_content is disabled', () => {
    cy.db_updateExtensionParameter('prepare_content', '0', 'com_weblinks');
    cy.db_createCategory({ title: 'automated test weblink category for plugin test', extension: 'com_weblinks', rgt: 2 })
      .then((id) => cy.db_createWeblink({ title: 'automated test weblink for plugin test', catid: id, description: '<p>You can contact us at myemail@example.com.</p>' }))
      .then((weblink) => cy.db_createMenuItem({
        title: 'automated test weblink categories',
        link: `index.php?option=com_weblinks&view=category&id=${weblink.catid}`,
        params: JSON.stringify({
          show_link_description: '1',
        }),
      }))
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(automated test weblink categories)').click();
        cy.get('joomla-hidden-mail').should('not.exist');
      });
  });
});