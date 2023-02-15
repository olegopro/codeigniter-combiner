<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class WebWalkerTasks extends Migration
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

            'entry_point' => [
                'type'       => 'VARCHAR',
                'constraint' => 255
            ],

            'internal_transitions' => [
                'type'       => 'INT',
                'constraint' => 3
            ],

            'proxy_type'     => [
                'type'       => 'VARCHAR',
                'constraint' => 10
            ],

            'proxy_username' => [
                'type'       => 'VARCHAR',
                'constraint' => 32
            ],

            'proxy_password' => [
                'type'       => 'VARCHAR',
                'constraint' => 32
            ],

            'proxy_ip'       => [
                'type'       => 'VARCHAR',
                'constraint' => 32
            ],

            'proxy_port'     => [
                'type'       => 'VARCHAR',
                'constraint' => 10
            ],

            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 64
            ]
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('web_walker_tasks');
    }

    public function down()
    {
        $this->forge->dropTable('web_walker_tasks');
    }
}
