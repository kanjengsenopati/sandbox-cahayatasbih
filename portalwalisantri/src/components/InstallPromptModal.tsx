import { useEffect, useState } from 'react'
import { X, Download, ShieldCheck } from 'lucide-react'

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
    <div className="fixed inset-0 z-[100] flex items-end justify-center sm:items-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in duration-300">
      <div className="bg-white rounded-[32px] shadow-[0_15px_40px_rgba(155,29,232,0.12)] p-6 w-full max-w-sm border border-slate-100 animate-in slide-in-from-bottom-8 duration-500 relative overflow-hidden">
        {/* Decorative corner glows */}
        <div className="absolute -top-12 -right-12 w-28 h-28 bg-[#b445ff]/10 rounded-full blur-2xl"></div>
        <div className="absolute -bottom-12 -left-12 w-28 h-28 bg-[#610a9c]/10 rounded-full blur-2xl"></div>

        <div className="relative z-10 flex justify-between items-start mb-5">
          <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#9b1de8]/10 to-[#610a9c]/10 flex items-center justify-center border border-[#9b1de8]/15">
            <Download className="text-[#9b1de8]" size={22} strokeWidth={2.5} />
          </div>
          <button 
            onClick={() => setShow(false)} 
            className="w-8 h-8 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600 transition"
          >
            <X size={16} strokeWidth={2.5} />
          </button>
        </div>
        
        <div className="relative z-10 space-y-2">
          <h2 className="text-xl font-extrabold text-slate-900 tracking-tight">Pasang Aplikasi CT-Mobile</h2>
          <p className="text-slate-500 text-[13px] font-medium leading-relaxed">
            Tambahkan aplikasi <span className="font-bold text-slate-800">CT-Mobile</span> ke layar utama gawai Anda untuk akses pemantauan perizinan dan keuangan ananda secara instan, stabil, dan hemat kuota.
          </p>
        </div>

        <div className="relative z-10 mt-5 p-3 rounded-2xl bg-emerald-50/50 border border-emerald-100 flex items-center gap-2 text-emerald-800 text-[11px] font-bold">
          <ShieldCheck size={16} className="text-emerald-600 shrink-0" />
          <span>Verifikasi Keamanan Terjamin (PWA Resmi)</span>
        </div>
        
        <div className="relative z-10 mt-6 flex flex-col gap-2.5">
          <button 
            onClick={install}
            className="w-full bg-gradient-to-r from-[#9b1de8] to-[#610a9c] text-white font-extrabold py-4 rounded-[20px] shadow-[0_8px_25px_rgba(155,29,232,0.3)] transition active:scale-[0.98] text-sm"
          >
            Pasang Sekarang
          </button>
          <button 
            onClick={() => setShow(false)}
            className="w-full py-2.5 text-slate-400 text-xs font-bold hover:text-slate-600 transition"
          >
            Nanti Saja
          </button>
        </div>
      </div>
    </div>
  )
}
