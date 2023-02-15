<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class WebWalkerLog extends Migration
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

            'key' => [
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
                    ->addForeignKey('key', 'web_walker_tasks', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('web_walker_log');
    }

    public function down()
    {
        $this->forge->dropTable('web_walker_log');
    }
}
