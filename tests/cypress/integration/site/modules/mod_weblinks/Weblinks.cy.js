describe('Test in frontend that the weblinks module', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.task('queryDB', "DELETE FROM #__weblinks WHERE title like '%test weblink%'");
    cy.task('queryDB', "DELETE FROM #__categories WHERE title like '%test category%'");
    cy.task('queryDB', "DELETE FROM #__modules WHERE module = 'mod_weblinks'");
  });

  it('can display a list of weblinks', () => {
    cy.db_createCategory({ title: 'test category', extension: 'com_weblinks' }).then((id) => {
      cy.db_createWeblink({ title: 'test weblink', catid: id });
      const params = {
        catid: id,
        groupby: '0',
      };
      cy.db_createModule({ module: 'mod_weblinks', params: JSON.stringify(params) }).then(() => {
        cy.visit('/');
        cy.get('body').should('contain', 'test module');
        cy.get('.card-body').find('ul.weblinks li').should('contain.text', 'test weblink');
      });
    });
  });

  it('can display a weblink with description', () => {
    cy.db_createCategory({ title: 'test category', extension: 'com_weblinks' }).then((id) => {
      cy.db_createWeblink({ title: 'test weblink', catid: id, description: 'test description' });
      const params = {
        catid: id,
        description: '1',
      };
      cy.db_createModule({ module: 'mod_weblinks', params: JSON.stringify(params) }).then(() => {
        cy.visit('/');
        cy.get('body').should('contain', 'test module');
        cy.get('.card-body').find('ul.weblinks li').should('contain.text', 'test description');
      });
    });
  });

  it('can display a weblink with hits', () => {
    cy.db_createCategory({ title: 'test category', extension: 'com_weblinks' }).then((id) => {
      cy.db_createWeblink({ title: 'test weblink', catid: id, hits: '123' });
      const params = {
        catid: id,
        hits: '1',
      };
      cy.db_createModule({ module: 'mod_weblinks', params: JSON.stringify(params) }).then(() => {
        cy.visit('/');
        cy.get('body').should('contain', 'test module');
        cy.get('.card-body').find('ul.weblinks li').should('contain.text', '123');
      });
    });
  });

  it('can order the weblinks by title asc', () => {
    cy.db_createCategory({ title: 'test category', extension: 'com_weblinks' }).then((id) => {
      cy.db_createWeblink({ title: 'c test weblink', catid: id });
      cy.db_createWeblink({ title: 'a test weblink', catid: id });
      cy.db_createWeblink({ title: 'b test weblink', catid: id });
      const params = {
        catid: id,
        ordering: 'title',
        direction: 'asc',
      };
      cy.db_createModule({ module: 'mod_weblinks', params: JSON.stringify(params) }).then(() => {
        cy.visit('/');
        cy.get('body').should('contain', 'test module');
        const expectedOrder = ['a test weblink', 'b test weblink', 'c test weblink'];
        cy.get('.card-body ul.weblinks li').each(($li, index) => {
          cy.wrap($li).should('contain.text', expectedOrder[index]);
        });
      });
    });
  });

  it('can order the weblinks by title desc', () => {
    cy.db_createCategory({ title: 'test category', extension: 'com_weblinks' }).then((id) => {
      cy.db_createWeblink({ title: 'c test weblink', catid: id });
      cy.db_createWeblink({ title: 'a test weblink', catid: id });
      cy.db_createWeblink({ title: 'b test weblink', catid: id });
      const params = {
        catid: id,
        ordering: 'title',
        direction: 'desc',
      };
      cy.db_createModule({ module: 'mod_weblinks', params: JSON.stringify(params) }).then(() => {
        cy.visit('/');
        cy.get('body').should('contain', 'test module');
        const expectedOrder = ['c test weblink', 'b test weblink', 'a test weblink'];
        cy.get('.card-body ul.weblinks li').each(($li, index) => {
          cy.wrap($li).should('contain.text', expectedOrder[index]);
        });
      });
    });
  });

  it('can order the weblinks by hits asc', () => {
    cy.db_createCategory({ title: 'test category', extension: 'com_weblinks' }).then((id) => {
      cy.db_createWeblink({ title: 'a test weblink', catid: id, hits: '1' });
      cy.db_createWeblink({ title: 'c test weblink', catid: id, hits: '1000' });
      cy.db_createWeblink({ title: 'b test weblink', catid: id, hits: '100' });
      const params = {
        catid: id,
        ordering: 'hits',
        direction: 'asc',
      };
      cy.db_createModule({ module: 'mod_weblinks', params: JSON.stringify(params) }).then(() => {
        cy.visit('/');
        cy.get('body').should('contain', 'test module');
        const expectedOrder = ['a test weblink', 'b test weblink', 'c test weblink'];
        cy.get('.card-body ul.weblinks li').each(($li, index) => {
          cy.wrap($li).should('contain.text', expectedOrder[index]);
        });
      });
    });
  });

  it('can order the weblinks by hits desc', () => {
    cy.db_createCategory({ title: 'test category', extension: 'com_weblinks' }).then((id) => {
      cy.db_createWeblink({ title: 'a test weblink', catid: id, hits: '1' });
      cy.db_createWeblink({ title: 'c test weblink', catid: id, hits: '1000' });
      cy.db_createWeblink({ title: 'b test weblink', catid: id, hits: '100' });
      const params = {
        catid: id,
        ordering: 'hits',
        direction: 'desc',
      };
      cy.db_createModule({ module: 'mod_weblinks', params: JSON.stringify(params) }).then(() => {
        cy.visit('/');
        cy.get('body').should('contain', 'test module');
        const expectedOrder = ['c test weblink', 'b test weblink', 'a test weblink'];
        cy.get('.card-body ul.weblinks li').each(($li, index) => {
          cy.wrap($li).should('contain.text', expectedOrder[index]);
        });
      });
    });
  });

  it('can limit the number of weblinks', () => {
    cy.db_createCategory({ title: 'test category', extension: 'com_weblinks' }).then((id) => {
      cy.db_createWeblink({ title: 'a test weblink', catid: id });
      cy.db_createWeblink({ title: 'b test weblink', catid: id });
      const params = {
        catid: id,
        count: '1',
      };
      cy.db_createModule({ module: 'mod_weblinks', params: JSON.stringify(params) }).then(() => {
        cy.visit('/');
        cy.get('body').should('contain', 'test module');
        cy.get('.card-body').find('ul.weblinks li').should('have.length', 1);
      });
    });
  });
  
});
