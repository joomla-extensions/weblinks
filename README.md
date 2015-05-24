# Weblinks for Joomla!

This repo is meant to hold the decoupled com_weblinks component and related code.

# Tests
To prepare the system tests (Selenium) to be run in your local machine you are asked to rename the file `tests/acceptance.suite.dist.yml` to `tests/acceptance.suite.yml`. Afterwards, please edit the file according to your system needs.

To run the tests please execute the following commands (for the moment only working in Linux and MacOS, for more information see: https://docs.joomla.org/Testing_Joomla_Extensions_with_Codeception):

```bash
$ composer update
$ vendor/bin/robo
$ vendor/bin/robo test:acceptance
```
