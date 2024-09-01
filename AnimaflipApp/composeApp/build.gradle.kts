import org.jetbrains.kotlin.gradle.ExperimentalKotlinGradlePluginApi
import org.jetbrains.kotlin.gradle.dsl.JvmTarget

plugins {
    alias(libs.plugins.kotlinMultiplatform)
    alias(libs.plugins.androidApplication)
    alias(libs.plugins.jetbrainsCompose)
    alias(libs.plugins.compose.compiler)
    kotlin("plugin.serialization") version "1.7.0"
}

kotlin {
    androidTarget {
        @OptIn(ExperimentalKotlinGradlePluginApi::class)
        compilerOptions {
            jvmTarget.set(JvmTarget.JVM_11)
        }
    }
    
    listOf(
        iosX64(),
        iosArm64(),
        iosSimulatorArm64()
    ).forEach { iosTarget ->
        iosTarget.binaries.framework {
            baseName = "ComposeApp"
            isStatic = true
        }
    }
    
    sourceSets {
        
        androidMain.dependencies {
            implementation(compose.preview)
            implementation(libs.androidx.activity.compose)
        }
        commonMain.dependencies {
            implementation(compose.runtime)
            implementation(compose.foundation)
            implementation(compose.material)
            implementation(compose.ui)
            implementation(compose.components.resources)
            implementation(compose.components.uiToolingPreview)
//            implementation("network.chaintech:compose-multiplatform-media-player:1.0.11")
            implementation("network.chaintech:compose-multiplatform-media-player:1.0.19")
//            implementation("androidx.media3:media3-exoplayer:1.0.0")
//            implementation("androidx.media3:media3-ui:1.0.0")
            implementation("org.jetbrains.kotlinx:kotlinx-serialization-json:1.3.2")
            implementation("io.ktor:ktor-client-core:2.0.0")
            implementation("io.ktor:ktor-client-logging:2.0.0")
            implementation("io.ktor:ktor-client-content-negotiation:2.0.0")
            implementation("io.ktor:ktor-serialization-kotlinx-json:2.0.0")
            implementation("io.ktor:ktor-client-okhttp:2.0.0")
            //noinspection UseTomlInstead
//            implementation("androidx.navigation:navigation-compose:2.4.0-alpha10")
//            implementation(libs.androidx.lifecycle.viewmodel.compose.v240alpha02)
//            implementation("androidx.activity:activity-compose:1.3.1")
//            implementation("org.jetbrains.kotlinx:kotlinx-coroutines-core:1.5.1")
//            implementation("org.jetbrains.kotlinx:kotlinx-coroutines-android:1.5.1")
//            implementation("io.github.panpf.sketch4:sketch-compose:3.3.2")
//            implementation("io.coil-kt.coil3:coil:3.0.0-alpha01")
//            implementation("io.coil-kt:coil-gif:2.6.0")
//            implementation(libs.androidx.lifecycle.viewmodel.ktx)
//            implementation(libs.androidx.lifecycle.livedata.ktx)
//            implementation(libs.kotlinx.coroutines.core)
//            implementation(libs.kotlinx.coroutines.android)
//            implementation(libs.ktor.client.core)
//            implementation(libs.ktor.client.json)
//            implementation(libs.ktor.client.serialization)
//            implementation(libs.androidx.ui)
//            implementation(libs.material)
//            implementation(libs.androidx.ui.tooling.preview)
//            implementation(libs.androidx.lifecycle.runtime.ktx) // ViewModel and LiveData support
//            implementation(libs.androidx.activity.compose.v160) // Compose support in activities
//            implementation(libs.androidx.lifecycle.viewmodel.compose) // ViewModel support in Compose
        }
    }
}

android {
    namespace = "org.animaflip.app"
    compileSdk = libs.versions.android.compileSdk.get().toInt()

    sourceSets["main"].manifest.srcFile("src/androidMain/AndroidManifest.xml")
    sourceSets["main"].res.srcDirs("src/androidMain/res")
    //sourceSets["main"].resources.srcDirs("src/commonMain/resources")

    defaultConfig {
        applicationId = "org.animaflip.app"
        minSdk = libs.versions.android.minSdk.get().toInt()
        targetSdk = libs.versions.android.targetSdk.get().toInt()
        versionCode = 1
        versionName = "1.0"
    }
    packaging {
        resources {
            excludes += "/META-INF/{AL2.0,LGPL2.1}"
        }
    }
    buildTypes {
        getByName("release") {
            isMinifyEnabled = false
        }
    }
    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_11
        targetCompatibility = JavaVersion.VERSION_11
    }
    buildFeatures {
        compose = true
    }
    dependencies {
        debugImplementation(compose.uiTooling)
    }
}

dependencies {
    implementation(kotlin("stdlib-jdk8"))
}
repositories {
    google()
    mavenCentral()
    maven { url = uri("https://maven.pkg.jetbrains.space/public/p/compose/dev") }
}

