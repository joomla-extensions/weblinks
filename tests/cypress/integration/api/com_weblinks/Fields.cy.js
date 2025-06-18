describe('Test that weblinks fields API endpoint', () => {
  afterEach(() => cy.task('queryDB', "DELETE FROM #__fields WHERE title LIKE '%automated test field%' AND context = 'com_weblinks.weblink'"));

  it('can deliver a list of weblink fields', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.db_createField({ title: 'automated test field', context: 'com_weblinks.weblink' })
      .then(() => cy.api_get('/fields/weblinks'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('title')
        .should('include', 'automated test field'));
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });

  it('can deliver a single weblink field', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.db_createField({ title: 'automated test field', context: 'com_weblinks.weblink' })
      .then((fieldId) => cy.api_get(`/fields/weblinks/${fieldId}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test field'));
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });

  it('can create a weblink field', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.api_post('/fields/weblinks', {
      title: 'automated test field',
      name: 'automated-test-field',
      type: 'text',
      context: 'com_weblinks.weblink',
      description: 'automated test field description',
      params: {
        showlabel: '1'
      },
      state: 1,
      access: 1,
      language: '*',
    })
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test field'));
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });

  it('can update a weblink field', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.db_createField({ title: 'automated test field', context: 'com_weblinks.weblink' })
      .then((fieldId) => cy.api_patch(`/fields/weblinks/${fieldId}`, { title: 'updated automated test field' }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'updated automated test field'));
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });

  it('can delete a weblink field', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.db_createField({ title: 'automated test field', context: 'com_weblinks.weblink', state: -2 })
      .then((fieldId) => cy.api_delete(`/fields/weblinks/${fieldId}`))
      .then((response) => cy.wrap(response).its('status').should('eq', 204));
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });
});
