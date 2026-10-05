package id.or.cahayatasbih.mobile

import android.content.ActivityNotFoundException
import android.content.Context
import android.content.Intent
import android.graphics.Bitmap
import android.net.Uri
import android.view.View
import android.webkit.CookieManager
import android.webkit.WebResourceError
import android.webkit.WebResourceRequest
import android.webkit.WebView
import android.webkit.WebViewClient
import android.widget.Toast
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout

class CustomWebViewClient(
    private val context: Context,
    private val swipeRefreshLayout: SwipeRefreshLayout?,
    private val errorView: View?,
    private val onPageFinishedCallback: ((String?) -> Unit)? = null
) : WebViewClient() {

    private var hasError = false

    override fun shouldOverrideUrlLoading(view: WebView?, request: WebResourceRequest?): Boolean {
        val uri = request?.url ?: return false
        val url = uri.toString()
        val scheme = uri.scheme?.lowercase() ?: ""

        // 1. Skema Khusus Aplikasi Eksternal (WhatsApp, Telepon, Email, SMS, dsb.)
        if (scheme == "tel" || scheme == "mailto" || scheme == "sms" || scheme == "whatsapp" ||
            url.contains("api.whatsapp.com") || url.contains("wa.me")
        ) {
            try {
                val intent = Intent(Intent.ACTION_VIEW, uri)
                intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK
                context.startActivity(intent)
            } catch (e: ActivityNotFoundException) {
                Toast.makeText(context, "Aplikasi untuk membuka tautan ini tidak ditemukan.", Toast.LENGTH_SHORT).show()
            }
            return true
        }

        // 2. Skema Intent Android (Misal: Deep link QRIS, E-Wallet, atau Perbankan)
        if (scheme == "intent") {
            try {
                val intent = Intent.parseUri(url, Intent.URI_INTENT_SCHEME)
                if (intent != null) {
                    context.startActivity(intent)
                    return true
                }
            } catch (e: Exception) {
                // Abaikan jika intent gagal diurai
            }
        }

        // 3. Verifikasi Host Domain Aplikasi
        val host = uri.host?.lowercase() ?: ""
        val allowedHost = BuildConfig.HOST_DOMAIN.lowercase()

        // Jika URL berada di bawah domain aplikasi, izinkan WebView memuatnya
        if (host.isEmpty() || host == allowedHost || host.endsWith(".$allowedHost")) {
            return false
        }

        // 4. Jika tautan eksternal (di luar domain Cahaya Tasbih), buka di Browser Eksternal
        return try {
            val intent = Intent(Intent.ACTION_VIEW, uri)
            intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK
            context.startActivity(intent)
            true
        } catch (e: Exception) {
            false
        }
    }

    override fun onPageStarted(view: WebView?, url: String?, favicon: Bitmap?) {
        super.onPageStarted(view, url, favicon)
        hasError = false
        errorView?.visibility = View.GONE
        view?.visibility = View.VISIBLE
    }

    override fun onPageFinished(view: WebView?, url: String?) {
        super.onPageFinished(view, url)
        swipeRefreshLayout?.isRefreshing = false
        CookieManager.getInstance().flush()

        if (!hasError) {
            errorView?.visibility = View.GONE
            view?.visibility = View.VISIBLE
        }

        onPageFinishedCallback?.invoke(url)
    }

    override fun onReceivedError(
        view: WebView?,
        request: WebResourceRequest?,
        error: WebResourceError?
    ) {
        super.onReceivedError(view, request, error)

        // Hanya tampilkan layar error jika kegagalan terjadi pada frame utama (bukan asset/iklan)
        if (request?.isForMainFrame == true) {
            hasError = true
            view?.visibility = View.GONE
            errorView?.visibility = View.VISIBLE
            swipeRefreshLayout?.isRefreshing = false
        }
    }
}
