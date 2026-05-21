import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { ArrowLeft, BellRing, Bell, BellOff, CheckCircle2, XCircle, Info } from "lucide-react";
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
          // Send a test notification
          new Notification("SantriPay", {
            body: "Push Notification berhasil diaktifkan! 🎉",
            icon: "/icons/icon-192.png",
          });
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
      setPushEnabled(!pushEnabled);
      if (pushEnabled) {
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

  // Test notification buttons
  const sendTestPush = () => {
    if (pushEnabled && pushPermission === "granted") {
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
    if (toasterEnabled) {
      toast("Tagihan SPP bulan ini telah terbit", {
        description: "Segera lakukan pembayaran melalui menu Tagihan.",
        action: { label: "Lihat", onClick: () => navigate({ to: "/tagihan" }) },
        icon: <Bell size={16} />,
        duration: 5000,
      });
    } else {
      toast.error("Aktifkan Toaster Notification terlebih dahulu.");
    }
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
          {/* Push Notification */}
          <div className="bg-card rounded-3xl border border-border shadow-[var(--shadow-card)] overflow-hidden">
            <button
              onClick={handleTogglePush}
              className="w-full flex items-center gap-3 p-4 active:opacity-70 transition text-left"
            >
              <div className={`w-10 h-10 rounded-xl flex items-center justify-center ${pushEnabled ? 'bg-primary/10 text-primary' : 'bg-slate-100 text-slate-400'}`}>
                <BellRing size={18} />
              </div>
              <div className="flex-1">
                <p className="text-sm font-bold text-foreground">Push Notification</p>
                <p className="text-[11px] text-muted-foreground mt-0.5">
                  {pushPermission === "denied"
                    ? "Diblokir oleh browser — buka pengaturan"
                    : pushEnabled
                    ? "Aktif — notifikasi dikirim ke perangkat"
                    : "Nonaktif — ketuk untuk mengaktifkan"}
                </p>
              </div>
              <div className={`w-10 h-6 rounded-full flex items-center px-1 transition-all duration-300 ${pushEnabled ? 'bg-primary justify-end' : 'bg-slate-200 justify-start'}`}>
                <div className={`w-4 h-4 rounded-full shadow-sm transition-all duration-300 ${pushEnabled ? 'bg-white' : 'bg-slate-400'}`} />
              </div>
            </button>
            {pushEnabled && (
              <div className="px-4 pb-4">
                <button
                  onClick={sendTestPush}
                  className="w-full py-2.5 rounded-xl bg-primary/5 text-primary text-xs font-bold flex items-center justify-center gap-2 active:bg-primary/10 transition"
                >
                  <Bell size={14} /> Kirim Test Push Notification
                </button>
              </div>
            )}
          </div>

          {/* Toaster Notification */}
          <div className="bg-card rounded-3xl border border-border shadow-[var(--shadow-card)] overflow-hidden">
            <button
              onClick={handleToggleToaster}
              className="w-full flex items-center gap-3 p-4 active:opacity-70 transition text-left"
            >
              <div className={`w-10 h-10 rounded-xl flex items-center justify-center ${toasterEnabled ? 'bg-primary/10 text-primary' : 'bg-slate-100 text-slate-400'}`}>
                <Bell size={18} />
              </div>
              <div className="flex-1">
                <p className="text-sm font-bold text-foreground">Toaster Notification</p>
                <p className="text-[11px] text-muted-foreground mt-0.5">
                  {toasterEnabled
                    ? "Aktif — notifikasi in-app muncul di layar"
                    : "Nonaktif — notifikasi in-app disembunyikan"}
                </p>
              </div>
              <div className={`w-10 h-6 rounded-full flex items-center px-1 transition-all duration-300 ${toasterEnabled ? 'bg-primary justify-end' : 'bg-slate-200 justify-start'}`}>
                <div className={`w-4 h-4 rounded-full shadow-sm transition-all duration-300 ${toasterEnabled ? 'bg-white' : 'bg-slate-400'}`} />
              </div>
            </button>
            {toasterEnabled && (
              <div className="px-4 pb-4">
                <button
                  onClick={sendTestToaster}
                  className="w-full py-2.5 rounded-xl bg-primary/5 text-primary text-xs font-bold flex items-center justify-center gap-2 active:bg-primary/10 transition"
                >
                  <Bell size={14} /> Kirim Test Toaster Notification
                </button>
              </div>
            )}
          </div>

          {/* Status Info */}
          <div className="bg-card rounded-3xl border border-border shadow-[var(--shadow-soft)] p-4">
            <div className="flex items-start gap-3">
              <Info size={16} className="text-primary mt-0.5 shrink-0" />
              <div className="text-[12px] text-muted-foreground leading-relaxed space-y-1">
                <p className="font-semibold text-foreground text-[13px]">Tentang Notifikasi</p>
                <p><strong>Push Notification</strong> — Dikirim oleh sistem meskipun aplikasi tidak terbuka. Memerlukan izin browser.</p>
                <p><strong>Toaster Notification</strong> — Muncul di dalam aplikasi saat Anda sedang menggunakannya (pop-up kecil di bagian atas layar).</p>
              </div>
            </div>
          </div>

          {/* Permission Status Badge */}
          <div className="flex items-center gap-2 px-1">
            {pushPermission === "granted" ? (
              <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-600 text-[11px] font-bold">
                <CheckCircle2 size={12} /> Browser Push diizinkan
              </span>
            ) : pushPermission === "denied" ? (
              <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-red-50 text-red-600 text-[11px] font-bold">
                <XCircle size={12} /> Browser Push diblokir
              </span>
            ) : (
              <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-50 text-amber-600 text-[11px] font-bold">
                <Info size={12} /> Browser Push belum dikonfigurasi
              </span>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
