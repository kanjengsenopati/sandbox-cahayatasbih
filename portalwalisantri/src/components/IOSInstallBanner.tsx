import { useEffect, useState } from 'react'
import { Share, PlusSquare, X } from 'lucide-react'

export const IOSInstallBanner = () => {
  const [show, setShow] = useState(false)

  useEffect(() => {
    // Check if it's iOS and not already in standalone mode
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !(window as any).MSStream
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches
    
    if (isIOS && !isStandalone) {
      // Check if user has already dismissed it this session
      const dismissed = sessionStorage.getItem('ios-prompt-dismissed')
      if (!dismissed) {
        setShow(true)
      }
    }
  }, [])

  const dismiss = () => {
    setShow(false)
    sessionStorage.setItem('ios-prompt-dismissed', 'true')
  }

  if (!show) return null

  return (
    <div className="fixed bottom-6 left-4 right-4 z-[99] animate-in slide-in-from-bottom-full duration-700">
      <div className="bg-white/90 backdrop-blur-xl border border-white/20 rounded-[24px] shadow-premium p-4 relative overflow-hidden">
        <div className="absolute top-0 left-0 w-1 h-full bg-blue-600"></div>
        <button 
          onClick={dismiss}
          className="absolute top-3 right-3 text-slate-400 p-1"
        >
          <X size={16} />
        </button>
        
        <div className="flex items-center gap-4 pr-6">
          <div className="flex-shrink-0 w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-100">
            <span className="text-white font-bold text-xl">W</span>
          </div>
          <div>
            <p className="text-slate-900 font-bold text-sm leading-tight mb-1">
              Pasang di iPhone Anda
            </p>
            <p className="text-slate-500 text-[11px] leading-snug">
              Ketuk <Share className="inline-block mx-0.5" size={12} /> lalu pilih <PlusSquare className="inline-block mx-0.5" size={12} /> <span className="font-semibold text-slate-700">Tambah ke Layar Utama</span>
            </p>
          </div>
        </div>
      </div>
    </div>
  )
}
