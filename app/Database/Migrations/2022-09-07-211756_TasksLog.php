<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class TasksLog extends Migration
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

			'task_key' => [
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

		$this->forge->addPrimaryKey('task_id')
					->addForeignKey('task_key', 'tasks', 'task_id', 'CASCADE', 'CASCADE');

		$this->forge->createTable('tasks_logs');
	}

	public function down()
	{
		$this->forge->dropTable('tasks_logs');
	}
}
