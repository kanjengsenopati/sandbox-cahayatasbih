/**
 * Google Apps Script Web App: Master Financial Report Auto-Backup Receiver
 * Ponpes Cahaya Tasbih - Khusus Super Admin & Background Auto-Backup
 * 
 * CARA DEPLOY:
 * 1. Buka https://script.google.com
 * 2. Buat "New Project" (Proyek Baru) beri nama "CT Master Sheets Backup Receiver"
 * 3. Hapus kode default, lalu paste seluruh isi file ini ke Code.gs
 * 4. Klik tombol "Deploy" di pojok kanan atas > "New deployment"
 * 5. Pilih icon gear (Select type) > "Web app"
 * 6. Konfigurasi:
 *    - Description: Master Sheets Backup Receiver (Super Admin)
 *    - Execute as: "Me" (Akun Google pemilik master spreadsheet)
 *    - Who has access: "Anyone" (Siapa saja - agar server Laravel bisa mengirim payload webhook)
 * 7. Klik "Deploy", berikan izin akses (Authorize access), lalu salin "Web App URL" (akhiran /exec)
 * 8. Tempelkan URL tersebut ke file .env di Laravel:
 *    GOOGLE_SHEETS_BACKUP_WEBAPP_URL="https://script.google.com/macros/s/.../exec"
 *    GOOGLE_SHEETS_BACKUP_SPREADSHEET_ID="1NPPyqpaArOVW0vAsZ4jjBwOLuK9mutnhqWtgQDeSEY8"
 */

function doPost(e) {
  try {
    var data = JSON.parse(e.postData.contents);
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
        message: "Payload values kosong atau sheetName tidak ditentukan."
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

      // Cari atau buat sheet tab baru jika belum ada
      var targetSheet = ss.getSheetByName(sName);
      if (!targetSheet) {
        targetSheet = ss.insertSheet(sName);
      }

      // STRATEGI: INSERT (Clear & Replace)
      // Membersihkan data lama agar 100% konsisten dengan ground truth database
      targetSheet.clearContents();

      var numRows = sValues.length;
      var numCols = sValues[0].length;
      var range = targetSheet.getRange(1, 1, numRows, numCols);
      range.setValues(sValues);

      // Format Baris Header (Baris 1) - Warna Ungu (#7C3AED) sesuai template master spreadsheet
      var headerColor = item.headerColor || data.headerColor || "#7C3AED";
      var headerRange = targetSheet.getRange(1, 1, 1, numCols);
      headerRange.setFontWeight("bold");
      headerRange.setBackground(headerColor);
      headerRange.setFontColor("#FFFFFF");
      headerRange.setHorizontalAlignment("center");
      headerRange.setVerticalAlignment("middle");
      targetSheet.setRowHeight(1, 28);
      targetSheet.setFrozenRows(1);

      // Freeze Pane kolom A..C (No, Tanggal, Nama)
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

  } catch (error) {
    return ContentService.createTextOutput(JSON.stringify({
      success: false,
      message: error.toString()
    })).setMimeType(ContentService.MimeType.JSON);
  }
}

function doGet(e) {
  return ContentService.createTextOutput("CT Master Sheets Backup Receiver is Active!").setMimeType(ContentService.MimeType.TEXT);
}
