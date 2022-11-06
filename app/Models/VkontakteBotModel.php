<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Database;

class VkontakteBotModel extends Model
{

	public function newTask($account_id, $count, $action_type, $status)
	{
		$this->db->table('vk_bot_tasks')->insert([
			'account_id'  => $account_id,
			'task_type'   => $action_type,
			'task_count'  => $count,
			'task_status' => $status
		]);

		return $this->db->insertID();
	}

	public function newAccount($account_name, $account_password)
	{
		$this->db
			->table('vk_bot_accounts')
			->insert([
				'account_name'     => $account_name,
				'account_password' => $account_password
			]);

		return $this->db->insertID();
	}

	public function allAccounts()
	{
		return $this->db
			->table('vk_bot_accounts')
			->select('id, account_name, created_at')
			->get()
			->getResult();
	}

	public function allTasks()
	{
		return $this->db
			->table('vk_bot_tasks')
			->select('vk_bot_tasks.*, vk_bot_accounts.account_name')
			->join('vk_bot_accounts', 'vk_bot_accounts.id = vk_bot_tasks.account_id')
			->get()
			->getResult();
	}

	public function activeTask()
	{
		return $this->db
			->table('vk_bot_tasks')
			->getWhere('task_status', 'active', 1)
			->getFirstRow();
	}

	public function taskById($id)
	{
		return $this->db
			->table('vk_bot_tasks')
			->getWhere(['task_id' => $id], 1)
			->getFirstRow('array');
	}

	public function changeTaskStatus($taskID, $status)
	{
		return $this->db
			->table('vk_bot_tasks')
			->where('task_id', $taskID)
			->set('task_status', $status)
			->update();
	}

	public function accountById($id)
	{
		return $this->db
			->table('vk_bot_accounts')
			->getWhere(['id' => $id], 1)
			->getFirstRow('array');
	}

	public function accountNameById($account_id) {}

	public function accountIdByName($account_name)
	{
		return $this->db
			->table('vk_bot_accounts')
			->select('id')
			->getWhere(['account_name' => $account_name], 1)
			->getRow()
			->id;
	}

}
