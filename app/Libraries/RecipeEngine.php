<?php

namespace App\Libraries;

/**
 * Inventory recipe engine — deducts ingredients from stock on every item
 * sold (recipe per menu item in menu_item_ingredients) and puts the
 * ingredients back when the order is cancelled.
 *
 * One table row per consumed ingredient is written to
 * inventory_transactions, so purchases/reports stay reconcilable.
 */
class RecipeEngine
{
    /**
     * Deduct the recipe of every ordered item from that branch's stock.
     * $items must be [{menu_item_id, quantity}, ...].
     * Returns the list of inventory_item_ids touched (for logging/tests).
     */
    public static function deduct($db, int $restaurantId, int $branchId, array $items, int $userId, ?int $orderId = null): array
    {
        $touched = [];
        $qtySold = [];

        // Sum recipe quantities across duplicate items (negative qty = restock)
        foreach ($items as $item) {
            $rid = (int)($item['menu_item_id'] ?? 0);
            $qty = (float)($item['quantity'] ?? 0);
            if (!$rid || $qty == 0.0) continue;

            $rows = $db->table('menu_item_ingredients')
                ->where('menu_item_id', $rid)
                ->get()->getResultArray();
            foreach ($rows as $row) {
                $invId = (int)$row['inventory_item_id'];
                $qtySold[$invId] = ($qtySold[$invId] ?? 0) + ((float)$row['quantity'] * $qty);
            }
        }

        foreach ($qtySold as $invId => $qty) {
            // Stock row per branch (create lazily if not there yet)
            $stock = $db->table('inventory_stock')
                ->where('inventory_item_id', $invId)
                ->where('branch_id', $branchId)
                ->get()->getRowArray();
            if (!$stock) {
                $menuRow = $db->table('inventory_items')
                    ->where('id', $invId)->where('restaurant_id', $restaurantId)
                    ->get()->getRowArray();
                if (!$menuRow) continue; // other tenant's item — skip silently
                $db->table('inventory_stock')->insert([
                    'inventory_item_id' => $invId,
                    'branch_id'         => $branchId,
                    'current_stock'     => 0,
                    'unit_cost'         => 0,
                    'updated_at'        => date('Y-m-d H:i:s'),
                ]);
            }

            $db->table('inventory_stock')
                ->where('inventory_item_id', $invId)
                ->where('branch_id', $branchId)
                ->set('current_stock', "current_stock - {$qty}", false)
                ->update();

            $db->table('inventory_transactions')->insert([
                'inventory_item_id' => $invId,
                'branch_id'         => $branchId,
                'user_id'           => $userId,
                'transaction_type'  => $qty > 0 ? 'sale' : 'return',
                'quantity'          => $qty,
                'reference_id'      => $orderId,
                'notes'             => $orderId
                    ? ($qty > 0 ? 'Auto deduct — order #' . $orderId : 'Restock — order #' . $orderId . ' cancelled')
                    : 'Auto deduct',
                'created_at'        => date('Y-m-d H:i:s'),
            ]);
            $touched[] = $invId;
        }

        return $touched;
    }

    /**
     * Put the ingredients of a cancelled order back into stock.
     * Uses the same recipe math, reading quantities from order_items.
     */
    public static function restockOrder($db, array $order): array
    {
        $items = $db->table('order_items')
            ->select('menu_item_id, quantity')
            ->where('order_id', (int)$order['id'])
            ->get()->getResultArray();
        if (empty($items)) return [];

        return self::deduct(
            $db,
            (int)$order['restaurant_id'],
            (int)$order['branch_id'],
            array_map(fn($r) => ['menu_item_id' => $r['menu_item_id'], 'quantity' => -(float)$r['quantity']], $items),
            (int)($order['cancelled_by'] ?? $order['user_id'] ?? 0),
            (int)$order['id']
        );
    }
}
