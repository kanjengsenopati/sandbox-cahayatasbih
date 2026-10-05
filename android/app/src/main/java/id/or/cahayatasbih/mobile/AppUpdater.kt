package id.or.cahayatasbih.mobile

import android.app.Activity
import android.app.DownloadManager
import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.content.IntentFilter
import android.net.Uri
import android.os.Build
import android.os.Environment
import android.widget.Toast
import androidx.core.content.FileProvider
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import okhttp3.OkHttpClient
import okhttp3.Request
import org.json.JSONObject
import java.io.File
import java.util.concurrent.TimeUnit

class AppUpdater(private val activity: Activity) {

    private val client = OkHttpClient.Builder()
        .connectTimeout(10, TimeUnit.SECONDS)
        .readTimeout(10, TimeUnit.SECONDS)
        .build()

    fun checkForUpdate(silent: Boolean = true) {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val request = Request.Builder()
                    .url(BuildConfig.VERSION_CHECK_URL)
                    .header("User-Agent", "CTMobileApp/${BuildConfig.VERSION_NAME}")
                    .build()

                val response = client.newCall(request).execute()
                val body = response.body?.string() ?: return@launch

                val json = JSONObject(body)
                if (json.optString("status") != "success") return@launch

                val data = json.getJSONObject("data")
                val latestCode = data.getInt("latest_version_code")
                val latestName = data.getString("latest_version_name")
                val downloadUrl = data.getString("download_url")
                val releaseNotes = data.optString("release_notes", "Pembaruan sistem dan perbaikan performa.")
                val forceUpdate = data.optBoolean("force_update", false)

                if (latestCode > BuildConfig.VERSION_CODE) {
                    withContext(Dispatchers.Main) {
                        showUpdateDialog(latestName, downloadUrl, releaseNotes, forceUpdate)
                    }
                } else if (!silent) {
                    withContext(Dispatchers.Main) {
                        Toast.makeText(activity, "Aplikasi Anda sudah versi terbaru.", Toast.LENGTH_SHORT).show()
                    }
                }
            } catch (e: Exception) {
                if (!silent) {
                    withContext(Dispatchers.Main) {
                        Toast.makeText(activity, "Gagal memeriksa pembaruan: ${e.localizedMessage}", Toast.LENGTH_SHORT).show()
                    }
                }
            }
        }
    }

    private fun showUpdateDialog(
        versionName: String,
        downloadUrl: String,
        releaseNotes: String,
        forceUpdate: Boolean
    ) {
        if (activity.isFinishing || activity.isDestroyed) return

        val builder = MaterialAlertDialogBuilder(activity)
            .setTitle("${activity.getString(R.string.update_available)} (v$versionName)")
            .setMessage(releaseNotes)
            .setPositiveButton(R.string.update_now) { _, _ ->
                downloadAndInstallApk(downloadUrl)
            }
            .setCancelable(!forceUpdate)

        if (!forceUpdate) {
            builder.setNegativeButton(R.string.later) { dialog, _ ->
                dialog.dismiss()
            }
        }

        builder.show()
    }

    private fun downloadAndInstallApk(downloadUrl: String) {
        Toast.makeText(activity, "Mulai mengunduh pembaruan...", Toast.LENGTH_SHORT).show()

        val fileName = "ct-mobile-update.apk"
        val destinationDir = activity.getExternalFilesDir(Environment.DIRECTORY_DOWNLOADS)
        val apkFile = File(destinationDir, fileName)

        // Hapus file lama jika ada
        if (apkFile.exists()) {
            apkFile.delete()
        }

        val request = DownloadManager.Request(Uri.parse(downloadUrl))
            .setTitle("CT-Mobile Update")
            .setDescription("Mengunduh versi terbaru aplikasi...")
            .setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE_NOTIFY_COMPLETED)
            .setDestinationUri(Uri.fromFile(apkFile))
            .setMimeType("application/vnd.android.package-archive")

        val downloadManager = activity.getSystemService(Context.DOWNLOAD_SERVICE) as DownloadManager
        val downloadId = downloadManager.enqueue(request)

        val onComplete = object : BroadcastReceiver() {
            override fun onReceive(context: Context?, intent: Intent?) {
                val id = intent?.getLongExtra(DownloadManager.EXTRA_DOWNLOAD_ID, -1L) ?: -1L
                if (id == downloadId) {
                    activity.unregisterReceiver(this)
                    installApk(apkFile)
                }
            }
        }

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            activity.registerReceiver(
                onComplete,
                IntentFilter(DownloadManager.ACTION_DOWNLOAD_COMPLETE),
                Context.RECEIVER_EXPORTED
            )
        } else {
            activity.registerReceiver(
                onComplete,
                IntentFilter(DownloadManager.ACTION_DOWNLOAD_COMPLETE)
            )
        }
    }

    private fun installApk(file: File) {
        if (!file.exists()) {
            Toast.makeText(activity, "Berkas unduhan tidak ditemukan.", Toast.LENGTH_SHORT).show()
            return
        }

        try {
            val contentUri = FileProvider.getUriForFile(
                activity,
                "${activity.packageName}.fileprovider",
                file
            )

            val installIntent = Intent(Intent.ACTION_VIEW).apply {
                setDataAndType(contentUri, "application/vnd.android.package-archive")
                flags = Intent.FLAG_GRANT_READ_URI_PERMISSION or Intent.FLAG_ACTIVITY_NEW_TASK
            }

            activity.startActivity(installIntent)
        } catch (e: Exception) {
            Toast.makeText(activity, "Gagal memasang pembaruan: ${e.localizedMessage}", Toast.LENGTH_LONG).show()
        }
    }
}
