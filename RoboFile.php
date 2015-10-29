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
	use \JBuild\Tasks\loadTasks;

	private $extension = '';

	private $configuration = array();

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
		$this->configuration = $this->getConfiguration();

		$this->setExecExtension();

		$this->createTestingSite();

		$this->getComposer();

		$this->taskComposerInstall()->run();

		$this->runSelenium();

		$this->_exec('php' . $this->extension . ' vendor/bin/codecept build');

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
	public function runTest($pathToTestFile = null, $suite = 'acceptance')
	{
		$this->runSelenium();

		// Make sure to Run the Build Command to Generate AcceptanceTester
		$this->_exec("php vendor/bin/codecept build");

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

		// Kill selenium server
		// $this->_exec('curl http://localhost:4444/selenium-server/driver/?cmd=shutDownSeleniumServer');
	}

	/**
	 * Creates a testing Joomla site for running the tests (use it before run:test)
	 */
	public function createTestingSite()
	{
		if (!empty($this->configuration->skipClone)) {
			$this->say('Reusing Joomla CMS site already present at tests/joomla-cms3');
			return;
		}

		// Get Joomla Clean Testing sites
		if (is_dir('tests/joomla-cms3'))
		{
			$this->taskDeleteDir('tests/joomla-cms3')->run();
		}

		$this->_exec('git' . $this->extension . ' clone -b staging --single-branch --depth 1 https://github.com/joomla/joomla-cms.git tests/weblinksnew');
		$this->say('Joomla CMS site created at tests/joomla-cms3');
	}

	/**
	 * Get (optional) configuration from an external file
	 *
	 * @return \stdClass|null
	 */
	public function getConfiguration()
	{
		$configurationFile = __DIR__ . '/RoboFile.ini';

		if (!file_exists($configurationFile)) {
			$this->say("No local configuration file");
			return null;
		}

		$configuration = parse_ini_file($configurationFile);
		if ($configuration === false) {
			$this->say('Local configuration file is empty or wrong (check is it in correct .ini format');
			return null;
		}

		return json_decode(json_encode($configuration));
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
	 * Build the joomla extension package
	 *
	 * @param   array  $params  Additional params
	 *
	 * @return  void
	 */
	public function build($params = ['dev' => false])
	{
		$this->taskBuild($params)->run();
	}

	/**
	 * Tags and Creates a new release in Github
	 */
	public function release()
	{
		$bump = $this->confirm('Have you already bumped the extension version', false);

		if (!$bump)
		{
			$this->yell('please bump the extension version of the XML manifest before running this function');
			exit(1);
		}

		$this->buildPackage('extension_packager.xml'); //TODO-Niels what is when we only have a component without package

		$remote = $this->askDefault("What is the git remote where you want to do the release?", 'origin');

		$version = $this->getExtensionVersion();
		$this->changelogUpdate();
		$this->taskGitStack()
			->add('CHANGELOG.md')
			->commit("Prepare for release version $version")
			->push($remote,'develop') //TODO-Niels develop or master or what
			->run();

		$this->say("Creating github tag: $version");
		$githubRepository = $this->getGithubRepo();
		$githubToken = $this->getGithubToken();

		$this->taskGitStack()
			->stopOnFail()
			->tag($version)
			->push($remote, $version)
			->run();
		$this->say("Tag created: $version and published at $githubRepository->owner/$githubRepository->name");

		$this->say("Creating the release at: https://github.com/$githubRepository->owner/$githubRepository->name/releases/tag/$version");
		$github = $this->getGithub();
		$changesInRelease = "# Changelog: \n\n" . implode("\n* ", $this->changelogGetPullsInLatestRelease());
		$response = $github->repositories->releases->create(
			$githubRepository->owner,
			$githubRepository->name,
			(string) $version,
			'',
			"redSHOPB $version", //TODO-Niels best way
			$changesInRelease,
			false,
			true
		);

		$this->say("Uploading the Extension package to the Github release: $version");
		$uploadUrl = str_replace("{?name}", "?access_token=$githubToken&name=redslider-v${version}_fullpackage-unzipfirst.zip", $response->upload_url); //TODO-Niels redslider

		$http    = new Http();
		$data    = array("file" => "@.dist/redslider-v${version}_fullpackage-unzipfirst.zip"); //TODO-Niels redslider
		$headers = array("Content-Type" => "application/zip");
		$http->post($uploadUrl, $data, $headers);
	}

	private function changelogGetPullsInLatestRelease()
	{
		$github           = $this->getGithub();

		$latestRelease = $github->repositories->releases->get(
			$this->getGithubRepo()->owner,
			$this->getGithubRepo()->name,
			'latest'
		);

		$pulls = $this->getAllRepoPulls();


		$changes = array();

		foreach ($pulls as $pull)
		{
			if (strtotime($pull->merged_at) > strtotime($latestRelease->published_at))
			{
				$changes[] = $pull->title;
			}
		}

		return $changes;
	}

	/**
	 * @param   string  $release1  You can use Release Tag, for example tags/2.0.24. Or use Release Id, for example: 1643513
	 * @param   string  $release2
	 *
	 * @return array
	 */
	public function changelogGetPullsBetweenTwoVersions($release1, $release2)
	{
		$github           = $this->getGithub();
		$githubRepository = $this->getGithubRepo();

		$release1 = $github->repositories->releases->get($githubRepository->owner, $githubRepository->name, $release1);
		$release2 = $github->repositories->releases->get($githubRepository->owner, $githubRepository->name, $release2);
		$pulls = $this->getAllRepoPulls();

		$changes = array();

		foreach ($pulls as $pull)
		{
			if (
				(strtotime($pull->merged_at) > strtotime($release1->published_at))
				&& strtotime($pull->merged_at) < strtotime($release2->published_at)
			)
			{
				$changes[] = $pull->title;
			}
		}

		return $changes;
	}

	/**
	 * Updates changelog with the changes since the last release
	 */
	public function changelogUpdate()
	{
		$version = $this->getExtensionVersion();

		$changes = $this->changelogGetPullsInLatestRelease();

		if (!empty($changes))
		{
			$this->taskChangelog()
				->changes($changes)
				->version($version)
				->run();
		}
	}

	/**
	 * Creates the full Changelog file
	 */
	public function changelogCreate()
	{
		$github           = $this->getGithub();
		$githubRepository = $this->getGithubRepo();

		$releases = array_values($github->repositories->releases->getList($githubRepository->owner, $githubRepository->name));

		for ($i = 0, $j = count($releases);$i<$j;$i++)
		{
			if(!array_key_exists($i+1, $releases))
			{
				break;
			}

			$version = $releases[$i]->tag_name;
			$tag = 'tags/' . $releases[$i]->tag_name;
			$previousTag = 'tags/' . $releases[$i+1]->tag_name;

			$changes = $this->changelogGetPullsBetweenTwoVersions($previousTag,$tag);

			if ($changes)
			{
				$this->taskChangelog()
					->changes($this->changelogGetPullsBetweenTwoVersions($previousTag,$tag))
					->version($version)
					->run();
			}
		}
	}

	private function getGithub()
	{
		$githubToken = $this->getGithubToken();

		$options = new Registry;
		$options->set('api.url', 'https://api.github.com');
		$options->set('gh.token', (string) $githubToken);

		return new Github($options);
	}

	private function getGithubRepo()
	{
		if (!isset($this->githubRepository))
		{
			$this->githubRepository = new stdClass;
			$this->githubRepository->owner = $this->askDefault("What is the reporitory user?", 'redCOMPONENT-COM'); //TODO-Niels default
			$this->githubRepository->name = $this->askDefault("What is the reporitory project?", 'redSHOPB2B'); //TODO-Niels default
		}

		return $this->githubRepository;
	}

	private function getExtensionVersion()
	{
		if (!isset($this->extensionVersion))
		{
			$componentManifest      = simplexml_load_file('redshopb.xml'); //TODO-Niels config?
			$this->extensionVersion = $componentManifest->version;
		}

		return $this->extensionVersion;
	}

	private function getGithubToken()
	{
		if (!isset($this->githubToken))
		{
			$this->githubToken = $this->askHidden("What is your Github Auth token? get it at https://github.com/settings/tokens");
		}

		return $this->githubToken;
	}

	/**
	 * Packages the extension to .dist/redshopb-version.zip
	 *
	 * @param $buildFile Name of the XML build PHING file
	 */
	public function buildPackage($buildFile)
	{
		$this->_exec("vendor/bin/phing -f $buildFile autopack");
	}

	private function getAllRepoPulls($state = 'closed')
	{
		$github = $this->getGithub();

		if (!isset($this->allClosedPulls))
		{
			$this->allClosedPulls = $github->pulls->getList($this->getGithubRepo()->owner, $this->getGithubRepo()->name, $state);
		}

		return $this->allClosedPulls;
	}
}
