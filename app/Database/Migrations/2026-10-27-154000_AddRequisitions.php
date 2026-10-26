<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Stock requisitions: staff creates a request for inventory items
 * (e.g. kitchen needs 5 kg paneer), a manager/admin approves or
 * rejects it, and approval is issued — stock is deducted from the
 * requesting branch with a proper inventory ledger entry so
 * purchases/reports stay reconcilable.
 *
 * Lifecycle: pending → approved → issued
 *                    └→ rejected / cancelled (pending only)
 */
class AddRequisitions extends Migration
{
    public function up(): void
    {
        if (!$this->db->tableExists('requisitions')) {
            // counter for reading requisition numbers (mirrors billing_counter)
            $rCols = $this->db->getFieldData('restaurants');
            $hasCounter = false;
            foreach ($rCols as $c) {
                if ($c->name === 'requisition_counter') { $hasCounter = true; break; }
            }
            if (!$hasCounter) {
                $this->forge->addColumn('restaurants', [
                    'requisition_counter' => ['type' => 'INT', 'constraint' => 11, 'default' => 0, 'after' => 'billing_counter'],
                ]);
            }

            $this->forge->addField([
                'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'restaurant_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'branch_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'req_number'     => ['type' => 'VARCHAR', 'constraint' => 24],
                'status'         => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'rejected', 'issued', 'cancelled'], 'default' => 'pending'],
                'requested_by'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'note'           => ['type' => 'TEXT', 'null' => true],
                'reject_reason'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'approved_by'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'approved_at'    => ['type' => 'DATETIME', 'null' => true],
                'issued_by'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'issued_at'      => ['type' => 'DATETIME', 'null' => true],
                'created_at'     => ['type' => 'DATETIME', 'null' => true],
                'updated_at'     => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['restaurant_id', 'req_number']);
            $this->forge->addKey(['restaurant_id', 'branch_id', 'status']);
            $this->forge->createTable('requisitions', true, ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4']);
        }

        if (!$this->db->tableExists('requisition_items')) {
            $this->forge->addField([
                'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'requisition_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'inventory_item_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'qty_requested'     => ['type' => 'DECIMAL', 'constraint' => '10,3'],
                'qty_approved'      => ['type' => 'DECIMAL', 'constraint' => '10,3', 'null' => true],
                'qty_issued'        => ['type' => 'DECIMAL', 'constraint' => '10,3', 'null' => true],
                'unit'              => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
                'created_at'        => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('requisition_id');
            $this->forge->createTable('requisition_items', true, ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4']);
        }
    }

    public function down(): void
    {
        // additive only
    }
}
