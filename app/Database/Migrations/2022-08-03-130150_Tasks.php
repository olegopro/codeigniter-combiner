<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Tasks extends Migration
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

			'task_firstname' => [
				'type'       => 'VARCHAR',
				'constraint' => 64
			],

			'task_lastname' => [
				'type'       => 'VARCHAR',
				'constraint' => 64
			],

			'task_day' => [
				'type'       => 'VARCHAR',
				'constraint' => 16
			],

			'task_month' => [
				'type'       => 'VARCHAR',
				'constraint' => 16
			],

			'task_year' => [
				'type'       => 'VARCHAR',
				'constraint' => 16
			],

			'task_email' => [
				'type'       => 'VARCHAR',
				'constraint' => 64
			],

			'task_telephone' => [
				'type'       => 'VARCHAR',
				'constraint' => 32
			],

			'task_password' => [
				'type'       => 'VARCHAR',
				'constraint' => 64
			],

			'task_status' => [
				'type'       => 'VARCHAR',
				'constraint' => 64
			]
		]);

		$this->forge->addPrimaryKey('task_id');
		$this->forge->createTable('tasks');
	}

	public function down()
	{
		$this->forge->dropTable('tasks');
	}
}
