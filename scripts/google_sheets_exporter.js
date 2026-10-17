/**
 * Google Apps Script Web App Exporter & Auto-Backup
 * Ponpes Cahaya Tasbih - Laporan Transaksi & Auto Placement Sheets
 * 
 * CARA DEPLOY / UPDATE:
 * 1. Buka https://script.google.com
 * 2. Buka project script yang sudah ada atau buat "New Project"
 * 3. Hapus kode lama, lalu paste seluruh isi file ini ke Code.gs
 * 4. Klik tombol "Deploy" di pojok kanan atas > "Manage deployments" (atau "New deployment")
 * 5. Klik icon pensil (Edit) pada deployment aktif > pilih Version: "New version"
 * 6. Pastikan konfigurasi:
 *    - Execute as: "Me" (Email Google pemilik spreadsheet)
 *    - Who has access: "Anyone" (Siapa saja - agar backend Laravel bisa mengirim payload)
 * 7. Klik "Deploy", lalu salin Web App URL (akhiran /exec)
 * 8. Tempelkan URL tersebut ke file .env di Laravel:
 *    GOOGLE_SHEETS_WEBAPP_URL="https://script.google.com/macros/s/.../exec"
 *    GOOGLE_SHEETS_BACKUP_SPREADSHEET_ID="1NPPyqpaArOVW0vAsZ4jjBwOLuK9mutnhqWtgQDeSEY8"
 */

function doPost(e) {
  try {
    var data = JSON.parse(e.postData.contents);
    
    // =========================================================================
    // MODE 1: AUTO PLACEMENT KE SPREADSHEET EKSISTING (Backup Mode)
    // =========================================================================
    if (data.spreadsheetId || data.action === "backup_placement") {
      var targetSpreadsheetId = data.spreadsheetId || "1NPPyqpaArOVW0vAsZ4jjBwOLuK9mutnhqWtgQDeSEY8";
      var ss = SpreadsheetApp.openById(targetSpreadsheetId);

      // Support multi-sheets batch atau single-sheet
      var sheetsToProcess = [];
      if (data.sheets && Array.isArray(data.sheets)) {
        sheetsToProcess = data.sheets;
      } else if (data.sheetName && data.values) {
        sheetsToProcess.push({
          sheetName: data.sheetName,
          values: data.values,
          headerColor: data.headerColor,
          freezeCols: data.freezeCols
        });
      }

      if (sheetsToProcess.length === 0) {
        return ContentService.createTextOutput(JSON.stringify({
          success: false,
          message: "Tidak ada data sheet yang diberikan (sheetName/values kosong)."
        })).setMimeType(ContentService.MimeType.JSON);
      }

      var processedCount = 0;
      for (var s = 0; s < sheetsToProcess.length; s++) {
        var item = sheetsToProcess[s];
        var sName = item.sheetName;
        var sValues = item.values;

        if (!sValues || !Array.isArray(sValues) || sValues.length === 0) {
          continue;
        }

        // Cari atau buat sheet tab baru
        var targetSheet = ss.getSheetByName(sName);
        if (!targetSheet) {
          targetSheet = ss.insertSheet(sName);
        }

        // STRATEGI: INSERT (Clear & Replace)
        // Membersihkan data lama agar 100% konsisten dengan database
        targetSheet.clearContents();

        var numRows = sValues.length;
        var numCols = sValues[0].length;
        var range = targetSheet.getRange(1, 1, numRows, numCols);
        range.setValues(sValues);

        // Format Header (Baris 1) - Warna Ungu (#7C3AED) sesuai template screenshot
        var headerColor = item.headerColor || data.headerColor || "#7C3AED";
        var headerRange = targetSheet.getRange(1, 1, 1, numCols);
        headerRange.setFontWeight("bold");
        headerRange.setBackground(headerColor);
        headerRange.setFontColor("#FFFFFF");
        headerRange.setHorizontalAlignment("center");
        headerRange.setVerticalAlignment("middle");
        targetSheet.setRowHeight(1, 28);
        targetSheet.setFrozenRows(1);

        // Freeze Pane kolom A..C (No, Tanggal, Nama) jika ditentukan (default: 3)
        var freezeCols = (item.freezeCols !== undefined) ? item.freezeCols : (data.freezeCols !== undefined ? data.freezeCols : 3);
        if (freezeCols > 0) {
          targetSheet.setFrozenColumns(freezeCols);
        }

        // Border Tipis pada Seluruh Data
        range.setBorder(true, true, true, true, true, true, "#E2E8F0", SpreadsheetApp.BorderStyle.SOLID);

        // Auto Resize Kolom secara aman
        for (var col = 1; col <= Math.min(numCols, 26); col++) {
          targetSheet.autoResizeColumn(col);
        }

        processedCount++;
      }

      return ContentService.createTextOutput(JSON.stringify({
        success: true,
        mode: "placement",
        spreadsheetId: targetSpreadsheetId,
        url: ss.getUrl(),
        sheetsProcessed: processedCount
      })).setMimeType(ContentService.MimeType.JSON);
    }

    // =========================================================================
    // MODE 2: EXPORT BARU KE SPREADSHEET BARU (Legacy / Manual Export Mode)
    // =========================================================================
    var title = data.title || "Laporan Transaksi - Cahaya Tasbih";
    var values = data.values; // Array 2D: [ [headings...], [row1...], [row2...] ]

    if (!values || !Array.isArray(values) || values.length === 0) {
      return ContentService.createTextOutput(JSON.stringify({
        success: false,
        message: "Payload values kosong atau tidak valid."
      })).setMimeType(ContentService.MimeType.JSON);
    }

    // 1. Buat Spreadsheet Baru di Google Drive
    var ssNew = SpreadsheetApp.create(title);
    var sheet = ssNew.getActiveSheet();
    sheet.setName("Data Transaksi");

    // 2. Tulis seluruh baris dan kolom secara batch
    var numRowsNew = values.length;
    var numColsNew = values[0].length;
    var rangeNew = sheet.getRange(1, 1, numRowsNew, numColsNew);
    rangeNew.setValues(values);

    // 3. Format Baris Header (Baris 1)
    var headerRangeNew = sheet.getRange(1, 1, 1, numColsNew);
    headerRangeNew.setFontWeight("bold");
    headerRangeNew.setBackground("#2563EB"); // Blue-600
    headerRangeNew.setFontColor("#FFFFFF");
    headerRangeNew.setHorizontalAlignment("center");
    headerRangeNew.setVerticalAlignment("middle");
    sheet.setRowHeight(1, 28);
    sheet.setFrozenRows(1);

    // 4. Border Tipis pada Seluruh Data
    rangeNew.setBorder(true, true, true, true, true, true, "#E2E8F0", SpreadsheetApp.BorderStyle.SOLID);

    // 5. Auto Resize Kolom
    for (var col = 1; col <= Math.min(numColsNew, 26); col++) {
      sheet.autoResizeColumn(col);
    }

    // 6. Set Sharing Permission
    try {
      var file = DriveApp.getFileById(ssNew.getId());
      file.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.EDIT);
    } catch (permError) {
      // Abaikan jika akun Google Workspace organisasi memiliki restriction sharing
    }

    return ContentService.createTextOutput(JSON.stringify({
      success: true,
      mode: "new_spreadsheet",
      url: ssNew.getUrl()
    })).setMimeType(ContentService.MimeType.JSON);

  } catch (error) {
    return ContentService.createTextOutput(JSON.stringify({
      success: false,
      message: error.toString()
    })).setMimeType(ContentService.MimeType.JSON);
  }
}

function doGet(e) {
  return ContentService.createTextOutput("Google Apps Script Exporter & Auto-Backup is Active!").setMimeType(ContentService.MimeType.TEXT);
}
