<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * Download robo.phar from http://robo.li/robo.phar and type in the root of the repo: $ php robo.phar
 * Or do: $ composer update, and afterwards you will be able to execute robo like $ php vendor/bin/robo
 *
 * @see http://robo.li/
 */
require_once 'vendor/autoload.php';


class RoboFile extends \Robo\Tasks
{

    // load tasks from composer, see composer.json
    use \redcomponent\robo\loadTasks;

    /**
     * Downloads and prepares a Joomla CMS site for testing
     *
     * @return mixed
     */
    public function testPrepareAcceptance()
    {
        // Get Joomla Clean Testing sites
        if (is_dir('tests/joomla-cms3')) {
            $this->taskDeleteDir('tests/joomla-cms3')->run();
        }

        $this->_exec('git clone -b staging --single-branch --depth 1 https://github.com/joomla/joomla-cms.git tests/joomla-cms3');
        $this->say('Joomla CMS site created at tests/joomla-cms3');
    }

    /**
     * Executes Selenium System Tests in your machine
     *
     * @param null $seleniumPath Optional path to selenium-standalone-server-x.jar
     *
     * @return mixed
     */
    public function testAcceptance($seleniumPath = null)
    {
        if (!$seleniumPath) {
            if (!file_exists('selenium-server-standalone.jar')) {
                $this->say('Downloading Selenium Server, this may take a while.');
                $this->taskExec('wget')
                    ->arg('http://selenium-release.storage.googleapis.com/2.45/selenium-server-standalone-2.45.0.jar')
                    ->arg('-O selenium-server-standalone.jar')
                    ->printed(false)
                    ->run();
            }
            $seleniumPath = 'selenium-server-standalone.jar';
        }

        // Make sure we have Composer
        if (!file_exists('./composer.phar')) {
            $this->_exec('curl -sS https://getcomposer.org/installer | php');
        }
        $this->taskComposerUpdate()->run();

        // Running Selenium server
        $this->_exec("java -jar $seleniumPath > selenium-errors.log 2>selenium.log &");

        $this->taskWaitForSeleniumStandaloneServer()
            ->run()
            ->stopOnFail();

        // Loading Symfony Command and running with passed argument
        $this->_exec('php vendor/bin/codecept build');

        $this->taskCodecept()
            ->suite('acceptance')
            ->arg('--steps')
            ->arg('--debug')
            ->run()
            ->stopOnFail();

        // Kill selenium server
        // $this->_exec('curl http://localhost:4444/selenium-server/driver/?cmd=shutDownSeleniumServer');

        $this->say('Printing Selenium Log files');
        $this->say('------ selenium.log (start) ---------');
        $seleniumErrors = file_get_contents('selenium.log');
        if ($seleniumErrors) {
            $this->say(file_get_contents('selenium.log'));
        }
        else {
            $this->say('no errors were found');
        }
        $this->say('------ selenium.log (end) -----------');
    }
}