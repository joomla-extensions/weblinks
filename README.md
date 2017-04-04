# Weblinks for Joomla! Travis:

Travis: [![Travis Build Status](https://travis-ci.org/joomla-extensions/weblinks.svg?branch=master)](https://travis-ci.org/joomla-extensions/weblinks) 

Drone: [![Drone Build Status](http://213.160.72.75/api/badges/joomla-extensions/weblinks/status.svg)](http://213.160.72.75/joomla-extensions/weblinks)

This repo is meant to hold the decoupled com_weblinks component and related code.

# How to test a PR

## With the [Patch Tester Component](https://github.com/joomla-extensions/patchtester/releases/latest)


Easily apply changes from a pull requests against this repo:
Install the last release of [com_weblinks](https://github.com/joomla-extensions/weblinks/releases/latest) into your Joomla.
Install the last release of [com_patchtester](https://github.com/joomla-extensions/patchtester/releases/latest) into your Joomla (weblinks is supported since Version 3.0.0 Alpha 2).
Log into your site's administrator section, go to the Patch Tester Component Options (Components -> Patch Tester -> Options)
Switch the `GitHub Repository` to `Joomla Weblinks Package`. For this you have to fill the field  `GitHub Project Owner` with `joomla-extensions` and the field `GitHub Project Repository` with `weblinks`.
Click `Save & Close` and hit the `Fetch Data` Button to the the lastest pull requests
Click `Apply Patch` to apply the proposed changes from the pull request.
Click `Revert Patch` to revert an applied patch.

You can read more about the Patch Tester extension on the [Joomla! Documentation Wiki](https://docs.joomla.org/Component_Patchtester_for_Testers).

## With Github


If you want to test a patch you can apply the patch via git.

If you cloned this repo under the name upstream - your remote is upstream – you can user the command

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

# For Linux


## Install


### 1. Open a session and change to the document root of your local webserver.

```
$ cd /var/www/html/
/var/www/html$
```


### 2. Clone the current repository into your webserver root folder

```
/var/www/html$ git clone git@github.com:joomla-extensions/weblinks.git
Clone nach 'weblinks' ...
remote: Counting objects: 2446, done.
remote: Compressing objects: 100% (75/75), done.
remote: Total 2446 (delta 10), reused 0 (delta 0), pack-reused 2361
Empfange Objekte: 100% (2446/2446), 615.02 KiB | 375.00 KiB/s, Fertig.
Löse Unterschiede auf: 100% (1232/1232), Fertig.
Prüfe Konnektivität ... Fertig.
```

Are you new with github? Here you can find informations about setting it up: https://help.github.com/articles/set-up-git/
If you get an error you can try git clone https://github.com:joomla-extensions/weblinks.git instead of git clone git@github.com:joomla-extensions/weblinks.git


### 3. Change to the directory weblinks

```
/var/www/html$ cd weblinks
/var/www/html/weblinks$
```


### 4. This files should be in your weblinks folder.

```
/var/www/html/weblinks$ ls
codeception.yml  docs		  LICENSE	RoboFile.dist.ini  tests
composer.json	 jed_update.xml   manifest.xml	RoboFile.php
composer.lock	 jorobo.dist.ini  README.md	src
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


### 7. Install via composer

```
/var/www/html/weblinks$ composer install
Loading composer repositories with package information
Installing dependencies (including require-dev) from lock file
  - Installing symfony/yaml (v3.1.3)
    Downloading: 100%
...
...
Generating autoload files

```


### 8. After that you have to build [robo](http://robotframework.org/)

```
/var/www/html/weblinks$ vendor/bin/robo build
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


### 10. Copy the file acceptance.suite.dist.yml  into  acceptance.suite.dist.yml

```
/var/www/html/weblinks$ cd tests
/var/www/html/weblinks/tests$ cp acceptance.suite.dist.yml acceptance.suite.yml
```


### 11. Update the file acceptance.suite.yml to your needs. At least you have to update the options url, database name and  counter_test_url.

```
/var/www/html/weblinks/tests$ cat acceptance.suite.yml

class_name: AcceptanceTester
modules:
    enabled:
        - JoomlaBrowser
        - AcceptanceHelper
    config:
        JoomlaBrowser:
            url: 'http://localhost/weblinks/tests/joomla-cms3'     # the url that points to the joomla installation at /tests/system/joomla-cms
            browser: 'firefox'
            window_size: 1024x768
            capabilities:
            unexpectedAlertBehaviour: 'accept'
            username: 'admin'                      # UserName for the Administrator
            password: 'admin'                      # Password for the Administrator
            database host: 'localhost'             # place where the Application is Hosted #server Address
            database user: 'root'                  # MySQL Server user ID, usually root
            database password: 'yourPassword'                  # MySQL Server password, usually empty or root
            database name: 'weblinks'         # DB Name, at the Server
            database type: 'mysqli'                # type in lowercase one of the options: MySQL\MySQLi\PDO
            database prefix: 'jos_'                # DB Prefix for tables
            install sample data: 'no'              # Do you want to Download the Sample Data Along with Joomla Installation, then keep it Yes
            sample data: 'Default English (GB) Sample Data'    # Default Sample Data
            admin email: 'admin@mydomain.com'      # email Id of the Admin
            language: 'English (United Kingdom)'   # Language in which you want the Application to be Installed
        AcceptanceHelper:
            repo_folder: '/home/travis/build/joomla-extensions/weblinks/' # Path to the Extension repository. To be used by tests to install via Install from folder
            counter_test_url: 'http://localhost/weblinks/tests/joomla-cms3' # the url for the weblink item used to test hits counter
            url: 'http://localhost/weblinks/tests/joomla-cms3' # the url that points to the joomla installation at /tests/system/joomla-cms - we need it twice here
error_level: "E_ALL & ~E_STRICT & ~E_DEPRECATED"
env:
    desktop: ~
    tablet:
        modules:
            config:
                JoomlaBrowser:
                    window_size: 768x1024
    phone:
        modules:
            config:
                JoomlaBrowser:
                    window_size: 480x640
```


### 12. Optional: Go back to weblinks directory and create and edit the file RoboFile.ini. Delete the local user www-data.

```
/var/www/html/weblinks$ cp RoboFile.dist.ini RoboFile.ini
/var/www/html/weblinks$ cat RoboFile.ini
; If set to true, the repo will not be cloned from GitHub and the local copy will be reused.
; This setting will be obsolete once we have local Git cache enabled
skipClone = false

; If you want to setup your test website in a different folder, you can do that here.
; You can also set an absolute path, i.e. /path/to/my/cms/folder
cmsPath = tests/joomla-cms3

; If you want to clone a different branch, you can set it here
branch = staging

; (Linux / Mac only) If you want to set a different owner for the CMS root folder, you can set it here.
localUser =

; Set this to true, if your curl binaries are not able to create an https connection
insecure = false
```


### 13. Optional: Set use owner of the project to your user.

```
/var/www/html/weblinks$sudo chown -R username:usergroup /var/www
```


### 14. Ready! Run the first tests:

```
/var/www/html/weblinks$ vendor/bin/robo run:tests
Clone nach 'tests/cache' ...
 [Exec] Done in 13.18s
 [FileSystem\CopyDir] Copied from tests/cache to tests/joomla-cms3
...
```


## Tests

The tests in Weblinks Extension use Codeception Testing Framework, if you want to know more about the technology used for testing please check: [Testing Joomla Extensions with Codeception](https://docs.joomla.org/Testing_Joomla_Extensions_with_Codeception).

## Optional: extra configuration for RoboFile

This is not required, and if in doubt you can just skip this section, but there may be some specific use cases when you need (or want) to override the default behaviour of RoboFile.php. To do this, copy `RoboFile.dist.ini` to `RoboFile.ini` and add options in INI format, one per line, e.g.

    skipClone = true
    cmsPath = tests/joomla-cms3

The currently available options are as follows:

* `skipClone`: set to `true` to avoid the cms repo being deleted and re-cloned at each test execution. Useful to save time and bandwidth while you're debugging your test environment. But please be aware that if you don't refresh the repo you'll have to manually check the `installation` folder is present and the `configuration.php` is not.
* `cmsPath`: set to the local path (absolute or relative) where you'd like the test website to be installed. Default is `tests/joomla-cms3`.
* `branch`: set to whatever existing branch from the `joomla-cms` project if you want to clone that specific branch. Default is `staging`.

## Additional options

You can run the tests against different resolutions. The default acceptance YAML configuration file provides three options:

* "desktop": default, 1024x768px
* "tablet": tablet in portrait mode, 768x1024px
* "phone": phone in portrait mode, 480x640px

To set a specific resolution, set is as an option of the command:

`$ vendor/bin/robo run:tests --env=tablet`

Note: the first parameter is used by Travis and you should always set it to "0" when you run your tests locally.


## Video
[Here](https://www.youtube.com/watch?v=fWO_Ed_wxpw) you can finde a video that shows the installation of com_weblinks for testing.


# For Windows:

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
