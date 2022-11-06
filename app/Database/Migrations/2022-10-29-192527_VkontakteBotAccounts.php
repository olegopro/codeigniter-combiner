<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class VkontakteBotAccounts extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'id' => [
				'type'           => 'INT',
				'constraint'     => 5,
				'unsigned'       => true,
				'auto_increment' => true
			],

			'account_name' => [
				'type'       => 'VARCHAR',
				'constraint' => 30
			],

			'account_password' => [
				'type'       => 'VARCHAR',
				'constraint' => 30
			],

			'proxy_type' => [
				'type'       => 'VARCHAR',
				'constraint' => 120
			],

			'proxy_ip' => [
				'type'       => 'VARCHAR',
				'constraint' => 120
			],

			'proxy_port' => [
				'type'       => 'VARCHAR',
				'constraint' => 120
			],

			'proxy_username' => [
				'type'       => 'VARCHAR',
				'constraint' => 120
			],

			'proxy_password' => [
				'type'       => 'VARCHAR',
				'constraint' => 120
			],

			'created_at' => [
				'type'    => 'DATETIME',
				'default' => new RawSql('CURRENT_TIMESTAMP'),
			]

		]);

		$this->forge->addPrimaryKey('id');
		$this->forge->createTable('vk_bot_accounts');
	}

	public function down()
	{
		$this->forge->dropTable('vk_bot_account');
	}
}
