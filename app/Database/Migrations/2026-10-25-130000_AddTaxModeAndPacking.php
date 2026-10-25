<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * IGST / CESS / packaging-charge support:
 *  - restaurants.tax_mode: intra (CGST+SGST, default) vs inter (single IGST)
 *  - restaurants.packaging_charge: flat add-on for takeaway/delivery orders
 * (menu_items.cess_percent and orders.igst/cess/packing columns already exist)
 */
class AddTaxModeAndPacking extends Migration
{
    public function up(): void
    {
        if (!$this->db->tableExists('restaurants')) {
            return;
        }
        $fields = $this->db->getFieldNames('restaurants');

        if (!in_array('tax_mode', $fields, true)) {
            $this->forge->addColumn('restaurants', [
                'tax_mode' => [
                    'type'       => 'ENUM',
                    'constraint' => ['intra', 'inter'],
                    'default'    => 'intra',
                    'after'      => 'tax_type',
                ],
            ]);
        }

        if (!in_array('packaging_charge', $fields, true)) {
            $this->forge->addColumn('restaurants', [
                'packaging_charge' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '8,2',
                    'default'    => 0,
                    'after'      => 'service_charge_percent',
                ],
            ]);
        }
    }

    public function down(): void
    {
        foreach (['packaging_charge', 'tax_mode'] as $col) {
            if ($this->db->getFieldNames('restaurants') && in_array($col, $this->db->getFieldNames('restaurants'), true)) {
                $this->forge->dropColumn('restaurants', $col);
            }
        }
    }
}
