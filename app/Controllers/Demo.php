<?php

namespace App\Controllers;

use App\GoLogin;
use App\Models\TasksModel;
use App\Projects\MailRu\Tasks\RegisterAccount;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;
use Exception;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Parallel\Parallel;
use Parallel\Storage\ApcuStorage;
use CodeIgniter\CLI\CLI;
use Throwable;
use TypeError;

error_reporting(E_ALL ^ (E_DEPRECATED));
ini_set("auto_detect_line_endings", true);

class Demo extends Controller
{
	private function getActiveTask()
	{
		$model = new TasksModel();

		return $active_task = $model->where('task_status', 'active')->first();
	}

	public function runTask($profile_id = null)
	{
		while (ob_get_level() > 0) {
			ob_end_clean();
		}

		$model = new TasksModel();
		$activeTask = $this->getActiveTask();

		if (!$activeTask) {
			exit('no tasks to execute' . PHP_EOL);
		}

		$model->where('task_id', $activeTask['task_id'])
			  ->set('task_status', 'pending')
			  ->update();

		$gl = new GoLogin([
			'token' => $_ENV['TOKEN']
		]);

		$profile_id = $gl->create([
				'name'         => 'profile_mac',
				'os'           => 'mac',
				'navigator'    => [
					'language'   => 'ru-RU',
					'userAgent'  => 'random',
					'resolution' => 'random',
					'platform'   => 'mac'
				],
				'proxyEnabled' => true,
				'proxy'        => [
					'mode'            => 'gologin',
					'autoProxyRegion' => 'de'
					//'host'            => '',
					//'port'            => '',
					//'username'        => '',
					//'password'        => '',
				],
				'webRTC'       => [
					'mode'    => 'alerted',
					'enabled' => true
				]
			]
		);

		echo 'profile id=' . $profile_id . PHP_EOL;
		$profile = $gl->getProfile($profile_id);

		echo 'new profile name=' . $profile->name . PHP_EOL;

		$fdout = fopen($profile_id . '.log', 'wb');
		eio_dup2($fdout, STDOUT);
		eio_event_loop();

		$gl = new GoLogin([
			'token'      => $_ENV['TOKEN'],
			'profile_id' => $profile_id,
			'port'       => GoLogin::getRandomPort(),
			//'tmpdir'     => __DIR__ . '/temp',
		]);

		$debugger_address = $gl->start();
		var_dump($debugger_address) . PHP_EOL;

		$chromeOptions = new ChromeOptions();
		$chromeOptions->setExperimentalOption('debuggerAddress', $debugger_address);

		$capabilities = DesiredCapabilities::chrome();
		$capabilities->setCapability(ChromeOptions::CAPABILITY_W3C, $chromeOptions);

		$driver = ChromeDriver::start($capabilities);
		$createAccount = new RegisterAccount($driver);

		$Parallel = new Parallel(new ApcuStorage());
		$Parallel->run('parallels', function () use ($profile_id, $activeTask) {
			$fp = fopen($profile_id . '.log', "r");
			$currentOffset = ftell($fp);

			while (file_exists($profile_id . '.log')) {
				sleep(2);
				fseek($fp, $currentOffset);
				$stringText = fgets($fp);

				if ($stringText) {
					try {
						$db = \Config\Database::connect();
						$builder = $db->table('tasks_logs');

						$builder->insert([
							'task_key' => $activeTask['task_id'],
							'log_data' => $stringText
						]);
					} catch (Exception $exception) {
						echo $exception->getMessage();
					}

					$currentOffset = ftell($fp);
				}
			}

			fclose($fp);
		});

		try {
			$createAccount
				->openMainPage('https://mail.ru')
				->humanSleep(1, 3)
				->goToRegisterPage()
				->waitAfterLoad(5)
				->fillUsername($activeTask['task_firstname'])
				->humanSleep(5, 10)
				->fillLastname($activeTask['task_lastname'])
				->selectDayBirthday($activeTask['task_day'])
				->selectMonthBirthday($activeTask['task_month'])
				->selectYearBirthday($activeTask['task_year'])
				->selectGender('male')
				->fillEmailName($activeTask['task_email'])
				->fillPassword($activeTask['task_password'])
				->fillPasswordConfirm($activeTask['task_password'])
				->fillTelephone()
				->clickCreate();

			$model->where('task_id', $activeTask['task_id'])
				  ->set('task_status', 'done')
				  ->update();
		} catch (Throwable $exception) {
			echo 'ERROR: ' . $exception->getMessage();

			$model->where('task_id', $activeTask['task_id'])
				  ->set('task_status', 'cancelled')
				  ->update();

			$this->deleteLog($profile_id);
		}

		sleep(10);

		$driver->close();
		$gl->stop();

		$this->deleteLog($profile_id);

		fclose($fdout);
	}

	private function deleteLog($profile_id)
	{
		try {
			unlink($profile_id . '.log');
		} catch (Exception $exception) {
			echo 'Ошибка удаления файла';
			echo $exception->getMessage();
		}
	}
}
