describe('Test that weblinks API endpoint', () => {
  afterEach(() => cy.task('queryDB', "DELETE FROM #__weblinks WHERE title LIKE '%automated test weblink%'"));

  it('can deliver a list of weblinks', () => {
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

describe('Test that weblinks API endpoint filters', () => {
  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__weblinks WHERE title LIKE '%automated test weblink%'");
    cy.task('queryDB', "DELETE FROM #__categories WHERE title LIKE '%automated test category%' AND extension = 'com_weblinks'");
  });

  it('can filter by category', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.db_createCategory({ title: 'automated test category', extension: 'com_weblinks' })
      .then((categoryId) => {
        cy.db_createWeblink({ title: 'automated test weblink', catid: categoryId });
        cy.db_createWeblink({ title: 'automated test weblink 2' });

        cy.api_get(`/weblinks?filter[category]=${categoryId}`)
          .then((response) => {
            cy.wrap(response).its('body').its('data').should('have.length', 1);
            cy.wrap(response).its('body').its('data.0').its('attributes')
              .its('title')
              .should('include', 'automated test weblink');
          });
      });
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });

  it('can filter by search term', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.db_createWeblink({ title: 'automated test weblink' });
    cy.db_createWeblink({ title: 'another weblink' });

    cy.api_get('/weblinks?filter[search]=automated')
      .then((response) => {
        cy.wrap(response).its('body').its('data').should('have.length', 1);
        cy.wrap(response).its('body').its('data.0').its('attributes')
          .its('title')
          .should('include', 'automated test weblink');
      });
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });

  it('can filter by state', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.db_createWeblink({ title: 'automated test weblink', state: 1 });
    cy.db_createWeblink({ title: 'automated test weblink 2', state: 0 });

    cy.api_get('/weblinks?filter[state]=1')
      .then((response) => {
        cy.wrap(response).its('body').its('data').should('have.length', 1);
        cy.wrap(response).its('body').its('data.0').its('attributes')
          .its('title')
          .should('include', 'automated test weblink');
      });
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });

  it('can sort the weblinks', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.db_createWeblink({ title: 'automated test weblink a' });
    cy.db_createWeblink({ title: 'automated test weblink b' });

    cy.api_get('/weblinks?list[ordering]=title&list[direction]=DESC')
      .then((response) => {
        cy.wrap(response).its('body').its('data.0').its('attributes')
          .its('title')
          .should('include', 'automated test weblink b');
      });
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });

  it('can paginate the weblinks', () => {
    cy.db_enableExtension('1', 'plg_webservices_weblinks');
    cy.db_createWeblink({ title: 'automated test weblink 1' });
    cy.db_createWeblink({ title: 'automated test weblink 2' });

    cy.api_get('/weblinks?page[limit]=1&page[offset]=1')
      .then((response) => {
        cy.wrap(response).its('body').its('data').should('have.length', 1);
        cy.wrap(response).its('body').its('data.0').its('attributes')
          .its('title')
          .should('include', 'automated test weblink 2');
      });
    cy.db_enableExtension('0', 'plg_webservices_weblinks');
  });
});
