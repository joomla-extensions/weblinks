describe('Test that group field weblink API endpoint', () => {
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__fields_groups'));

  ['weblink', 'categories'].forEach((context) => {
    const endpoint = context === 'weblink' ? 'weblinks' : context;
    it(`can deliver a list of group fields (${context})`, () => {
      cy.db_createFieldGroup({ title: `automated test group field weblink ${context}`, context: `com_weblink.${context}` })
        .then(() => cy.api_get(`/fields/groups/weblink/${endpoint}`))
        .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
          .its('title')
          .should('include', `automated test group field weblink ${context}`));
    });

    it(`can deliver a single group field (${context})`, () => {
      cy.db_createFieldGroup({ title: `automated test group field weblink ${context}`, context: `com_weblink.${context}` })
        .then((id) => cy.api_get(`/fields/groups/weblink/${endpoint}/${id}`))
        .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', `automated test group field weblink ${context}`));
    });

    it(`can create a group field (${context})`, () => {
      cy.api_post(`/fields/groups/weblink/${endpoint}`, {
        title: `automated test group field weblink ${context}`,
        access: 1,
        context: `com_weblink.${context}`,
        default_value: '',
        description: '',
        group_id: 0,
        label: 'weblink group field',
        language: '*',
        name: 'weblink-group_field',
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
          .should('include', `automated test group field weblink ${context}`));
    });

    it(`can update a group field (${context})`, () => {
      cy.db_createFieldGroup({ title: 'automated test group field', access: 1, context: `com_weblink.${context}` })
        .then((id) => cy.api_patch(`/fields/groups/weblink/${endpoint}/${id}`, { title: `updated automated test group field ${context}` }))
        .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', `updated automated test group field ${context}`));
    });

    it(`can delete a group field (${context})`, () => {
      cy.db_createFieldGroup({ title: 'automated test group field', context: `com_weblink.${context}`, state: -2 })
        .then((id) => cy.api_delete(`/fields/groups/weblink/${endpoint}/${id}`));
    });
  });
});
