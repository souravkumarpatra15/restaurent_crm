<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * DinoviX two-stream delivery model.
 *
 *  - riders.employer_type        'restaurant' (own staff rider — restaurant
 *                                sets their commission) vs 'dinovix' (fleet
 *                                rider — DinoviX pays the payout and books
 *                                its commission).
 *  - orders.delivery_mode/_km/_lat/_lng — which stream delivered the order
 *                                plus the distance the fees are computed on.
 *  - dinovix_fees                platform revenue ledger: fleet commission,
 *                                customer delivery fee kept by DinoviX, and
 *                                the ₹ per order fee restaurants pay when
 *                                their OWN riders deliver.
 *  - settings rows are NOT seeded here — FleetSettings carries defaults so
 *                                every restaurant stays flexible.
 */
class AddFleetDelivery extends Migration
{
    public function up(): void
    {
        $db = $this->db;

        if (!$db->fieldExists('employer_type', 'riders')) {
            $this->forge->addColumn('riders', [
                'employer_type' => [
                    'type'    => 'ENUM',
                    'constraint' => ['restaurant', 'dinovix'],
                    'default' => 'restaurant',
                    'after'   => 'branch_id',
                ],
            ]);
        }

        foreach ([
            'delivery_mode' => [
                'type' => 'ENUM', 'constraint' => ['self', 'dinovix'],
                'null' => true, 'after' => 'delivery_status',
            ],
            'delivery_km' => [
                'type' => 'DECIMAL', 'constraint' => '6,2',
                'null' => true, 'after' => 'delivery_mode',
            ],
            'delivery_lat' => [
                'type' => 'DECIMAL', 'constraint' => '10,7',
                'null' => true, 'after' => 'delivery_km',
            ],
            'delivery_lng' => [
                'type' => 'DECIMAL', 'constraint' => '10,7',
                'null' => true, 'after' => 'delivery_lat',
            ],
        ] as $name => $def) {
            if (!$db->fieldExists($name, 'orders')) {
                $this->forge->addColumn('orders', [$name => $def]);
            }
        }

        if (!$db->tableExists('dinovix_fees')) {
            $this->forge->addField([
                'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'order_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'restaurant_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'fee_type'      => ['type' => 'ENUM', 'constraint' => ['fleet_commission', 'delivery_fee', 'own_platform_fee']],
                'amount'        => ['type' => 'DECIMAL', 'constraint' => '10,2'],
                'created_at'    => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['restaurant_id', 'created_at']);
            $this->forge->addUniqueKey(['order_id', 'fee_type']);
            $this->forge->createTable('dinovix_fees', true, ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4']);
        }

        // Duty switch lives on the rider's GPS snapshot row — the Android
        // foreground service reads it on every app launch to restart itself
        if (!$db->fieldExists('duty_on', 'rider_locations')) {
            $this->forge->addColumn('rider_locations', [
                'duty_on' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'accuracy'],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('dinovix_fees')) {
            $this->forge->dropTable('dinovix_fees');
        }
        if ($this->db->fieldExists('delivery_lng', 'orders')) {
            $this->forge->dropColumn('orders', ['delivery_mode', 'delivery_km', 'delivery_lat', 'delivery_lng']);
        }
        if ($this->db->fieldExists('employer_type', 'riders')) {
            $this->forge->dropColumn('riders', ['employer_type']);
        }
    }
}
