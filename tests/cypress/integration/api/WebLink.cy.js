describe('Test that weblink API endpoint', () => {
    ['joomla.org'].forEach((file) => {
      it(`can deliver a list of links from ${file}`, () => {
        cy.db_createWeblink({ name: 'automated test link', link: `${Cypress.config('baseUrl')}/tests/System/data/com_weblinks/${file}.xml` })
          .then(() => cy.api_get('/weblinks/links'))
          .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
            .its('name')
            .should('include', 'automated test link'));
      });
  
      it('can create a link', () => {
        cy.db_createCategory({ extension: 'com_weblinks' })
          .then((categoryId) => cy.api_post('/weblinks/links', {
            title: 'automated test link',
            alias: 'test-link',
            url: `${Cypress.config('baseUrl')}/tests/System/data/com_weblinks/${file}.xml`,
            catid: categoryId,
            description: '',
            hits:0,
            state:0,
            ordering:1,
            access:1,
            language: '*',
            published: 1,
            metadesc: '',
            metakey: '',
            images: { 
              image_first:"",
              float_first:"",
              image_first_alt:"",
              image_first_caption:"",
              image_second:"",
              float_second:"",
              image_second_alt:"",
              image_second_caption:""
            },
            metadata: {
              robots:"",
              rights:""
            },
            params: {
              target:"",
              width:"",
              height:"",
              count_clicks:""
            },
          }))
          .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
            .its('name')
            .should('include', 'automated test link'));
      });
  
      it('can update a link', () => {
        cy.db_createWeblink({ name: 'automated test contact', access: 1, link: `${Cypress.config('baseUrl')}/tests/System/data/com_weblinks/${file}.xml` })
          .then((link) => cy.api_patch(`/weblinks/links/${link.id}`, { name: 'updated automated test link' }))
          .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
            .its('name')
            .should('include', 'updated automated test link'));
      });
  
      it('can delete a link', () => {
        cy.db_createWeblink({
          name: 'automated test contact', access: 1, link: `${Cypress.config('baseUrl')}/tests/System/data/com_weblinks/${file}.xml`, published: -2,
        })
          .then((link) => cy.api_delete(`/weblinks/links/${link.id}`));
      });
    });
  });
  