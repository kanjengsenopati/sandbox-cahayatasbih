/**
 * Google Apps Script Web App Exporter
 * Ponpes Cahaya Tasbih - Laporan Transaksi
 * 
 * CARA DEPLOY:
 * 1. Buka https://script.google.com
 * 2. Buat "New Project" (Proyek Baru)
 * 3. Hapus kode default, lalu paste seluruh isi file ini ke Code.gs
 * 4. Klik tombol "Deploy" di pojok kanan atas > "New deployment"
 * 5. Pilih icon gear (Select type) > "Web app"
 * 6. Konfigurasi:
 *    - Description: Exporter Laporan Transaksi
 *    - Execute as: "Me" (Email Google Anda)
 *    - Who has access: "Anyone" (Siapa saja - agar web app Anda bisa mengirim data)
 * 7. Klik "Deploy", berikan izin akses (Authorize access), lalu salin "Web App URL" (akhiran /exec)
 * 8. Tempelkan URL tersebut ke file .env di Laravel:
 *    GOOGLE_SHEETS_WEBAPP_URL="https://script.google.com/macros/s/.../exec"
 */

function doPost(e) {
  try {
    var data = JSON.parse(e.postData.contents);
    var title = data.title || "Laporan Transaksi - Cahaya Tasbih";
    var values = data.values; // Array 2D: [ [headings...], [row1...], [row2...] ]

    if (!values || !Array.isArray(values) || values.length === 0) {
      return ContentService.createTextOutput(JSON.stringify({
        success: false,
        message: "Payload values kosong atau tidak valid."
      })).setMimeType(ContentService.MimeType.JSON);
    }

    // 1. Buat Spreadsheet Baru di Google Drive
    var ss = SpreadsheetApp.create(title);
    var sheet = ss.getActiveSheet();
    sheet.setName("Data Transaksi");

    // 2. Tulis seluruh baris dan kolom secara batch (Cepat & Hemat Kuota)
    var numRows = values.length;
    var numCols = values[0].length;
    var range = sheet.getRange(1, 1, numRows, numCols);
    range.setValues(values);

    // 3. Format Baris Header (Baris 1)
    var headerRange = sheet.getRange(1, 1, 1, numCols);
    headerRange.setFontWeight("bold");
    headerRange.setBackground("#2563EB"); // Blue-600
    headerRange.setFontColor("#FFFFFF");
    headerRange.setHorizontalAlignment("center");
    headerRange.setVerticalAlignment("middle");
    sheet.setRowHeight(1, 28);
    sheet.setFrozenRows(1);

    // 4. Border Tipis pada Seluruh Data
    range.setBorder(true, true, true, true, true, true, "#E2E8F0", SpreadsheetApp.BorderStyle.SOLID);

    // 5. Auto Resize Kolom secara aman (kolom 1 s/d min(numCols, 22))
    for (var col = 1; col <= Math.min(numCols, 22); col++) {
      sheet.autoResizeColumn(col);
    }

    // 6. Set Sharing Permission agar pembuat & pembawa link bisa membuka dan mengedit
    try {
      var file = DriveApp.getFileById(ss.getId());
      file.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.EDIT);
    } catch (permError) {
      // Abaikan jika akun Google Workspace organisasi memiliki restriction sharing
    }

    return ContentService.createTextOutput(JSON.stringify({
      success: true,
      url: ss.getUrl()
    })).setMimeType(ContentService.MimeType.JSON);

  } catch (error) {
    return ContentService.createTextOutput(JSON.stringify({
      success: false,
      message: error.toString()
    })).setMimeType(ContentService.MimeType.JSON);
  }
}

function doGet(e) {
  return ContentService.createTextOutput("Google Apps Script Exporter is Active!").setMimeType(ContentService.MimeType.TEXT);
}
