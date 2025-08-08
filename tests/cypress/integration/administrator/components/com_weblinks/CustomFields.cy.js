describe('Test in backend that the weblinks component with custom fields', () => {
  let fieldGroupId;

  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.db_createFieldGroup({ title: 'Test Field Group', context: 'com_weblinks.weblink' }).then((id) => {
      fieldGroupId = id;
    });
  });

  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__weblinks WHERE title LIKE '%Test weblink%'");
    cy.task('queryDB', "DELETE FROM #__fields_groups WHERE context = 'com_weblinks.weblink'");
    cy.task('queryDB', "DELETE FROM #__fields WHERE context = 'com_weblinks.weblink'");
  });

  it('can save a weblink with a custom text field', () => {
    cy.db_createField({
      title: 'Test Text Field', name: 'test-text-field', type: 'text',
      context: 'com_weblinks.weblink', group_id: fieldGroupId,
    });

    cy.visit('/administrator/index.php?option=com_weblinks&task=weblink.add');
    cy.get('#jform_title').clear().type('Test weblink');
    cy.get('#jform_url').clear().type('www.example.com');
    cy.contains('#myTab button', 'Test Field Group').click();
    cy.get('input[name="jform[com_fields][test-text-field]"]').clear().type('My custom text');
    cy.clickToolbarButton('Save & Close');

    cy.checkForSystemMessage('Web link saved');
  });

  it('enforces required custom fields', () => {
    cy.db_createField({
      title: 'Test Required Field', name: 'test-required-field', type: 'text',
      context: 'com_weblinks.weblink', group_id: fieldGroupId, required: 1,
    });

    cy.visit('/administrator/index.php?option=com_weblinks&task=weblink.add');
    cy.get('#jform_title').clear().type('Another Test weblink');
    cy.get('#jform_url').clear().type('www.example.com');
    cy.contains('#myTab button', 'Test Field Group').click();
    cy.clickToolbarButton('Save');

    cy.get('.form-control-feedback').should('contain', 'Please fill in this field');
  });

  it('populates default values for custom fields', () => {
    cy.db_createField({
      title: 'Test Default Value Field', name: 'test-default-value-field', type: 'text',
      context: 'com_weblinks.weblink', group_id: fieldGroupId, default_value: 'Default Text',
    });

    cy.visit('/administrator/index.php?option=com_weblinks&task=weblink.add');
    cy.contains('#myTab button', 'Test Field Group').click();
    cy.get('input[name="jform[com_fields][test-default-value-field]"]').should('have.value', 'Default Text');
  });

  it('displays the field description as a tooltip', () => {
    cy.db_createField({
      title: 'Test Description Field', name: 'test-description-field', type: 'text',
      context: 'com_weblinks.weblink', group_id: fieldGroupId, description: 'This is the field description.',
    });

    cy.visit('/administrator/index.php?option=com_weblinks&task=weblink.add');
    cy.contains('#myTab button', 'Test Field Group').click();
    cy.get('.form-text').should('contain', 'This is the field description.');
  });

  it('does not display an unpublished field in the form', () => {
    cy.db_createField({
      title: 'Test Unpublished Field', name: 'test-unpublished-field', type: 'text',
      context: 'com_weblinks.weblink', group_id: fieldGroupId, state: 0, // Unpublished
    });

    cy.visit('/administrator/index.php?option=com_weblinks&task=weblink.add');
    cy.contains('#myTab button', 'Test Field Group').should('not.exist');
  });
});
