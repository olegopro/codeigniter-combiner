<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class VkontakteBotTasksLogs extends Migration
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

			'task_id' => [
				'type'       => 'INT',
				'constraint' => 5,
				'unsigned'   => true,
			],

			'log_data' => [
				'type'       => 'VARCHAR',
				'constraint' => 2048,
			],

			'created_at' => [
				'type'    => 'DATETIME',
				'default' => new RawSql('CURRENT_TIMESTAMP'),
			]
		]);

		$this->forge->addPrimaryKey('id')
					->addForeignKey('task_id', 'vk_bot_tasks', 'task_id', 'CASCADE', 'CASCADE');

		$this->forge->createTable('vk_bot_tasks_log');
	}

	public function down()
	{
		$this->forge->dropTable('vk_bot_tasks_log');
	}
}
