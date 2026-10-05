package id.or.cahayatasbih.mobile

import android.content.Context
import android.net.Uri
import android.view.View
import android.webkit.GeolocationPermissions
import android.webkit.JsPromptResult
import android.webkit.JsResult
import android.webkit.ValueCallback
import android.webkit.WebChromeClient
import android.webkit.WebView
import android.widget.EditText
import android.widget.ProgressBar
import com.google.android.material.dialog.MaterialAlertDialogBuilder

class CustomWebChromeClient(
    private val context: Context,
    private val progressBar: ProgressBar?,
    private val onFileChooser: (ValueCallback<Array<Uri>>?, FileChooserParams?) -> Boolean
) : WebChromeClient() {

    override fun onProgressChanged(view: WebView?, newProgress: Int) {
        super.onProgressChanged(view, newProgress)
        progressBar?.let {
            if (newProgress < 100) {
                it.visibility = View.VISIBLE
                it.progress = newProgress
            } else {
                it.visibility = View.GONE
            }
        }
    }

    override fun onShowFileChooser(
        webView: WebView?,
        filePathCallback: ValueCallback<Array<Uri>>?,
        fileChooserParams: FileChooserParams?
    ): Boolean {
        return onFileChooser(filePathCallback, fileChooserParams)
    }

    override fun onGeolocationPermissionsShowPrompt(
        origin: String?,
        callback: GeolocationPermissions.Callback?
    ) {
        // Otomatis izinkan geolokasi jika diminta oleh domain aplikasi
        callback?.invoke(origin, true, false)
    }

    override fun onJsAlert(view: WebView?, url: String?, message: String?, result: JsResult?): Boolean {
        MaterialAlertDialogBuilder(context)
            .setTitle(context.getString(R.string.app_name))
            .setMessage(message ?: "")
            .setPositiveButton(android.R.string.ok) { dialog, _ ->
                result?.confirm()
                dialog.dismiss()
            }
            .setCancelable(false)
            .show()
        return true
    }

    override fun onJsConfirm(view: WebView?, url: String?, message: String?, result: JsResult?): Boolean {
        MaterialAlertDialogBuilder(context)
            .setTitle(context.getString(R.string.app_name))
            .setMessage(message ?: "")
            .setPositiveButton(android.R.string.ok) { dialog, _ ->
                result?.confirm()
                dialog.dismiss()
            }
            .setNegativeButton(android.R.string.cancel) { dialog, _ ->
                result?.cancel()
                dialog.dismiss()
            }
            .setCancelable(false)
            .show()
        return true
    }

    override fun onJsPrompt(
        view: WebView?,
        url: String?,
        message: String?,
        defaultValue: String?,
        result: JsPromptResult?
    ): Boolean {
        val input = EditText(context).apply {
            setText(defaultValue ?: "")
        }

        MaterialAlertDialogBuilder(context)
            .setTitle(context.getString(R.string.app_name))
            .setMessage(message ?: "")
            .setView(input)
            .setPositiveButton(android.R.string.ok) { dialog, _ ->
                result?.confirm(input.text.toString())
                dialog.dismiss()
            }
            .setNegativeButton(android.R.string.cancel) { dialog, _ ->
                result?.cancel()
                dialog.dismiss()
            }
            .setCancelable(false)
            .show()
        return true
    }
}
