<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Restaurant-owned UPI QR payment support — Phase 1 (schema).
 *
 * Strictly ADDITIVE: existing columns/enums are never modified or dropped.
 *  - payments: UPI attempt columns (expected amount already lives in
 *    payments.amount; attempt ref, verify state, bank txn ref, who/time
 *    verified, expiry for QR validity, customer claim markers).
 *  - restaurants: the receiving UPI account (VPA) + payee display name
 *    and who/when last changed them. NO secrets are stored (no PINs/
 *    passwords/OTPs — a VPA is public, like an email address).
 *  - payment_verifications: audit trail (status changes, customer claims,
 *    settings changes) — the verification history for every UPI attempt.
 */
class AddUpiPaymentSupport extends Migration
{
    public function up(): void
    {
        // ── payments: per-attempt UPI columns ────────────────────
        if ($this->db->tableExists('payments')) {
            $fields = $this->db->getFieldNames('payments');
            $add    = [];

            if (!in_array('verify_status', $fields, true)) {
                $add['verify_status'] = [
                    'type'       => 'ENUM',
                    'constraint' => ['pending', 'paid', 'failed', 'expired', 'rejected'],
                    'null'       => true,
                    'comment'    => 'UPI QR attempt state; NULL = not a QR attempt (e.g. cash/card)',
                ];
            }
            if (!in_array('attempt_ref', $fields, true)) {
                $add['attempt_ref'] = [
                    'type'    => 'VARCHAR',
                    'constraint' => 32,
                    'null'    => true,
                    'comment' => 'Public unguessable ref shown as tr= in the UPI URI',
                ];
            }
            if (!in_array('verify_txn_ref', $fields, true)) {
                $add['verify_txn_ref'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                    'null'       => true,
                    'comment'    => 'Bank/UPI UTR entered by staff after checking the real account',
                ];
            }
            if (!in_array('expires_at', $fields, true)) {
                $add['expires_at'] = ['type' => 'DATETIME', 'null' => true];
            }
            if (!in_array('claimed_by_customer', $fields, true)) {
                $add['claimed_by_customer'] = ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => true];
            }
            if (!in_array('claimed_at', $fields, true)) {
                $add['claimed_at'] = ['type' => 'DATETIME', 'null' => true];
            }
            if (!in_array('verify_note', $fields, true)) {
                $add['verify_note'] = ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true];
            }
            if (!in_array('verified_by', $fields, true)) {
                $add['verified_by'] = ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true];
            }
            if (!in_array('verified_at', $fields, true)) {
                $add['verified_at'] = ['type' => 'DATETIME', 'null' => true];
            }

            if (!empty($add)) {
                $this->forge->addColumn('payments', $add);

                // Unique attempt refs; plain index for UTR lookups
                if (!in_array('attempt_ref', $fields, true)) {
                    $this->forge->addUniqueKey('attempt_ref', 'payments_attempt_ref_unique');
                }
                if (!in_array('verify_txn_ref', $fields, true)) {
                    $this->forge->addKey('verify_txn_ref', false, false, 'payments_verify_txn_ref_idx');
                }
            }
        }

        // ── restaurants: receiving UPI account (no secrets) ──────
        if ($this->db->tableExists('restaurants')) {
            $fields = $this->db->getFieldNames('restaurants');
            $add    = [];

            if (!in_array('upi_enabled', $fields, true)) {
                $add['upi_enabled'] = ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0];
            }
            if (!in_array('upi_vpa', $fields, true)) {
                $add['upi_vpa'] = ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true];
            }
            if (!in_array('upi_payee_name', $fields, true)) {
                $add['upi_payee_name'] = ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true];
            }
            if (!in_array('upi_settings_updated_at', $fields, true)) {
                $add['upi_settings_updated_at'] = ['type' => 'DATETIME', 'null' => true];
            }
            if (!in_array('upi_settings_updated_by', $fields, true)) {
                $add['upi_settings_updated_by'] = ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true];
            }

            if (!empty($add)) {
                $this->forge->addColumn('restaurants', $add);
            }
        }

        // ── audit trail ──────────────────────────────────────────
        if (!$this->db->tableExists('payment_verifications')) {
            $this->forge->addField([
                'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'payment_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'restaurant_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'branch_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'event_type'    => ['type' => 'ENUM', 'constraint' => ['verification', 'claim', 'settings_change', 'note'], 'default' => 'verification'],
                'prev_state'    => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true],
                'new_state'     => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true],
                'acted_by'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'txn_ref'       => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
                'note'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'created_at'    => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('payment_id');
            $this->forge->addKey('restaurant_id');
            $this->forge->createTable('payment_verifications', true, [
                'ENGINE'          => 'InnoDB',
                'DEFAULT CHARSET' => 'utf8mb4',
            ]);
        }
    }

    public function down(): void
    {
        // Rollback is manual by design — payment history must never be
        // casually dropped. The columns are additive and harmless in place.
    }
}
