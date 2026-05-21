import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { ArrowLeft, ShieldCheck, Key, Smartphone, ChevronRight } from "lucide-react";

export const Route = createFileRoute("/profil_/keamanan")({
  component: KeamananPage,
  head: () => ({ meta: [{ title: "Keamanan & PIN — SantriPay" }] }),
});

function KeamananPage() {
  const navigate = useNavigate();
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
              <p className="text-base font-bold text-white">Keamanan & PIN</p>
            </div>
          </div>
        </div>

        {/* Content */}
        <div className="px-6 -mt-12 relative z-10 space-y-4">
          <div className="bg-card rounded-3xl border border-border shadow-[var(--shadow-card)] p-4 divide-y divide-border">
            <button className="w-full flex items-center gap-3 py-3 active:opacity-70 transition text-left">
              <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                <Key size={18} />
              </div>
              <div className="flex-1">
                <p className="text-sm font-bold text-foreground">Ubah PIN Transaksi</p>
                <p className="text-[11px] text-muted-foreground mt-0.5">Perbarui PIN 6-digit secara berkala</p>
              </div>
              <ChevronRight size={18} className="text-muted-foreground" />
            </button>
            <button className="w-full flex items-center gap-3 py-3 active:opacity-70 transition text-left">
              <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                <ShieldCheck size={18} />
              </div>
              <div className="flex-1">
                <p className="text-sm font-bold text-foreground">Ubah Password Akun</p>
                <p className="text-[11px] text-muted-foreground mt-0.5">Password login ke portal web/aplikasi</p>
              </div>
              <ChevronRight size={18} className="text-muted-foreground" />
            </button>
            <button className="w-full flex items-center gap-3 py-3 active:opacity-70 transition text-left">
              <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                <Smartphone size={18} />
              </div>
              <div className="flex-1">
                <p className="text-sm font-bold text-foreground">Autentikasi Biometrik</p>
                <p className="text-[11px] text-muted-foreground mt-0.5">Login cepat dengan sidik jari/FaceID</p>
              </div>
              <div className="w-10 h-6 bg-secondary rounded-full flex items-center px-1">
                <div className="w-4 h-4 bg-muted-foreground rounded-full" />
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
