<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Phase 2 of UPI support — the attempt-ref / UTR indexes as raw SQL.
 * (forge->addColumn does not attach keys, so they are created separately,
 * guarded by SHOW INDEX to stay idempotent on every environment.)
 */
class AddUpiPaymentIndexes extends Migration
{
    public function up(): void
    {
        if (!$this->db->tableExists('payments')) {
            return;
        }

        $existing = [];
        foreach ($this->db->query('SHOW INDEX FROM payments')->getResultArray() as $row) {
            $existing[$row['Key_name']] = true;
        }

        if (!isset($existing['payments_attempt_ref_unique'])) {
            $this->db->query('ALTER TABLE payments ADD UNIQUE KEY payments_attempt_ref_unique (attempt_ref)');
        }
        if (!isset($existing['payments_verify_txn_ref_idx'])) {
            $this->db->query('ALTER TABLE payments ADD KEY payments_verify_txn_ref_idx (verify_txn_ref)');
        }
    }

    public function down(): void
    {
        // additive only
    }
}
