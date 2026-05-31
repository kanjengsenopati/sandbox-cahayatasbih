# Technical Design Document

## Overview

This document provides the technical design for implementing null-safe access to the `activeProof` relationship in the `BillController` class. The implementation will apply PHP 8.1+'s null-safe operator (`?->`) to all property accesses on the `activeProof` relationship to prevent null pointer exceptions when the relationship returns null.

### Context

The `BillController` class in `app/Http/Controllers/Admin/BillController.php` contains three private methods that format data for DataTables display:
- `formatProofColumn()` - Formats payment proof images
- `formatStatusColumn()` - Formats transaction status badges with OCR information
- `formatActionColumn()` - Formats action buttons for transaction management

Each of these methods accesses properties on the `activeProof` relationship without null-safety checks. The `activeProof` relationship is defined in the `Transaction` model as:

```php
public function activeProof()
{
    return $this->hasOne(TransactionProof::class)->where('is_active', true);
}
```

This relationship can return `null` when no active proof exists, leading to potential runtime errors when properties are accessed directly.

### Goals

1. Eliminate null pointer exceptions when accessing `activeProof` properties
2. Maintain all existing functionality and business logic
3. Ensure code remains readable and maintainable
4. Provide comprehensive test coverage for null-safety

### Non-Goals

1. Modifying the Transaction or TransactionProof models
2. Changing the database schema or relationships
3. Altering the behavior of other controllers
4. Modifying view files or JavaScript code

## Architecture

### Component Overview

The fix is isolated to the `BillController` class and affects three private methods:

```
BillController
├── formatProofColumn($transaction)
│   ├── Accesses: activeProof->proof_image_url
│   └── Accesses: activeProof->proof_image
├── formatStatusColumn($transaction)
│   ├── Accesses: activeProof->ocr_status
│   ├── Accesses: activeProof->ocr_amount
│   └── Accesses: activeProof->note
└── formatActionColumn($transaction)
    └── Accesses: activeProof->note
```

### Design Decisions

**Decision 1: Use PHP 8.1+ Null-Safe Operator**
- **Rationale**: The null-safe operator (`?->`) is the idiomatic PHP 8.1+ approach for handling potentially null objects. It's concise, readable, and prevents exceptions.
- **Alternative Considered**: Using null coalescing with explicit null checks (`$transaction->activeProof ? $transaction->activeProof->property : null`) - rejected as more verbose and less readable.

**Decision 2: Preserve Existing Logic Flow**
- **Rationale**: The existing conditional logic already handles null values appropriately (e.g., returning '-' when proof URL is null). We only need to make the property access safe.
- **Alternative Considered**: Restructuring the methods to check for null activeProof first - rejected as unnecessary and would change more code than needed.

**Decision 3: Minimal Scope**
- **Rationale**: Limiting changes to only the necessary null-safe operator additions reduces risk and makes the change easy to review and test.
- **Alternative Considered**: Refactoring the entire controller - rejected as out of scope and introduces unnecessary risk.

## Implementation Details

### Code Changes

#### 1. formatProofColumn Method

**Current Code (Lines ~120-126):**
```php
private function formatProofColumn($transaction)
{
    $proofUrl = $transaction->activeProof?->proof_image_url ?? $transaction->activeProof?->proof_image;
    if (!$proofUrl) return '-';
    return "<a href='{$proofUrl}' target='_blank'>
            <img src='{$proofUrl}' class='img-fluid img-thumbnail' style='max-width: 100px;'>
        </a>";
}
```

**Analysis**: This method already uses null-safe operators correctly. No changes needed.

#### 2. formatStatusColumn Method

**Current Code (Lines ~128-165):**
```php
private function formatStatusColumn($transaction)
{
    // ... status label and class arrays ...
    
    $statusText = $statusLabels[$transaction->status] ?? '';
    $statusBadge = "<span class='badge badge-{$statusClass[$transaction->status]}'>{$statusText}</span>";

    if ($transaction->activeProof) {
        if ($transaction->activeProof->ocr_status === 'processed') {
            $statusBadge .= "<br><span class='badge badge-light-success mt-1' style='font-size: 0.7rem;'><i class='fas fa-robot text-success me-1'></i> AI Checked</span>";
            if ($transaction->activeProof->ocr_amount) {
                $statusBadge .= "<br><small class='text-muted'>Nominal Terbaca: Rp " . number_format($transaction->activeProof->ocr_amount, 0, ',', '.') . "</small>";
            }
        } elseif ($transaction->activeProof->ocr_status === 'failed') {
            $statusBadge .= "<br><span class='badge badge-light-danger mt-1' style='font-size: 0.7rem;'><i class='fas fa-robot text-danger me-1'></i> AI Gagal Membaca</span>";
        }
    }

    if ($transaction->status === Transaction::STATUS_REJECTED && $transaction->activeProof) {
        $statusBadge .= "<br><small class='text-danger d-block mt-1 fw-bold'>{$transaction->activeProof->note}</small>";
    }

    return $statusBadge;
}
```

**Changes Required:**
- Line ~138: Change `$transaction->activeProof->ocr_status` to `$transaction->activeProof?->ocr_status`
- Line ~140: Change `$transaction->activeProof->ocr_amount` to `$transaction->activeProof?->ocr_amount`
- Line ~143: Change `$transaction->activeProof->ocr_status` to `$transaction->activeProof?->ocr_status`
- Line ~148: Change `$transaction->activeProof->note` to `$transaction->activeProof?->note`

**Modified Code:**
```php
private function formatStatusColumn($transaction)
{
    $statusLabels = [
        Transaction::STATUS_PENDING => 'Belum Dibayar',
        Transaction::STATUS_PENDING_PAYMENT => 'Menunggu Pembayaran',
        Transaction::STATUS_PENDING_CONFIRMATION => 'Menunggu Verifikasi',
        Transaction::STATUS_PAID => 'Lunas',
        Transaction::STATUS_EXPIRED => 'Kedaluwarsa',
        Transaction::STATUS_CANCELLED => 'Dibatalkan',
        Transaction::STATUS_REJECTED => 'Ditolak'
    ];

    $statusClass = [
        Transaction::STATUS_PENDING => 'primary',
        Transaction::STATUS_PENDING_PAYMENT => 'warning',
        Transaction::STATUS_PENDING_CONFIRMATION => 'danger',
        Transaction::STATUS_PAID => 'success',
        Transaction::STATUS_EXPIRED => 'secondary',
        Transaction::STATUS_CANCELLED => 'secondary',
        Transaction::STATUS_REJECTED => 'danger'
    ];

    $statusText = $statusLabels[$transaction->status] ?? '';
    $statusBadge = "<span class='badge badge-{$statusClass[$transaction->status]}'>{$statusText}</span>";

    if ($transaction->activeProof) {
        if ($transaction->activeProof?->ocr_status === 'processed') {
            $statusBadge .= "<br><span class='badge badge-light-success mt-1' style='font-size: 0.7rem;'><i class='fas fa-robot text-success me-1'></i> AI Checked</span>";
            if ($transaction->activeProof?->ocr_amount) {
                $statusBadge .= "<br><small class='text-muted'>Nominal Terbaca: Rp " . number_format($transaction->activeProof?->ocr_amount, 0, ',', '.') . "</small>";
            }
        } elseif ($transaction->activeProof?->ocr_status === 'failed') {
            $statusBadge .= "<br><span class='badge badge-light-danger mt-1' style='font-size: 0.7rem;'><i class='fas fa-robot text-danger me-1'></i> AI Gagal Membaca</span>";
        }
    }

    if ($transaction->status === Transaction::STATUS_REJECTED && $transaction->activeProof) {
        $statusBadge .= "<br><small class='text-danger d-block mt-1 fw-bold'>{$transaction->activeProof?->note}</small>";
    }

    return $statusBadge;
}
```

#### 3. formatActionColumn Method

**Current Code (Lines ~167-191):**
```php
private function formatActionColumn($transaction)
{
    if (!Auth::user()->can('Edit Tagihan')) {
        return '';
    }

    if ($transaction->status === Transaction::STATUS_PAID) {
        return "<span class='badge badge-success'>Lunas</span>";
    }

    $options = [
        Transaction::STATUS_PAID => 'Lunas',
        Transaction::STATUS_REJECTED => 'Cek Ulang'
    ];

    $action = "<select class='form-control status-transaction' name='status' id='status-{$transaction->id}' onchange='updateStatus(this.value, \"{$transaction->id}\")'>
            <option value=''>Pilih Status</option>";

    foreach ($options as $value => $label) {
        $selected = $transaction->status == $value ? 'selected' : '';
        $action .= "<option value='{$value}' {$selected}>{$label}</option>";
    }

    $action .= "</select>
            <input type='hidden' name='note' id='note-{$transaction->id}' value='{$transaction->activeProof->note}'>
            <button class='btn btn-primary btn-sm mt-2' onclick='saveStatus(\"{$transaction->id}\")'>Simpan</button>";

    return $action;
}
```

**Changes Required:**
- Line ~186: Change `$transaction->activeProof->note` to `$transaction->activeProof?->note`

**Modified Code:**
```php
private function formatActionColumn($transaction)
{
    if (!Auth::user()->can('Edit Tagihan')) {
        return '';
    }

    if ($transaction->status === Transaction::STATUS_PAID) {
        return "<span class='badge badge-success'>Lunas</span>";
    }

    $options = [
        Transaction::STATUS_PAID => 'Lunas',
        Transaction::STATUS_REJECTED => 'Cek Ulang'
    ];

    $action = "<select class='form-control status-transaction' name='status' id='status-{$transaction->id}' onchange='updateStatus(this.value, \"{$transaction->id}\")'>
            <option value=''>Pilih Status</option>";

    foreach ($options as $value => $label) {
        $selected = $transaction->status == $value ? 'selected' : '';
        $action .= "<option value='{$value}' {$selected}>{$label}</option>";
    }

    $action .= "</select>
            <input type='hidden' name='note' id='note-{$transaction->id}' value='{$transaction->activeProof?->note}'>
            <button class='btn btn-primary btn-sm mt-2' onclick='saveStatus(\"{$transaction->id}\")'>Simpan</button>";

    return $action;
}
```

### Summary of Changes

| Method | Line | Current Code | Modified Code |
|--------|------|--------------|---------------|
| formatStatusColumn | ~138 | `$transaction->activeProof->ocr_status` | `$transaction->activeProof?->ocr_status` |
| formatStatusColumn | ~140 | `$transaction->activeProof->ocr_amount` | `$transaction->activeProof?->ocr_amount` |
| formatStatusColumn | ~143 | `$transaction->activeProof->ocr_status` | `$transaction->activeProof?->ocr_status` |
| formatStatusColumn | ~148 | `$transaction->activeProof->note` | `$transaction->activeProof?->note` |
| formatActionColumn | ~186 | `$transaction->activeProof->note` | `$transaction->activeProof?->note` |

## Data Models

### Transaction Model

```php
class Transaction extends Model
{
    // Relationship that can return null
    public function activeProof()
    {
        return $this->hasOne(TransactionProof::class)->where('is_active', true);
    }
}
```

**Key Characteristics:**
- Returns a single `TransactionProof` instance or `null`
- Filters by `is_active = true`
- Used in DataTables queries with eager loading: `Transaction::with('activeProof')`

### TransactionProof Model

**Relevant Properties:**
- `proof_image_url` (string|null) - URL to proof image
- `proof_image` (string|null) - Alternative image path
- `ocr_status` (string|null) - OCR processing status ('processed', 'failed', or null)
- `ocr_amount` (decimal|null) - Amount extracted by OCR
- `note` (string|null) - Admin notes or rejection reason
- `is_active` (boolean) - Whether this is the active proof

## Error Handling

### Current Behavior (Before Fix)

When `activeProof` is null and properties are accessed directly:
```php
$transaction->activeProof->note  // Throws: "Attempt to read property on null"
```

### New Behavior (After Fix)

With null-safe operator:
```php
$transaction->activeProof?->note  // Returns: null (no exception)
```

### Error Scenarios

1. **No Active Proof Exists**
   - Scenario: Transaction has no TransactionProof records with `is_active = true`
   - Before: Runtime exception when accessing properties
   - After: Properties evaluate to `null`, handled by existing conditional logic

2. **Active Proof Deleted**
   - Scenario: TransactionProof soft-deleted but relationship not updated
   - Before: Runtime exception
   - After: Graceful null handling

3. **Database Relationship Not Eager Loaded**
   - Scenario: Query doesn't include `->with('activeProof')`
   - Before: N+1 query issue, then potential null exception
   - After: N+1 query issue remains (separate concern), but null-safe access prevents exceptions

## Testing Strategy

### Unit Tests

**Test File**: `tests/Unit/Controllers/Admin/BillControllerTest.php`

#### Test Suite Structure

```php
<?php

namespace Tests\Unit\Controllers\Admin;

use Tests\TestCase;
use App\Models\Transaction;
use App\Models\TransactionProof;
use App\Http\Controllers\Admin\BillController;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BillControllerTest extends TestCase
{
    use RefreshDatabase;

    // Test cases defined below
}
```

#### Test Cases

**1. Test formatProofColumn with null activeProof**
```php
public function test_format_proof_column_returns_dash_when_active_proof_is_null()
{
    $transaction = Transaction::factory()->create();
    // Ensure no activeProof relationship
    $transaction->setRelation('activeProof', null);
    
    $controller = new BillController();
    $reflection = new \ReflectionClass($controller);
    $method = $reflection->getMethod('formatProofColumn');
    $method->setAccessible(true);
    
    $result = $method->invoke($controller, $transaction);
    
    $this->assertEquals('-', $result);
}
```

**2. Test formatProofColumn with valid activeProof**
```php
public function test_format_proof_column_returns_image_html_when_active_proof_exists()
{
    $transaction = Transaction::factory()->create();
    $proof = TransactionProof::factory()->create([
        'transaction_id' => $transaction->id,
        'is_active' => true,
        'proof_image_url' => 'https://example.com/proof.jpg'
    ]);
    $transaction->setRelation('activeProof', $proof);
    
    $controller = new BillController();
    $reflection = new \ReflectionClass($controller);
    $method = $reflection->getMethod('formatProofColumn');
    $method->setAccessible(true);
    
    $result = $method->invoke($controller, $transaction);
    
    $this->assertStringContainsString('https://example.com/proof.jpg', $result);
    $this->assertStringContainsString('<img', $result);
}
```

**3. Test formatStatusColumn with null activeProof**
```php
public function test_format_status_column_returns_badge_when_active_proof_is_null()
{
    $transaction = Transaction::factory()->create([
        'status' => Transaction::STATUS_PENDING_CONFIRMATION
    ]);
    $transaction->setRelation('activeProof', null);
    
    $controller = new BillController();
    $reflection = new \ReflectionClass($controller);
    $method = $reflection->getMethod('formatStatusColumn');
    $method->setAccessible(true);
    
    $result = $method->invoke($controller, $transaction);
    
    $this->assertStringContainsString('badge', $result);
    $this->assertStringContainsString('Menunggu Verifikasi', $result);
}
```

**4. Test formatStatusColumn with OCR processed**
```php
public function test_format_status_column_includes_ocr_badge_when_processed()
{
    $transaction = Transaction::factory()->create([
        'status' => Transaction::STATUS_PENDING_CONFIRMATION
    ]);
    $proof = TransactionProof::factory()->create([
        'transaction_id' => $transaction->id,
        'is_active' => true,
        'ocr_status' => 'processed',
        'ocr_amount' => 100000
    ]);
    $transaction->setRelation('activeProof', $proof);
    
    $controller = new BillController();
    $reflection = new \ReflectionClass($controller);
    $method = $reflection->getMethod('formatStatusColumn');
    $method->setAccessible(true);
    
    $result = $method->invoke($controller, $transaction);
    
    $this->assertStringContainsString('AI Checked', $result);
    $this->assertStringContainsString('100.000', $result);
}
```

**5. Test formatStatusColumn with OCR failed**
```php
public function test_format_status_column_includes_failed_badge_when_ocr_fails()
{
    $transaction = Transaction::factory()->create([
        'status' => Transaction::STATUS_PENDING_CONFIRMATION
    ]);
    $proof = TransactionProof::factory()->create([
        'transaction_id' => $transaction->id,
        'is_active' => true,
        'ocr_status' => 'failed'
    ]);
    $transaction->setRelation('activeProof', $proof);
    
    $controller = new BillController();
    $reflection = new \ReflectionClass($controller);
    $method = $reflection->getMethod('formatStatusColumn');
    $method->setAccessible(true);
    
    $result = $method->invoke($controller, $transaction);
    
    $this->assertStringContainsString('AI Gagal Membaca', $result);
}
```

**6. Test formatStatusColumn with rejected status and note**
```php
public function test_format_status_column_includes_note_when_rejected()
{
    $transaction = Transaction::factory()->create([
        'status' => Transaction::STATUS_REJECTED
    ]);
    $proof = TransactionProof::factory()->create([
        'transaction_id' => $transaction->id,
        'is_active' => true,
        'note' => 'Bukti transfer tidak jelas'
    ]);
    $transaction->setRelation('activeProof', $proof);
    
    $controller = new BillController();
    $reflection = new \ReflectionClass($controller);
    $method = $reflection->getMethod('formatStatusColumn');
    $method->setAccessible(true);
    
    $result = $method->invoke($controller, $transaction);
    
    $this->assertStringContainsString('Bukti transfer tidak jelas', $result);
}
```

**7. Test formatActionColumn with null activeProof**
```php
public function test_format_action_column_returns_html_when_active_proof_is_null()
{
    $this->actingAs(Admin::factory()->create());
    
    $transaction = Transaction::factory()->create([
        'status' => Transaction::STATUS_PENDING_CONFIRMATION
    ]);
    $transaction->setRelation('activeProof', null);
    
    $controller = new BillController();
    $reflection = new \ReflectionClass($controller);
    $method = $reflection->getMethod('formatActionColumn');
    $method->setAccessible(true);
    
    $result = $method->invoke($controller, $transaction);
    
    $this->assertStringContainsString('status-transaction', $result);
    $this->assertStringContainsString('value=""', $result); // Empty note value
}
```

**8. Test formatActionColumn with valid activeProof note**
```php
public function test_format_action_column_includes_note_when_active_proof_exists()
{
    $this->actingAs(Admin::factory()->create());
    
    $transaction = Transaction::factory()->create([
        'status' => Transaction::STATUS_PENDING_CONFIRMATION
    ]);
    $proof = TransactionProof::factory()->create([
        'transaction_id' => $transaction->id,
        'is_active' => true,
        'note' => 'Perlu verifikasi ulang'
    ]);
    $transaction->setRelation('activeProof', $proof);
    
    $controller = new BillController();
    $reflection = new \ReflectionClass($controller);
    $method = $reflection->getMethod('formatActionColumn');
    $method->setAccessible(true);
    
    $result = $method->invoke($controller, $transaction);
    
    $this->assertStringContainsString('Perlu verifikasi ulang', $result);
}
```

### Integration Tests

**Test File**: `tests/Feature/Admin/BillControllerTest.php`

```php
public function test_transaction_datatable_handles_null_active_proof()
{
    $this->actingAs(Admin::factory()->create());
    
    // Create transaction without activeProof
    $transaction = Transaction::factory()->create([
        'type' => Transaction::TYPE_BILL,
        'status' => Transaction::STATUS_PENDING_CONFIRMATION
    ]);
    
    $response = $this->getJson(route('admin.bills.index'));
    
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => ['proof', 'status', 'action']
        ]
    ]);
}
```

### Test Coverage Goals

- **Line Coverage**: 100% of modified lines
- **Branch Coverage**: All conditional branches in modified methods
- **Edge Cases**: Null activeProof, valid activeProof, various OCR states

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Null-Safe Execution

*For any* Transaction instance (with or without an activeProof relationship), calling `formatProofColumn`, `formatStatusColumn`, or `formatActionColumn` SHALL execute without throwing null pointer exceptions.

**Validates: Requirements 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 2.2, 2.3, 2.4**

### Property 2: Proof Column Formatting Correctness

*For any* Transaction instance, `formatProofColumn` SHALL return '-' when activeProof is null OR when both `proof_image_url` and `proof_image` are null, AND SHALL return valid HTML containing an image tag when activeProof exists with a valid image URL.

**Validates: Requirements 2.2, 2.5**

### Property 3: Status Column Formatting Correctness

*For any* Transaction instance with a valid status, `formatStatusColumn` SHALL return HTML containing a status badge, AND SHALL include OCR status badges when activeProof exists with `ocr_status` set to 'processed' or 'failed', AND SHALL include the rejection note when status is REJECTED and activeProof contains a note.

**Validates: Requirements 2.3, 2.6**

### Property 4: Action Column Formatting Correctness

*For any* Transaction instance with PENDING_CONFIRMATION status, `formatActionColumn` SHALL return HTML containing a status select dropdown and a hidden note input field, AND the note input value SHALL be empty when activeProof is null OR SHALL contain the activeProof note when activeProof exists.

**Validates: Requirements 2.4**

### Property 5: Behavioral Preservation

*For any* Transaction instance, the output of `formatProofColumn`, `formatStatusColumn`, and `formatActionColumn` after applying null-safe operators SHALL be functionally equivalent to the output before the change when activeProof is not null.

**Validates: Requirements 3.5, 4.5**

## Security Considerations

### XSS Prevention

**Current State**: The methods generate HTML strings that are marked as raw in DataTables (`->rawColumns(['proof', 'action', 'status'])`).

**Risk Assessment**: 
- `proof_image_url` and `proof_image` are URLs stored in the database
- `note` field contains user-generated content (admin notes)
- `ocr_amount` is numeric data

**Mitigation**:
- The null-safe operator change does not introduce new XSS vectors
- Existing XSS risks (if any) are out of scope for this fix
- Recommendation: Future work should sanitize `note` field output using `htmlspecialchars()` or Laravel's `e()` helper

### Authorization

**Current State**: 
- `formatActionColumn` checks `Auth::user()->can('Edit Tagihan')` before rendering action buttons
- Other format methods don't perform authorization checks (they're called within authorized contexts)

**Impact**: No changes to authorization logic. The null-safe operator does not affect access control.

## Performance Considerations

### Impact Analysis

**Null-Safe Operator Performance**: 
- The `?->` operator has negligible performance overhead compared to explicit null checks
- No additional database queries introduced
- No changes to eager loading strategy

**DataTables Performance**:
- The methods are called once per row in the DataTables response
- Typical page size: 10-50 rows
- Performance impact: < 1ms per request

### Optimization Opportunities (Out of Scope)

1. **Eager Loading**: Ensure `activeProof` is always eager loaded in the query
2. **Caching**: Consider caching formatted output for frequently accessed transactions
3. **View Composition**: Move HTML generation to Blade components for better performance and maintainability

## Deployment Plan

### Pre-Deployment Checklist

- [ ] All unit tests pass
- [ ] All integration tests pass
- [ ] Code review completed
- [ ] No changes to files outside `BillController.php`
- [ ] PHP 8.1+ environment confirmed

### Deployment Steps

1. **Backup**: Ensure database backup is current (no schema changes, but good practice)
2. **Deploy Code**: Deploy updated `BillController.php` to production
3. **Verify**: Check application logs for any null pointer exceptions in BillController
4. **Monitor**: Monitor error rates for 24 hours post-deployment

### Rollback Plan

If issues are detected:
1. Revert `BillController.php` to previous version
2. Investigate root cause
3. Fix and redeploy

**Rollback Risk**: Low - changes are isolated and backward compatible

## Maintenance and Future Work

### Code Maintenance

**Documentation**: 
- Add inline comments explaining null-safety where non-obvious
- Update controller docblocks if needed

**Code Style**:
- Follow existing PSR-12 formatting
- Maintain consistency with Laravel conventions

### Future Enhancements

1. **Refactor HTML Generation**: Move HTML string building to Blade components or view composers
2. **Strengthen Type Safety**: Add return type hints to private methods
3. **Improve Test Coverage**: Add property-based tests using Pest PHP or similar
4. **XSS Hardening**: Sanitize all user-generated content in HTML output
5. **Eager Loading Enforcement**: Add query scopes to ensure activeProof is always loaded

### Related Work

**Similar Issues in Codebase**:
- Other controllers may have similar null-safety issues with relationships
- Consider a codebase-wide audit for unsafe property access patterns
- Implement static analysis tools (PHPStan, Psalm) to detect these issues automatically

## Appendix

### PHP 8.1+ Null-Safe Operator Reference

**Syntax**: `$object?->property`

**Behavior**:
- If `$object` is null, the entire expression evaluates to null
- If `$object` is not null, the property is accessed normally
- Can be chained: `$a?->b?->c`

**Example**:
```php
// Before (throws exception if $user is null)
$name = $user->profile->name;

// After (returns null if $user or $profile is null)
$name = $user?->profile?->name;
```

### Laravel Testing Utilities Reference

**Reflection for Private Methods**:
```php
$reflection = new \ReflectionClass($controller);
$method = $reflection->getMethod('privateMethodName');
$method->setAccessible(true);
$result = $method->invoke($controller, $arg1, $arg2);
```

**Factory Usage**:
```php
// Create model instance
$model = Model::factory()->create(['field' => 'value']);

// Set relationship without database query
$model->setRelation('relationName', $relatedModel);
```

### Requirements Traceability Matrix

| Requirement | Design Section | Test Coverage |
|-------------|----------------|---------------|
| 1.1 - 1.7 | Implementation Details | Unit Tests 1-8 |
| 2.1 | Testing Strategy | Test Suite Structure |
| 2.2 | Implementation Details, Property 2 | Unit Test 1 |
| 2.3 | Implementation Details, Property 3 | Unit Test 3 |
| 2.4 | Implementation Details, Property 4 | Unit Test 7 |
| 2.5 | Implementation Details, Property 2 | Unit Test 2 |
| 2.6 | Implementation Details, Property 3 | Unit Tests 4-6 |
| 2.7 | Testing Strategy | All Unit Tests |
| 3.1 - 3.4 | Architecture | Deployment Checklist |
| 3.5 | Implementation Details, Property 5 | Integration Tests |
| 4.1 | Implementation Details | Code Changes |
| 4.2 | Architecture | Laravel Conventions |
| 4.3 | Maintenance | Code Style |
| 4.4 | Architecture | No Dependencies |
| 4.5 | Implementation Details, Property 5 | Unit Tests |
