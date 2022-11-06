<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class VkontakteBotTasks extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'task_id' => [
				'type'           => 'INT',
				'constraint'     => 5,
				'unsigned'       => true,
				'auto_increment' => true
			],

			'account_id' => [
				'type'       => 'INT',
				'constraint' => 5,
				'unsigned'   => true,
			],

			'task_type' => [
				'type'       => 'VARCHAR',
				'constraint' => 64
			],

			'task_count' => [
				'type'       => 'INT',
				'constraint' => 5,
				'unsigned'   => true,
			],

			'captcha_count' => [
				'type'       => 'INT',
				'constraint' => 5,
				'unsigned'   => true,
			],

			'task_status' => [
				'type'       => 'VARCHAR',
				'constraint' => 64
			],

			'created_at' => [
				'type'    => 'DATETIME',
				'default' => new RawSql('CURRENT_TIMESTAMP'),
			]

		]);

		$this->forge->addPrimaryKey('task_id')
					->addForeignKey('account_id', 'vk_bot_accounts', 'id', 'CASCADE', 'CASCADE');

		$this->forge->createTable('vk_bot_tasks');
	}

	public function down()
	{
		$this->forge->dropTable('vk_bot_tasks');
	}
}
