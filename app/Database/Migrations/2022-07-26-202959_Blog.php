<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class Blog extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'post_id' => [
				'type'           => 'INT',
				'constraint'     => 5,
				'unsigned'       => true,
				'auto_increment' => true
			],

			'post_title' => [
				'type'       => 'VARCHAR',
				'constraint' => 255
			],

			'post_description' => [
				'type' => 'TEXT',
			],

			'post_featured_image' => [
				'type'       => 'VARCHAR',
				'constraint' => 255
			],

			'post_created_at' => [
				'type'    => 'DATETIME',
				'default' => new RawSql('CURRENT_TIMESTAMP'),
			]
		]);

		$this->forge->addPrimaryKey('post_id');
		$this->forge->createTable('blog');
	}

	public function down()
	{
		$this->forge->dropTable('blog');
	}
}
