# StackEstate System — Remaining Work Summary

**Generated:** 2026-06-15  
**Source:** FORENSIC_AUDIT_REPORT_2026_06_14.md + IMPLEMENTATION_TRACKER.md  
**Completed:** 7 findings fixed  
**Remaining:** 40 items

---

## 🔴 CRITICAL (0 remaining)

---

## 🟠 HIGH (8 remaining)

| ID | Finding | File | Impact | Dependencies |
|----|---------|------|--------|--------------|
| F-004/U-002 | Timezone set to UTC — system is Pakistan-based | `config/app.php:73` | Timestamps off by 5 hours | None |
| F-005/D-004 | CNIC uniqueness not enforced — duplicates possible | `ClientController::store()` validation | Data integrity — duplicate client CNICs | May need cleanup migration |
| F-006/SEC-005 | Admin can delete any admin — no role hierarchy check | `UserController.php:81-87` | Security — lower-privilege admin can delete super_admin | None |
| P-001/S-001 | PaymentController::create() loads ALL clients into memory | `PaymentController.php:22-44` | Performance — multi-MB JSON at 500+ clients | None |
| B-004/MF-001/PF-005 | PaymentGatewayService — stub only, no real API integration | `PaymentGatewayService.php:19-24` | Functional — online payments don't work | Merchant API credentials |
| S-004 | No queue worker or Horizon configured | `config/app.php`, `Kernel.php` | Reliability — queued jobs silently fail | Server configuration |
| SEC-004 | Google credentials path stored in DB — SEC-004 ✅ FIXED | `SettingsController.php:24` | Security — path traversal risk | Fixed |
| AW-004 | No API versioning / no API layer | Missing `api.php` | Architecture — mobile/third-party integration impossible | None |

---

## 🟡 MEDIUM (16 remaining)

| ID | Finding | File | Severity |
|----|---------|------|----------|
| U-003 | DataTables HTML injection in action column | `ClientController.php:136-188` | Medium |
| P-002 | DashboardController monthlyRevenue not cached | `DashboardController.php` | Medium |
| P-003 | DocumentController auditDocumentIntegrity N+1 inside loop | `DocumentController.php:395` | Medium |
| B-002/BR-003 | ClientController show() — duplicate `receipts` relation key | `ClientController.php:483-491` | Medium |
| B-005/BR-004 | BackupService — no cloud upload, local only | `BackupService.php`, `BackupJob.php` | Medium |
| D-001 | No unique constraint on `payments.payment_number` per client | `PaymentController.php:96` | Medium |
| D-002 | `receipts.total_received_to_date` denormalized — stale on deletion | `PaymentController.php:123-125` | Medium |
| D-003 | No soft delete on `installments` or `payments` | `Installment.php`, `Payment.php` | Medium |
| S-002 | SyncToGoogleSheetJob dispatched on almost every write | `ClientController`, `PaymentController` | Medium |
| S-003 | DocumentController auditDocumentIntegrity loads all docs in memory | `DocumentController.php:361-365` | Medium |
| AW-002 | Settings not cached — DB hit on every request | `ReceiptService.php`, Settings | Medium |
| PF-001 | Invoice system — auto-created but no manual creation UI | `InvoiceController` | Medium |
| PF-002 | Document versioning — backend complete, UI incomplete | `DocumentController` | Medium |
| PF-004 | Google Drive subfolder hierarchy not triggered on new clients | `ClientController::store()` | Medium |
| MF-002 | No email configuration UI / SMTP test | Settings UI | Medium |
| MF-003 | No report/export UI — ExportService exists but no routes | `ExportService.php` | Medium |
| MF-005 | No notification bell / in-app notification reader | Layouts + controller | Medium |
| F-007 | `$paymentNumberForRef` race condition — count inconsistent before commit | `PaymentController.php:120-121` | Medium |

---

## 🟢 LOW (9 remaining)

| ID | Finding | File | Severity |
|----|---------|------|----------|
| F-008 | Block/Phase dropdown hardcoded A–Z only | `create.blade.php:557` | Low |
| U-004 | Installment calculator floating-point rounding loss | `create.blade.php:685-686` | Low |
| P-004 | ReceiptService queries settings table 4 times | `ReceiptService.php:18-21` | Low |
| B-007 | Unit assignment bug — unit_id written before availability check | `ClientController.php:565` | Low |
| SEC-006 | Google Drive query not properly parameterized | `GoogleDriveService.php:166-170` | Low |
| AW-003 | Hardcoded personal data in receipt fallbacks (names, CNIC) | `ReceiptService.php:18-21` | Low |
| AW-006 | No rate limiting on AJAX endpoints | Routes | Low |
| MF-004 | No bulk payment / bulk operations | Missing | Low |
| PF-003 | Late fee system — scheduler OK, no manual override UI | Missing | Low |

---

## ❓ UNVERIFIED (2 remaining)

| ID | Finding | File | Action Required |
|----|---------|------|-----------------|
| SEC-003 | CSRF on API endpoints — need to verify route group | `DocumentController` | Check route group (web vs api) |
| AW-005 | Queue driver unknown — depends on .env | `.env` | Verify `QUEUE_CONNECTION` setting |

---

## Completed Fixes (7)

| ID | Finding | Fix Summary |
|----|---------|-------------|
| SEC-004 | Google Credentials Path Traversal | Path validation at controller + service level |
| AW-001 | Dual Invoice Creation Race Condition | QueryException catch for duplicate key |
| SEC-002 | Exception Messages Exposed (13 points) | Replaced `$e->getMessage()` with generic messages + logging |
| B-003 | restore() status outside transaction | Moved `$client->status = 'active'; $client->save()` inside transaction |
| F-001/B-001/BR-001 | InvoiceService signature mismatch | FALSE POSITIVE — all callers pass 1 arg, matching signature |
| F-002/BR-002 | Unit selection on create form | FALSE POSITIVE (overstated severity) — form degrades gracefully; updated placeholder text |
| F-003/U-001/B-006/SEC-001/BR-005 | Missing is_active middleware | FALSE POSITIVE — `CheckUserActive.php` middleware exists and is registered in `Kernel.php:38` |
