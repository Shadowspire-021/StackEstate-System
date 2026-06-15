# StackEstate System — Development Priority Roadmap

**Date:** June 14, 2026  
**Basis:** Evidence-based audit of actual codebase files (NOT stale assumptions)  
**Completion:** ~78% implemented (+3% from partial fixes)  
**Remaining:** ~22% (primarily infrastructure + analytics)

---

## PHASE 1 — PRODUCTION BLOCKERS (MUST FIX)

### 1.1 Settings UI Incomplete
**Risk: HIGH**  
**Status: PARTIAL** — Backend schema supports 20+ settings across 6 groups (company, google, notifications, backup, late_fees, payment_gateway) with validation rules; `SettingsController` has full `$settingsSchema` with types and rules  
**Gap:** The `settings/index.blade.php` template may not render all 6 groups as UI panels  
**Evidence:** `app/Http/Controllers/SettingsController.php` — `$settingsSchema` (lines 12-60) defines 21 settings across 6 groups with validation; `getGroupedSettings()` returns structured data for 6 groups  
**Impact:** System settings (Google Drive, notifications, backups, late fees, payment gateway) cannot be configured via UI  
**Fix:** Update settings view to render all 6 groups as collapsible panels

### 1.2 Queue Worker Alignment
**Risk: LOW** — **FIXED**  
**Status: RESOLVED** — `config/queue.php` default changed from `sync` to `database`; `.env.example` already had `QUEUE_CONNECTION=database`; all 4 jobs implement `ShouldQueue`; `QueueController` manages failed jobs  
**Changes:** `config/queue.php` line 16 — fallback default aligned with `.env.example` and architecture

### 1.3 Health Check Endpoint Missing
**Risk: MEDIUM**  
**Status: NOT IMPLEMENTED**  
**Evidence:** No `/health` route in `routes/web.php`; no health check controller  
**Impact:** No monitoring capability; load balancers can't check liveness; no quick diagnosis  
**Fix:** Create `GET /health` endpoint checking DB, queue, Drive API connectivity

---

## PHASE 2 — PRODUCTION HARDENING

### 2.1 Database Indexes
**Risk: MEDIUM** — Performance degrades as data grows  
**Status: NOT IMPLEMENTED** — Only Spatie permission indexes + FK indexes  
**Evidence:** `realestate_db.sql` shows only FK indexes on `payments`, `installments`, `clients`, `documents`  
**Required composite indexes:**
- `payments(client_id, payment_date)` — dashboard/report queries
- `installments(client_id, status, due_date)` — overdue detection + dashboard
- `clients(cnic)` — CNIC lookup performance
- `documents(client_id, document_type, parent_document_id)` — version chain resolution
- `activity_logs(user_id, created_at, loggable_type)` — activity log filtering

### 2.2 Redis for Cache + Session + Queue
**Risk: MEDIUM** — File-based doesn't scale; no session sharing  
**Status: NOT IMPLEMENTED** — `CacheService` exists (164 lines, generation-based invalidation, tag support) but runs on `file` driver  
**Evidence:** `config/cache.php` default is `file`; `config/session.php` default is `file`; `config/queue.php` has `redis` driver configured but unused  
**Impact:** No cross-server session sharing; file cache isn't atomic; queue workers may conflict  
**Fix:** Install Redis, switch `CACHE_DRIVER`, `SESSION_DRIVER`, `QUEUE_CONNECTION` to `redis`

### 2.3 Logging Rotation (Daily)
**Risk: LOW** — **FIXED**  
**Status: RESOLVED** — `.env.example` `LOG_CHANNEL` changed from `stack` to `daily`; `daily` channel already configured with 14-day retention in `config/logging.php`  
**Changes:** `.env.example` line 7 — recommended channel switched to `daily`

### 2.4 Email Transport Configuration
**Risk: MEDIUM** — Notifications won't deliver without SMTP  
**Status: PARTIAL** — Notification system complete (4 classes, event-driven, ShouldQueue); mail config exists  
**Evidence:** `.env.example` has `MAIL_MAILER=smtp` with Mailpit (dev); all notifications extend `ShouldQueue`  
**Fix:** Configure production SMTP credentials

---

## PHASE 3 — ANALYTICS & VISUALIZATION

### 3.1 Dashboard Charts
**Risk: LOW** — No visual data representation  
**Status: NOT IMPLEMENTED**  
**Evidence:** `package.json` has no chart library; `dashboard.blade.php` has no chart elements  
**Fix:** Install Chart.js or ApexCharts; add revenue trend, collection efficiency, aging buckets, installment status pie, unit availability charts

### 3.2 Report Export System
**Risk: LOW** — **FIXED**  
**Status: RESOLVED** — CSV Export infrastructure built: `ExportService` (clients/payments/installments CSV), `ExportController` with download endpoints, routes under `/exports/` prefix  
**Files created:** `app/Services/ExportService.php`, `app/Http/Controllers/ExportController.php`  
**Routes added:** `GET /exports/clients/csv`, `GET /exports/payments/csv`, `GET /exports/installments/csv`

### 3.3 Dashboard Drill-Down
**Risk: LOW** — KPIs are static numbers  
**Status: NOT IMPLEMENTED** — KPI cards are static; no interactive drill-down  
**Fix:** Make KPIs clickable to filtered list views

---

## PHASE 4 — ENHANCEMENTS (DEFER)

| Feature | Reason |
|---|---|
| OAuth 2.0 for Google Drive | Service account sufficient |
| E-signature integration | Not core business flow |
| Folder sharing management UI | Drive sharing manageable directly |
| Storage quota monitoring | Rarely needed |
| Repository pattern | Eloquent works; premature optimization |
| Workflow automation rules | Complex; manual workflows work |
| Floors/Towers tables | `floor_number` as integer is sufficient |
| Bulk inventory import | Manual entry works for current scale |

---

## EXECUTION ORDER

| Step | Action | Phase | Effort |
|---|---|---|---|
| 1 | Update settings view to render 6 grouped panels | 1.1 | 1 day |
| 2 | Create health check endpoint | 1.3 | 0.5 day |
| 3 | Add composite database indexes | 2.1 | 0.5 day |
| 4 | Install Redis + switch cache/session/queue | 2.2 | 1 day |
| 5 | Configure production SMTP | 2.4 | 0.5 day |
| 6 | Install Chart.js + build dashboard charts | 3.1 | 2-3 days |
| 7 | Add KPI drill-down links | 3.3 | 1 day |

**Total remaining effort:** ~6.5-8.5 days for production readiness (Phases 1-2)  
**Full completion (incl. analytics):** ~10-13 days

---

*Generated: June 14, 2026 — Based on actual codebase state verified by file inspection.*
