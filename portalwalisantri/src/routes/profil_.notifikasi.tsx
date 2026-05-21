import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { ArrowLeft, BellRing, Smartphone, Mail } from "lucide-react";
import { useState } from "react";

export const Route = createFileRoute("/profil_/notifikasi")({
  component: NotifikasiPage,
  head: () => ({ meta: [{ title: "Notifikasi — SantriPay" }] }),
});

function NotifikasiPage() {
  const navigate = useNavigate();
  const [pushEnabled, setPushEnabled] = useState(true);
  const [waEnabled, setWaEnabled] = useState(true);
  const [emailEnabled, setEmailEnabled] = useState(false);

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
          <div className="bg-card rounded-3xl border border-border shadow-[var(--shadow-card)] p-4 divide-y divide-border">
            <button 
              onClick={() => setPushEnabled(!pushEnabled)}
              className="w-full flex items-center gap-3 py-3 active:opacity-70 transition text-left"
            >
              <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                <BellRing size={18} />
              </div>
              <div className="flex-1">
                <p className="text-sm font-bold text-foreground">Push Notification</p>
                <p className="text-[11px] text-muted-foreground mt-0.5">Notifikasi langsung di perangkat ini</p>
              </div>
              <div className={`w-10 h-6 rounded-full flex items-center px-1 transition-colors ${pushEnabled ? 'bg-primary justify-end' : 'bg-secondary justify-start'}`}>
                <div className={`w-4 h-4 rounded-full shadow-sm transition-colors ${pushEnabled ? 'bg-white' : 'bg-muted-foreground'}`} />
              </div>
            </button>
            <button 
              onClick={() => setWaEnabled(!waEnabled)}
              className="w-full flex items-center gap-3 py-3 active:opacity-70 transition text-left"
            >
              <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                <Smartphone size={18} />
              </div>
              <div className="flex-1">
                <p className="text-sm font-bold text-foreground">WhatsApp Notification</p>
                <p className="text-[11px] text-muted-foreground mt-0.5">Pesan tagihan & informasi via WA</p>
              </div>
              <div className={`w-10 h-6 rounded-full flex items-center px-1 transition-colors ${waEnabled ? 'bg-primary justify-end' : 'bg-secondary justify-start'}`}>
                <div className={`w-4 h-4 rounded-full shadow-sm transition-colors ${waEnabled ? 'bg-white' : 'bg-muted-foreground'}`} />
              </div>
            </button>
            <button 
              onClick={() => setEmailEnabled(!emailEnabled)}
              className="w-full flex items-center gap-3 py-3 active:opacity-70 transition text-left"
            >
              <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                <Mail size={18} />
              </div>
              <div className="flex-1">
                <p className="text-sm font-bold text-foreground">Email Notification</p>
                <p className="text-[11px] text-muted-foreground mt-0.5">Bukti bayar & laporan bulanan</p>
              </div>
              <div className={`w-10 h-6 rounded-full flex items-center px-1 transition-colors ${emailEnabled ? 'bg-primary justify-end' : 'bg-secondary justify-start'}`}>
                <div className={`w-4 h-4 rounded-full shadow-sm transition-colors ${emailEnabled ? 'bg-white' : 'bg-muted-foreground'}`} />
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
