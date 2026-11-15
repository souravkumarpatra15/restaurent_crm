<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Manager override audit — every restricted action a staff member takes
 * with a manager PIN approval is recorded here, so the owner always has
 * a "who authorised what" trail. This is the accountability layer that
 * makes role permissions practical on a busy counter.
 */
class AddOverrideAudit extends Migration
{
    public function up(): void
    {
        if (!$this->db->tableExists('override_audit')) {
            $this->forge->addField([
                'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'restaurant_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'branch_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'user_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'user_name'     => ['type' => 'VARCHAR', 'constraint' => 150],
                'manager_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'manager_name'  => ['type' => 'VARCHAR', 'constraint' => 150],
                'action'        => ['type' => 'VARCHAR', 'constraint' => 60],
                'context'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'created_at'    => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['restaurant_id', 'created_at']);
            $this->forge->createTable('override_audit', true);
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('override_audit')) {
            $this->forge->dropTable('override_audit');
        }
    }
}
