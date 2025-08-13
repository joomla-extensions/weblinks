describe('Test in frontend that the weblinks module', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.task('queryDB', "DELETE FROM #__weblinks WHERE title like '%test weblink%'");
    cy.task('queryDB', "DELETE FROM #__categories WHERE title like '%test category%'");
    cy.task('queryDB', "DELETE FROM #__modules WHERE module = 'mod_weblinks'");
  });

  it('can display a hierarchical list of categories', () => {
    // 1. Create category structure
    cy.db_createCategory({ title: 'test category 1', extension: 'com_weblinks', parent_id: 1, path: 'test-category-1' }).then((id1) => {
      cy.db_createCategory({ title: 'test category 2', extension: 'com_weblinks', parent_id: id1, path: 'test-category-1/test-category-2' }).then((id2) => {
        cy.db_createCategory({ title: 'test category 3', extension: 'com_weblinks', parent_id: id2, path: 'test-category-1/test-category-2/test-category-3' }).then((id3) => {
          // 2. Create weblinks
          cy.db_createWeblink({ title: 'test weblink 1', catid: id1 });
          cy.db_createWeblink({ title: 'test weblink 2', catid: id2 });
          cy.db_createWeblink({ title: 'test weblink 3', catid: id3 });

          // Rebuild categories      
          cy.visit('/administrator/index.php?option=com_categories&view=categories&extension=com_weblinks');
          cy.clickToolbarButton('Rebuild');
          cy.checkForSystemMessage('Categories tree data rebuilt.');

          // 3. Create module
          const params = {
            catid: id1,
            maxLevel: '3',
            groupby: '1',
            show_parent_category: '1'
          };
          cy.db_createModule({ module: 'mod_weblinks', params: JSON.stringify(params) }).then(() => {
            // 4. Visit page and test
            cy.visit('/', { failOnStatusCode: false });
            cy.get('body').should('contain', 'test module');

            // Check for weblinks in correct order and structure
            cy.get('.card-body').find('.weblinks-category').should('have.length', 3);

            // Check first category
            cy.get('.card-body').find('.weblinks-category').first().as('cat1');
            cy.get('@cat1').find('strong').should('contain.text', 'test category 1');
            cy.get('@cat1').find('ul.weblinks li').should('contain.text', 'test weblink 1');

            // Check second category
            cy.get('@cat1').find('.weblinks-category').as('cat2');
            cy.get('@cat2').should('have.class', 'ps-4');
            cy.get('@cat2').find('strong').should('contain.text', 'test category 2');
            cy.get('@cat2').find('ul.weblinks li').should('contain.text', 'test weblink 2');

            // Check third category
            cy.get('@cat2').find('.weblinks-category').as('cat3');
            cy.get('@cat3').should('have.class', 'ps-4');
            cy.get('@cat3').find('strong').should('contain.text', 'test category 3');
            cy.get('@cat3').find('ul.weblinks li').should('contain.text', 'test weblink 3');
          });
        });
      });
    });
  });

  it('can display a hierarchical list of categories with maxLevel', () => {
    // 1. Create category structure
    let catId1;
    let catId2;
    let catId3;

    // 1. Create category structure
    cy.db_createCategory({ title: 'test category 1', alias: 'test-category-1', extension: 'com_weblinks', parent_id: 1, path: 'test-category-1' }).then((id) => {
      catId1 = id;
      cy.db_createCategory({ title: 'test category 2', alias: 'test-category-2', extension: 'com_weblinks', parent_id: id, path: 'test-category-1/test-category-2' }).then((id) => {
        catId2 = id;
        cy.db_createCategory({ title: 'test category 3', alias: 'test-category-3', extension: 'com_weblinks', parent_id: id, path: 'test-category-1/test-category-2/test-category-3' }).then((id) => {
          catId3 = id;

          // 2. Create weblinks
          cy.db_createWeblink({ title: 'test weblink 1', catid: catId1 });
          cy.db_createWeblink({ title: 'test weblink 2', catid: catId2 });
          cy.db_createWeblink({ title: 'test weblink 3', catid: catId3 });

          // Rebuild categories      
          cy.visit('/administrator/index.php?option=com_categories&view=categories&extension=com_weblinks');
          cy.clickToolbarButton('Rebuild');
          cy.checkForSystemMessage('Categories tree data rebuilt.');

          // 3. Create module
          const params = {
            catid: catId1,
            maxLevel: '1',
            groupby: '1',
            show_parent_category: '1'
          };

          cy.db_createModule({ module: 'mod_weblinks', params: JSON.stringify(params) }).then(() => {
            // 4. Visit page and test
            cy.visit('/', { failOnStatusCode: false });
            cy.get('body').should('contain', 'test module');

            // Check for weblinks in correct order and structure
            cy.get('.card-body').find('.weblinks-category').should('have.length', 2);

            // Check first category
            cy.get('.card-body').find('.weblinks-category').first().as('cat1');
            cy.get('@cat1').find('strong').should('contain.text', 'test category 1');
            cy.get('@cat1').find('ul.weblinks li').should('contain.text', 'test weblink 1');

            // Check second category
            cy.get('@cat1').find('.weblinks-category').as('cat2');
            cy.get('@cat2').should('have.class', 'ps-4');
            cy.get('@cat2').find('strong').should('contain.text', 'test category 2');
            cy.get('@cat2').find('ul.weblinks li').should('contain.text', 'test weblink 2');

            // Check that third category is not present
            cy.get('body').should('not.contain.text', 'test category 3');
            cy.get('body').should('not.contain.text', 'test weblink 3');
          });
        });
      });
    });
  });

  it('can display a hierarchical list of categories with show parent category off', () => {
    // 1. Create category structure
    let catId1;
    let catId2;
    let catId3;

    // 1. Create category structure
    cy.db_createCategory({ title: 'test category 1', alias: 'test-category-1', extension: 'com_weblinks', parent_id: 1, path: 'test-category-1' }).then((id) => {
      catId1 = id;
      cy.db_createCategory({ title: 'test category 2', alias: 'test-category-2', extension: 'com_weblinks', parent_id: id, path: 'test-category-1/test-category-2' }).then((id) => {
        catId2 = id;
        cy.db_createCategory({ title: 'test category 3', alias: 'test-category-3', extension: 'com_weblinks', parent_id: id, path: 'test-category-1/test-category-2/test-category-3' }).then((id) => {
          catId3 = id;

          // 2. Create weblinks
          cy.db_createWeblink({ title: 'test weblink 1', catid: catId1 });
          cy.db_createWeblink({ title: 'test weblink 2', catid: catId2 });
          cy.db_createWeblink({ title: 'test weblink 3', catid: catId3 });

          // Rebuild categories      
          cy.visit('/administrator/index.php?option=com_categories&view=categories&extension=com_weblinks');
          cy.clickToolbarButton('Rebuild');
          cy.checkForSystemMessage('Categories tree data rebuilt.');

          // 3. Create module
          const params = {
            catid: catId1,
            maxLevel: '2',
            groupby: '1',
            show_parent_category: '0'
          };

          cy.db_createModule({ module: 'mod_weblinks', params: JSON.stringify(params) }).then(() => {
            // 4. Visit page and test
            cy.visit('/', { failOnStatusCode: false });
            cy.get('body').should('contain', 'test module');

            // Check for weblinks in correct order and structure
            cy.get('.card-body').find('.weblinks-category').should('have.length', 2);

            // Check second category
            cy.get('.card-body').find('.weblinks-category').first().as('cat2');
            cy.get('@cat2').find('strong').should('contain.text', 'test category 2');
            cy.get('@cat2').find('ul.weblinks li').should('contain.text', 'test weblink 2');

            // Check third category
            cy.get('@cat2').find('.weblinks-category').as('cat3');
            cy.get('@cat3').should('have.class', 'ps-4');
            cy.get('@cat3').find('strong').should('contain.text', 'test category 3');
            cy.get('@cat3').find('ul.weblinks li').should('contain.text', 'test weblink 3');

            // Check that first category is not present
            cy.get('body').should('not.contain.text', 'test category 1');
            cy.get('body').should('not.contain.text', 'test weblink 1');
          });
        });
      });
    });
  });

  it('can display a hierarchical list of multiple categories with show parent category on', () => {
    // 1. Create category structure
    cy.db_createCategory({ title: 'test category 1', extension: 'com_weblinks', parent_id: 1, path: 'test-category-1' }).then((id1) => {
      cy.db_createCategory({ title: 'test category 2', extension: 'com_weblinks', parent_id: id1, path: 'test-category-1/test-category-2' }).then((id2) => {
          cy.db_createCategory({ title: 'test category 3', extension: 'com_weblinks', parent_id: 1, path: 'test-category-3' }).then((id3) => {

            // 2. Create weblinks
            cy.db_createWeblink({ title: 'test weblink 1', catid: id1 });
            cy.db_createWeblink({ title: 'test weblink 2', catid: id2 });
            cy.db_createWeblink({ title: 'test weblink 3', catid: id3 });

            // Rebuild categories      
            cy.visit('/administrator/index.php?option=com_categories&view=categories&extension=com_weblinks');
            cy.clickToolbarButton('Rebuild');
            cy.checkForSystemMessage('Categories tree data rebuilt.');

            // 3. Create module
            const params = {
              catid: [id1, id3],
              maxLevel: '3',
              groupby: '1',
              show_parent_category: '1'
            };
            cy.db_createModule({ module: 'mod_weblinks', params: JSON.stringify(params) }).then(() => {
              // 4. Visit page and test
              cy.visit('/', { failOnStatusCode: false });
              cy.get('body').should('contain', 'test module');

              // Check for weblinks in correct order and structure
              cy.get('.card-body').find('.weblinks-category').should('have.length', 3);

              // Check first category
              cy.get('.card-body').find('.weblinks-category').first().as('cat1');
              cy.get('@cat1').find('strong').should('contain.text', 'test category 1');
              cy.get('@cat1').find('ul.weblinks li').should('contain.text', 'test weblink 1');

              // Check second category
              cy.get('@cat1').find('.weblinks-category').as('cat2');
              cy.get('@cat2').should('have.class', 'ps-4');
              cy.get('@cat2').find('strong').should('contain.text', 'test category 2');
              cy.get('@cat2').find('ul.weblinks li').should('contain.text', 'test weblink 2');

              // Check that third category is present
              cy.get('.card-body').find('.weblinks-category').eq(2).as('cat3');
              cy.get('@cat3').find('strong').should('contain.text', 'test category 3');
            });
          });
        });
    });
  });

  it('can display a hierarchical list of multiple categories with show parent category off', () => {
    // 1. Create category structure
    cy.db_createCategory({ title: 'test category 1', extension: 'com_weblinks', parent_id: 1, path: 'test-category-1' }).then((id1) => {
      cy.db_createCategory({ title: 'test category 2', extension: 'com_weblinks', parent_id: id1, path: 'test-category-1/test-category-2' }).then((id2) => {
        cy.db_createCategory({ title: 'test category 3', extension: 'com_weblinks', parent_id: 1, path: 'test-category-3' }).then((id3) => {
          cy.db_createCategory({ title: 'test category 4', extension: 'com_weblinks', parent_id: id3, path: 'test-category-3/test-category-4' }).then((id4) => {
            // 2. Create weblinks
            cy.db_createWeblink({ title: 'test weblink 1', catid: id1 });
            cy.db_createWeblink({ title: 'test weblink 2', catid: id2 });
            cy.db_createWeblink({ title: 'test weblink 3', catid: id3 });
            cy.db_createWeblink({ title: 'test weblink 4', catid: id4 });

            // Rebuild categories      
            cy.visit('/administrator/index.php?option=com_categories&view=categories&extension=com_weblinks');
            cy.clickToolbarButton('Rebuild');
            cy.checkForSystemMessage('Categories tree data rebuilt.');

            // 3. Create module
            const params = {
              catid: [id1, id3],
              maxLevel: '3',
              groupby: '1',
              show_parent_category: '0'
            };
            cy.db_createModule({ module: 'mod_weblinks', params: JSON.stringify(params) }).then(() => {
              // 4. Visit page and test
              cy.visit('/', { failOnStatusCode: false });
              cy.get('body').should('contain', 'test module');

              // Check for weblinks in correct order and structure
              cy.get('.card-body').find('.weblinks-category').should('have.length', 2);

              // Check second category (child of first)
              cy.get('.card-body').find('.weblinks-category').first().as('cat2');
              cy.get('@cat2').find('strong').should('contain.text', 'test category 2');
              cy.get('@cat2').find('ul.weblinks li').should('contain.text', 'test weblink 2');

              // Check forth category (child of third)
              cy.get('.card-body').find('.weblinks-category').eq(1).as('cat4');
              cy.get('@cat4').find('strong').should('contain.text', 'test category 4');
              cy.get('@cat4').find('ul.weblinks li').should('contain.text', 'test weblink 4');
            });
          });
        });
      });
    });
  });

  it('does not display empty categories', () => {
    let catId1;
    let catId2;

    // 1. Create category structure
    cy.db_createCategory({ title: 'test category 1', extension: 'com_weblinks', parent_id: 1, path: 'test-category-1' }).then((id) => {
      catId1 = id;
      cy.db_createCategory({ title: 'test category 2', extension: 'com_weblinks', parent_id: id, path: 'test-category-1/test-category-2' }).then((id) => {
        catId2 = id;

        // 2. Create weblinks
        cy.db_createWeblink({ title: 'test weblink 1', catid: catId1 });

        // Rebuild categories
        cy.visit('/administrator/index.php?option=com_categories&view=categories&extension=com_weblinks');
        cy.clickToolbarButton('Rebuild');
        cy.checkForSystemMessage('Categories tree data rebuilt.');

        // 3. Create module
        const params = {
          catid: catId1,
          maxLevel: '2',
          groupby: '1',
          show_parent_category: '1'
        };
        cy.db_createModule({ module: 'mod_weblinks', params: JSON.stringify(params) }).then(() => {
          // 4. Visit page and test
          cy.visit('/', { failOnStatusCode: false });
          cy.get('body').should('contain', 'test module');

          // Check for weblinks in correct order and structure
          cy.get('.card-body').find('.weblinks-category').should('have.length', 1);
          cy.get('.card-body').find('.weblinks-category').first().as('cat1');
          cy.get('@cat1').find('strong').should('contain.text', 'test category 1');
          cy.get('@cat1').find('ul.weblinks li').should('contain.text', 'test weblink 1');
          cy.get('body').should('not.contain.text', 'test category 2');
        });
      });
    });
  });

});
