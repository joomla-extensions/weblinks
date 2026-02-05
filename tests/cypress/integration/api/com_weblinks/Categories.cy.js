describe('Test that weblinks categories API endpoint', () => {
  afterEach(() => cy.task('queryDB', "DELETE FROM #__categories WHERE title LIKE '%automated test category%' AND extension = 'com_weblinks'"));

  it('can deliver a list of weblink categories', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.db_createCategory({ title: 'automated test category', extension: 'com_weblinks' })
      .then(() => cy.api_get('/weblinks/categories'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('title')
        .should('include', 'automated test category'));
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });

  it('can deliver a single weblink category', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.db_createCategory({ title: 'automated test category', extension: 'com_weblinks' })
      .then((categoryId) => cy.api_get(`/weblinks/categories/${categoryId}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test category'));
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });

  it('can create a weblink category', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.api_post('/weblinks/categories', {
      title: 'automated test category',
      alias: 'automated-test-category',
      extension: 'com_weblinks',
      published: 1,
      access: 1,
      language: '*',
      parent_id: 1,
    })
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test category'));
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });

  it('can update a weblink category', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.db_createCategory({ title: 'automated test category', extension: 'com_weblinks' })
      .then((categoryId) => cy.api_patch(`/weblinks/categories/${categoryId}`, { title: 'updated automated test category' }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'updated automated test category'));
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });

  it('can delete a weblink category', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.api_post('/weblinks/categories', {
      title: 'automated test category',
      alias: 'automated-test-category',
      extension: 'com_weblinks',
      published: -2,
      access: 1,
      language: '*',
      parent_id: 1,
    })
      .then((response) => response.body.data.id)
      .then((categoryId) => cy.api_delete(`/weblinks/categories/${categoryId}`))
      .then((response) => {
        expect(response.status).to.eq(204);
      });
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });
});

describe('Test that weblinks categories API endpoint filters', () => {
  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__categories WHERE title LIKE '%automated test category%' AND extension = 'com_weblinks'");
  });

  it('can filter by search term', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.db_createCategory({ title: 'automated test category', extension: 'com_weblinks' });
    cy.db_createCategory({ title: 'another category', extension: 'com_weblinks' });

    cy.api_get('/weblinks/categories?filter[search]=automated')
      .then((response) => {
        cy.wrap(response).its('body').its('data').should('have.length', 1);
        cy.wrap(response).its('body').its('data.0').its('attributes')
          .its('title')
          .should('include', 'automated test category');
      });
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });

  it('can paginate the categories', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.db_createCategory({ title: 'automated test category 1', extension: 'com_weblinks' });
    cy.db_createCategory({ title: 'automated test category 2', extension: 'com_weblinks' });

    cy.api_get('/weblinks/categories?page[limit]=1&page[offset]=1')
      .then((response) => {
        cy.wrap(response).its('body').its('data').should('have.length', 1);
        cy.wrap(response).its('body').its('data.0').its('attributes')
          .its('title')
          .should('include', 'automated test category 2');
      });
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });
});
