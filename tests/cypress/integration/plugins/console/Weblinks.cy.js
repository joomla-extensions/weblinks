describe('Test console command weblinks:sync-csv', () => {
  const csvExportPath = `${Cypress.expose('cmsPath')}/tmp/weblinks_export.csv`;
  const csvImportPath = `${Cypress.expose('cmsPath')}/tmp/weblinks_import.csv`;

  beforeEach(() => {
    // Clean up any existing test files
    cy.exec(`rm -f ${csvExportPath} ${csvImportPath}`, { failOnNonZeroExit: false });
  });

  afterEach(() => {
    // Clean up test files after each test
    cy.exec(`rm -f ${csvExportPath} ${csvImportPath}`, { failOnNonZeroExit: false });
    // Clean up test weblinks
    cy.task('queryDB', "DELETE FROM #__weblinks WHERE title LIKE 'automated test weblink%'");
  });

  it('can export weblinks to CSV', () => {
    cy.db_createWeblink({ title: 'automated test weblink', url: 'http://example.com/', state: 1 }).then(() => {
      cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php weblinks:sync-csv --action=export --file=${csvExportPath}`)
        .its('stdout')
        .should('contain', 'Successfully exported')
        .should('contain', 'links into file path');

      // Verify file exists
      cy.readFile(csvExportPath).should('exist').should('contain', 'automated test weblink');
    });
  });

  it('can export weblinks with default file path', () => {
    cy.db_createWeblink({ title: 'automated test weblink', url: 'http://example.com/', state: 1 });
    cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php weblinks:sync-csv --action=export`)
      .its('stdout')
      .should('contain', 'Successfully exported')
      .should('contain', 'weblinks_export.csv');
  });

  it('can import weblinks from CSV', () => {
    // First create a test weblink in database
    cy.db_createWeblink({ title: 'automated test weblink', url: 'http://example.com/', state: 1 }).then(() => {
      // Export to CSV
      cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php weblinks:sync-csv --action=export --file=${csvExportPath}`).then(() => {
        // Verify CSV was created
        cy.readFile(csvExportPath).should('contain', 'automated test weblink').then(() => {
          // Copy export file to import file
          cy.exec(`cp ${csvExportPath} ${csvImportPath}`).then(() => {
            // Delete the original weblink
            cy.task('queryDB', "DELETE FROM #__weblinks WHERE title = 'automated test weblink'").then(() => {
              // Import from CSV
              cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php weblinks:sync-csv --action=import --file=${csvImportPath}`)
                .its('stdout')
                .should('contain', 'Data synchronization run finalized completed successfully').then(() => {
                  // Verify weblink was imported
                  cy.task('queryDB', "SELECT title FROM #__weblinks WHERE title = 'automated test weblink'").then((result) => {
                    expect(result).to.have.length(1);
                  });
                });
            });
          });
        });
      });
    });
  });

  it('can update existing weblinks during import', () => {
    // Clean all weblinks first to ensure only one record
    cy.task('queryDB', "DELETE FROM #__weblinks").then(() => {
      // Create a test weblink
      cy.db_createWeblink({ title: 'automated test weblink', url: 'https://example.com/', state: 1 }).then(() => {
        // Export to CSV
        cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php weblinks:sync-csv --action=export --file=${csvExportPath}`).then(() => {
          // Modify the exported CSV to change URL
          cy.readFile(csvExportPath).then((content) => {
            // Replace URL in the CSV, handling the quoted format
            const modified = content.replace("https://example.com/", "https://updated-example.com/");
            cy.writeFile(csvImportPath, modified).then(() => {
              // Import modified CSV
              cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php weblinks:sync-csv --action=import --file=${csvImportPath}`)
                .its('stdout')
                .should('contain', 'Existing Links Modified: 1').then(() => {
                  // Verify the URL was updated
                  cy.task('queryDB', "SELECT url FROM #__weblinks WHERE title = 'automated test weblink'").then((result) => {
                    expect(result).to.have.length(1);
                    expect(result[0]).to.have.property('url', 'https://updated-example.com/');
                  });
                });
            });
          });
        });
      });
    });
  });

  it('shows error for invalid action', () => {
    cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php weblinks:sync-csv --action=invalid`, { failOnNonZeroExit: false })
      .its('stdout')
      .should('contain', 'Invalid action: "invalid"');
  });

  it('shows error when import file does not exist', () => {
    cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php weblinks:sync-csv --action=import --file=/nonexistent/file.csv`, { failOnNonZeroExit: false })
      .its('stdout')
      .should('contain', 'does not exist or cannot be parsed');
  });

  it('shows warning when no weblinks to export', () => {
    // Delete all weblinks temporarily
    cy.task('queryDB', "DELETE FROM #__weblinks");

    cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php weblinks:sync-csv --action=export --file=${csvExportPath}`)
      .its('stdout')
      .should('contain', 'No data records found');
  });

  it('handles invalid category ID during import with fallback', () => {
    // Create CSV with invalid category ID and all required fields
    const csvContent = `"id","catid","title","alias","url","description","hits","state","checked_out","checked_out_time","ordering","access","params","language","created","created_by","created_by_alias","modified","modified_by","metakey","metadesc","metadata","featured","xreference","publish_up","publish_down","version","images"
"","99999","Test Invalid Cat","test-invalid-cat","https://example.com","","0","1","","","0","1","","*","2025-01-01 00:00:00","990","","2025-01-01 00:00:00","990","","","","0","","","","1",""`;
    cy.writeFile(csvImportPath, csvContent).then(() => {
      // Import with invalid category
      cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php weblinks:sync-csv --action=import --file=${csvImportPath}`)
        .then((result) => {
          // Strip ANSI color codes and normalize whitespace
          const cleanOutput = result.stdout.replace(/\u001b\[\d+;?\d*m/g, '').replace(/\s+/g, ' ');
          expect(cleanOutput).to.include('was not found');
          expect(cleanOutput).to.include('Fallback applied');
        });
    });
  });
});
