describe('Test in frontend that the weblinks component with custom fields', () => {
  let fieldGroupId;
  let weblink;

  const insertFieldValue = (fieldId, value) => {
    return cy.db_createFieldValue({
      field_id: fieldId,
      item_id: weblink.id,
      value: value,
    });
  };
  
  const visitWeblinkFrontend = () => {
    cy.visit(`/index.php?option=com_weblinks&view=weblink&id=${weblink.id}&catid=${weblink.catid}`);
  };

  const createFieldWithValue = (fieldOptions, value) => {
    return cy.db_createField({
      context: 'com_weblinks.weblink',
      group_id: fieldGroupId,
      ...fieldOptions
    }).then((fieldId) => insertFieldValue(fieldId, value));
  };

  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.db_createWeblink({ title: 'Test Weblink Frontend' }).then((createdWeblink) => {
      weblink = createdWeblink;
      return cy.db_createFieldGroup({
        title: 'Test Field Group Frontend',
        context: 'com_weblinks.weblink'
      });
    }).then((id) => {
      fieldGroupId = id;
    });
  });

  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__weblinks WHERE title LIKE '%Test Weblink%'");
    cy.task('queryDB', "DELETE FROM #__fields_groups WHERE context = 'com_weblinks.weblink'");
    cy.task('queryDB', "DELETE FROM #__fields WHERE context = 'com_weblinks.weblink'");
  });

  it('displays a custom text field value', () => {
    createFieldWithValue(
      { title: 'Test Text Field', name: 'test-text-field-frontend', type: 'text' },
      'My custom text value'
    ).then(() => {
      visitWeblinkFrontend();
      cy.contains('My custom text value').should('be.visible');
    });
  });

  it('does not display an unpublished field', () => {
    createFieldWithValue(
      { title: 'Unpublished Field', name: 'unpublished-field-frontend', type: 'text', state: 0 },
      'This is a secret'
    ).then(() => {
      visitWeblinkFrontend();
      cy.contains('This is a secret').should('not.exist');
    });
  });

  it('hides the field label when configured', () => {
    createFieldWithValue(
      {
        title: 'Hidden Label Field',
        label: 'Hidden Label Field',
        name: 'hidden-label-field-frontend',
        type: 'text',
        params: JSON.stringify({ showlabel: "0" })
      },
      'Value with a hidden label'
    ).then(() => {
      visitWeblinkFrontend();
      cy.contains('Hidden Label Field').should('not.exist');
      cy.contains('Value with a hidden label').should('be.visible');
    });
  });

  it('applies a custom display class to the field container', () => {
    createFieldWithValue(
      { title: 'Render Class Field', name: 'render-class-field', type: 'text',
        params: JSON.stringify({ render_class: 'my-custom-class' })
      },
      'This field has a custom class'
    ).then(() => {
      visitWeblinkFrontend();
      cy.get('.my-custom-class')
        .should('contain', 'This field has a custom class')
        .and('be.visible');
    });
  });

  it('applies a custom value class to the field value', () => {
    createFieldWithValue(
      { title: 'Render Class Field', name: 'render-class-field', type: 'text',
        params: JSON.stringify({ value_render_class: 'my-custom-class' })
      },
      'This field has a custom class'
    ).then(() => {
      visitWeblinkFrontend();
      cy.get('.my-custom-class')
        .should('contain', 'This field has a custom class')
        .and('be.visible');
    });
  });

  it('displays a prefix and suffix around the value', () => {
    createFieldWithValue(
      { title: 'Prefix Suffix Field', name: 'prefix-suffix-field', type: 'text',
        params: JSON.stringify({ prefix: 'Before...', suffix: '...After' })
      },
      'the value'
    ).then(() => {
      visitWeblinkFrontend();
      cy.contains('Before... the value ...After').should('be.visible');
    });
  });
});