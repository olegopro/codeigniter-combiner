<?php

namespace App\Controllers;

use App\GoLogin;
use App\Libraries\GoLoginProfile;
use App\Libraries\ProxyTask;
use App\Models\MailRegisterModel;
use App\Projects\MailRu\Tasks\RegisterAccount;
use CodeIgniter\RESTful\ResourceController;
use Config\Database;
use Exception;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverDimension;
use Faker\Factory;
use Faker\Generator;
use Faker\Provider\ru_RU\Person;
use Parallel\Parallel;
use Parallel\Storage\ApcuStorage;
use Throwable;

class MailRegister extends ResourceController
{

	/**
	 * @var MailRegisterModel
	 */
	protected $model = 'App\Models\MailRegisterModel';
	protected $modelName = 'App\Models\MailRegisterModel';
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
			// 'proxy'  => 'required',
			'status'    => 'required'
		];

		if (!$this->validate($rules)) {
			return $this->fail($this->validator->getErrors());
		} else {

			$proxy = $this->proxyFormatter($this->request->getVar('proxy'));

			$data = [
				'task_firstname'      => $this->request->getVar('firstname'),
				'task_lastname'       => $this->request->getVar('lastname'),
				'task_day'            => $this->request->getVar('day'),
				'task_month'          => $this->request->getVar('month'),
				'task_year'           => $this->request->getVar('year'),
				'task_email'          => $this->request->getVar('email'),
				'task_proxy_type'     => $proxy[0]['type'] ?? '',
				'task_proxy_username' => $proxy[0]['username'] ?? '',
				'task_proxy_password' => $proxy[0]['password'] ?? '',
				'task_proxy_ip'       => $proxy[0]['ip'] ?? '',
				'task_proxy_port'     => $proxy[0]['port'] ?? '',
				'task_status'         => $this->request->getVar('status')
			];

			$task_id = $this->model->insert($data);
			$data['task_id'] = $task_id;

			return $this->respondCreated($data);
		}
	}

	public function createMulti()
	{
		$rules = [
			'count'  => 'required',
			'status' => 'required'
		];

		$months = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];

		if (!$this->validate($rules)) {
			return $this->fail($this->validator->getErrors());
		} else {

			$faker = Factory::create('ru_RU');

			$proxyList = $this->proxyFormatter($this->request->getVar('proxyList'));

			$count = 0;
			$proxyCount = 0;

			while ($count <= $this->request->getVar('count') - 1) {

				$randomGender = rand(0, 1);
				if ($randomGender === 1) {
					$firstName = $faker->firstNameMale();
					$lastName = $faker->lastName('male');
				} else {
					$firstName = $faker->firstNameFemale();
					$lastName = $faker->lastName('female');
				}

				$data = [
					'task_firstname'      => $firstName,
					'task_lastname'       => $lastName,
					'task_day'            => rand(1, 28),
					'task_month'          => $months[array_rand($months)],
					'task_year'           => rand(1960, 2005),
					'task_email'          => $faker->word() . $faker->word(),
					'task_password'       => 'Pa$$w0rd!1',
					'task_proxy_type'     => $proxyList[$proxyCount]['type'] ?? '',
					'task_proxy_username' => $proxyList[$proxyCount]['username'] ?? '',
					'task_proxy_password' => $proxyList[$proxyCount]['password'] ?? '',
					'task_proxy_ip'       => $proxyList[$proxyCount]['ip'] ?? '',
					'task_proxy_port'     => $proxyList[$proxyCount]['port'] ?? '',
					'task_status'         => $this->request->getVar('status')
				];

				$proxyCount++;
				if ($proxyCount === count($proxyList)) {
					$proxyCount = 0;
				}

				$task_id = $this->model->insert($data);
				$data['task_id'] = $task_id;

				$count++;

			}

			return $this->respondCreated($data);

		}
	}

	public function edit($id = null) {}

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

		$model = new MailRegisterModel();
		$model->where('task_id', $activeTask['task_id'])
			  ->set('task_status', 'pending')
			  ->update();

		$taskData = $model->where('task_id', $activeTask['task_id'])
						  ->first();

		$GoLogin = new GoLoginProfile;
		$proxyData = (new ProxyTask)->setProxy($taskData);
		$profile_id = $GoLogin->createProfile($proxyData);

		$fdout = fopen($profile_id . '.log', 'wb');
		eio_dup2($fdout, STDOUT);
		eio_event_loop();

		echo 'profile id = ' . $profile_id . PHP_EOL;
		$profile = $GoLogin->gl->getProfile($profile_id);

		echo 'new profile name = ' . $profile->name . PHP_EOL;

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

		$orbita = null;
		$debugger_address = null;

		try {
			$orbita = $GoLogin->setOrbitaBrowser($profile_id);
			$debugger_address = $orbita->start();
		} catch (Exception $exception) {
			echo $exception->getMessage() . PHP_EOL;

			$model->where('task_id', $activeTask['task_id'])
				  ->set('task_status', 'cancelled')
				  ->update();
		}

		if ($debugger_address) {

			$driver = $GoLogin->runOrbitaBrowser($debugger_address);
			$createAccount = new RegisterAccount($driver);

			try {
				$createAccount
					->openMainPage('https://mail.ru')
					->humanSleep(1, 3)
					->goToRegisterPage()
					->fillUsername($activeTask['task_firstname'])
					->humanSleep(1, 3)
					->fillLastname($activeTask['task_lastname'])
					->selectMonthBirthday($activeTask['task_month'])
					->selectDayBirthday($activeTask['task_day'])
					->selectYearBirthday($activeTask['task_year'])
					->selectGender('male')
					->fillEmailName($activeTask['task_email'])
					->fillPassword($activeTask['task_password'])
					->fillPasswordConfirm($activeTask['task_password'])
					->fillTelephone()
					->clickCreate()
					->humanSleep(1, 10)
					->setMinimumConfig();

				global $telephone;
				global $mailLogin;

				$model->where('task_id', $activeTask['task_id'])
					  ->set('task_telephone', $telephone)
					  ->update();

				$model->where('task_id', $activeTask['task_id'])
					  ->set('task_email', $mailLogin)
					  ->update();

				$model->where('task_id', $activeTask['task_id'])
					  ->set('task_status', 'done')
					  ->update();

			} catch (Throwable|Exception $e) {
				echo 'ERROR-CODE: ' . $e->getCode() . PHP_EOL;
				echo 'ERROR-FILE: ' . $e->getFile() . PHP_EOL;
				echo 'ERROR-LINE: ' . $e->getLine() . PHP_EOL;
				echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
				// echo 'ERROR-TRACE: ' . $e->getTraceAsString() . PHP_EOL;

				$model->where('task_id', $activeTask['task_id'])
					  ->set('task_status', 'cancelled')
					  ->update();

				$this->deleteLog($profile_id);
			}

			sleep(100);

			$driver->close();
			$orbita->stop();
		}

		sleep(5);

		$GoLogin->gl->delete($profile_id);
		$this->deleteLog($profile_id);

		$Parallel->wait();
		fclose($fdout);
	}

	private function getActiveTask()
	{
		$model = new MailRegisterModel();

		return $active_task = $model->where('task_status', 'active')->first();
	}

	private function deleteLog($profile_id)
	{
		try {
			unlink($profile_id . '.log');
		} catch (Exception $exception) {
			echo 'Ошибка удаления файла' . PHP_EOL;
			echo $exception->getMessage() . PHP_EOL;
		}
	}

	public function proxyFormatter(string $string): array
	{
		$matches = [];
		preg_match_all('~(?P<type>socks[4|5]?|http|https)?:?/?/?(?P<username>\w{1,15})*?:?(?P<password>\w{1,15})*?@?(?P<ip>\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})[: ](?P<port>\K\d*)~', $string, $matches, PREG_SET_ORDER);

		$result = [];
		foreach ($matches as $match) {
			if (($match['type'] == '') || ($match['type'] == 'https')) {
				$match['type'] = 'http';
			}

			$result[] = $match;
		}

		return $result;
	}

}
