# DinoviX — Future Implementation Plan

Last updated: 2026-10-25 (all past feature requests completed — see git `e46a5d1`).

> **Decision recorded:** SMS / WhatsApp / OTP messaging is NOT needed — do not build unless requested later.

This file stores everything that is pending/appears pending for future implementation, so it is not lost from memory.

---

## 1. Deployment / Configuration (do before go-live)
- [ ] **app.baseURL** is hardwired to `localhost:8080` in `app/Config/App.php` — must be set to the live domain/URL at deployment.
- [ ] **Core schema migrations** — some early base tables were created directly in MySQL instead of through `spark migrate`; production deployments should get a clean migration set.
- [ ] **getPlanFeature()** helper (SaaS plan gating) exists but is unused by controllers — wire it when SaaS billing goes live.

## 2. Reports & Compliance
- [ ] **CSV/PDF export** for reports (Sales, Orders, Customers, Delivery, Payments) — currently all reports are view-only HTML.
- [ ] **GSTR-1 / GST summary export** — parse menu tax_percent into GSTIN-ready slabs (5%/12%) summary.
- [ ] **IGST / CESS / packing-charge logic** — billing assumes intra-state GST only; add per-item IGST flag, cess and packing-charge tax treatment for other-state/delivery.
- [ ] **Shift Z-report printing** — the shift close exists; add a printable 80mm Z-receipt (day total, method-wise split, discounts, voids).
- [ ] **Multi-branch consolidated reports** — owner-level dashboard reporting across branches with comparison.

## 3. POS / Operations
- [ ] **Kitchen per-item status UI** — KDS shows whole tickets; add item-level "preparing / done" toggles so the waiter knows partial readiness.
- [ ] **Inventory auto-deduction** — recipes/BOM per menu item and ingredient stock auto-deduct per order item.
- [ ] **Per-customer coupon rules** — coupons are generic; add usage limits per customer, birthday/special-day coupons, first-order-only.
- [ ] **Printer type filter** — printer_type is fixed 58mm; support 80mm layout option per receipt/slip.

## 4. Public / Growth
- [ ] **Public table-booking API** — bookings are staff-side only; expose a public endpoint/widget for websites.
- [ ] **SaaS onboarding flow** — self-serve restaurant signup: tenant creation → plan selection → branch wizard → template menu.

---

## Completed in past sessions (reference — do NOT redo)
- Rider delivery flow (assignment → out_for_delivery → delivered, board UI)
- QR customer menu with variants + add-ons
- Order cancel + bulk cancel / bill-together + combined receipt with centered feedback QR
- Admin CSV/XLSX import with column mapping + custom values (no spreadsheet library needed)
- /admin/orders/:id alias fix, cash counter, major billing fixes
