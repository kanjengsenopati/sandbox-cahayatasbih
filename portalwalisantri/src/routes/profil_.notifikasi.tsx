import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { ArrowLeft, BellRing, Bell, BellOff, CheckCircle2, XCircle, Info, Sparkles, Smartphone, ShieldCheck } from "lucide-react";
import { useState, useEffect, useCallback } from "react";
import { toast } from "sonner";

export const Route = createFileRoute("/profil_/notifikasi")({
  component: NotifikasiPage,
  head: () => ({ meta: [{ title: "Notifikasi — SantriPay" }] }),
});

function NotifikasiPage() {
  const navigate = useNavigate();

  // Push Notification state
  const [pushSupported, setPushSupported] = useState(false);
  const [pushPermission, setPushPermission] = useState<NotificationPermission>("default");
  const [pushEnabled, setPushEnabled] = useState(false);

  // Toaster Notification state (in-app)
  const [toasterEnabled, setToasterEnabled] = useState(() => {
    const stored = localStorage.getItem("ct_toaster_enabled");
    return stored !== null ? stored === "true" : true;
  });

  // Check push notification support & permission on mount
  useEffect(() => {
    if ("Notification" in window) {
      setPushSupported(true);
      setPushPermission(Notification.permission);
      setPushEnabled(Notification.permission === "granted");
    }
  }, []);

  // Handle Push Notification toggle
  const handleTogglePush = useCallback(async () => {
    if (!pushSupported) {
      toast.error("Browser ini tidak mendukung Push Notification", {
        description: "Gunakan Chrome atau Safari versi terbaru.",
      });
      return;
    }

    if (pushPermission === "denied") {
      toast.error("Izin notifikasi diblokir", {
        description: "Buka Pengaturan Browser → Izin Situs → Notifikasi untuk mengaktifkan kembali.",
      });
      return;
    }

    if (pushPermission === "default" || pushPermission !== "granted") {
      try {
        const result = await Notification.requestPermission();
        setPushPermission(result);
        if (result === "granted") {
          setPushEnabled(true);
          toast.success("Push Notification diaktifkan!", {
            description: "Anda akan menerima notifikasi tagihan dan informasi penting.",
          });
          // Send a test notification via SW if available
          if ('serviceWorker' in navigator) {
            const reg = await navigator.serviceWorker.ready;
            reg.showNotification("SantriPay", {
              body: "Push Notification berhasil diaktifkan! 🎉",
              icon: "/icons/icon-192.png",
            });
          } else {
            new Notification("SantriPay", {
              body: "Push Notification berhasil diaktifkan! 🎉",
              icon: "/icons/icon-192.png",
            });
          }
        } else {
          setPushEnabled(false);
          toast.info("Push Notification tidak diizinkan", {
            description: "Anda bisa mengaktifkannya nanti melalui pengaturan browser.",
          });
        }
      } catch {
        toast.error("Gagal meminta izin notifikasi");
      }
    } else {
      // Already granted — toggling off (local state only, can't revoke browser permission)
      const nextState = !pushEnabled;
      setPushEnabled(nextState);
      if (!nextState) {
        toast.info("Push Notification dinonaktifkan", {
          description: "Anda tidak akan menerima notifikasi push.",
        });
      } else {
        toast.success("Push Notification diaktifkan kembali!");
      }
    }
  }, [pushSupported, pushPermission, pushEnabled]);

  // Handle Toaster Notification toggle
  const handleToggleToaster = useCallback(() => {
    const newVal = !toasterEnabled;
    setToasterEnabled(newVal);
    localStorage.setItem("ct_toaster_enabled", String(newVal));
    if (newVal) {
      toast.success("Toaster Notification diaktifkan!", {
        description: "Notifikasi in-app akan muncul di layar.",
      });
    } else {
      toast("Toaster Notification dinonaktifkan", {
        description: "Notifikasi in-app tidak akan muncul.",
        icon: <BellOff size={16} />,
      });
    }
  }, [toasterEnabled]);

  // Test notification functions
  const sendTestPush = async () => {
    if (pushEnabled && pushPermission === "granted") {
      if ('serviceWorker' in navigator) {
        try {
          const reg = await navigator.serviceWorker.ready;
          if (reg) {
            reg.showNotification("SantriPay — Tagihan Baru", {
              body: "Tagihan SPP bulan ini telah terbit. Segera lakukan pembayaran.",
              icon: "/icons/icon-192.png",
              badge: "/icons/icon-192.png",
              vibrate: [100, 50, 100],
              data: {
                url: "/tagihan"
              }
            });
            toast.success("Test Push Notification terkirim via Service Worker!");
            return;
          }
        } catch (e) {
          console.error("SW notification failed, falling back to window Notification", e);
        }
      }
      
      // Fallback
      new Notification("SantriPay — Tagihan Baru", {
        body: "Tagihan SPP bulan ini telah terbit. Segera lakukan pembayaran.",
        icon: "/icons/icon-192.png",
      });
      toast.success("Test Push Notification terkirim!");
    } else {
      toast.error("Aktifkan Push Notification terlebih dahulu.");
    }
  };

  const sendTestToaster = () => {
    if (!toasterEnabled) {
      toast.error("Aktifkan Toaster Notification terlebih dahulu.");
      return;
    }

    toast.custom((t) => (
      <div className="w-full max-w-sm bg-white/95 backdrop-blur-md rounded-[24px] border border-slate-100 shadow-[0_10px_30px_rgba(0,0,0,0.08)] p-5 flex gap-4 items-start animate-in fade-in slide-in-from-top-4 duration-300">
        <div className="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 shadow-sm border border-emerald-100">
          <BellRing size={22} className="animate-bounce" />
        </div>
        <div className="flex-1 min-w-0">
          <div className="flex justify-between items-baseline">
            <span className="text-[10px] font-extrabold uppercase tracking-widest text-emerald-600">Tagihan Terbit</span>
            <span className="text-[10px] text-slate-400">Baru saja</span>
          </div>
          <p className="text-[14px] font-bold text-slate-900 mt-1">SPP & Uang Makan Mei 2026</p>
          <p className="text-[12px] text-slate-500 mt-1.5 leading-relaxed">
            Tagihan SPP bulan ini sebesar <strong className="text-emerald-600 font-bold">Rp 350.000</strong> telah terbit.
          </p>
          <div className="mt-4 flex gap-2">
            <button
              onClick={() => {
                toast.dismiss(t);
                navigate({ to: "/tagihan" });
              }}
              className="px-4 py-2 rounded-xl bg-primary text-white text-[11px] font-bold hover:opacity-90 active:scale-95 transition-all shadow-sm shadow-primary/20"
            >
              Bayar Sekarang
            </button>
            <button
              onClick={() => toast.dismiss(t)}
              className="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-[11px] font-bold hover:bg-slate-200 active:scale-95 transition-all"
            >
              Nanti saja
            </button>
          </div>
        </div>
      </div>
    ), {
      duration: 7000,
    });
  };

  return (
    <div className="min-h-screen w-full flex justify-center bg-secondary">
      <div className="relative w-full max-w-md min-h-screen bg-background pb-24">
        {/* Hero */}
        <div
          className="relative px-6 pt-12 pb-20 rounded-b-[2rem] overflow-hidden"
          style={{ background: "var(--gradient-hero)" }}
        >
          <div className="absolute -top-16 -right-12 w-56 h-56 rounded-full bg-primary-glow/30 blur-3xl" />
          <div className="relative flex items-center gap-3">
            <button
              onClick={() => navigate({ to: "/profil" })}
              className="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center text-white"
            >
              <ArrowLeft size={18} />
            </button>
            <div>
              <p className="text-[11px] text-white/70 font-semibold uppercase tracking-wider">Profil</p>
              <p className="text-base font-bold text-white">Notifikasi</p>
            </div>
          </div>
        </div>

        {/* Content */}
        <div className="px-6 -mt-12 relative z-10 space-y-4">
          {/* Push Notification Card */}
          <div className="bg-card rounded-[24px] border border-border shadow-[var(--shadow-card)] overflow-hidden p-5 transition-all duration-300">
            <div className="flex items-start justify-between">
              <div className="flex gap-4">
                <div className={`w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 border transition-colors ${pushEnabled ? 'bg-primary/10 text-primary border-primary/20' : 'bg-slate-100 text-slate-400 border-slate-200'}`}>
                  <Smartphone size={22} />
                </div>
                <div>
                  <p className="text-[14px] font-bold text-foreground">Push Notification</p>
                  <p className="text-[12px] text-muted-foreground mt-1 leading-relaxed">
                    Terima info tagihan instan & darurat langsung pada status bar perangkat Anda.
                  </p>
                </div>
              </div>
              <button 
                onClick={handleTogglePush}
                className={`w-12 h-7 rounded-full flex items-center px-1 transition-all duration-300 ${pushEnabled ? 'bg-primary justify-end' : 'bg-slate-200 justify-start'}`}
              >
                <div className="w-5 h-5 rounded-full bg-white shadow-sm" />
              </button>
            </div>
            
            {pushEnabled && (
              <div className="mt-4 pt-4 border-t border-dashed border-border">
                <button
                  onClick={sendTestPush}
                  className="w-full py-3 rounded-2xl bg-primary/5 text-primary text-[12px] font-bold flex items-center justify-center gap-2 hover:bg-primary/10 active:scale-[0.98] transition-all"
                >
                  <Sparkles size={14} /> Kirim Test Push Notification
                </button>
              </div>
            )}
          </div>

          {/* Toaster Notification Card */}
          <div className="bg-card rounded-[24px] border border-border shadow-[var(--shadow-card)] overflow-hidden p-5 transition-all duration-300">
            <div className="flex items-start justify-between">
              <div className="flex gap-4">
                <div className={`w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 border transition-colors ${toasterEnabled ? 'bg-primary/10 text-primary border-primary/20' : 'bg-slate-100 text-slate-400 border-slate-200'}`}>
                  <BellRing size={22} />
                </div>
                <div>
                  <p className="text-[14px] font-bold text-foreground">Toaster Notification</p>
                  <p className="text-[12px] text-muted-foreground mt-1 leading-relaxed">
                    Tampilkan alert pop-up yang informatif & interaktif saat aplikasi sedang dibuka.
                  </p>
                </div>
              </div>
              <button 
                onClick={handleToggleToaster}
                className={`w-12 h-7 rounded-full flex items-center px-1 transition-all duration-300 ${toasterEnabled ? 'bg-primary justify-end' : 'bg-slate-200 justify-start'}`}
              >
                <div className="w-5 h-5 rounded-full bg-white shadow-sm" />
              </button>
            </div>

            {toasterEnabled && (
              <div className="mt-4 pt-4 border-t border-dashed border-border">
                <button
                  onClick={sendTestToaster}
                  className="w-full py-3 rounded-2xl bg-emerald-500/10 text-emerald-600 text-[12px] font-bold flex items-center justify-center gap-2 hover:bg-emerald-500/20 active:scale-[0.98] transition-all"
                >
                  <Sparkles size={14} /> Kirim Test Toaster Notification
                </button>
              </div>
            )}
          </div>

          {/* Status Badge */}
          <div className="flex items-center gap-2 px-1">
            {pushPermission === "granted" ? (
              <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-600 text-[11px] font-bold">
                <ShieldCheck size={12} /> Browser Push Aktif
              </span>
            ) : pushPermission === "denied" ? (
              <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-red-50 text-red-600 text-[11px] font-bold">
                <XCircle size={12} /> Izin Push Diblokir Browser
              </span>
            ) : (
              <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-50 text-amber-600 text-[11px] font-bold">
                <Info size={12} /> Perlu Izin Browser
              </span>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
