# Weblinks for Joomla! [![Build Status](https://travis-ci.org/joomla-extensions/weblinks.svg?branch=master)](https://travis-ci.org/joomla-extensions/weblinks)

This repo is meant to hold the decoupled com_weblinks component and related code.

# Tests
To prepare the system tests (Selenium) to be run in your local machine you are asked to rename the file `tests/acceptance.suite.dist.yml` to `tests/acceptance.suite.yml`. Afterwards, please edit the file according to your system needs.

To run the tests please execute the following commands (for the moment only working in Linux and MacOS, for more information see: https://docs.joomla.org/Testing_Joomla_Extensions_with_Codeception):

```bash
$ composer install
$ vendor/bin/robo
$ vendor/bin/robo run:tests
```


##For Windows:

You need to install:
- Git for windows (https://msysgit.github.io/)
- GitHub for windows (https://windows.github.com/)
- Curl for Windows if necesssary.

Note: For commands line is better if you use the 'Git shell' program.

First you should create a fork of the official repository and clone the fork into your web server folder.

To prepare the system tests (Selenium) to be run in your local machine you are asked to rename the file `tests/acceptance.suite.dist.yml` to `tests/acceptance.suite.yml`. Afterwards, please edit the file according to your system needs.

The next step is only required if you don't place the weblinks folder into your web server folder. Create a symbolic link from your tests\joomla-cms3 to a subfolder of your web server. For example:

```bash
mklink /J C:\wamp\www\tests\joomla-cms3 C:\Users\Name\Documents\GitHub\weblinks\tests\joomla-cms3
```

Open the console and go in the folder of weblinks, for example:

```bash
cd C:\wamp\www\weblinks
```

Then run the command:

```bash
$ composer install
```

You can then run the following command to start the tests:

```bash
$ vendor/bin/robo run:tests
```

Once all tests were executed, you may also run a specific test:

```bash
$ vendor/bin/robo run:test // Then select the test you want to run!
```