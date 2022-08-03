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

			'task_fio' => [
				'type'       => 'VARCHAR',
				'constraint' => 64
			],

			'task_telephone' => [
				'type'       => 'VARCHAR',
				'constraint' => 64
			],

			'task_summa' => [
				'type'       => 'VARCHAR',
				'constraint' => 16
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
