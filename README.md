# Weblinks for Joomla! [![Build Status](https://travis-ci.org/joomla-extensions/weblinks.svg?branch=master)](https://travis-ci.org/joomla-extensions/weblinks)

This repo is meant to hold the decoupled com_weblinks component and related code.

# Building

```bash
$ composer install
$ vendor/bin/robo build
```

# Tests
The tests in Weblinks Extension use Codeception Testing Framework, if you want to know more about the technology used for testing please check: [Testing Joomla Extensions with Codeception](https://docs.joomla.org/Testing_Joomla_Extensions_with_Codeception).

To prepare the system tests (Selenium) to be run in your local machine you are asked to rename the file `tests/acceptance.suite.dist.yml` to `tests/acceptance.suite.yml`. Afterwards, please edit the file according to your system needs.

## Optional: extra configuration for RoboFile

This is not required, and if in doubt you can just skip this section, but there may be some specific use cases when you need (or want) to override the default behaviour of RoboFile.php. To do this, copy `RoboFile.dist.ini` to `RoboFile.ini` and add options in INI format, one per line, e.g.

    skipClone = true
    cmsPath = tests/joomla-cms3

The currently available options are as follows:

* `skipClone`: set to `true` to avoid the cms repo being deleted and re-cloned at each test execution. Useful to save time and bandwidth while you're debugging your test environment. But please be aware that if you don't refresh the repo you'll have to manually check the `installation` folder is present and the `configuration.php` is not.
* `cmsPath`: set to the local path (absolute or relative) where you'd like the test website to be installed. Default is `tests/joomla-cms3`.
* `branch`: set to whatever existing branch from the `joomla-cms` project if you want to clone that specific branch. Default is `staging`.
 
## Run the tests

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
- Curl for Windows if necessary.
- for complete list of necessary software and tips check this [wiki page](https://github.com/joomla-extensions/weblinks/wiki/Programs-needed-on-Windows-to-get-the-tests-running)


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
