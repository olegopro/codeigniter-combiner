<?php

namespace App\Controllers;

use App\GoLogin;
use App\Models\TasksModel;
use CodeIgniter\RESTful\ResourceController;
use Exception;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;

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
			$db = \Config\Database::connect();
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
}
