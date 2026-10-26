<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * inventory_transactions.transaction_type gains 'requisition_issue'.
 * Additive: keeps every existing value untouched.
 */
class AlterInvTxnEnum extends Migration
{
    public function up(): void
    {
        $this->db->query(
            "ALTER TABLE inventory_transactions
             MODIFY transaction_type
             ENUM('purchase','sale','transfer_in','transfer_out','adjustment','waste','return','requisition_issue')
             NOT NULL"
        );
    }

    public function down(): void
    {
        // additive only — revert would corrupt issued rows
    }
}
