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

	/**
	 * File extension for executables
	 *
	 * @var string
	 */
	private $executableExtension = '';

	/**
	 * Local configuration parameters
	 *
	 * @var array
	 */
	private $configuration = array();

	/**
	 * Path to the local CMS root
	 *
	 * @var string
	 */
	private $cmsPath = '';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->configuration = $this->getConfiguration();

		$this->cmsPath = $this->getCmsPath();

		$this->executableExtension = $this->getExecutableExtension();
	}

	/**
	 * Get the executable extension according to Operating System
	 *
	 * @return void
	 */
	private function getExecutableExtension()
	{
		if ($this->isWindows())
		{
			return '.exe';
		}

		return '';
	}

	/**
	 * Executes all the Selenium System Tests in a suite on your machine
	 *
	 * @param   bool  $use_htaccess  Renames and enable embedded Joomla .htaccess file
	 *
	 * @return mixed
	 */
	public function runTests($use_htaccess = false)
	{
		$this->createTestingSite($use_htaccess);

		$this->getComposer();

		$this->taskComposerInstall()->run();

		$this->runSelenium();

		// Make sure to run the build command to generate AcceptanceTester
		$this->_exec($this->isWindows() ? 'vendor\bin\codecept.bat build' : 'php vendor/bin/codecept build');

		$this->taskCodecept()
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->arg('tests/acceptance/install/')
			->run()
			->stopOnFail();

		$this->taskCodecept()
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->arg('tests/acceptance/administrator/')
			->run()
			->stopOnFail();

		$this->taskCodecept()
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->arg('tests/acceptance/frontend/')
			->run()
			->stopOnFail();
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
	public function runTest($pathToTestFile = null, $suite = 'acceptance')
	{
		$this->runSelenium();

		// Make sure to run the build command to generate AcceptanceTester
		$this->_exec($this->isWindows() ? 'vendor\bin\codecept.bat build' : 'php vendor/bin/codecept build');

		if (!$pathToTestFile)
		{
			$this->say('Available tests in the system:');

			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator(
					'tests/' . $suite,
					RecursiveDirectoryIterator::SKIP_DOTS
				),
				RecursiveIteratorIterator::SELF_FIRST
			);

			$tests = array();

			$iterator->rewind();
			$i = 1;

			while ($iterator->valid())
			{
				if (strripos($iterator->getSubPathName(), 'cept.php')
					|| strripos($iterator->getSubPathName(), 'cest.php'))
				{
					$this->say('[' . $i . '] ' . $iterator->getSubPathName());
					$tests[$i] = $iterator->getSubPathName();
					$i++;
				}

				$iterator->next();
			}

			$this->say('');
			$testNumber	= $this->ask('Type the number of the test  in the list that you want to run...');
			$test = $tests[$testNumber];
		}

		$pathToTestFile = 'tests/' . $suite . '/' . $test;

		$this->taskCodecept()
			->test($pathToTestFile)
			->arg('--steps')
			->arg('--debug')
			->run()
			->stopOnFail();
	}

	/**
	 * Creates a testing Joomla site for running the tests (use it before run:test)
	 *
	 * @param   bool  $use_htaccess  (1/0) Rename and enable embedded Joomla .htaccess file
	 */
	public function createTestingSite($use_htaccess = false)
	{
		if (!empty($this->configuration->skipClone))
		{
			$this->say('Reusing Joomla CMS site already present at ' . $this->cmsPath);
			return;
		}

		// Caching cloned installations locally
		if (!is_dir('tests/cache') || (time() - filemtime('tests/cache') > 60 * 60 * 24))
		{
			$this->_exec($this->buildGitCloneCommand());
		}

		// Get Joomla Clean Testing sites
		if (is_dir($this->cmsPath))
		{
			try
			{
				$this->taskDeleteDir($this->cmsPath)->run();
			}
			catch (Exception $e)
			{
				// Sorry, we tried :(
				$this->say('Sorry, you will have to delete ' . $this->cmsPath . ' manually. ');
				exit(1);
			}
		}

		$this->_copyDir('tests/cache', $this->cmsPath);
		$this->say('Joomla CMS site created at ' . $this->cmsPath);

		// Optionally uses Joomla default htaccess file. Used by TravisCI
		if ($use_htaccess == true)
		{
			$this->_copy('/tests/joomla-cms3/htaccess.txt', 'tests/joomla-cms3/.htaccess');
			$this->_exec('sed -e "s,# RewriteBase /,RewriteBase /tests/joomla-cms3/,g" --in-place tests/joomla-cms3/.htaccess');
		}
	}

	/**
	 * Get (optional) configuration from an external file
	 *
	 * @return \stdClass|null
	 */
	public function getConfiguration()
	{
		$configurationFile = __DIR__ . '/RoboFile.ini';

		if (!file_exists($configurationFile))
		{
			$this->say("No local configuration file");
			return null;
		}

		$configuration = parse_ini_file($configurationFile);
		if ($configuration === false)
		{
			$this->say('Local configuration file is empty or wrong (check is it in correct .ini format');
			return null;
		}

		return json_decode(json_encode($configuration));
	}

	/**
	 * Build correct git clone command according to local configuration and OS
	 *
	 * @return string
	 */
	private function buildGitCloneCommand()
	{
		$branch = empty($this->configuration->branch) ? 'staging' : $this->configuration->branch;
		$insecure = $this->isWindows() ? ' --insecure' : '';

		return "git" . $this->executableExtension . " clone -b $branch $insecure --single-branch --depth 1 https://github.com/joomla/joomla-cms.git tests/cache";
	}

	/**
	 * Check if local OS is Windows
	 *
	 * @return bool
	 */
	private function isWindows()
	{
		return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}

	/**
	 * Get the correct CMS root path
	 *
	 * @return string
	 */
	private function getCmsPath()
	{
		if (empty($this->configuration->cmsPath))
		{
			return 'tests/joomla-cms3';
		}

		if (!file_exists(dirname($this->configuration->cmsPath)))
		{
			$this->say("Cms path written in local configuration does not exists or is not readable");
			return 'tests/joomla-cms3';
		}

		return $this->configuration->cmsPath;
	}

	/**
	 * Runs Selenium Standalone Server.
	 *
	 * @return void
	 */
	public function runSelenium()
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
		{
			$this->_exec("vendor/bin/selenium-server-standalone >> selenium.log 2>&1 &");
		}
		else
		{
			$this->_exec("START java.exe -jar .\\vendor\\joomla-projects\\selenium-server-standalone\\bin\\selenium-server-standalone.jar");
		}

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
	}

	/**
	 * Downloads Composer
	 *
	 * @return void
	 */
	private function getComposer()
	{
		// Make sure we have Composer
		if (!file_exists('./composer.phar'))
		{
			$this->_exec('curl --retry 3 --retry-delay 5 -sS https://getcomposer.org/installer | php');
		}
	}

	/**
	 * Kills the selenium server running
	 *
	 * @param   string  $host  Web host of the remote server.
	 * @param   string  $port  Server port.
	 */
	public function killSelenium($host = 'localhost', $port = '4444')
	{
		$this->say('Trying to kill the selenium server.');
		$this->_exec("curl http://$host:$port/selenium-server/driver/?cmd=shutDownSeleniumServer");
	}
}
