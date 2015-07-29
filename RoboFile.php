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
	// Load tasks from composer, see composer.json
	use \joomla_projects\robo\loadTasks;

	private $extension = '';

	/**
	* Set the Execute extension for Windows Operating System
	*
	* @return void
	*/
	private function setExecExtension()
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
		{
			$this->extension = '.exe';
		}
	}

	/**
	* Executes all the Selenium System Tests in a suite on your machine
	*
	* @param string $seleniumPath Optional path to selenium-standalone-server-x.jar
	* @param string $suite        Optional, the name of the tests suite
	*
	* @return mixed
	*/
	public function runTests($seleniumPath = null, $suite = 'acceptance')
	{
		$this->setExecExtension();

		// Get Joomla Clean Testing sites
		if (is_dir('tests/joomla-cms3'))
		{
			$this->taskDeleteDir('tests/joomla-cms3')->run();
		}

		$this->_exec('git' . $this->extension . ' clone -b staging --single-branch --depth 1 https://github.com/joomla/joomla-cms.git tests/joomla-cms3');
		$this->say('Joomla CMS site created at tests/joomla-cms3');

		if (!$seleniumPath)
		{
			if (!file_exists('selenium-server-standalone.jar'))
			{
				$this->say('Downloading Selenium Server, this may take a while.');

				if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
				{
					$this->_exec('curl.exe -sS http://selenium-release.storage.googleapis.com/2.45/selenium-server-standalone-2.45.0.jar > selenium-server-standalone.jar');
				}
				else
				{
					$this->taskExec('wget')
					->arg('http://selenium-release.storage.googleapis.com/2.45/selenium-server-standalone-2.45.0.jar')
					->arg('-O selenium-server-standalone.jar')
					->printed(false)
					->run();
				}
			}

			$seleniumPath = 'selenium-server-standalone.jar';
		}

		if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
		{
			$seleniumPath = "java -jar $seleniumPath >> selenium.log 2>&1 &";
		}
		else
		{
			$seleniumPath = "START java.exe -jar .\\" . $seleniumPath;
		}
	
		// Make sure we have Composer
		if (!file_exists('./composer.phar'))
		{
			$this->_exec('curl' . $this->extension . ' -sS https://getcomposer.org/installer | php');
		}

		$this->taskComposerUpdate()->run();

		// Running Selenium server
		$this->_exec($seleniumPath);

		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
		{
			sleep(10);
		}
		else
		{
			$this->taskWaitForSeleniumStandaloneServer()
			->run()
			->stopOnFail();
		}

		// Loading Symfony Command and running with passed argument
		$this->_exec('php' . $this->extension . ' vendor/bin/codecept build');

		$this->taskCodecept()
			->suite($suite)
			->arg('--steps')
			->arg('--debug')
			->run()
			->stopOnFail();

		// Kill selenium server
		// $this->_exec('curl http://localhost:4444/selenium-server/driver/?cmd=shutDownSeleniumServer');

		/*
		// Uncomment this lines if you need to debug selenium errors
		$seleniumErrors = file_get_contents('selenium.log');
		if ($seleniumErrors) {
			$this->say('Printing Selenium Log files');
			$this->say('------ selenium.log (start) ---------');
			$this->say($seleniumErrors);
			$this->say('------ selenium.log (end) -----------');
		}
		*/
	}

	/**
	 * Executes a specific Selenium System Tests in your machine
	 *
	 * @param string $seleniumPath   Optional path to selenium-standalone-server-x.jar
	 * @param string $pathToTestFile Optional name of the test to be run
	 * @param string $suite          Optional name of the suite containing the tests, Acceptance by default.
	 *
	 * @return mixed
	 */
	public function runTest($seleniumPath = null, $pathToTestFile = null, $suite = 'acceptance')
	{
		if (!$seleniumPath)
		{
			if (!file_exists('selenium-server-standalone.jar'))
			{
				$this->say('Downloading Selenium Server, this may take a while.');
				$this->taskExec('wget')
				     ->arg('http://selenium-release.storage.googleapis.com/2.46/selenium-server-standalone-2.46.0.jar')
				     ->arg('-O selenium-server-standalone.jar')
				     ->printed(false)
				     ->run();
			}

			$seleniumPath = 'selenium-server-standalone.jar';
		}

		// Make sure we have Composer
		if (!file_exists('./composer.phar'))
		{
			$this->_exec('curl -sS https://getcomposer.org/installer | php');
		}
		$this->taskComposerUpdate()->run();

		// Running Selenium server
		$this->_exec("java -jar $seleniumPath > selenium-errors.log 2>selenium.log &");

		$this->taskWaitForSeleniumStandaloneServer()
		     ->run()
		     ->stopOnFail();

		// Make sure to Run the Build Command to Generate AcceptanceTester
		$this->_exec("php vendor/bin/codecept build");

		if (!$pathToTestFile)
		{
			$tests = array();
			$this->say('Available tests in the system:');
			$filesInSuite = scandir(getcwd() . '/tests/' . $suite);
			$i = 1;

			foreach ($filesInSuite as $file)
			{
				// Make sure the file is a Test file
				if (strripos($file, 'cept.php') || strripos($file, 'cest.php'))
				{
					$tests[$i] = $file;
					$this->say('[' . $i . '] ' . $file);
					$i++;
				}
			}

			$this->say('');
			$testNumber     = $this->ask('Type the number of the test  in the list that you want to run...');
			$pathToTestFile = "tests/$suite/" . $tests[$testNumber];
		}

		$this->taskCodecept()
		     ->test($pathToTestFile)
		     ->arg('--steps')
		     ->arg('--debug')
		     ->run()
		     ->stopOnFail();

		// Kill selenium server
		// $this->_exec('curl http://localhost:4444/selenium-server/driver/?cmd=shutDownSeleniumServer');

		$this->say('Printing Selenium Log files');
		$this->say('------ selenium-errors.log (start) ---------');
		$seleniumErrors = file_get_contents('selenium-errors.log');

		if ($seleniumErrors)
		{
			$this->say(file_get_contents('selenium-errors.log'));
		}
		else
		{
			$this->say('no errors were found');
		}
		$this->say('------ selenium-errors.log (end) -----------');

		/*
		// Uncomment if you need to debug issues in selenium
		$this->say('');
		$this->say('------ selenium.log (start) -----------');
		$this->say(file_get_contents('selenium.log'));
		$this->say('------ selenium.log (end) -----------');
		*/
	}

	/**
	 * Creates a testing Joomla site for running the tests (use it before run:test)
	 */
	public function createTestingSite()
	{
		// Get Joomla Clean Testing sites
		if (is_dir('tests/joomla-cms3'))
		{
			$this->taskDeleteDir('tests/joomla-cms3')->run();
		}

		$this->_exec('git' . $this->extension . ' clone -b staging --single-branch --depth 1 https://github.com/joomla/joomla-cms.git tests/joomla-cms3');
		$this->say('Joomla CMS site created at tests/joomla-cms3');
	}
}
