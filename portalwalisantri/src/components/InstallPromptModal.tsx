import { useEffect, useState } from 'react'
import { X, Download, ShieldCheck, Share, PlusSquare, MoreVertical, Menu, Smartphone, Compass } from 'lucide-react'
import { Text } from './Text'

export type BrowserType = 
  | 'chrome' 
  | 'xiaomi' 
  | 'vivo' 
  | 'oppo' 
  | 'samsung' 
  | 'huawei' 
  | 'uc' 
  | 'firefox' 
  | 'opera' 
  | 'safari' 
  | 'generic'

export interface DeviceInfo {
  isMobile: boolean
  isStandalone: boolean
  os: 'ios' | 'android' | 'other'
  browser: BrowserType
  browserName: string
}

let globalDeferredPrompt: any = null

export const detectDevice = (): DeviceInfo => {
  if (typeof window === 'undefined') {
    return {
      isMobile: false,
      isStandalone: false,
      os: 'other',
      browser: 'generic',
      browserName: 'Peramban Web'
    }
  }

  const ua = navigator.userAgent || ''
  const hasTouch = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0)
  const isMobileUA = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile|MiuiBrowser|VivoBrowser|HeyTapBrowser|OppoBrowser|SamsungBrowser|HuaweiBrowser|UCBrowser/i.test(ua)
  const isSmallScreen = window.innerWidth <= 840
  const isMobile = isMobileUA || (hasTouch && isSmallScreen)

  const isStandalone = 
    window.matchMedia('(display-mode: standalone)').matches || 
    (navigator as any).standalone === true || 
    document.referrer.includes('android-app://') ||
    window.matchMedia('(display-mode: fullscreen)').matches ||
    window.matchMedia('(display-mode: minimal-ui)').matches

  let os: 'ios' | 'android' | 'other' = 'other'
  if (/iPad|iPhone|iPod/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1)) {
    os = 'ios'
  } else if (/Android/i.test(ua)) {
    os = 'android'
  }

  let browser: BrowserType = 'generic'
  let browserName = 'Peramban Bawaan'

  // Check OEM & specific mobile browsers FIRST before standard Chrome/Safari
  if (/MiuiBrowser|XiaoMi|Mint Browser/i.test(ua)) {
    browser = 'xiaomi'
    browserName = 'Browser Xiaomi / Redmi'
  } else if (/VivoBrowser/i.test(ua)) {
    browser = 'vivo'
    browserName = 'Browser Bawaan Vivo'
  } else if (/HeyTapBrowser|OppoBrowser/i.test(ua)) {
    browser = 'oppo'
    browserName = 'Browser Bawaan Oppo / Realme'
  } else if (/HuaweiBrowser/i.test(ua)) {
    browser = 'huawei'
    browserName = 'Huawei Browser'
  } else if (/SamsungBrowser/i.test(ua)) {
    browser = 'samsung'
    browserName = 'Samsung Internet'
  } else if (/UCBrowser|UCWEB/i.test(ua)) {
    browser = 'uc'
    browserName = 'UC Browser'
  } else if (/Firefox|FxiOS/i.test(ua)) {
    browser = 'firefox'
    browserName = 'Mozilla Firefox'
  } else if (/OPR|Opera|OPT/i.test(ua)) {
    browser = 'opera'
    browserName = 'Opera Mobile'
  } else if (os === 'ios' || (/Safari/i.test(ua) && !/Chrome|CriOS/i.test(ua))) {
    browser = 'safari'
    browserName = 'Safari (iOS)'
  } else if (/Chrome|CriOS/i.test(ua)) {
    browser = 'chrome'
    browserName = 'Google Chrome'
  }

  return { isMobile, isStandalone, os, browser, browserName }
}

export const InstallPromptModal = () => {
  const [deferredPrompt, setDeferredPrompt] = useState<any>(globalDeferredPrompt)
  const [show, setShow] = useState(false)
  const [installType, setInstallType] = useState<'native' | 'manual'>(globalDeferredPrompt ? 'native' : 'manual')
  const [deviceInfo, setDeviceInfo] = useState<DeviceInfo>({
    isMobile: false,
    isStandalone: false,
    os: 'other',
    browser: 'generic',
    browserName: 'Peramban Bawaan'
  })

  useEffect(() => {
    const info = detectDevice()
    setDeviceInfo(info)

    // If already running inside standalone PWA, never auto-show
    if (info.isStandalone) return

    // Check force-show URL params for immediate testing: ?install=1, ?prompt=1, ?pwa=1
    const searchParams = new URLSearchParams(window.location.search)
    const isForced = searchParams.has('install') || searchParams.has('prompt') || searchParams.has('pwa')

    // Check cooldown: 20 minutes cooldown or per-session dismiss
    let isCooldownActive = false
    if (!isForced) {
      const sessionDismissed = sessionStorage.getItem('ct_pwa_dismissed_session')
      const lastDismissTime = localStorage.getItem('ct_pwa_dismissed_time')
      if (sessionDismissed) {
        isCooldownActive = true
      } else if (lastDismissTime) {
        const diff = Date.now() - parseInt(lastDismissTime, 10)
        if (diff < 20 * 60 * 1000) {
          isCooldownActive = true
        }
      }
    }

    // Pick up early prompt if captured in layout head
    const earlyPrompt = (window as any).__deferredPwaPrompt
    if (earlyPrompt) {
      globalDeferredPrompt = earlyPrompt
      setDeferredPrompt(earlyPrompt)
      setInstallType('native')
    }

    const handleBeforeInstall = (e: any) => {
      e.preventDefault()
      globalDeferredPrompt = e
      ;(window as any).__deferredPwaPrompt = e
      setDeferredPrompt(e)
      setInstallType('native')
      if (info.isMobile && !info.isStandalone && (!isCooldownActive || isForced)) {
        setShow(true)
      }
    }

    window.addEventListener('beforeinstallprompt', handleBeforeInstall)
    ;(window as any).__onPwaPromptReady = handleBeforeInstall
    ;(window as any).__showPwaInstallModal = () => {
      // Manual trigger opens modal directly and bypasses cooldown
      setShow(true)
    }

    // Auto-show trigger for ALL mobile browsers (Chrome, Xiaomi, Vivo, Oppo, Safari, etc.)
    // A slight delay (400ms) guarantees clean first paint without route flicker
    const autoShowTimer = setTimeout(() => {
      if (info.isMobile && !info.isStandalone && (!isCooldownActive || isForced)) {
        setShow(true)
      }
    }, 400)

    return () => {
      window.removeEventListener('beforeinstallprompt', handleBeforeInstall)
      clearTimeout(autoShowTimer)
    }
  }, [])

  const installNative = async () => {
    const promptToUse = deferredPrompt || (window as any).__deferredPwaPrompt || globalDeferredPrompt
    if (!promptToUse) {
      setInstallType('manual')
      return
    }
    try {
      promptToUse.prompt()
      const { outcome } = await promptToUse.userChoice
      setShow(false)
      if (outcome === 'accepted') {
        console.log('PWA installed successfully via native prompt')
        dismissPrompt()
      }
    } catch (err) {
      console.warn('Error during native install prompt:', err)
      setInstallType('manual')
    }
  }

  const dismissPrompt = () => {
    setShow(false)
    try {
      sessionStorage.setItem('ct_pwa_dismissed_session', 'true')
      localStorage.setItem('ct_pwa_dismissed_time', Date.now().toString())
    } catch (e) {
      console.warn('Storage not available for PWA prompt cooldown', e)
    }
  }

  if (!show || deviceInfo.isStandalone) return null

  // Tailored instructions based on device & browser
  const renderInstructions = () => {
    if (deviceInfo.browser === 'xiaomi') {
      return (
        <ul className="space-y-2.5 text-left text-slate-600">
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">1</span>
            <span>Ketuk ikon <strong>Menu / Tiga Garis</strong> <Menu className="inline mx-0.5 text-purple-600 animate-pulse" size={14} /> di <strong>bilah menu bawah</strong> browser.</span>
          </li>
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">2</span>
            <span>Pilih menu <strong>Tambahkan ke Layar Utama</strong> (ikon tanda tambah atau layar beranda).</span>
          </li>
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">3</span>
            <span>Ketuk <strong>Tambah</strong> atau <strong>Izinkan</strong> untuk memasang ikon aplikasi di HP.</span>
          </li>
        </ul>
      )
    }

    if (deviceInfo.browser === 'vivo') {
      return (
        <ul className="space-y-2.5 text-left text-slate-600">
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">1</span>
            <span>Ketuk ikon <strong>Tiga Titik / Menu</strong> <Menu className="inline mx-0.5 text-purple-600 animate-pulse" size={14} /> di <strong>bilah navigasi bagian bawah</strong> layar.</span>
          </li>
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">2</span>
            <span>Pilih opsi <strong>Tambahkan ke Layar Beranda</strong> (atau <strong>Tambah Pintasan</strong>).</span>
          </li>
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">3</span>
            <span>Konfirmasi dengan memilih <strong>Tambahkan Otomatis</strong>.</span>
          </li>
        </ul>
      )
    }

    if (deviceInfo.browser === 'oppo') {
      return (
        <ul className="space-y-2.5 text-left text-slate-600">
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">1</span>
            <span>Ketuk ikon <strong>Menu (Dua / Tiga Garis)</strong> <Menu className="inline mx-0.5 text-purple-600 animate-pulse" size={14} /> di <strong>bilah menu bawah</strong> layar.</span>
          </li>
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">2</span>
            <span>Gulir menu lalu pilih <strong>Tambahkan ke Layar Beranda</strong> (atau <strong>Pasang Aplikasi</strong>).</span>
          </li>
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">3</span>
            <span>Ketuk tombol <strong>Tambahkan</strong> untuk menyelesaikan.</span>
          </li>
        </ul>
      )
    }

    if (deviceInfo.browser === 'samsung') {
      return (
        <ul className="space-y-2.5 text-left text-slate-600">
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">1</span>
            <span>Ketuk ikon <strong>Tiga Garis (Menu)</strong> <Menu className="inline mx-0.5 text-purple-600 animate-pulse" size={14} /> di <strong>sudut kanan bawah</strong> browser.</span>
          </li>
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">2</span>
            <span>Pilih menu <strong>Tambah Halaman ke (Add page to)</strong>.</span>
          </li>
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">3</span>
            <span>Pilih <strong>Layar Utama (Home screen)</strong> atau <strong>Pasang Aplikasi</strong>.</span>
          </li>
        </ul>
      )
    }

    if (deviceInfo.browser === 'huawei') {
      return (
        <ul className="space-y-2.5 text-left text-slate-600">
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">1</span>
            <span>Ketuk ikon <strong>Empat Titik / Menu</strong> <MoreVertical className="inline mx-0.5 text-purple-600 animate-pulse" size={14} /> di bilah bawah atau sudut browser.</span>
          </li>
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">2</span>
            <span>Pilih opsi <strong>Tambahkan ke Layar Beranda</strong>.</span>
          </li>
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">3</span>
            <span>Ketuk <strong>Tambahkan</strong> untuk menyelesaikan.</span>
          </li>
        </ul>
      )
    }

    if (deviceInfo.browser === 'uc') {
      return (
        <ul className="space-y-2.5 text-left text-slate-600">
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">1</span>
            <span>Ketuk ikon <strong>Menu (Tiga Garis)</strong> <Menu className="inline mx-0.5 text-purple-600 animate-pulse" size={14} /> di bagian tengah bilah bawah.</span>
          </li>
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">2</span>
            <span>Pilih menu <strong>Alat (Tools)</strong> lalu pilih <strong>Tambahkan ke Layar Utama</strong>.</span>
          </li>
        </ul>
      )
    }

    if (deviceInfo.os === 'ios' || deviceInfo.browser === 'safari') {
      return (
        <ul className="space-y-2.5 text-left text-slate-600">
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">1</span>
            <span>Ketuk tombol <strong>Bagikan (Share)</strong> <Share className="inline mx-0.5 text-blue-600 animate-pulse" size={14} /> di bilah menu Safari (bawah pada iPhone, atas pada iPad).</span>
          </li>
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">2</span>
            <span>Gulir menu ke bawah lalu pilih <strong>Tambah ke Layar Utama (Add to Home Screen)</strong> <PlusSquare className="inline mx-0.5 text-slate-800" size={14} />.</span>
          </li>
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">3</span>
            <span>Ketuk tombol <strong>Tambah (Add)</strong> di pojok kanan atas layar.</span>
          </li>
        </ul>
      )
    }

    if (deviceInfo.browser === 'firefox') {
      return (
        <ul className="space-y-2.5 text-left text-slate-600">
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">1</span>
            <span>Ketuk ikon <strong>Tiga Titik</strong> <MoreVertical className="inline mx-0.5 text-purple-600 animate-pulse" size={14} /> di samping bilah alamat URL.</span>
          </li>
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">2</span>
            <span>Pilih <strong>Pasang (Install)</strong> atau <strong>Tambahkan ke Layar Utama</strong>.</span>
          </li>
        </ul>
      )
    }

    if (deviceInfo.browser === 'opera') {
      return (
        <ul className="space-y-2.5 text-left text-slate-600">
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">1</span>
            <span>Ketuk <strong>Menu Opera</strong> <Menu className="inline mx-0.5 text-purple-600 animate-pulse" size={14} /> di sudut kanan bawah browser.</span>
          </li>
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">2</span>
            <span>Pilih <strong>Tambahkan ke (Add to)</strong>, lalu ketuk <strong>Layar Utama (Home Screen)</strong>.</span>
          </li>
        </ul>
      )
    }

    if (deviceInfo.browser === 'chrome') {
      return (
        <ul className="space-y-2.5 text-left text-slate-600">
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">1</span>
            <span>Ketuk ikon <strong>Tiga Titik</strong> <MoreVertical className="inline mx-0.5 text-purple-600 animate-pulse" size={14} /> di sudut kanan atas layar browser.</span>
          </li>
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">2</span>
            <span>Pilih opsi <strong>Instal Aplikasi (Install App)</strong> atau <strong>Tambahkan ke Layar Utama</strong>.</span>
          </li>
        </ul>
      )
    }

    // Default Android / Generic Fallback
    return (
      <ul className="space-y-2.5 text-left text-slate-600">
        <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
          <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">1</span>
          <span>Buka <strong>Menu Peramban</strong> (ikon <strong>Tiga Titik / Tiga Garis</strong> di bilah menu bawah atau atas layar).</span>
        </li>
        <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
          <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">2</span>
          <span>Pilih opsi <strong>Tambahkan ke Layar Utama (Add to Home Screen)</strong> atau <strong>Pasang Aplikasi Web</strong>.</span>
        </li>
        <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
          <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">3</span>
          <span>Konfirmasi penambahan agar ikon aplikasi CT-Mobile muncul di layar HP Anda.</span>
        </li>
      </ul>
    )
  }

  return (
    <div className="fixed inset-0 z-[100] flex items-end justify-center p-4 bg-slate-900/60 backdrop-blur-md animate-in fade-in duration-300">
      <div 
        className="absolute inset-0 bg-transparent" 
        onClick={dismissPrompt} 
      />
      <div className="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.06)] p-6 w-full max-w-sm animate-in slide-in-from-bottom-8 duration-500 relative overflow-hidden z-10 border border-slate-100">
        {/* Glowing Decorative Details */}
        <div className="absolute -top-12 -right-12 w-28 h-28 bg-[#9b1de8]/10 rounded-full blur-2xl pointer-events-none"></div>
        <div className="absolute -bottom-12 -left-12 w-28 h-28 bg-[#610a9c]/10 rounded-full blur-2xl pointer-events-none"></div>

        {/* Top Header Section */}
        <div className="relative z-10 flex justify-between items-start mb-4">
          <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#9b1de8]/15 to-[#610a9c]/15 flex items-center justify-center border border-[#9b1de8]/20 shadow-sm">
            <Download className="text-[#9b1de8]" size={22} strokeWidth={2.2} />
          </div>
          <button 
            onClick={dismissPrompt} 
            className="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 hover:text-slate-700 transition active:scale-95"
            aria-label="Tutup"
          >
            <X size={18} strokeWidth={2} />
          </button>
        </div>
        
        {/* Content Section */}
        <div className="relative z-10 space-y-2 mb-4">
          <div className="flex items-center gap-2">
            <Text.H2 className="tracking-tight text-[18px]">Pasang Aplikasi CT-Mobile</Text.H2>
          </div>
          <div className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-purple-50 text-[#9b1de8] text-[11px] font-semibold border border-purple-100/60">
            <Compass size={13} />
            <span>{deviceInfo.browserName}</span>
          </div>
          <Text.Body className="text-slate-500 text-[13px] leading-relaxed pt-1">
            Tambahkan aplikasi <span className="font-bold text-slate-800">CT-Mobile</span> ke layar utama gawai Anda untuk akses pemantauan perizinan dan keuangan ananda secara instan, stabil, dan hemat kuota.
          </Text.Body>
        </div>

        {/* Instructions / Interaction Area */}
        <div className="relative z-10 my-4 bg-slate-50/80 border border-slate-100 p-4 rounded-[16px]">
          {installType === 'native' ? (
            <div className="text-center py-2">
              <Smartphone className="mx-auto text-[#9b1de8] mb-2 animate-bounce" size={36} strokeWidth={1.5} />
              <Text.Body className="text-[13px] font-medium text-slate-700">
                Browser Anda mendukung instalasi langsung 1-klik. Tekan tombol di bawah untuk memasang aplikasi.
              </Text.Body>
            </div>
          ) : (
            <div className="space-y-3">
              <div className="flex items-center justify-between">
                <span className="inline-block text-[11px] font-bold tracking-wider uppercase text-purple-700">
                  Langkah Pemasangan
                </span>
                <span className="text-[10px] text-slate-400 font-medium">Panduan Otomatis</span>
              </div>
              {renderInstructions()}
            </div>
          )}
        </div>

        {/* Security / Verification Badge */}
        <div className="relative z-10 mt-3 p-2.5 rounded-[14px] bg-emerald-500/10 flex items-center gap-2 text-emerald-800 text-[11px] font-bold">
          <ShieldCheck size={18} className="text-[#10B981] shrink-0" strokeWidth={2} />
          <span>Verifikasi Keamanan Terjamin (PWA Resmi)</span>
        </div>
        
        {/* Action Buttons */}
        <div className="relative z-10 mt-5 flex flex-col gap-2.5">
          {installType === 'native' ? (
            <button 
              onClick={installNative}
              className="w-full bg-gradient-to-r from-[#9b1de8] to-[#610a9c] hover:opacity-95 text-white font-extrabold py-3.5 rounded-[20px] shadow-[0_8px_25px_rgba(155,29,232,0.3)] transition active:scale-[0.98] text-sm flex items-center justify-center gap-2"
            >
              <Download size={16} />
              <span>Pasang Sekarang</span>
            </button>
          ) : (
            <button 
              onClick={dismissPrompt}
              className="w-full bg-gradient-to-r from-[#9b1de8] to-[#610a9c] hover:opacity-95 text-white font-extrabold py-3.5 rounded-[20px] shadow-[0_8px_25px_rgba(155,29,232,0.3)] transition active:scale-[0.98] text-sm"
            >
              Saya Mengerti
            </button>
          )}
          <button 
            onClick={dismissPrompt}
            className="w-full py-2.5 text-slate-400 text-xs font-bold hover:text-slate-600 transition"
          >
            Nanti Saja
          </button>
        </div>
      </div>
    </div>
  )
}
