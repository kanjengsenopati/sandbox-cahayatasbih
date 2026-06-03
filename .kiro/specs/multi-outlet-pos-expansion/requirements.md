# Requirements Document - Multi Outlet POS Expansion

## Introduction

Ekspansi sistem Point of Sale (PoS) untuk mendukung multi-outlet dengan penambahan outlet CT-Mart sebagai tambahan dari Cahaya Mart yang sudah ada. Sistem ini akan mengimplementasikan pemisahan akses berdasarkan role (Manager vs Kasir), user management dengan outlet-specific scopes, pelacakan transaksi terpisah per outlet, skema ID unik untuk invoice/transaksi, pelaporan terpusat, dan manajemen arus kas per outlet dan konsolidasi.

## Glossary

- **Outlet**: Lokasi fisik toko/mart yang melakukan transaksi POS
- **Cahaya_Mart**: Outlet utama yang sudah ada dalam sistem
- **CT_Mart**: Outlet baru yang akan ditambahkan ke sistem
- **Manager**: Role pengguna dengan akses penuh ke data outlet dan transaksi
- **Cashier**: Role pengguna dengan akses terbatas untuk melakukan transaksi di outlet tertentu
- **POS_System**: Sistem Point of Sale yang mengelola transaksi retail
- **Transaction_ID**: Identifier unik untuk setiap transaksi dalam sistem
- **Invoice_ID**: Identifier unik untuk setiap invoice yang dihasilkan
- **User_Manager**: Komponen sistem yang mengelola pengguna dan role
- **Scope**: Batasan akses pengguna terhadap outlet atau fitur tertentu
- **Cash_Flow_Manager**: Komponen yang mengelola arus kas per outlet
- **Transaction_Tracer**: Komponen yang melacak dan mengaudit transaksi
- **Report_Generator**: Komponen yang menghasilkan laporan konsolidasi dan per outlet

## Requirements

### Requirement 1: Multi-Outlet Infrastructure

**User Story:** Sebagai Manager sistem, saya ingin dapat mengelola multiple outlet, sehingga sistem dapat mengakomodasi Cahaya Mart dan CT-Mart secara terpisah.

#### Acceptance Criteria

1. THE POS_System SHALL support multiple outlet configuration
2. WHEN an outlet is created, THE POS_System SHALL assign unique outlet identifier  
3. THE POS_System SHALL maintain separate data context for each outlet
4. WHEN accessing outlet data, THE POS_System SHALL filter based on outlet scope and require separate API calls for cross-outlet operations
5. THE POS_System SHALL preserve existing Cahaya_Mart data integrity during expansion

### Requirement 2: Role-Based Access Control

**User Story:** Sebagai Administrator, saya ingin mengatur akses berdasarkan role Manager dan Kasir, sehingga setiap pengguna hanya dapat mengakses fitur yang sesuai dengan perannya.

#### Acceptance Criteria

1. THE User_Manager SHALL implement Manager role with full outlet access
2. THE User_Manager SHALL implement Cashier role with limited transaction access
3. WHEN a Manager logs in, THE POS_System SHALL grant access to all outlet data
4. WHEN a Cashier logs in, THE POS_System SHALL grant access only to assigned outlet
5. THE User_Manager SHALL prevent unauthorized access to restricted functions

### Requirement 3: Outlet-Specific User Scopes

**User Story:** Sebagai Administrator, saya ingin mengatur scope pengguna per outlet, sehingga kasir hanya dapat mengakses outlet yang ditugaskan kepadanya.

#### Acceptance Criteria

1. WHEN creating a user, THE User_Manager SHALL assign outlet scope
2. THE User_Manager SHALL support multiple outlet assignment for Manager role
3. THE User_Manager SHALL restrict single outlet assignment for Cashier role
4. WHEN accessing system features, THE POS_System SHALL validate user scope
5. THE User_Manager SHALL allow scope modification by authorized administrators

### Requirement 4: Unique Transaction and Invoice ID Schemes

**User Story:** Sebagai Manager, saya ingin setiap outlet memiliki skema ID transaksi dan invoice yang unik, sehingga dapat dengan mudah mengidentifikasi asal transaksi.

#### Acceptance Criteria

1. WHEN generating Transaction_ID, THE POS_System SHALL include outlet-specific prefix
2. WHEN generating Invoice_ID, THE POS_System SHALL include outlet-specific prefix  
3. THE POS_System SHALL use "CHY" prefix for Cahaya_Mart transactions
4. THE POS_System SHALL use "CTM" prefix for CT_Mart transactions
5. THE POS_System SHALL ensure Transaction_ID and Invoice_ID uniqueness across system
6. THE POS_System SHALL maintain sequential numbering per outlet per day

### Requirement 5: Transaction Tracking and Audit

**User Story:** Sebagai Manager, saya ingin dapat melacak semua transaksi dengan audit trail lengkap, sehingga dapat memantau aktivitas dan performa setiap outlet.

#### Acceptance Criteria

1. WHEN a transaction occurs, THE Transaction_Tracer SHALL record complete audit trail
2. THE Transaction_Tracer SHALL capture outlet identifier in transaction record
3. THE Transaction_Tracer SHALL capture cashier information in transaction record
4. THE Transaction_Tracer SHALL record transaction timestamp with timezone
5. WHEN retrieving transaction history, THE POS_System SHALL support outlet filtering
6. THE Transaction_Tracer SHALL maintain transaction modification history

### Requirement 6: Cash Flow Management per Outlet

**User Story:** Sebagai Manager, saya ingin mengelola arus kas per outlet secara terpisah, sehingga dapat memantau performa keuangan masing-masing outlet.

#### Acceptance Criteria

1. THE Cash_Flow_Manager SHALL track cash flow separately per outlet
2. WHEN recording cash transaction, THE Cash_Flow_Manager SHALL associate with outlet
3. THE Cash_Flow_Manager SHALL calculate daily cash position per outlet
4. THE Cash_Flow_Manager SHALL track cash inflow and outflow categories per outlet
5. WHEN generating cash report, THE Cash_Flow_Manager SHALL support outlet filtering

### Requirement 7: Consolidated Reporting System

**User Story:** Sebagai Manager, saya ingin dapat melihat laporan konsolidasi dari semua outlet, sehingga dapat memantau performa bisnis secara keseluruhan.

#### Acceptance Criteria

1. THE Report_Generator SHALL create consolidated reports across all outlets
2. THE Report_Generator SHALL create individual outlet reports
3. WHEN generating sales report, THE Report_Generator SHALL include outlet breakdown
4. WHEN generating cash flow report, THE Report_Generator SHALL support consolidation view
5. THE Report_Generator SHALL export reports in multiple formats (PDF, Excel)
6. WHEN accessing reports, THE POS_System SHALL respect user outlet scope

### Requirement 8: Transaction Data Migration

**User Story:** Sebagai Administrator, saya ingin memastikan data transaksi existing tetap kompatibel, sehingga tidak ada gangguan pada operasional yang sudah berjalan.

#### Acceptance Criteria

1. WHEN migrating existing data, THE POS_System SHALL assign Cahaya_Mart outlet to existing transactions
2. THE POS_System SHALL update existing Transaction_ID format without breaking references
3. THE POS_System SHALL preserve all existing transaction relationships
4. WHEN querying historical data, THE POS_System SHALL return consistent results
5. THE POS_System SHALL maintain backward compatibility for existing reports

### Requirement 9: User Interface Adaptation

**User Story:** Sebagai Kasir, saya ingin interface yang menampilkan informasi outlet yang relevan, sehingga dapat bekerja dengan efisien di outlet yang ditugaskan.

#### Acceptance Criteria

1. WHEN Cashier logs in, THE POS_System SHALL display assigned outlet information
2. THE POS_System SHALL show outlet-specific transaction forms for Cashier
3. WHEN Manager logs in, THE POS_System SHALL provide outlet selection interface
4. THE POS_System SHALL display outlet identifier in transaction screens
5. WHEN generating receipts, THE POS_System SHALL include outlet branding information

### Requirement 10: System Configuration Management

**User Story:** Sebagai Administrator, saya ingin mengkonfigurasi pengaturan per outlet, sehingga setiap outlet dapat beroperasi sesuai dengan kebutuhan spesifiknya.

#### Acceptance Criteria

1. THE POS_System SHALL support outlet-specific configuration settings
2. THE POS_System SHALL allow different tax rates per outlet
3. THE POS_System SHALL support outlet-specific receipt templates
4. WHEN configuring outlet, THE POS_System SHALL validate configuration completeness
5. THE POS_System SHALL maintain default settings inheritance from main system