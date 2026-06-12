import { useEffect, useState } from 'react'
import { X, Download, ShieldCheck, Share, PlusSquare, MoreVertical, Menu, Smartphone } from 'lucide-react'
import { Text } from './Text'

interface DeviceInfo {
  isMobile: boolean
  isStandalone: boolean
  os: 'ios' | 'android' | 'other'
  browser: 'chrome' | 'firefox' | 'opera' | 'samsung' | 'safari' | 'generic'
}

export const InstallPromptModal = () => {
  const [deferredPrompt, setDeferredPrompt] = useState<any>(null)
  const [show, setShow] = useState(false)
  const [installType, setInstallType] = useState<'native' | 'manual'>('manual')
  const [deviceInfo, setDeviceInfo] = useState<DeviceInfo>({
    isMobile: false,
    isStandalone: false,
    os: 'other',
    browser: 'generic'
  })

  // Detect device and browser details
  const detectDevice = () => {
    const ua = navigator.userAgent
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(ua)
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || (navigator as any).standalone === true

    let os: 'ios' | 'android' | 'other' = 'other'
    if (/iPad|iPhone|iPod/.test(ua) && !(window as any).MSStream) {
      os = 'ios'
    } else if (/Android/i.test(ua)) {
      os = 'android'
    }

    let browser: 'chrome' | 'firefox' | 'opera' | 'samsung' | 'safari' | 'generic' = 'generic'
    if (/Firefox/i.test(ua)) {
      browser = 'firefox'
    } else if (/OPR/i.test(ua) || /Opera/i.test(ua)) {
      browser = 'opera'
    } else if (/SamsungBrowser/i.test(ua)) {
      browser = 'samsung'
    } else if (/Chrome/i.test(ua)) {
      browser = 'chrome'
    } else if (/Safari/i.test(ua) && os === 'ios') {
      browser = 'safari'
    }

    return { isMobile, isStandalone, os, browser }
  }

  useEffect(() => {
    const info = detectDevice()
    setDeviceInfo(info)

    // Check if dismissed recently (24 hour cooldown)
    const dismissedAt = localStorage.getItem('pwa-prompt-dismissed-at')
    let isCooldownActive = false
    if (dismissedAt) {
      const hoursSinceDismiss = (Date.now() - parseInt(dismissedAt, 10)) / (1000 * 60 * 60)
      if (hoursSinceDismiss < 24) {
        isCooldownActive = true
      }
    }

    // Capture the native install prompt event
    const handleBeforeInstall = (e: any) => {
      e.preventDefault()
      setDeferredPrompt(e)
      setInstallType('native')
      if (info.isMobile && !info.isStandalone && !isCooldownActive) {
        setShow(true)
      }
    }

    window.addEventListener('beforeinstallprompt', handleBeforeInstall)

    // Fallback: If native event doesn't fire, show manual guidelines after a short delay
    const fallbackTimer = setTimeout(() => {
      if (info.isMobile && !info.isStandalone && !isCooldownActive && !deferredPrompt) {
        setInstallType('manual')
        setShow(true)
      }
    }, 2000)

    return () => {
      window.removeEventListener('beforeinstallprompt', handleBeforeInstall)
      clearTimeout(fallbackTimer)
    }
  }, [deferredPrompt])

  const installNative = async () => {
    if (!deferredPrompt) return
    deferredPrompt.prompt()
    const { outcome } = await deferredPrompt.userChoice
    setShow(false)
    if (outcome === 'accepted') {
      console.log('PWA installed successfully via native prompt')
    }
    setDeferredPrompt(null)
  }

  const dismissPrompt = () => {
    setShow(false)
    localStorage.setItem('pwa-prompt-dismissed-at', Date.now().toString())
  }

  if (!show || deviceInfo.isStandalone) return null

  // Get instructions text based on browser & OS
  const renderInstructions = () => {
    if (deviceInfo.os === 'ios') {
      return (
        <ul className="space-y-2.5 text-left text-slate-600">
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-800">1</span>
            <span>Ketuk tombol <strong>Bagikan (Share)</strong> <Share className="inline mx-0.5 text-blue-600 animate-pulse" size={14} /> di bilah menu Safari.</span>
          </li>
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-800">2</span>
            <span>Pilih menu <strong>Tambah ke Layar Utama (Add to Home Screen)</strong> <PlusSquare className="inline mx-0.5 text-slate-800" size={14} />.</span>
          </li>
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-800">3</span>
            <span>Ketuk <strong>Tambah</strong> di pojok kanan atas layar.</span>
          </li>
        </ul>
      )
    }

    if (deviceInfo.browser === 'firefox') {
      return (
        <ul className="space-y-2.5 text-left text-slate-600">
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-800">1</span>
            <span>Ketuk ikon <strong>Tiga Titik</strong> <MoreVertical className="inline mx-0.5 text-slate-800 animate-pulse" size={14} /> di sudut kanan atas atau bawah browser.</span>
          </li>
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-800">2</span>
            <span>Pilih <strong>Pasang (Install)</strong> atau <strong>Tambahkan ke Layar Utama</strong>.</span>
          </li>
        </ul>
      )
    }

    if (deviceInfo.browser === 'opera') {
      return (
        <ul className="space-y-2.5 text-left text-slate-600">
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-800">1</span>
            <span>Ketuk <strong>Menu Opera</strong> atau <strong>Tiga Titik</strong> <Menu className="inline mx-0.5 text-slate-800 animate-pulse" size={14} /> di sudut kanan bawah.</span>
          </li>
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-800">2</span>
            <span>Pilih <strong>Tambahkan ke (Add to)</strong>, lalu ketuk <strong>Layar Utama (Home Screen)</strong>.</span>
          </li>
        </ul>
      )
    }

    if (deviceInfo.browser === 'samsung') {
      return (
        <ul className="space-y-2.5 text-left text-slate-600">
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-800">1</span>
            <span>Ketuk ikon <strong>Tiga Garis (Menu)</strong> <Menu className="inline mx-0.5 text-slate-800 animate-pulse" size={14} /> di sudut kanan bawah browser.</span>
          </li>
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-800">2</span>
            <span>Pilih <strong>Tambah Halaman ke (Add page to)</strong>.</span>
          </li>
          <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-800">3</span>
            <span>Pilih <strong>Layar Utama (Home Screen)</strong>.</span>
          </li>
        </ul>
      )
    }

    // Default Android Chrome / Generic
    return (
      <ul className="space-y-2.5 text-left text-slate-600">
        <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
          <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-800">1</span>
          <span>Ketuk ikon <strong>Tiga Titik</strong> <MoreVertical className="inline mx-0.5 text-slate-800 animate-pulse" size={14} /> di sudut kanan atas layar.</span>
        </li>
        <li className="flex items-start gap-2.5 text-[13px] leading-relaxed">
          <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-800">2</span>
          <span>Pilih opsi <strong>Instal Aplikasi (Install App)</strong> atau <strong>Tambahkan ke Layar Utama (Add to Home Screen)</strong>.</span>
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
      <div className="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 w-full max-w-sm animate-in slide-in-from-bottom-8 duration-500 relative overflow-hidden z-10">
        {/* Glowing Decorative Details */}
        <div className="absolute -top-12 -right-12 w-28 h-28 bg-[#9b1de8]/10 rounded-full blur-2xl"></div>
        <div className="absolute -bottom-12 -left-12 w-28 h-28 bg-[#610a9c]/10 rounded-full blur-2xl"></div>

        {/* Top Header Section */}
        <div className="relative z-10 flex justify-between items-start mb-5">
          <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#9b1de8]/10 to-[#610a9c]/10 flex items-center justify-center border border-[#9b1de8]/15">
            <Download className="text-[#9b1de8]" size={22} strokeWidth={2} />
          </div>
          <button 
            onClick={dismissPrompt} 
            className="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:text-slate-600 transition"
          >
            <X size={18} strokeWidth={2} />
          </button>
        </div>
        
        {/* Content Section */}
        <div className="relative z-10 space-y-2 mb-4">
          <Text.H2 className="tracking-tight">Pasang Aplikasi CT-Mobile</Text.H2>
          <Text.Body className="text-slate-500 text-[13px] leading-relaxed">
            Tambahkan aplikasi <span className="font-bold text-slate-800">CT-Mobile</span> ke layar utama gawai Anda untuk akses pemantauan perizinan dan keuangan ananda secara instan, stabil, dan hemat kuota.
          </Text.Body>
        </div>

        {/* Instructions / Interaction Area */}
        <div className="relative z-10 my-4 bg-slate-50/50 p-4 rounded-[16px]">
          {installType === 'native' ? (
            <div className="text-center py-2">
              <Smartphone className="mx-auto text-[#2563EB] mb-2" size={36} strokeWidth={1.5} />
              <Text.Body className="text-[13px] text-slate-600">
                Tekan tombol di bawah untuk langsung memasang aplikasi di gawai Anda.
              </Text.Body>
            </div>
          ) : (
            <div className="space-y-3">
              <span className="inline-block text-[10px] font-bold tracking-wider uppercase text-slate-400">Petunjuk Pemasangan</span>
              {renderInstructions()}
            </div>
          )}
        </div>

        {/* Security / Verification Badge */}
        <div className="relative z-10 mt-4 p-3 rounded-[16px] bg-emerald-500/10 flex items-center gap-2 text-emerald-800 text-[11px] font-bold">
          <ShieldCheck size={18} className="text-[#10B981] shrink-0" strokeWidth={2} />
          <span>Verifikasi Keamanan Terjamin (PWA Resmi)</span>
        </div>
        
        {/* Action Buttons */}
        <div className="relative z-10 mt-6 flex flex-col gap-2.5">
          {installType === 'native' ? (
            <button 
              onClick={installNative}
              className="w-full bg-gradient-to-r from-[#9b1de8] to-[#610a9c] text-white font-extrabold py-3.5 rounded-[20px] shadow-[0_8px_25px_rgba(155,29,232,0.3)] transition active:scale-[0.98] text-sm"
            >
              Pasang Sekarang
            </button>
          ) : (
            <button 
              onClick={dismissPrompt}
              className="w-full bg-gradient-to-r from-[#9b1de8] to-[#610a9c] text-white font-extrabold py-3.5 rounded-[20px] shadow-[0_8px_25px_rgba(155,29,232,0.3)] transition active:scale-[0.98] text-sm"
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
