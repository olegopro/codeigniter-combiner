<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class OAuth2Server extends Migration
{
	public function up(): void
	{
		$this->forge->addField([
			'client_id'     => [
				'type'       => 'VARCHAR',
				'constraint' => 80,
				'null'       => false
			],
			'client_secret' => [
				'type'       => 'VARCHAR',
				'constraint' => 80,
				'null'       => true
			],
			'redirect_uri'  => [
				'type'       => 'VARCHAR',
				'constraint' => 2000,
				'null'       => true
			],
			'grant_types'   => [
				'type'       => 'VARCHAR',
				'constraint' => 80,
				'null'       => true
			],
			'scope'         => [
				'type'       => 'VARCHAR',
				'constraint' => 4000,
				'null'       => true
			],
			'user_id'       => [
				'type'       => 'VARCHAR',
				'constraint' => 80,
				'null'       => true
			]
		]);
		$this->forge->addPrimaryKey('client_id');
		$this->forge->createTable('oauth_clients', true);

		$this->forge->addField([
			'access_token' => [
				'type'       => 'VARCHAR',
				'constraint' => 40,
				'null'       => false
			],
			'client_id'    => [
				'type'       => 'VARCHAR',
				'constraint' => 80,
				'null'       => false
			],
			'user_id'      => [
				'type'       => 'VARCHAR',
				'constraint' => 80,
				'null'       => true
			],
			'expires'      => [
				'type' => 'TIMESTAMP',
				'null' => false
			],
			'scope'        => [
				'type'       => 'VARCHAR',
				'constraint' => 4000,
				'null'       => true
			]
		]);
		$this->forge->addPrimaryKey('access_token');
		$this->forge->createTable('oauth_access_tokens', true);

		$this->forge->addField([
			'authorization_code' => [
				'type'       => 'VARCHAR',
				'constraint' => 40,
				'null'       => false
			],
			'client_id'          => [
				'type'       => 'VARCHAR',
				'constraint' => 80,
				'null'       => false
			],
			'user_id'            => [
				'type'       => 'VARCHAR',
				'constraint' => 80,
				'null'       => true
			],
			'redirect_uri'       => [
				'type'       => 'VARCHAR',
				'constraint' => 2000,
				'null'       => true
			],
			'expires'            => [
				'type' => 'TIMESTAMP',
				'null' => false
			],
			'scope'              => [
				'type'       => 'VARCHAR',
				'constraint' => 4000,
				'null'       => true
			],
			'id_token'           => [
				'type'       => 'VARCHAR',
				'constraint' => 1000,
				'null'       => true
			]
		]);
		$this->forge->addPrimaryKey('authorization_code');
		$this->forge->createTable('oauth_authorization_codes', true);

		$this->forge->addField([
			'refresh_token' => [
				'type'       => 'VARCHAR',
				'constraint' => 40,
				'null'       => false
			],
			'client_id'     => [
				'type'       => 'VARCHAR',
				'constraint' => 40,
				'null'       => false
			],
			'user_id'       => [
				'type'       => 'VARCHAR',
				'constraint' => 40,
				'null'       => true
			],
			'expires'       => [
				'type' => 'TIMESTAMP',
				'null' => false
			],
			'scope'         => [
				'type'       => 'VARCHAR',
				'constraint' => 4000,
				'null'       => true
			]
		]);
		$this->forge->addPrimaryKey('refresh_token');
		$this->forge->createTable('oauth_refresh_tokens', true);

		$this->forge->addField([
			'username'       => [
				'type'       => 'VARCHAR',
				'constraint' => 80,
			],
			'password'       => [
				'type'       => 'VARCHAR',
				'constraint' => 80,
				'null'       => true
			],
			'first_name'     => [
				'type'       => 'VARCHAR',
				'constraint' => 80,
				'null'       => true
			],
			'last_name'      => [
				'type'       => 'VARCHAR',
				'constraint' => 80,
				'null'       => true
			],
			'email'          => [
				'type'       => 'VARCHAR',
				'constraint' => 80,
				'null'       => true
			],
			'email_verified' => [
				'type' => 'BOOLEAN',
				'null' => true
			],
			'scope'          => [
				'type'       => 'VARCHAR',
				'constraint' => 4000,
				'null'       => true
			]
		]);
		$this->forge->addPrimaryKey('username');
		$this->forge->createTable('oauth_users', true);

		$this->forge->addField([
			'scope'      => [
				'type'       => 'VARCHAR',
				'constraint' => 80,
				'null'       => false
			],
			'is_default' => [
				'type' => 'BOOLEAN',
				'null' => true
			]
		]);
		$this->forge->addPrimaryKey('scope');
		$this->forge->createTable('oauth_scopes', true);

		$this->forge->addField([
			'client_id'  => [
				'type'       => 'VARCHAR',
				'constraint' => 80,
				'null'       => false
			],
			'subject'    => [
				'type'       => 'VARCHAR',
				'constraint' => 80,
				'null'       => true
			],
			'public_key' => [
				'type'       => 'VARCHAR',
				'constraint' => 2000,
				'null'       => false
			],
		]);
		$this->forge->createTable('oauth_jwt', true);
	}

	public function down(): void
	{
		$this->forge->dropTable('oauth_clients', true);
		$this->forge->dropTable('oauth_access_tokens', true);
		$this->forge->dropTable('oauth_authorization_codes', true);
		$this->forge->dropTable('oauth_refresh_tokens', true);
		$this->forge->dropTable('oauth_users', true);
		$this->forge->dropTable('oauth_scopes', true);
		$this->forge->dropTable('oauth_jwt', true);
	}
}
