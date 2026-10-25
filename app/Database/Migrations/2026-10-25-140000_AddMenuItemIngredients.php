<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Recipe/BOM mapping — how much of each inventory item a menu item
 * consumes. Used for automatic stock deduction on every sale (and
 * restock when an order is cancelled).
 */
class AddMenuItemIngredients extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('menu_item_ingredients')) {
            return;
        }

        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'menu_item_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'inventory_item_id'=> ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'quantity'         => ['type' => 'DECIMAL', 'constraint' => '10,3', 'default' => 0],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['menu_item_id', 'inventory_item_id']);
        $this->forge->createTable('menu_item_ingredients', true, [
            'ENGINE'          => 'InnoDB',
            'DEFAULT CHARSET' => 'utf8mb4',
        ]);
    }

    public function down(): void
    {
        if ($this->db->tableExists('menu_item_ingredients')) {
            $this->forge->dropTable('menu_item_ingredients');
        }
    }
}
