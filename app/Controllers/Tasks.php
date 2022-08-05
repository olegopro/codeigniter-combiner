<?php

namespace App\Controllers;

use App\Models\TasksModel;
use CodeIgniter\RESTful\ResourceController;

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

	public function new()
	{
		//
	}

	public function create()
	{
		$rules = [
			'fio'       => 'required',
			'telephone' => 'required',
			'sum'       => 'required',
			'status'    => 'required'
		];

		if (!$this->validate($rules)) {
			return $this->fail($this->validator->getErrors());
		} else {
			$data = [
				'task_fio'       => $this->request->getVar('fio'),
				'task_telephone' => $this->request->getVar('telephone'),
				'task_summa'     => $this->request->getVar('sum'),
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
