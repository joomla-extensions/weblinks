# Weblinks for Joomla! [![Build Status](https://travis-ci.org/joomla-extensions/weblinks.svg?branch=master)](https://travis-ci.org/joomla-extensions/weblinks)

This repo is meant to hold the decoupled com_weblinks component and related code.

# Tests
To prepare the system tests (Selenium) to be run in your local machine you are asked to rename the file `tests/acceptance.suite.dist.yml` to `tests/acceptance.suite.yml`. Afterwards, please edit the file according to your system needs.

To run the tests please execute the following commands (for the moment only working in Linux and MacOS, for more information see: https://docs.joomla.org/Testing_Joomla_Extensions_with_Codeception):

```bash
$ composer install
$ vendor/bin/robo
$ vendor/bin/robo run:test acceptance
```


##For Windows:

You need to install:
- Git for windows (https://msysgit.github.io/)
- GitHub for windows (https://windows.github.com/)
- Curl for windows if necesssary.

Note: For commands line is better if you use the 'Git shell' program.

Create a symbolic link from your tests\joomla-cms3 to a subfolder of your web server. For example, I'm creating a link between the tests folder of my weblinks folder and the tests folder of my web server:
mklink /J C:\wamp\www\tests\joomla-cms3 C:\Users\Nicolas\Documents\GitHub\weblinks\tests\joomla-cms3

Go in the folder of weblinks, for example:
cd C:\Users\Nicolas\Documents\GitHub\weblinks

Then, run the command:
composer install

That will add all the dependencies for the testing of weblinks
You can then run the command:
vendor\bin\robo.bat test:acceptance
