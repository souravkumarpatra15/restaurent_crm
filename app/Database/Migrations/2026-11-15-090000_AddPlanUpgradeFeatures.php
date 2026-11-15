<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * SaaS tier-gating pack: turns the already-shipped modules into
 * subscription options the super admin can switch per plan.
 *
 *   allow_kitchen_display  — Kitchen Display System (staff/kitchen)
 *   allow_staff_mgmt       — Staff Management (users CRUD beyond the plan cap)
 *   allow_advanced_billing — Split payments, cash drawer / day-close / Z-report
 *   allow_upi_qr           — UPI QR collect at the till
 *
 * Default 1 so every EXISTING tenant keeps full behaviour after migrate;
 * the super admin then unchecks per plan (e.g. Starter) to create tiers.
 */
class AddPlanUpgradeFeatures extends Migration
{
    private const COLUMNS = [
        'allow_kitchen_display'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        'allow_staff_mgmt'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        'allow_advanced_billing' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        'allow_upi_qr'           => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
    ];

    public function up(): void
    {
        foreach (self::COLUMNS as $name => $def) {
            if (!$this->db->fieldExists($name, 'saas_plans')) {
                $this->forge->addColumn('saas_plans', [$name => $def]);
            }
        }
    }

    public function down(): void
    {
        $existing = [];
        foreach (array_keys(self::COLUMNS) as $name) {
            if ($this->db->fieldExists($name, 'saas_plans')) {
                $existing[] = $name;
            }
        }
        if ($existing) {
            $this->forge->dropColumn('saas_plans', $existing);
        }
    }
}
