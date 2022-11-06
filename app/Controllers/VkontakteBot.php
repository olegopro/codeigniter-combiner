<?php

namespace App\Controllers;

use App\Libraries\GoLoginProfile;
use App\Libraries\ProxyTask;
use App\Models\VkontakteBotModel;
use App\Projects\MailRu\Tasks\RegisterAccount;
use App\Projects\Vkontakte\Pages\LoginPage;
use CodeIgniter\RESTful\ResourceController;
use Config\Database;
use Parallel\Parallel;
use Parallel\Storage\ApcuStorage;

class VkontakteBot extends ResourceController
{

	public function __construct()
	{
		$this->model = model('App\Models\VkontakteBotModel');
	}

	public function index() {}

	public function createTask()
	{
		$account_name = $this->request->getVar('account');
		$account_id = $this->model->accountIdByName($account_name);

		$count = $this->request->getVar('count');
		$action_type = $this->request->getVar('action');
		$status = $this->request->getVar('status');

		$insertID = $this->model->newTask($account_id, $count, $action_type, $status);
		$task_data = $this->model->taskById($insertID);

		return $this->respondCreated([...$task_data, 'account_name' => $account_name]);
	}

	public function addAccount()
	{
		$account_name = $this->request->getVar('login');
		$account_password = $this->request->getVar('password');
		// $account_proxy = $this->request->getVar('password');

		$insertID = $this->model->newAccount($account_name, $account_password);
		$account_data = $this->model->accountById($insertID);

		return $this->respondCreated($account_data);

	}

	public function getAllAccounts()
	{
		$accounts = $this->model->allAccounts();

		return $this->respond($accounts);
	}

	public function getAllTasks()
	{
		$tasks = $this->model->allTasks();

		return $this->respond($tasks);
	}

	public function runTask()
	{
		while (ob_get_level() > 0) {
			ob_end_clean();
		}

		$activeTask = $this->getActiveTask();
		$this->setTaskStatus($activeTask->task_id, 'pending');

		$GoLogin = new GoLoginProfile;

		$proxyData = (new ProxyTask)->setProxy([
			'task_proxy_ip' => ''
		]);

		$profile_id = $GoLogin->createProfile($proxyData);

		echo 'profile id = ' . $profile_id . PHP_EOL;
		$profile = $GoLogin->gl->getProfile($profile_id);
		echo 'new profile name = ' . $profile->name . PHP_EOL;

		$orbita = $GoLogin->setOrbitaBrowser($profile_id);
		$debugger_address = $orbita->start();

		$driver = $GoLogin->runOrbitaBrowser($debugger_address);
		$createAccount = new RegisterAccount($driver);

		$loginPage = new LoginPage($driver);

		$loginPage->openLoginPage('https://vk.com')
				  ->login('username', 'password')
				  ->likePosts();

		$driver->close();
		$orbita->stop();

	}

	public function getActiveTask()
	{

		return $this->model->activeTask();
	}

	public function setTaskStatus($taskID, $status)
	{
		$this->model->changeTaskStatus($taskID, $status);
	}

}
