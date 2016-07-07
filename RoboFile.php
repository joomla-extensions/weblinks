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

if (!defined('JPATH_BASE'))
{
	define('JPATH_BASE', __DIR__);
}

class RoboFile extends \Robo\Tasks
{
	// Load tasks from composer, see composer.json
	use \joomla_projects\robo\loadTasks;
	use \Joomla\Jorobo\Tasks\loadTasks;

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

		// Set default timezone (so no warnings are generated if it is not set)
		date_default_timezone_set('UTC');
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
			// check wehter git.exe or git as command should be used,
			// as on window both is possible
			if(!$this->_exec('git.exe --version')->getMessage())
			{
				return '';
			}
			else
			{
				return '.exe';
			}
		}
		return '';
	}

	/**
	 * Updates the Extension in the JoomlaCMS testing installation for manual tests
	 *
	 * @param   int     $use_htaccess    (1/0) Rename and enable embedded Joomla .htaccess file
	 * @param   string  $package_method  Optional. Availabe methods are "phing" or "gulp". "gulp" by default
	 *
	 * @return void
	 */
	public function runTestUpdate()
	{
		$this->runSelenium();

		$this->taskWaitForSeleniumStandaloneServer()
			->run()
			->stopOnFail();

		// Make sure to Run the Build Command to Generate AcceptanceTester
		$this->_exec($this->isWindows() ? 'vendor\bin\codecept.bat build' : 'php vendor/bin/codecept build');

		$this->taskCodecept()
			->arg('--steps')
			->arg('--debug')
			->arg('--tap')
			->arg('--fail-fast')
			->arg('tests/acceptance/update/')
			->run()
			->stopOnFail();
	}

	/**
	 * Executes all the Selenium System Tests in a suite on your machine
	 *
	 * @param   array $opts Array of configuration options:
	 *          - 'use-htaccess': renames and enable embedded Joomla .htaccess file
	 *          - 'env': set a specific environment to get configuration from
	 * @return mixed
	 */
	public function runTests($opts = ['use-htaccess' => false, 'env' => 'desktop'])
	{
		$this->createTestingSite($opts['use-htaccess']);

		$this->getComposer();

		$this->taskComposerInstall()->run();

		$this->runSelenium();

		// Make sure to run the build command to generate AcceptanceTester
		$this->_exec($this->isWindows() ? 'vendor\bin\codecept.bat build' : 'php vendor/bin/codecept build');

		$this->taskCodecept()
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->arg('--env ' . $opts['env'])
			->arg('tests/acceptance/install/')
			->run()
			->stopOnFail();

		$this->taskCodecept()
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->arg('--env ' . $opts['env'])
			->arg('tests/acceptance/administrator/')
			->run()
			->stopOnFail();

		$this->taskCodecept()
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->arg('--env ' . $opts['env'])
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

		//loading the class to display the methods in the class
		require 'tests/' . $suite . '/' . $test;

		//logic to fetch the class name from the file name
		$fileName = explode("/", $test);
		$className = explode(".", $fileName[1]);

		//if the selected file is cest only than we will give the option to execute individual methods, we don't need this in cept file
		$i = 1;
		if (strripos($className[0], 'cest'))
		{
			$class_methods = get_class_methods($className[0]);
			$this->say('[' . $i . '] ' . 'All');
			$methods[$i] = 'All';
			$i++;
			foreach ($class_methods as $method_name)
			{

				$reflect = new ReflectionMethod($className[0], $method_name);
				if(!$reflect->isConstructor())
				{
					if ($reflect->isPublic())
					{
						$this->say('[' . $i . '] ' . $method_name);
						$methods[$i] = $method_name;
						$i++;
					}
				}
			}
			$this->say('');
			$methodNumber = $this->ask('Please choose the method in the test that you would want to run...');
			$method = $methods[$methodNumber];
		}

		if(isset($method) && $method != 'All')
		{
			$pathToTestFile = $pathToTestFile . ':' . $method;
		}

		$this->taskCodecept()
			->test($pathToTestFile)
			->arg('--steps')
			->arg('--debug')
			->run()
			->stopOnFail();
	}

	/**
	 * Run the specified checker tool. Valid options are phpmd, phpcs, phpcpd
	 *
	 * @param string $tool
	 *
	 * @return bool
	 */
	public function runChecker($tool = null)
	{
		if ($tool === null) {
			$this->say('You have to specify a tool name as argument. Valid tools are phpmd, phpcs, phpcpd.');
			return false;
		}

		if (!in_array($tool, array('phpmd', 'phpcs', 'phpcpd') )) {
			$this->say('The tool you required is not known. Valid tools are phpmd, phpcs, phpcpd.');
			return false;
		}

		switch ($tool) {
			case 'phpmd':
				return $this->runPhpmd();

			case 'phpcs':
				return $this->runPhpcs();

			case 'phpcpd':
				return $this->runPhpcpd();
		}
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
			if (file_exists('tests/cache'))
			{
				$this->taskDeleteDir('tests/cache')->run();
			}

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

		// Optionally change owner to fix permissions issues
		if (!empty($this->configuration->localUser) && !$this->isWindows())
		{
			$this->_exec('chown -R ' . $this->configuration->localUser . ' ' . $this->cmsPath);
		}

		// Copy current package
		if (!file_exists('dist/pkg-weblinks-current.zip'))
		{
			$this->build(true);
		}

		$this->_copy('dist/pkg-weblinks-current.zip', $this->cmsPath . "/pkg-weblinks-current.zip");

		$this->say('Joomla CMS site created at ' . $this->cmsPath);

		// Optionally uses Joomla default htaccess file. Used by TravisCI
		if ($use_htaccess == true)
		{
			$this->_copy('./tests/joomla-cms3/htaccess.txt', './tests/joomla-cms3/.htaccess');
			$this->_exec('sed -e "s,# RewriteBase /,RewriteBase /tests/joomla-cms3/,g" -in-place tests/joomla-cms3/.htaccess');
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

		return "git" . $this->executableExtension . " clone -b $branch --single-branch --depth 1 https://github.com/joomla/joomla-cms.git tests/cache";
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
			$insecure = $this->isWindows() ? ' --insecure' : '';
			$this->_exec('curl ' . $insecure . ' --retry 3 --retry-delay 5 -sS https://getcomposer.org/installer | php');
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

	/**
	 * Run the phpmd tool
	 */
	private function runPhpmd()
	{
		return $this->_exec('phpmd' . $this->extension . ' ' . __DIR__ . '/src xml cleancode,codesize,controversial,design,naming,unusedcode');
	}

	/**
	 * Run the phpcs tool
	 */
	private function runPhpcs()
	{
		$this->_exec('phpcs' . $this->extension . ' ' . __DIR__ . '/src');
	}

	/**
	 * Run the phpcpd tool
	 */
	private function runPhpcpd()
	{
		$this->_exec('phpcpd' . $this->extension . ' ' . __DIR__ . '/src');
	}

	/**
	 * Build the joomla extension package
	 *
	 * @param   array  $params  Additional params
	 *
	 * @return  void
	 */
	public function build($params = ['dev' => false])
	{
		if (!file_exists('jorobo.ini'))
		{
			$this->_copy('jorobo.dist.ini', 'jorobo.ini');
		}

		$this->taskBuild($params)->run();
	}

	/**
	 * Executes all unit tests
	 */
	public function runUnit()
	{
		$this->createTestingSite();
		$this->getComposer();
		$this->taskComposerInstall()->run();

		// Make sure to run the build command to generate AcceptanceTester
		$this->_exec($this->isWindows() ? 'vendor\bin\codecept.bat build' : 'php vendor/bin/codecept build');

		$this->taskCodecept()
			->suite('unit')
			->run()
			->stopOnFail();
	}
}
