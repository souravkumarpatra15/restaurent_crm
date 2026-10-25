<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShifts extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'restaurant_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'branch_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'opening_cash'   => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'cash_sales'     => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'total_sales'    => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'expected_cash'  => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'counted_cash'   => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'difference'     => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'orders_count'   => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'status'         => ['type' => 'ENUM', 'constraint' => ['open', 'closed'], 'default' => 'open'],
            'notes'          => ['type' => 'TEXT', 'null' => true],
            'opened_at'      => ['type' => 'DATETIME', 'null' => true],
            'closed_at'      => ['type' => 'DATETIME', 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('branch_id');
        $this->forge->addKey('user_id');
        $this->forge->createTable('shifts', true);
    }

    public function down()
    {
        $this->forge->dropTable('shifts', true);
    }
}
