<?php

namespace App\Libraries;

use App\GoLogin;
use Exception;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverDimension;

class GoLoginProfile
{

	public GoLogin $gl;

	public function __construct()
	{

		if (strtolower(PHP_OS) == 'linux') {
			putenv("WEBDRIVER_CHROME_DRIVER=./chromedriver");
		} elseif (strtolower(PHP_OS) == 'darwin') {
			putenv("WEBDRIVER_CHROME_DRIVER=/Users/evilgazz/Downloads/chromedriver109");
		} elseif (strtolower(PHP_OS) == 'winnt') {
			putenv("WEBDRIVER_CHROME_DRIVER=chromedriver.exe");
		}

		$this->gl = new GoLogin([
			'token' => $_ENV['TOKEN']
		]);

	}

	public function createProfile($proxyData = [])
	{
		try {
			return
				$profile_id = $this->gl->create([
						'name'      => 'profile_mac',
						'os'        => 'mac',
						'navigator' => [
							'language'   => 'ru-RU,en-US',
							'userAgent'  => 'random',
							'resolution' => 'random',
							'platform'   => 'mac'
						],

                        'proxyEnabled' => true,
                        'proxy'        => [
                            'mode' => 'none',
                            // 'autoProxyRegion' => 'us'
                            // 'host'            => '',
                            // 'port'            => '',
                            // 'username'        => '',
                            // 'password'        => '',
                        ],
                        'webRTC'       => [
                            'mode'    => 'alerted',
                            'enabled' => true
                        ],

                        ...$proxyData
					]
				);
		} catch (Exception $exception) {
			echo $exception->getMessage();
		}

		return null;
	}

	public function setOrbitaBrowser($profile_id)
	{

		return (new GoLogin([
			'token'        => $_ENV['TOKEN'],
			'profile_id'   => $profile_id,
			'port'         => GoLogin::getRandomPort(),
			'extra_params' => ['--lang=ru']
		]));

	}

	public function runOrbitaBrowser($debugger_address)
	{
		var_dump($debugger_address) . PHP_EOL;

		$chromeOptions = new ChromeOptions();
		$chromeOptions->setExperimentalOption('debuggerAddress', $debugger_address);

		$capabilities = DesiredCapabilities::chrome();
		$capabilities->setCapability(ChromeOptions::CAPABILITY_W3C, $chromeOptions);

		$driver = ChromeDriver::start($capabilities);
		$driver->manage()->window()->maximize();

		$getWindowSize = $driver->manage()->window()->getSize();
		$height = $getWindowSize->getHeight();
		$width = $getWindowSize->getWidth();

		$driver->manage()->window()->setSize(new WebDriverDimension($width, $height - rand(40, 120)));

		sleep(1);

		return $driver;
	}
}
