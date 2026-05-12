import { useEffect, useState } from 'react'
import { XCircle, Download } from 'lucide-react'

export const InstallPromptModal = () => {
  const [deferredPrompt, setDeferredPrompt] = useState<any>(null)
  const [show, setShow] = useState(false)

  useEffect(() => {
    const handler = (e: any) => {
      e.preventDefault()
      setDeferredPrompt(e)
      setShow(true)
    }
    window.addEventListener('beforeinstallprompt', handler)
    return () => window.removeEventListener('beforeinstallprompt', handler)
  }, [])

  const install = async () => {
    if (!deferredPrompt) return
    deferredPrompt.prompt()
    const { outcome } = await deferredPrompt.userChoice
    setShow(false)
    if (outcome === 'accepted') {
      console.log('PWA installed')
    }
    setDeferredPrompt(null)
  }

  if (!show) return null

  return (
    <div className="fixed inset-0 z-[100] flex items-end justify-center sm:items-center p-4 bg-black/40 backdrop-blur-sm animate-in fade-in duration-300">
      <div className="bg-white rounded-[32px] shadow-2xl p-6 w-full max-w-sm animate-in slide-in-from-bottom-8 duration-500">
        <div className="flex justify-between items-start mb-4">
          <div className="bg-blue-50 p-3 rounded-2xl">
            <Download className="text-blue-600" size={24} />
          </div>
          <button onClick={() => setShow(false)} className="text-slate-400 hover:text-slate-600 transition-colors">
            <XCircle size={24} />
          </button>
        </div>
        
        <h2 className="text-xl font-bold text-slate-900 mb-2">Pasang Aplikasi</h2>
        <p className="text-slate-600 text-sm mb-6 leading-relaxed">
          Tambahkan Portal Wali ke layar utama ponsel Anda untuk akses yang lebih cepat, stabil, dan hemat kuota.
        </p>
        
        <div className="flex flex-col gap-3">
          <button 
            onClick={install}
            className="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 rounded-2xl transition-all active:scale-95 shadow-lg shadow-blue-200"
          >
            Pasang Sekarang
          </button>
          <button 
            onClick={() => setShow(false)}
            className="w-full py-3 text-slate-400 text-sm font-medium hover:text-slate-600 transition-colors"
          >
            Nanti Saja
          </button>
        </div>
      </div>
    </div>
  )
}
