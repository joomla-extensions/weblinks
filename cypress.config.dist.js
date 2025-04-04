import { defineConfig } from 'cypress';
import setupPlugins from './tests/cypress/plugins/index.mjs';

export default defineConfig({
  fixturesFolder: 'tests/cypress/fixtures',
  videosFolder: 'tests/cypress/output/videos',
  screenshotsFolder: 'tests/cypress/output/screenshots',
  viewportHeight: 1000,
  viewportWidth: 1200,
  e2e: {
    setupNodeEvents(on, config) {
      setupPlugins(on, config);
    },
    baseUrl: 'http://localhost/',
    specPattern: [
      'tests/cypress/integration/install/*.cy.{js,jsx,ts,tsx}',
      'tests/cypress/integration/administrator/**/*.cy.{js,jsx,ts,tsx}',
      'tests/cypress/integration/site/**/*.cy.{js,jsx,ts,tsx}',
      'tests/cypress/integration/api/**/*.cy.{js,jsx,ts,tsx}',
      'tests/cypress/integration/plugins/**/*.cy.{js,jsx,ts,tsx}',
    ],
    supportFile: 'tests/cypress/support/index.js',
    scrollBehavior: 'center',
    browser: 'firefox',
    screenshotOnRunFailure: true,
    video: false
  },
  env: {
    sitename: 'Joomla CMS Test',
    name: 'jane doe',
    email: 'admin@example.com',
    username: 'ci-admin',
    password: 'joomla-17082005',
    db_type: process.env.DB_TYPE || 'MySQLi',
    db_host: process.env.DB_HOST || 'mysql',
    db_port: process.env.DB_PORT || '',
    db_name: process.env.DB_NAME || 'test_joomla',
    db_user: process.env.DB_USER || 'joomla_ut',
    db_password: process.env.DB_PASSWORD || 'joomla_ut',
    db_prefix: process.env.DB_PREFIX || 'mysql_',
  },
})
