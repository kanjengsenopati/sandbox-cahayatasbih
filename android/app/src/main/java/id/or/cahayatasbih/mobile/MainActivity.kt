package id.or.cahayatasbih.mobile

import android.Manifest
import android.annotation.SuppressLint
import android.app.DownloadManager
import android.content.Context
import android.content.Intent
import android.content.pm.PackageManager
import android.net.Uri
import android.os.Build
import android.os.Bundle
import android.os.Environment
import android.provider.MediaStore
import android.view.View
import android.webkit.CookieManager
import android.webkit.ValueCallback
import android.webkit.WebChromeClient
import android.webkit.WebSettings
import android.widget.Button
import android.widget.ProgressBar
import android.widget.Toast
import androidx.activity.OnBackPressedCallback
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import androidx.core.content.FileProvider
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout
import id.or.cahayatasbih.mobile.databinding.ActivityMainBinding
import java.io.File
import java.io.IOException
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale

class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding
    private var fileUploadCallback: ValueCallback<Array<Uri>>? = null
    private var cameraImageUri: Uri? = null

    private var backPressedTime: Long = 0
    private val backPressDebounce: Long = 2000

    private val appUpdater by lazy { AppUpdater(this) }

    // Launcher untuk File Chooser (Kamera & Galeri)
    private val fileChooserLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (fileUploadCallback == null) return@registerForActivityResult

        val results: Array<Uri>? = when {
            result.resultCode == RESULT_OK -> {
                val data = result.data
                if (data?.data != null) {
                    arrayOf(data.data!!)
                } else if (cameraImageUri != null) {
                    arrayOf(cameraImageUri!!)
                } else {
                    null
                }
            }
            else -> null
        }

        fileUploadCallback?.onReceiveValue(results)
        fileUploadCallback = null
        cameraImageUri = null
    }

    // Launcher untuk Runtime Permission (Kamera & Notifikasi)
    private val permissionLauncher = registerForActivityResult(
        ActivityResultContracts.RequestMultiplePermissions()
    ) { permissions ->
        // Periksa izin yang diberikan jika diperlukan
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        setupPermissions()
        setupWebView()
        setupSwipeRefresh()
        setupBackButton()
        setupErrorHandling()

        // Muat URL Awal Aplikasi
        binding.webView.loadUrl(BuildConfig.BASE_URL)

        // Periksa Pembaruan Aplikasi Sideload di Background
        appUpdater.checkForUpdate(silent = true)
    }

    private fun setupPermissions() {
        val permissions = mutableListOf<String>()

        if (ContextCompat.checkSelfPermission(this, Manifest.permission.CAMERA) != PackageManager.PERMISSION_GRANTED) {
            permissions.add(Manifest.permission.CAMERA)
        }

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            if (ContextCompat.checkSelfPermission(this, Manifest.permission.POST_NOTIFICATIONS) != PackageManager.PERMISSION_GRANTED) {
                permissions.add(Manifest.permission.POST_NOTIFICATIONS)
            }
        }

        if (permissions.isNotEmpty()) {
            permissionLauncher.launch(permissions.toTypedArray())
        }
    }

    @SuppressLint("SetJavaScriptEnabled")
    private fun setupWebView() {
        val webView = binding.webView
        val settings = webView.settings

        // Konfigurasi Engine Webview Modern
        settings.javaScriptEnabled = true
        settings.domStorageEnabled = true
        settings.databaseEnabled = true
        settings.allowFileAccess = true
        settings.allowContentAccess = true
        settings.loadWithOverviewMode = true
        settings.useWideViewPort = true
        settings.setSupportZoom(false)
        settings.builtInZoomControls = false
        settings.displayZoomControls = false
        settings.mediaPlaybackRequiresUserGesture = false
        settings.cacheMode = WebSettings.LOAD_DEFAULT

        // Identitas User Agent Resmi CT-Mobile Native
        val defaultUserAgent = settings.userAgentString
        settings.userAgentString = "$defaultUserAgent CTMobileApp/${BuildConfig.VERSION_NAME} (Android)"

        // Konfigurasi Sinkronisasi Cookie Laravel
        val cookieManager = CookieManager.getInstance()
        cookieManager.setAcceptCookie(true)
        cookieManager.setAcceptThirdPartyCookies(webView, true)

        // WebChromeClient (Kamera, Galeri, Progress Bar, Dialog)
        webView.webChromeClient = CustomWebChromeClient(
            context = this,
            progressBar = binding.progressBar,
            onFileChooser = { callback, _ ->
                openFileChooser(callback)
            }
        )

        // WebViewClient (URL Routing, Deeplink, Error Handling)
        webView.webViewClient = CustomWebViewClient(
            context = this,
            swipeRefreshLayout = binding.swipeRefreshLayout,
            errorView = binding.includedError.root
        )

        // Download Listener untuk Kuitansi, Raport, atau Berkas PDF
        webView.setDownloadListener { url, userAgent, contentDisposition, mimetype, _ ->
            handleFileDownload(url, userAgent, contentDisposition, mimetype)
        }
    }

    private fun setupSwipeRefresh() {
        binding.swipeRefreshLayout.setColorSchemeColors(
            ContextCompat.getColor(this, R.color.accent_purple),
            ContextCompat.getColor(this, R.color.primary)
        )

        // Hanya aktifkan pull-to-refresh ketika scroll berada di bagian paling atas
        binding.swipeRefreshLayout.setOnChildScrollUpCallback { _, _ ->
            binding.webView.scrollY > 0
        }

        binding.swipeRefreshLayout.setOnRefreshListener {
            if (binding.includedError.root.visibility == View.VISIBLE) {
                binding.webView.loadUrl(BuildConfig.BASE_URL)
            } else {
                binding.webView.reload()
            }
        }
    }

    private fun setupBackButton() {
        onBackPressedDispatcher.addCallback(this, object : OnBackPressedCallback(true) {
            override fun handleOnBackPressed() {
                if (binding.includedError.root.visibility == View.VISIBLE) {
                    finish()
                    return
                }

                if (binding.webView.canGoBack()) {
                    binding.webView.goBack()
                } else {
                    val currentTime = System.currentTimeMillis()
                    if (currentTime - backPressedTime < backPressDebounce) {
                        finish()
                    } else {
                        backPressedTime = currentTime
                        Toast.makeText(this@MainActivity, getString(R.string.press_again_to_exit), Toast.LENGTH_SHORT).show()
                    }
                }
            }
        })
    }

    private fun setupErrorHandling() {
        val btnRetry: Button = binding.includedError.root.findViewById(R.id.btnRetry)
        btnRetry.setOnClickListener {
            binding.includedError.root.visibility = View.GONE
            binding.webView.visibility = View.VISIBLE
            binding.webView.reload()
        }
    }

    private fun openFileChooser(callback: ValueCallback<Array<Uri>>?): Boolean {
        fileUploadCallback?.onReceiveValue(null)
        fileUploadCallback = callback

        val chooserIntents = mutableListOf<Intent>()

        // 1. Intent Ambil Foto via Kamera
        val takePictureIntent = Intent(MediaStore.ACTION_IMAGE_CAPTURE)
        try {
            val photoFile = createTempImageFile()
            cameraImageUri = FileProvider.getUriForFile(
                this,
                "${packageName}.fileprovider",
                photoFile
            )
            takePictureIntent.putExtra(MediaStore.EXTRA_OUTPUT, cameraImageUri)
            takePictureIntent.addFlags(Intent.FLAG_GRANT_WRITE_URI_PERMISSION)
            chooserIntents.add(takePictureIntent)
        } catch (ex: IOException) {
            cameraImageUri = null
        }

        // 2. Intent Pilih Berkas dari Galeri / File Manager
        val selectFileIntent = Intent(Intent.ACTION_GET_CONTENT).apply {
            addCategory(Intent.CATEGORY_OPENABLE)
            type = "*/*"
            putExtra(Intent.EXTRA_MIME_TYPES, arrayOf("image/*", "application/pdf"))
        }

        val targetIntent = Intent(Intent.ACTION_CHOOSER).apply {
            putExtra(Intent.EXTRA_INTENT, selectFileIntent)
            putExtra(Intent.EXTRA_TITLE, getString(R.string.choose_file))
            if (chooserIntents.isNotEmpty()) {
                putExtra(Intent.EXTRA_INITIAL_INTENTS, chooserIntents.toTypedArray())
            }
        }

        fileChooserLauncher.launch(targetIntent)
        return true
    }

    @Throws(IOException::class)
    private fun createTempImageFile(): File {
        val timeStamp = SimpleDateFormat("yyyyMMdd_HHmmss", Locale.getDefault()).format(Date())
        val imageFileName = "IMG_${timeStamp}_"
        val storageDir = File(cacheDir, "images")
        if (!storageDir.exists()) {
            storageDir.mkdirs()
        }
        return File.createTempFile(imageFileName, ".jpg", storageDir)
    }

    private fun handleFileDownload(
        url: String,
        userAgent: String,
        contentDisposition: String,
        mimeType: String
    ) {
        try {
            val fileName = android.webkit.URLUtil.guessFileName(url, contentDisposition, mimeType)
            val request = DownloadManager.Request(Uri.parse(url)).apply {
                setMimeType(mimeType)
                addRequestHeader("User-Agent", userAgent)
                val cookies = CookieManager.getInstance().getCookie(url)
                if (cookies != null) {
                    addRequestHeader("Cookie", cookies)
                }
                setDescription(getString(R.string.downloading))
                setTitle(fileName)
                setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE_NOTIFY_COMPLETED)
                setDestinationInExternalPublicDir(Environment.DIRECTORY_DOWNLOADS, fileName)
            }

            val downloadManager = getSystemService(Context.DOWNLOAD_SERVICE) as DownloadManager
            downloadManager.enqueue(request)

            Toast.makeText(this, "${getString(R.string.downloading)} $fileName", Toast.LENGTH_SHORT).show()
        } catch (e: Exception) {
            Toast.makeText(this, "Gagal mengunduh berkas: ${e.localizedMessage}", Toast.LENGTH_LONG).show()
        }
    }

    override fun onPause() {
        super.onPause()
        CookieManager.getInstance().flush()
    }

    override fun onDestroy() {
        binding.webView.destroy()
        super.onDestroy()
    }
}
