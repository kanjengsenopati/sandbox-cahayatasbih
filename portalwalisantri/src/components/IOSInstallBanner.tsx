import { useEffect, useState } from 'react'
import { Share, PlusSquare, X, Smartphone } from 'lucide-react'

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
      <div className="bg-white/95 backdrop-blur-xl border border-slate-100 rounded-[28px] shadow-[0_12px_35px_rgba(155,29,232,0.1)] p-4 relative overflow-hidden flex items-center">
        {/* Decorative thin accent line */}
        <div className="absolute top-0 left-0 w-1.5 h-full bg-gradient-to-b from-[#9b1de8] to-[#610a9c]"></div>
        
        <button 
          onClick={dismiss}
          className="absolute top-3.5 right-3.5 text-slate-400 hover:text-slate-600 transition"
        >
          <X size={16} strokeWidth={2.5} />
        </button>
        
        <div className="flex items-center gap-4 pr-6">
          <div className="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-[#9b1de8] to-[#610a9c] rounded-2xl flex items-center justify-center shadow-md shadow-[#9b1de8]/20">
            <span className="text-white font-extrabold text-sm tracking-tight">CT</span>
          </div>
          <div>
            <p className="text-slate-900 font-extrabold text-xs leading-tight mb-1 flex items-center gap-1.5">
              <Smartphone size={13} className="text-[#9b1de8]" /> Pasang di iPhone Anda
            </p>
            <p className="text-slate-500 text-[10px] font-medium leading-snug">
              Ketuk <Share className="inline-block mx-0.5 text-slate-700" size={11} /> lalu pilih <PlusSquare className="inline-block mx-0.5 text-slate-700" size={11} /> <span className="font-bold text-slate-700">Tambah ke Layar Utama</span> untuk memasang aplikasi CT-Mobile.
            </p>
          </div>
        </div>
      </div>
    </div>
  )
}
