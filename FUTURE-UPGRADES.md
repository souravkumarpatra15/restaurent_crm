# DinoviX — Future Implementation Plan

Last updated: 2026-10-25 — **all items in this backlog are implemented and verified.**

> **Decision recorded:** SMS / WhatsApp / OTP messaging is NOT needed — do not build unless requested later.

This file previously stored pending work. Every item below was completed in this session, with live verification notes. Sections kept for historical reference.

---

## 1. Deployment / Configuration — ✅ done
- [x] **app.baseURL** — deployments override via `app.baseURL` in `.env` (BaseConfig binds `app.*` env keys automatically). The in-file default now documents the localhost fallback.
- [x] **Core schema migrations** — baseline captured: `mysqldump --no-data` of the reference schema lives in `app/Database/SQL/baseline_schema.sql`; guarded migration `BaselineCoreSchema` no-ops on existing servers and imports the file table-by-table on a fresh one.
- [x] **getPlanFeature()** — wired across the product (see plan enforcement below).

### Subscription & plan-wise enforcement (every tenant)
- **AuthFilter gate** — every admin/POS request re-checks `restaurants.subscription_status`; self-corrects trial → expired (trial_ends_at) and active → expired (subscription_ends_at) live. Blocked tenants exit to login; super-admin impersonation (`super_admin_backup`) is never locked out (support access).
- **Feature flags on saas_plans** now enforced: `allow_online_ordering` (QR menu index + placeOrder, plan-locked page), `allow_loyalty` (POS redeem + preview), `allow_reports_advanced` (items/payments/incomplete/GST/consolidated/exports → plan_locked view), `allow_thermal_print` (bill printing with web-slip fallback), `allow_kot_print` (KOT print, slip fallback), `allow_whitelabel` (bill footer "Powered by DinoviX" vs "Thank you!"), `allow_api_access` (booking API → 403).
- **Limits enforced:** `max_branches/max_users/max_menu_items/max_tables` via checkPlanLimit in Menu/Table/User/Branch controllers. Also published into the session for the dashboard.
- **Dashboard plan card** — plan name, state, days-left + usage bars (branches/staff/menu items/tables).
- **Expiry banner** in the manager layout when a trial/subscription has ≤ 7 days left.
- Verified live: Starter tenant (Maa Tara) blocked on QR ordering, advanced reports, JSON order API; Growth tenant (Spice Garden) fully open. Subscription expiry was proven by the gate auto-flagging Spice Garden's lapsed renewal.

## 2. Reports & Compliance — ✅ done
- [x] **CSV export** for Sales / Items / Payments / Expenses / Consolidated / GSTR-1 (`admin/reports/export/{type}`, BOM-headed CSV download).
- [x] **PDF (print-HTML)** — `admin/reports/print/{type}` opens a print-optimised page with a Print/Save-as-PDF button.
- [x] **GSTR-1 summary** — month picker, totals (invoices, taxable value, CGST/SGST/IGST/cess, gross), rate-wise slabs from `order_items.tax_percent`, invoice-wise list (100), CSV + print export.
- [x] **IGST / CESS / packing charge** — `restaurants.tax_mode` (intra/inter) + `packaging_charge` settings (migration `AddTaxModeAndPacking`); `OrderModel::calculateTotals` computes per-item cess (`menu_items.cess_percent`), IGST single-line split, packing charge added for takeaway/delivery; POS persists `igst/cess/packing` columns; bill slips + ThermalPrinter render IGST/Cess/Packing lines. Verified: 600 → IGST 30 + packing 20 = 650; 220 + IGST 11 + cess 4.40 + packing 20 = 255.
- [x] **Shift Z-report** — `pos/shift/z-slip/{id}` printable 80mm slip (bills, gross, discounts, service, tax, cancels, method split, cash drawer expected/counted/difference); opens automatically after counter close.
- [x] **Consolidated multi-branch report** — `admin/reports/consolidated` branch performance table + grand totals + CSV/print export, sidebar link added.

## 3. POS / Operations — ✅ done
- [x] **Kitchen per-item status** — tap a dish line to advance Quedued → Cooking → Ready (`pos/kitchen/item-status`, mirrors to kot_items + order_items, auto-advances the whole KOT when the last item is ready); ticket-level buttons never regress individually advanced items.
- [x] **Inventory auto-deduction** — `menu_item_ingredients` recipe table (migration), `RecipeEngine` library deducts recipe ingredients on every sale (POS + QR ordering) into per-branch `inventory_stock` with `sale` transactions, and restocks (`return` transactions) when an order is cancelled. Recipe editor UI at `admin/inventory/recipes` (+ per-item editor). Verified: 0.25 kg paneer deducted → order cancel returned it.
- [x] **Per-customer coupon rules** — `per_user_limit` enforced against the customer's order history (requires customer attached); plus **first-order-only** and **birthday** coupon types (`applicable_on` enum extended, validated at applyCoupon, form options added). Also fixed a latent bug where `max_discount_amount=0.00` (string "0.00" truthy) zeroed every percent coupon.
- [x] **Printer width** — branch form now has "Bill Paper Width" (80mm/58mm); bill slips scale typography/width per branch `printer_type`.

## 4. Public / Growth — ✅ done
- [x] **Public booking API** — `GET /api/bookings/slots` and `POST /api/bookings` (JSON or form), CSRF-exempt via the existing `api/*` carve-out. Enforces booking_settings (enabled, closed days, advance window, open/close hours, min/max guests, per-slot capacity, deposit rules) and **403s tenants whose plan lacks `allow_api_access`**. Verified: slot listing, successful booking, closed-day, bad-phone rejection, 403 on Starter.
- [x] **SaaS self-serve signup** — `/signup` (plan picker, validation, unique slugs/emails) creates tenant + Main branch + 4 QR tables + template menu categories + owner account (role 2), starts a 14-day trial, auto-logs in to the dashboard. Verified live end-to-end (Cafe Cinnamon tenant).

---

## Completed in past sessions (reference — do NOT redo)
- Rider delivery flow (assignment → out_for_delivery → delivered, board UI)
- QR customer menu with variants + add-ons
- Order cancel + bulk cancel / bill-together + combined receipt with centered feedback QR
- Admin CSV/XLSX import with column mapping + custom values (no spreadsheet library needed)
- /admin/orders/:id alias fix, cash counter, major billing fixes
