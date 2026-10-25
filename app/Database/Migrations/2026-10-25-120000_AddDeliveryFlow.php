<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeliveryFlow extends Migration
{
    public function up()
    {
        $this->forge->addColumn('orders', [
            'rider_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'waiter_id'],
            'rider_name'      => ['type' => 'VARCHAR', 'constraint' => '120', 'null' => true, 'after' => 'rider_id'],
            'delivery_address'=> ['type' => 'TEXT', 'null' => true, 'after' => 'customer_phone'],
            'delivery_status' => ['type' => 'ENUM', 'constraint' => ['unassigned', 'assigned', 'out_for_delivery', 'delivered', 'failed'], 'null' => true, 'after' => 'delivery_address'],
            'delivered_at'    => ['type' => 'DATETIME', 'null' => true, 'after' => 'delivery_status'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('orders', ['rider_id', 'rider_name', 'delivery_address', 'delivery_status', 'delivered_at']);
    }
}
