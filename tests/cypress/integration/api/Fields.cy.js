describe('Test that field weblink API endpoint', () => {
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__fields'));

  ['weblink', 'categories'].forEach((context) => {
    const endpoint = context === 'weblink' ? 'weblinks' : context;
    it(`can deliver a list of fields (${context})`, () => {
      cy.db_createField({ title: `automated test field weblink ${context}`, context: `com_weblink.${context}` })
        .then(() => cy.api_get(`/fields/weblink/${endpoint}`))
        .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
          .its('title')
          .should('include', `automated test field weblink ${context}`));
    });

    it(`can deliver a single field (${context})`, () => {
      cy.db_createField({ title: `automated test field weblink ${context}`, context: `com_weblink.${context}` })
        .then((id) => cy.api_get(`/fields/weblink/${endpoint}/${id}`))
        .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', `automated test field weblink ${context}`));
    });

    it(`can create a field (${context})`, () => {
      cy.api_post(`/fields/weblink/${endpoint}`, {
        title: `automated test field weblink ${context}`,
        access: 1,
        context: `com_weblink.${context}`,
        default_value: '',
        description: '',
        group_id: 0,
        label: 'weblink field',
        language: '*',
        name: `weblink-field-${context}`,
        note: '',
        params: {
          class: '',
          display: '2',
          display_readonly: '2',
          hint: '',
          label_class: '',
          label_render_class: '',
          layout: '',
          prefix: '',
          render_class: '',
          show_on: '',
          showlabel: '1',
          suffix: '',
        },
        required: 0,
        state: 1,
        type: 'text',
      })
        .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', `automated test field weblink ${context}`));
    });

    it(`can update a field (${context})`, () => {
      cy.db_createField({ title: 'automated test field', context: `com_weblink.${context}`, access: 1 })
        .then((id) => cy.api_patch(`/fields/weblink/${endpoint}/${id}`, { title: `updated automated test field ${context}` }))
        .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', `updated automated test field ${context}`));
    });

    it(`can delete a field (${context})`, () => {
      cy.db_createField({ title: 'automated test field', context: `com_weblink.${context}`, state: -2 })
        .then((id) => cy.api_delete(`/fields/weblink/${endpoint}/${id}`));
    });
  });
});
