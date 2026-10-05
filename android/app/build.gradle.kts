plugins {
    id("com.android.application")
    id("org.jetbrains.kotlin.android")
}

android {
    namespace = "id.or.cahayatasbih.mobile"
    compileSdk = 34

    defaultConfig {
        applicationId = "id.or.cahayatasbih.mobile"
        minSdk = 24
        targetSdk = 34
        versionCode = 1
        versionName = "1.0.0"

        testInstrumentationRunner = "androidx.test.runner.AndroidJUnitRunner"
    }

    flavorDimensions += "environment"

    productFlavors {
        create("staging") {
            dimension = "environment"
            applicationIdSuffix = ".staging"
            manifestPlaceholders["appName"] = "CT-Mobile [STAGING]"

            buildConfigField("String", "BASE_URL", "\"https://sim.cahayatasbih.or.id/ct-mobile/app\"")
            buildConfigField("String", "HOST_DOMAIN", "\"sim.cahayatasbih.or.id\"")
            buildConfigField("String", "DOWNLOAD_URL", "\"https://sim.cahayatasbih.or.id/download/ct-mobile-latest.apk\"")
            buildConfigField("String", "VERSION_CHECK_URL", "\"https://sim.cahayatasbih.or.id/api/mobile/version-check\"")
        }

        create("production") {
            dimension = "environment"
            manifestPlaceholders["appName"] = "CT-Mobile"

            buildConfigField("String", "BASE_URL", "\"https://aplikasi.cahayatasbih.or.id/ct-mobile/app\"")
            buildConfigField("String", "HOST_DOMAIN", "\"aplikasi.cahayatasbih.or.id\"")
            buildConfigField("String", "DOWNLOAD_URL", "\"https://aplikasi.cahayatasbih.or.id/download/ct-mobile-latest.apk\"")
            buildConfigField("String", "VERSION_CHECK_URL", "\"https://aplikasi.cahayatasbih.or.id/api/mobile/version-check\"")
        }
    }

    buildTypes {
        release {
            isMinifyEnabled = false
            proguardFiles(
                getDefaultProguardFile("proguard-android-optimize.txt"),
                "proguard-rules.pro"
            )
        }
        debug {
            isMinifyEnabled = false
        }
    }

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    kotlinOptions {
        jvmTarget = "17"
    }

    buildFeatures {
        buildConfig = true
        viewBinding = true
    }
}

dependencies {
    implementation("androidx.core:core-ktx:1.12.0")
    implementation("androidx.appcompat:appcompat:1.6.1")
    implementation("com.google.android.material:material:1.11.0")
    implementation("androidx.swiperefreshlayout:swiperefreshlayout:1.1.0")
    implementation("androidx.webkit:webkit:1.10.0")
    implementation("androidx.lifecycle:lifecycle-runtime-ktx:2.7.0")
    implementation("com.squareup.okhttp3:okhttp:4.12.0")
    implementation("org.jetbrains.kotlinx:kotlinx-coroutines-android:1.7.3")
}
