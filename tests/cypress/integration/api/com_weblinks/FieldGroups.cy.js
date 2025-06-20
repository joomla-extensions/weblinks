describe('Test that weblinks field groups API endpoint', () => {
  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__fields_groups WHERE title LIKE '%automated test field group%' AND context = 'com_weblinks.weblink'");
    cy.task('queryDB', "DELETE FROM #__fields WHERE title LIKE '%automated test field%' AND context = 'com_weblinks.weblink'");
  });

  it('can deliver a list of weblink field groups', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.db_createFieldGroup({ title: 'automated test field group', context: 'com_weblinks.weblink' })
      .then(() => cy.api_get('/fields/groups/weblinks'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('title')
        .should('include', 'automated test field group'));
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });

  it('can deliver a single weblink field group', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.db_createFieldGroup({ title: 'automated test field group', context: 'com_weblinks.weblink' })
      .then((fieldGroupId) => cy.api_get(`/fields/groups/weblinks/${fieldGroupId}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test field group'));
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });

  it('can create a weblink field group', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.api_post('/fields/groups/weblinks', {
      title: 'automated test field group',
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
        .should('include', 'automated test field group'));
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });

  it('can update a weblink field group', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.db_createFieldGroup({ title: 'automated test field group', context: 'com_weblinks.weblink' })
      .then((fieldGroupId) => cy.api_patch(`/fields/groups/weblinks/${fieldGroupId}`, { title: 'updated automated test field group' }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'updated automated test field group'));
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });

  it('can delete a weblink field group', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.db_createFieldGroup({ title: 'automated test field group', context: 'com_weblinks.weblink', state: -2 })
      .then((fieldGroupId) => cy.api_delete(`/fields/groups/weblinks/${fieldGroupId}`))
      .then((response) => cy.wrap(response).its('status').should('eq', 204));
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });

  it('can filter and paginate the field groups', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.db_createFieldGroup({ title: 'automated test field group 1', context: 'com_weblinks.weblink' });
    cy.db_createFieldGroup({ title: 'automated test field group 2', context: 'com_weblinks.weblink' });

    cy.api_get('/fields/groups/weblinks?page[limit]=1&page[offset]=1')
      .then((response) => {
        cy.wrap(response).its('body').its('data').should('have.length', 1);
        cy.wrap(response).its('body').its('data.0').its('attributes')
          .its('title')
          .should('include', 'automated test field group 2');
      });
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });
});
