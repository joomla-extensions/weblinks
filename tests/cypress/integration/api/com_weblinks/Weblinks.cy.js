describe('Test that weblinks API endpoint', () => {
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__weblinks WHERE title LIKE "%automated test weblink%"'));

  it('can deliver a list of weblinks', () => {
    // Update to the correct secret for the API tests because of the bearer token
    cy.config_setParameter('secret', 'tEstValue');
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.db_createWeblink({ title: 'automated test weblink' })
      .then(() => cy.api_get('/weblinks'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('title')
        .should('include', 'automated test weblink'));
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });

  it('can deliver a single weblink', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.db_createWeblink({ title: 'automated test weblink' })
      .then((weblink) => cy.api_get(`/weblinks/${weblink.id}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test weblink'));
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });

  it('can create a weblink', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.db_createCategory({ extension: 'com_weblinks' })
      .then((categoryId) => cy.api_post('/weblinks', {
        title: 'automated test weblink',
        alias: 'test-weblink',
        url: 'http://example.com',
        description: 'automated test weblink description',
        catid: categoryId,
        state: 1,
        language: '*',
      }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test weblink'));
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });

  it('can update a weblink', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.db_createWeblink({ title: 'automated test weblink' })
      .then((weblink) => cy.api_patch(`/weblinks/${weblink.id}`, { title: 'updated automated test weblink' }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'updated automated test weblink'));
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });

  it('can delete a weblink', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.db_createWeblink({ title: 'automated test weblink', state: -2 })
      .then((weblink) => cy.api_delete(`/weblinks/${weblink.id}`))
      .then((response) => cy.wrap(response).its('status').should('eq', 204));
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });
});
