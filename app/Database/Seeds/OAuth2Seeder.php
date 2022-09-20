<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class OAuth2Seeder extends Seeder
{
	public function run()
	{
		$data_oauth_users = [
			'username'       => 'evilgazz',
			'password'       => sha1('123456'),
			'first_name'     => 'Oleg',
			'last_name'      => 'Desyatnikov',
			'email'          => 'evilgazz@yandex.ru',
			'email_verified' => '1',
			'scope'          => 'app'
		];

		$this->db->table('oauth_users')->insert($data_oauth_users);

		$data_oauth_clients = [
			'client_id'     => 'testclient',
			'client_secret' => 'testsecret',
			'grant_types'   => 'password',
			'scope'         => 'app',
		];

		$this->db->table('oauth_clients')->insert($data_oauth_clients);
	}
}
