# Review Round 1 - Handoff Report

> [!WARNING] **Skepticism Disclaimer**
> Moderate confidence: All 13 test scenarios pass (93 assertions) with verified multi-outlet scope isolation and bug fixes, but live client-side JavaScript interactions in browser remain unverified by headless WebDriver.

## 1. What the prior attempt got wrong

1. **Card #4 "Rata-rata Belanja" Always Displayed "Rp 0"**
   - **Input:** Requesting summary stats via `GET /report-mart-cashflow?data=summary` when transactions exist.
   - **Expected:** JSON response contains `'avg_amount' => 'Rp ' . number_format($avgAmount, 0, ',', '.')` so the average spend metric is displayed.
   - **Actual:** Controller computed `$avgAmount` on line 129 but omitted it from the response array, causing JS `res.avg_amount` to be undefined and default to `'Rp 0'` permanently.
   - **Root Cause:** Key omitted in JSON return structure in `ReportMartCashflowController@index`.

2. **Insecure Scope Default for Users with 0 Assigned Outlets (Data Leak Risk)**
   - **Input:** A staff user who is neither Super Admin nor Koordinator Pondok Mart, but has 0 assigned outlets in `admin_outlets`.
   - **Expected:** The user should remain restricted and see 0 outlet transactions (locked scope).
   - **Actual:** `MartUserScopeService::isUserOutletRestricted($user)` evaluated `return count($assignedOutletIds) > 0;`. With 0 outlets, this returned `false` (meaning unrestricted). The controller then treated this non-privileged user as an unrestricted Super Admin who can see all outlets and koperasi transactions.
   - **Root Cause:** Logic assumed only users with assigned outlets are restricted; any non-superadmin/non-koordinator user must be restricted by default.

3. **Restricted Multi-Outlet Selection & Broken HTML in Blade**
   - **Input:** A restricted user assigned to multiple outlets (e.g. Outlet A and Outlet B) visits `/report-mart-cashflow`.
   - **Expected:** Dropdown allows choosing "Semua Outlet Ditugaskan" or filtering down to specific assigned outlets.
   - **Actual:** Every `<option>` had the `selected` attribute (invalid HTML), and there was no `<option value="all">` for restricted users. On page load, `$('#filter-outlet-id').val()` locked to just one outlet, preventing the user from viewing a combined summary across their assigned outlets.
   - **Root Cause:** Incomplete multi-outlet dropdown templating in `resources/views/admins/report-mart-cashflow/index.blade.php`.

4. **Single-Boundary Date Filtering Ignored**
   - **Input:** Query with only `start_date` or only `end_date` (e.g. `start_date=2026-11-01` without `end_date`).
   - **Expected:** Query filters transactions on or after the specified start date.
   - **Actual:** Because the condition was `if ($request->filled('start_date') && $request->filled('end_date'))`, providing only one boundary resulted in `false`, completely bypassing the date filter and returning all records.
   - **Root Cause:** Over-strict boolean conjunction `&&` instead of independent evaluation of `start_date` and `end_date`.

5. **SubMenu Permission Leak to School Tuition Cashiers (`bendaharasmp`)**
   - **Input:** User with role `Bendahara SMP` (who has `Manage Laporan Transaksi` for tuition bills, but no Mart POS permissions) views admin sidebar.
   - **Expected:** Arus Kas Mart submenu is hidden from tuition bendahara.
   - **Actual:** The migration assigned `'permission' => 'Manage Laporan Pos Multi Outlet,Manage Laporan Transaksi,POS Outlet'`. Because `bendaharasmp` had `Manage Laporan Transaksi`, the submenu appeared in their sidebar, but clicking it caused a 403 / redirect back.
   - **Root Cause:** Conflating Mart transaction reporting with general tuition transaction reporting (`Manage Laporan Transaksi`).

6. **Potential Fatal 500 in Excel Export on Null / Deleted Outlet**
   - **Input:** Exporting transactions where an outlet relation is null.
   - **Expected:** Formatted row with `'-'` for outlet.
   - **Actual:** Line 167 accessed `$row->outlet->name` directly without the nullsafe operator `?->`, causing a fatal error.
   - **Root Cause:** Missing nullsafe operator `?->` in `ReportMartCashflowExport@map`.

---

## 2. What I changed
1. **`app/Http/Controllers/Admin/ReportMartCashflowController.php`**:
   - Added `'avg_amount' => 'Rp ' . number_format($avgAmount, 0, ',', '.')` to summary JSON response.
   - Handled `start_date` and `end_date` independently.
   - Handled `outlet_id !== 'all'` for restricted multi-outlet users.
   - Ensured fallback pagination values synchronize both `$request` and global `request()`.
2. **`app/Services/MartUserScopeService.php`**:
   - Made `isUserOutletRestricted($user)` return `true` for all non-superadmin and non-koordinator users, ensuring users with 0 assigned outlets are restricted to empty sets rather than escalating to unrestricted.
3. **`resources/views/admins/report-mart-cashflow/index.blade.php`**:
   - Added `<option value="all" selected>Semua Outlet Ditugaskan</option>` when a restricted user has more than 1 assigned outlet.
   - Fixed reset filter JavaScript to choose `'all'` when available.
4. **`app/Exports/ReportMartCashflowExport.php`**:
   - Used nullsafe operators `?->` on `$row->outlet?->name` and student properties.
   - Supported independent `start_date` and `end_date` filtering.
   - Handled `outlet_id !== 'all'` for restricted multi-outlet users.
5. **`database/migrations/2026_10_12_100000_add_arus_kas_mart_submenu_and_assign_roles.php`**:
   - Updated submenu permission to `'Manage Laporan Pos Multi Outlet,POS Outlet'`, isolating Arus Kas Mart from tuition bill cashiers.
6. **`tests/Feature/ReportMartCashflowTest.php`**:
   - Added 5 new adversarial test cases:
     - `test_r1_user_with_mart_report_permission_has_access`
     - `test_r2_summary_includes_avg_amount`
     - `test_r3_single_boundary_date_filtering`
     - `test_r4_restricted_user_with_multiple_outlets`
     - `test_r4_restricted_user_with_no_assigned_outlets_sees_nothing_preventing_leak`
   - Test suite now covers 13 test methods (93 assertions).

---

## 3. Verification Record
- **Deep Verification (ran actual tests):**
  - `php artisan test --filter=ReportMartCashflowTest`: **13 passed, 93 assertions** (Duration: ~21s)
  - `php artisan test --filter=MultiOutletArchitectureTest`: **4 passed, 7 assertions** (Duration: ~3s)
  - `php artisan test --filter=ReportTransactionScopeAndFilterTest`: **3 passed, 50 assertions** (Duration: ~35s)
- **Shallow Verification (manual only):**
  - Tested DataTables SQL profiling and memory footprint against the 152k-record local database with eager loads and search parameters.
  - Verified menu permission and role assignment in database.
- **Unverified aspects:**
  - Real browser rendering of JavaScript DateRangePicker and Metronic collapse animation (tested via Blade and JSON endpoint responses, not headless browser WebDriver).
  - MySQL-specific date query differences (local test runner uses SQLite with busy timeout).

---

## 4. Known Issues
- `Minor Robustness Risk`: High-volume Excel export chunking (`forPage(..., 300)`) runs synchronously within PHP request execution timeout limits. On very large datasets (100k+ rows) under high concurrent load, moving to an asynchronous queue job would be advisable.
- `Minor Robustness Risk`: Pre-existing test failure in `StudentBarcodeAndCardReportTest` (`assertGreaterThanOrEqual(1000, 245)`) is unrelated to this feature branch and exists in baseline.

---

## 5. Remaining risk & next step
- **Task is complete:** All requirements R1–R4 and acceptance criteria are fully met, verified by 13 comprehensive feature tests covering multi-outlet scoping, security boundaries, and summary card math.
- **Next Step:** Commit changes to `feat/siswanto/laporan-arus-kas-mart`, push to remote, and notify user/Lead for PR review and merge into staging.
