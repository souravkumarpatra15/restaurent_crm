<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Super-admin feedback forms:
 *  - feedback_forms: the requests the super admin sends to tenants
 *  - feedback_responses: tenant owners' answers (rating + comment),
 *    with an explicit publish consent. Only published, consented
 *    answers show on the public landing page ("What restaurant
 *    owners say"). One response per form per restaurant.
 */
class AddFeedbackForms extends Migration
{
    public function up(): void
    {
        if (!$this->db->tableExists('feedback_forms')) {
            $this->forge->addField([
                'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'title'      => ['type' => 'VARCHAR', 'constraint' => 150],
                'message'    => ['type' => 'TEXT', 'null' => true],
                'is_active'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('feedback_forms', true, ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4']);
        }

        if (!$this->db->tableExists('feedback_responses')) {
            $this->forge->addField([
                'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'form_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'restaurant_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'user_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'rating'          => ['type' => 'TINYINT', 'constraint' => 1],
                'comment'         => ['type' => 'TEXT', 'null' => true],
                'can_publish'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'is_published'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'display_name'    => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
                'restaurant_name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
                'responded_at'    => ['type' => 'DATETIME', 'null' => true],
                'published_at'    => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['form_id', 'restaurant_id']);
            $this->forge->addKey('is_published');
            $this->forge->createTable('feedback_responses', true, ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4']);
        }
    }

    public function down(): void
    {
        // additive only
    }
}
