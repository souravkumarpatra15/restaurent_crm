<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCashCounter extends Migration
{
    public function up()
    {
        // Denomination-wise counting stored on the shift itself
        $this->forge->addColumn('shifts', [
            'opening_denominations' => ['type' => 'JSON', 'null' => true, 'after' => 'opening_cash'],
            'closing_denominations' => ['type' => 'JSON', 'null' => true, 'after' => 'expected_cash'],
        ]);

        // Handover / bank-drop movements from the drawer
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'restaurant_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'branch_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'shift_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            // handover = to manager/cash office, bank_deposit = to bank,
            // safe_drop = to on-premises safe
            'movement_type'  => ['type' => 'ENUM', 'constraint' => ['handover', 'bank_deposit', 'safe_drop'], 'default' => 'handover'],
            'amount'         => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'denominations'  => ['type' => 'JSON', 'null' => true],
            'slip_number'    => ['type' => 'VARCHAR', 'constraint' => '40'],
            'status'         => ['type' => 'ENUM', 'constraint' => ['pending', 'handed', 'deposited', 'cancelled'], 'default' => 'pending'],
            'received_by'    => ['type' => 'VARCHAR', 'constraint' => '120', 'null' => true],
            'reference_no'   => ['type' => 'VARCHAR', 'constraint' => '80', 'null' => true],
            'notes'          => ['type' => 'TEXT', 'null' => true],
            'handed_at'      => ['type' => 'DATETIME', 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('branch_id');
        $this->forge->addKey('shift_id');
        $this->forge->createTable('cash_handovers', true);
    }

    public function down()
    {
        $this->forge->dropTable('cash_handovers', true);
        $this->forge->dropColumn('shifts', ['opening_denominations', 'closing_denominations']);
    }
}
