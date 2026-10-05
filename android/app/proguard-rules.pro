# Proguard rules for CT-Mobile WebView
-keepattributes JavascriptInterface
-keepclassmembers class * {
    @android.webkit.JavascriptInterface <methods>;
}
-dontwarn okhttp3.**
-dontwarn okio.**
