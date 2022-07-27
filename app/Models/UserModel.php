<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
	protected $table = 'oauth_users';
	protected $primaryKey = 'username';
	protected $allowedFields = ['username', 'password', 'first_name', 'last_name', 'email'];

	protected $beforeInsert = ['beforeInsert'];
	protected $beforeUpdate = ['beforeUpdate'];

	protected function beforeInsert(array $data)
	{
		return $this->hashPassword($data);
	}

	protected function beforeUpdate(array $data)
	{
		return $this->hashPassword($data);
	}


	protected function hashPassword(array $data)
	{
		if (isset($data['data']['password'])) {
			$data['data']['password'] = sha1($data['data']['password']);
		}

		return $data;
	}
}
