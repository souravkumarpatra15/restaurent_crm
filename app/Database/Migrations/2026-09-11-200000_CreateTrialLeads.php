<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTrialLeads extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('trial_leads')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'restaurant_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
            ],
            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'branches' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 1,
            ],
            'plan_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'source' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'website',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'new',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        $this->forge->createTable('trial_leads');
    }

    public function down()
    {
        if ($this->db->tableExists('trial_leads')) {
            $this->forge->dropTable('trial_leads', true);
        }
    }
}
