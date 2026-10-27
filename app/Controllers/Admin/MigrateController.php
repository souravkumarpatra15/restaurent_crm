<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Database\MigrationRunner;

/**
 * One-click database migration for hosts without SSH (shared hosting).
 *
 * GET super/migrate  — super admin only. Runs every pending migration
 * (Migrations\Runner::latest), which is idempotent: already-applied
 * migrations are skipped and the runner keeps its own history table.
 * Safe to hit repeatedly. Use once after every git pull.
 */
class MigrateController extends BaseController
{
    public function run()
    {
        if (session('role_slug') !== 'super_admin') {
            return $this->response->setStatusCode(403)->setBody('Super admin only');
        }

        try {
            $runner = new MigrationRunner(config('Migrations'), \Config\Database::connect());
            $runner->latest();
            $history = array_reverse($runner->getHistory('default'));
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setBody(
                "MIGRATION FAILED\n\n" . $e->getMessage()
            );
        }

        $lines = [];
        foreach (array_slice($history, -5) as $h) {
            $h = is_array($h) ? $h : json_decode(json_encode($h), true);
            $lines[] = "batch {$h['batch']} — {$h['version']} {$h['class']}";
        }

        return $this->response->setBody(
            "Migration done — all pending migrations applied.\n\n"
            . implode("\n", $lines)
            . "\n\nNow reload the admin site — new features are live."
        );
    }
}
