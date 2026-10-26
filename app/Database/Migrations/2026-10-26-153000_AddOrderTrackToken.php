<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Customer live order tracking for takeaway & delivery orders:
 *  - orders.track_token: unguessable public token used by /track/{token}
 *    (the link + QR is printed on the bill slip, so no phone number or
 *    login is needed on any side).
 *  - every existing order gets a token backfilled so older bills
 *    work too.
 */
class AddOrderTrackToken extends Migration
{
    public function up(): void
    {
        $cols = $this->db->getFieldData('orders');
        $has  = false;
        foreach ($cols as $c) {
            if ($c->name === 'track_token') { $has = true; break; }
        }

        if (!$has) {
            $this->forge->addColumn('orders', [
                'track_token' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 32,
                    'null'       => true,
                    'after'      => 'feedback_token',
                ],
            ]);
            try {
                $this->forge->addUniqueKey('track_token', 'orders', 'orders_track_token_key');
            } catch (\Throwable $e) {
                // some drivers don't support naming keys on addColumn flow — ignore
            }
        }

        // Backfill tokens for every order that lacks one
        $rows = $this->db->query("SELECT id FROM orders WHERE track_token IS NULL OR track_token = ''")->getResultArray();
        if ($rows) {
            $builder = $this->db->table('orders');
            foreach ($rows as $row) {
                $builder->where('id', $row['id'])
                        ->update(['track_token' => bin2hex(random_bytes(16))]);
            }
        }
    }

    public function down(): void
    {
        // additive only — keep tokens
    }
}
