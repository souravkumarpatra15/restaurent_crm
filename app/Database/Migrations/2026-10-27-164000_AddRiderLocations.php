<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Rider GPS breadcrumbs for the mobile driver app.
 * One latest row per rider (unique user_id) — the driver app pings
 * its position while on duty so the delivery board knows where each
 * rider last reported. Designed to stay tiny: it is an upsert table,
 * never a history log.
 */
class AddRiderLocations extends Migration
{
    public function up(): void
    {
        if (!$this->db->tableExists('rider_locations')) {
            $this->forge->addField([
                'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'user_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'restaurant_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'branch_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'lat'           => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
                'lng'           => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
                'accuracy'      => ['type' => 'FLOAT', 'null' => true],
                'created_at'    => ['type' => 'DATETIME', 'null' => true],
                'updated_at'    => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['user_id']);
            $this->forge->addKey(['restaurant_id', 'branch_id']);
            $this->forge->createTable('rider_locations', true, ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4']);
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('rider_locations')) {
            $this->forge->dropTable('rider_locations');
        }
    }
}
