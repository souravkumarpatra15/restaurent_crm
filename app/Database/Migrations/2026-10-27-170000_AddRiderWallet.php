<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Rider onboarding & driver commission wallet.
 *
 * riders               — rider profile attached to a delivery-role staff
 *                        user: vehicle, commission rule, active flag.
 * rider_wallet_txns    — money ledger, one row per money event. Two
 *                        independent deltas so a single row can carry
 *                        both sides of the rider's business:
 *                          cod_delta  — +COD collected (rider holds
 *                                       restaurant cash), −cash deposited
 *                          earn_delta — +commission earned,
 *                                       −payout paid out, ±adjust
 *                        Wallet views just SUM the columns; the ledger
 *                        row IS the audit trail.
 */
class AddRiderWallet extends Migration
{
    public function up(): void
    {
        if (!$this->db->tableExists('riders')) {
            $this->forge->addField([
                'id'               => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'user_id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'restaurant_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'branch_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'vehicle_no'       => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
                'commission_type'  => ['type' => 'ENUM', 'constraint' => ['per_order', 'percent'], 'default' => 'per_order'],
                'commission_value' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
                'is_active'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'created_at'       => ['type' => 'DATETIME', 'null' => true],
                'updated_at'       => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['user_id']);
            $this->forge->addKey(['restaurant_id', 'branch_id']);
            $this->forge->createTable('riders', true, ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4']);
        }

        if (!$this->db->tableExists('rider_wallet_txns')) {
            $this->forge->addField([
                'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'rider_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'restaurant_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'branch_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'order_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'txn_type'      => ['type' => 'ENUM', 'constraint' => ['cod_collect', 'cod_deposit', 'commission', 'payout', 'adjust']],
                'cod_delta'     => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
                'earn_delta'    => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
                'notes'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'created_by'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'created_at'    => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['rider_user_id', 'created_at']);
            $this->forge->addKey(['order_id']);
            $this->forge->addKey(['restaurant_id', 'branch_id']);
            $this->forge->createTable('rider_wallet_txns', true, ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4']);
        }
    }

    public function down(): void
    {
        foreach (['rider_wallet_txns', 'riders'] as $t) {
            if ($this->db->tableExists($t)) {
                $this->forge->dropTable($t);
            }
        }
    }
}
