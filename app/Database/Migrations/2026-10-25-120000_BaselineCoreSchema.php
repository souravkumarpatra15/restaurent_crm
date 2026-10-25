<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Baseline core schema.
 *
 * The bulk of this app's tables were created directly in MySQL before
 * migrations were introduced. This migration records the baseline so
 * fresh deployments can build the whole schema from version control:
 *
 *  - On an existing server (core tables present) it is a no-op and
 *    simply adds the migration history row.
 *  - On an empty database it imports app/Database/SQL/baseline_schema.sql
 *    (mysqldump --no-data of the reference schema) table by table.
 */
class BaselineCoreSchema extends Migration
{
    private array $required = [
        'restaurants', 'branches', 'users', 'menu_categories', 'menu_items',
        'menu_variants', 'menu_addons', 'tables', 'orders', 'order_items',
        'payments', 'customers', 'coupons', 'invoices', 'kots', 'kot_items',
        'inventory_items', 'inventory_stock', 'inventory_transactions',
    ];

    public function up(): void
    {
        // Only build what is missing — existing deployments are unchanged
        $missing = [];
        foreach ($this->required as $table) {
            if (!$this->db->tableExists($table)) {
                $missing[] = $table;
            }
        }
        if (empty($missing)) {
            return;
        }

        $file = APPPATH . 'Database' . DIRECTORY_SEPARATOR . 'SQL' . DIRECTORY_SEPARATOR . 'baseline_schema.sql';
        if (!is_file($file)) {
            throw new \RuntimeException('Baseline schema file missing: ' . $file);
        }

        $script = file_get_contents($file);
        $statements = preg_split('/;\s*[\r\n]+/', $script) ?: [];

        $created = 0;
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement === '' || str_starts_with($statement, '--') || str_starts_with($statement, '/*')) {
                continue;
            }
            if (preg_match('/^(SET|LOCK|UNLOCK|\/\*!)/i', $statement)) {
                continue;
            }
            if (!preg_match('/^(CREATE TABLE IF NOT EXISTS|CREATE TABLE)/i', $statement)) {
                continue;
            }
            $this->db->query($statement);
            $created++;
        }

        // Report anything the dump could not cover
        $still = [];
        foreach ($this->required as $table) {
            if (!$this->db->tableExists($table)) {
                $still[] = $table;
            }
        }
        if (!empty($still)) {
            log_message('error', 'Baseline migration could not create: ' . implode(', ', $still));
        }
    }

    public function down(): void
    {
        // A baseline is not reversible
    }
}
