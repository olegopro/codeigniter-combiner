<?php

namespace App\Controllers;

use App\GoLogin;
use App\Models\TasksModel;
use App\Projects\MailRu\Tasks\RegisterAccount;
use CodeIgniter\RESTful\ResourceController;
use Config\Database;
use Exception;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverDimension;
use Nesk\Puphpeteer\Puppeteer;
use Parallel\Parallel;
use Parallel\Storage\ApcuStorage;

class Tasks extends ResourceController
{

	/**
	 * @var TasksModel
	 */
	protected $model = 'App\Models\TasksModel';
	protected $modelName = 'App\Models\TasksModel';
	protected $format = 'JSON';

	public function index()
	{
		//
	}

	public function show($id = null)
	{
		$tasks = $this->model->findAll();

		return $this->respond($tasks);
	}

	public function showById($id = null)
	{
		$task = $this->model->find($id);

		return $this->respond($task);
	}

	public function showTaskLog($id = null)
	{
		try {
			$db = Database::connect();
			$builder = $db->table('tasks_logs');

			$query = $builder->where('task_key', $id)->get()->getResultObject();

			return $this->respond($query);
		} catch (Exception $exception) {
			echo $exception->getMessage();
		}

		//$task = $this->model->find($id);
		//
		//return $this->respond($task);
		return null;
	}

	public function new()
	{
		//
	}

	public function create()
	{
		$rules = [
			'firstname' => 'required',
			'lastname'  => 'required',
			'day'       => 'required',
			'month'     => 'required',
			'year'      => 'required',
			'email'     => 'required',
			'password'  => 'required',
			'status'    => 'required'
		];

		if (!$this->validate($rules)) {
			return $this->fail($this->validator->getErrors());
		} else {
			$data = [
				'task_firstname' => $this->request->getVar('firstname'),
				'task_lastname'  => $this->request->getVar('lastname'),
				'task_day'       => $this->request->getVar('day'),
				'task_month'     => $this->request->getVar('month'),
				'task_year'      => $this->request->getVar('year'),
				'task_email'     => $this->request->getVar('email'),
				'task_password'  => $this->request->getVar('password'),
				'task_status'    => $this->request->getVar('status')
			];

			$task_id = $this->model->insert($data);
			$data['task_id'] = $task_id;

			return $this->respondCreated($data);
		}
	}

	public function edit($id = null)
	{
	}

	public function open($profile_id = null)
	{
		while (ob_get_level() > 0) {
			ob_end_clean();
		}

		system("php " . FCPATH . "index.php " . "Demo runTask '$profile_id'");
	}

	public function update($id = null)
	{
		$data = [
			'task_id'     => $id,
			'task_status' => $this->request->getVar('status'),

		];

		$this->model->save($data);

		return $this->respond($data);
	}

	public function delete($id = null)
	{
		$task = $this->model->find($id);

		if ($task) {
			$this->model->delete($id);

			return $this->respondDeleted($task);
		} else {
			return $this->failNotFound('Элемент не существует');
		}
	}

	public function runTask($profile_id = null)
	{
		while (ob_get_level() > 0) {
			ob_end_clean();
		}

		$activeTask = $this->getActiveTask();

		if (!$activeTask) {
			exit('no tasks to execute' . PHP_EOL);
		}

		$model = new TasksModel();
		$model->where('task_id', $activeTask['task_id'])
			  ->set('task_status', 'pending')
			  ->update();
		//
		$gl = new GoLogin([
			'token' => $_ENV['TOKEN']
		]);

		$profile_id = $gl->create([
				'name'         => 'profile_mac',
				'os'           => 'mac',
				'navigator'    => [
					'language'   => 'ru-RU,en-US',
					'userAgent'  => 'random',
					'resolution' => '1920x1080',
					'platform'   => 'mac'
				],
				'proxyEnabled' => false,
				'proxy'        => [
					'mode'     => 'none',
					// 'autoProxyRegion' => 'de'
					'host'     => '138.128.19.18',
					'port'     => '9109',
					'username' => 'AN4fQ0',
					'password' => 'nBco5L',
				],
			]
		);

		echo 'profile id = ' . $profile_id . PHP_EOL;
		$profile = $gl->getProfile($profile_id);

		echo 'new profile name = ' . $profile->name . PHP_EOL;

		$fdout = fopen($profile_id . '.log', 'wb');
		eio_dup2($fdout, STDOUT);
		eio_event_loop();

		$gl = new GoLogin([
			'token'        => $_ENV['TOKEN'],
			'profile_id'   => $profile_id,
			'port'         => GoLogin::getRandomPort(),
			'extra_params' => ['--lang=ru']
		]);

		if (strtolower(PHP_OS) == 'linux') {
			putenv("WEBDRIVER_CHROME_DRIVER=./chromedriver");
		} elseif (strtolower(PHP_OS) == 'darwin') {
			putenv("WEBDRIVER_CHROME_DRIVER=/Users/evilgazz/Downloads/chromedriver");
		} elseif (strtolower(PHP_OS) == 'winnt') {
			putenv("WEBDRIVER_CHROME_DRIVER=chromedriver.exe");
		}

		$debugger_address = $gl->start();
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
		$createAccount = new RegisterAccount($driver);

		$Parallel = new Parallel(new ApcuStorage());
		$Parallel->run('parallels', function () use ($profile_id, $activeTask) {
			$fp = fopen($profile_id . '.log', "r");
			$currentOffset = ftell($fp);

			while (file_exists($profile_id . '.log')) {
				sleep(1);
				fseek($fp, $currentOffset);
				$stringText = fgets($fp);

				if ($stringText) {
					try {
						$db = Database::connect();
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
				->clickCreate()
				->humanSleep(5, 10)
				->setMinimumConfig();

		} catch (Exception $e) {
			echo 'ERROR: ' . $e->getMessage();
			$model->where('task_id', $activeTask['task_id'])
				  ->set('task_status', 'cancelled')
				  ->update();
			$this->deleteLog($profile_id);
		}

		try {
			global $telephone;

			$model->where('task_id', $activeTask['task_id'])
				  ->set('task_telephone', $telephone)
				  ->update();

			$model->where('task_id', $activeTask['task_id'])
				  ->set('task_status', 'done')
				  ->update();

		} catch (Exception $exception) {
			echo $exception->getMessage() . PHP_EOL;
		}

		sleep(600);
		$driver->close();
		$gl->stop();

		$this->deleteLog($profile_id);

		$Parallel->wait();

		fclose($fdout);
	}

	private
	function getActiveTask()
	{
		$model = new TasksModel();

		return $active_task = $model->where('task_status', 'active')->first();
	}

	private
	function deleteLog($profile_id)
	{
		try {
			unlink($profile_id . '.log');
		} catch (Exception $exception) {
			echo 'Ошибка удаления файла' . PHP_EOL;
			echo $exception->getMessage() . PHP_EOL;
		}
	}
}
