describe('Weblinks API Rate Limiting', () => {

    const setPluginParams = (enablePublic, maxRequests, windowSeconds) => {
        cy.doAdministratorLogin();
        cy.visit('/administrator/index.php?option=com_plugins');
        cy.get('#filter_search').clear().type('Weblinks');
        cy.get('#adminForm').submit();

        // Click on the "Web Services - Weblinks" plugin link
        cy.contains('a[title="Edit Web Services - Weblinks"]', 'Web Services - Weblinks').click();

        // Configure Public Access
        if (enablePublic) {
            cy.get(`input[name="jform[params][public]"][value="1"]`).check({ force: true });
        } else {
            cy.get(`input[name="jform[params][public]"][value="0"]`).check({ force: true });
        }

        // Configure Max Requests - only if public access is enabled
        if (enablePublic) {
            cy.get('input[name="jform[params][max_requests]"]').clear().type(maxRequests.toString());
            cy.get('input[name="jform[params][window_seconds]"]').clear().type(windowSeconds.toString());
        }

        cy.clickToolbarButton('Save & Close');
        cy.doAdministratorLogout();
    };

    beforeEach(() => {
        cy.db_enableExtension('1', 'plg_webservices_weblinks');

        // Configure rate limiting params via UI
        setPluginParams(true, 2, 5); // Enable public, 2 requests, 5 seconds window
    });

    afterEach(() => {
        // Reset plugin params to disable public access
        setPluginParams(false, 2, 180); // Disable public, reset others to default
        cy.db_enableExtension('0', 'plg_webservices_weblinks');
    });

    it('should allow GET requests within the limit for public access', () => {
        cy.request({
            method: 'GET',
            url: `${Cypress.config('baseUrl')}/api/index.php/v1/weblinks`,
            failOnStatusCode: false,
        }).its('status').should('eq', 200);

        cy.request({
            method: 'GET',
            url: `${Cypress.config('baseUrl')}/api/index.php/v1/weblinks`,
            failOnStatusCode: false,
        }).its('status').should('eq', 200);
    });

    it('should return 429 when rate limit is exceeded for public access', () => {
        // First two requests should be fine
        cy.request({ method: 'GET', url: `${Cypress.config('baseUrl')}/api/index.php/v1/weblinks`, failOnStatusCode: false }).its('status').should('eq', 200);
        cy.request({ method: 'GET', url: `${Cypress.config('baseUrl')}/api/index.php/v1/weblinks`, failOnStatusCode: false }).its('status').should('eq', 200);

        // Third request should be rate-limited
        cy.request({
            method: 'GET',
            url: `${Cypress.config('baseUrl')}/api/index.php/v1/weblinks`,
            failOnStatusCode: false,
        }).then((response) => {
            expect(response.status).to.eq(429);
        });
    });

    it('should allow requests again after the rate limit window passes', () => {
        // Exceed the limit
        cy.request({ method: 'GET', url: `${Cypress.config('baseUrl')}/api/index.php/v1/weblinks`, failOnStatusCode: false });
        cy.request({ method: 'GET', url: `${Cypress.config('baseUrl')}/api/index.php/v1/weblinks`, failOnStatusCode: false });
        cy.request({ method: 'GET', url: `${Cypress.config('baseUrl')}/api/index.php/v1/weblinks`, failOnStatusCode: false })
            .its('status').should('eq', 429);

        // Wait for the window (5 seconds + buffer)
        cy.wait(6000);

        // Request should now be successful
        cy.request({
            method: 'GET',
            url: `${Cypress.config('baseUrl')}/api/index.php/v1/weblinks`,
            failOnStatusCode: false,
        }).its('status').should('eq', 200);
    });

    it('should not apply public rate limit to authenticated GET requests', () => {
        // Make more requests than the public limit
        for (let i = 0; i < 5; i++) {
            cy.api_get('/weblinks')
                .its('status').should('eq', 200);
        }
    });

    it('should still require authentication for POST requests when public GET is enabled', () => {
        let categoryId;
        cy.db_createCategory({ extension: 'com_weblinks', title: 'Category for POST test' })
            .then((id) => { categoryId = id; })
            .then(() => {
                cy.request({
                    method: 'POST',
                    url: `${Cypress.config('baseUrl')}/api/index.php/v1/weblinks`,
                    body: {
                        title: 'automated test weblink public post',
                        alias: 'test-weblink-public-post',
                        url: 'http://example.com',
                        catid: categoryId,
                        state: 1,
                        language: '*',
                    },
                    failOnStatusCode: false,
                }).its('status').should('eq', 401); // Returns unauthorized
            });
        // Clean up the category
        cy.task('queryDB', `DELETE FROM #__categories WHERE title = 'Category for POST test' AND extension = 'com_weblinks'`);
    });

    it('should include rate limiting response headers', () => {
        // First request
        cy.request({
            method: 'GET',
            url: `${Cypress.config('baseUrl')}/api/index.php/v1/weblinks`,
            failOnStatusCode: false,
        }).then((response) => {
            expect(response.status).to.eq(200);
            expect(response.headers).to.have.property('x-ratelimit-remaining', '1');
            expect(response.headers).to.have.property('x-ratelimit-reset', '5');
        });

        // Second request to exceed the limit
        cy.request({
            method: 'GET',
            url: `${Cypress.config('baseUrl')}/api/index.php/v1/weblinks`,
            failOnStatusCode: false,
        });

        // Third request - expecting 429
        cy.request({
            method: 'GET',
            url: `${Cypress.config('baseUrl')}/api/index.php/v1/weblinks`,
            failOnStatusCode: false,
        }).then((response) => {
            expect(response.status).to.eq(429);
            expect(response.headers).to.have.property('retry-after');
            expect(response.headers).to.have.property('x-ratelimit-remaining', '0');
        });
    });
});
