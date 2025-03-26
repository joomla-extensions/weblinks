# Weblinks for Joomla!

Build Status
---------------------
| Drone-CI |
| ------------- |
| [![Build Status](https://ci.joomla.org/api/badges/joomla-extensions/weblinks/status.svg?ref=refs/heads/4.0-dev)](https://ci.joomla.org/joomla-extensions/weblinks) |

Weblinks for Joomla! provides a component and accompanying extensions to create a directory of weblinks.

# How to test a PR

## With the [Patch Tester Component](https://github.com/joomla-extensions/patchtester/releases/latest)

Easily apply changes from a pull requests against this repo:
Install the last release of [com_weblinks](https://github.com/joomla-extensions/weblinks/releases/latest) into your Joomla.
Install the last release of [com_patchtester](https://github.com/joomla-extensions/patchtester/releases/latest) into your Joomla (weblinks is supported since Version 3.0.0 Alpha 2).
Log into your site's administrator section, go to the Patch Tester Component Options (Components -> Patch Tester -> Options)
Switch the `GitHub Repository` to `Joomla Weblinks Package`. For this you have to fill the field  `GitHub Project Owner` with `joomla-extensions` and the field `GitHub Project Repository` with `weblinks`.
Click `Save & Close` and hit the `Fetch Data` Button to the the latest pull requests
Click `Apply Patch` to apply the proposed changes from the pull request.
Click `Revert Patch` to revert an applied patch.

You can read more about the Patch Tester extension on the [Joomla! Documentation Wiki](https://docs.joomla.org/Component_Patchtester_for_Testers).

## With Github

If you want to test a patch you can apply the patch via git.

If you cloned this repo under the name upstream - your remote is upstream – you can use the command

```
git fetch upstream pull/PR_NUMBER/head:BRANCHNUMER
```

for fetching the branch of the PR https://github.com/joomla-extensions/weblinks/pull/290 this would be

```
git fetch upstream pull/290/head:move-lang-files
```

After that you can checkout the branch and start testing.

```
git checkout move-lang-files
```

# For Linux / MacOS

## Install

### 1. Open a session and change to the document root of your local webserver.

```
$ cd /var/www/html/
/var/www/html$
```

### 2. Clone the current repository into your webserver root folder

```
/var/www/html$ git clone git@github.com:joomla-extensions/weblinks.git
```

Are you new with github? Here you can find information about setting it up: https://help.github.com/articles/set-up-git/
If you get an error you can try git clone https://github.com:joomla-extensions/weblinks.git instead of git clone git@github.com:joomla-extensions/weblinks.git

### 3. Change to the directory weblinks

```
/var/www/html$ cd weblinks
/var/www/html/weblinks$
```

### 4. This files should be in your weblinks folder.

```
/var/www/html/weblinks$ ls
cypress.config.js docs  LICENSE	RoboFile.dist.ini  tests
composer.json	 manifest.xml	RoboFile.php
composer.lock	 jorobo.dist.ini  README.md	src package.json
```

### 5. Optional: Have a look into composer.json for information what software you will install via composer.

```
/var/www/html/weblinks$ cat composer.json
```

or https://github.com/joomla-extensions/weblinks/blob/master/composer.json

Read more about [how to install composer](https://getcomposer.org/doc/00-intro.md) here.

### 6. Optional: If you have problems using composer set a timeout.

```
/var/www/html/weblinks$export COMPOSER_PROCESS_TIMEOUT=1500;
```

### 7. Install PHP dependencies via composer

```
/var/www/html/weblinks$ composer install
```

### 8. Install Javascript dependencies via npm

```
/var/www/html/weblinks$ npm ci
```

### 9. Optional: Prepare the database

If you use MySQL or PostgreSQL as database and your user has create database privileges the Database is automatically created by the Joomla installer.
But the safest way is to create the database before running Joomla's web installer.

```
/var/www/html/weblinks$ mysql -u root -p

mysql> create database weblinks;
Query OK, 1 row affected (0,00 sec)

mysql> quit;
Bye
```

### 10. Optional: Set use owner of the project to your user.

```
/var/www/html/weblinks$ sudo chown -R username:usergroup /var/www
```

## Testing

The tests in Weblinks Extension use Cypress,
Below is the Test Files Structure

```
tests/cypress/
├── integration/
│   ├── administrator/    # Administrator panel tests (Joomla backend)
│   ├── site/             # Frontend UI website tests
│   └── plugins/          # Joomla extension integration tests
├── fixtures/             # Mock data and API responses
├── support/              
│   ├── commands.mjs      # Custom Cypress commands
│   └── db.mjs            # Database utilities
└── downloads/            # Automated test file downloads
```

### 1. Copy the file cypress.config.dist.js into cypress.config.js

```
/var/www/html/weblinks$ cp cypress.config.dist.js cypress.config.js
```

### 2. Update the file cypress.config.js to your needs. At least you have to update the base url, environment credentials.

```
export default defineConfig({
  fixturesFolder: 'tests/cypress/fixtures',
  videosFolder: 'tests/cypress/output/videos',
  screenshotsFolder: 'tests/cypress/output/screenshots',
  viewportHeight: 1000,
  viewportWidth: 1200,
  e2e:  {
    setupNodeEvents(on, config) {
      setupPlugins(on, config);
    },
    baseUrl: 'http://localhost/',
    specPattern: [
      'tests/cypress/integration/install/*.cy.{js,jsx,ts,tsx}',
      'tests/cypress/integration/administrator/**/*.cy.{js,jsx,ts,tsx}',
      'tests/cypress/integration/site/**/*.cy.{js,jsx,ts,tsx}',
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
```

## Running Tests

#### Run headless tests

```
/var/www/html/weblinks$ npx cypress run
```

#### Open interactive runner

```
/var/www/html/weblinks$ npx cypress open
```

#### Run specific test suite

```
/var/www/html/weblinks$ npx cypress run --spec "tests/cypress/integration/administrator/**/*"
```

### Additional options for Cypress testing

You can control test environment dimensions to simulate different devices:

```
# CLI configuration for tablet view
[var/www/html/weblinks$ npx cypress run --config viewportWidth=768,viewportHeight=1024

# In-spec dynamic viewport change
cy.viewport('ipad-2')  # Preconfigured tablet profile
cy.viewport(375, 667)  # Custom phone dimensions
Browser Selection - Cross-Browser Testing
```

To execute tests in different browsers for compatibility verification:

```
# Firefox (default)
/var/www/html/weblinks$ npx cypress run --browser firefox

# Chrome
[var/www/html/weblinks$ npx cypress run --browser chrome
```

if you want to know more about the technology used for testing please check: [Checkout this repository](https://github.com/joomla-projects/joomla-cypress).

## Development and Package Building

### Build Package

```
/var/www/html/weblinks$ vendor/bin/robo build
```

This command creates an installable Joomla package of the extension, outputting a .zip archive to the dist/ directory.

### Development Mapping

```
/var/www/html/weblinks$ vendor/bin/robo map /path/to/joomla-cms
```

This command create symbolic links between the extension's source files and the target Joomla CMS installation. This allows for immediate visualization of code modifications within the Joomla environment, eliminating manual extension installs. Please provide the **absolute** path to the Joomla installation's root directory when running this command.

# For Windows:

You need to install:
- Git for Windows (https://gitforwindows.org/) (includes Git Bash, recommended for commands)
- GitHub for windows (https://windows.github.com/)
- Composer (https://getcomposer.org/download/)
- Node.js (https://nodejs.org/) (includes npm)

- for complete list of necessary software and tips check this [wiki page](https://github.com/joomla-extensions/weblinks/wiki/Programs-needed-on-Windows-to-get-the-tests-running)

Note: For commands line is better if you use the 'Git Bash' program.

The next step is only required if you don't place the weblinks folder into your web server folder. Create a symbolic link from your tests\joomla-cms to a subfolder of your web server. For example:

```bash
mklink /J C:\wamp\www\tests\joomla-cms C:\Users\Name\Documents\GitHub\weblinks\tests\joomla-cms
```

Open the console and go in the folder of weblinks, for example:

```bash
cd C:\wamp\www\weblinks
```

## Next Steps

Once the prerequisites are installed, follow the "For Linux / MacOS" section starting from Install. The commands (e.g., git clone, composer install, npm ci) work the same in Git Bash, with the only adjustment is replacing paths (e.g., /var/www/html/weblinks) with Windows equivalents (e.g., C:\wamp\www\weblinks if using WAMP, or C:\xampp\htdocs\weblinks for XAMPP)

