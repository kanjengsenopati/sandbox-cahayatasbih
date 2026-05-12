import { createFileRoute, useNavigate } from "@tanstack/react-router";
import {
  Bell,
  Plus,
  Sliders,
  History,
  Eye,
  EyeOff,
  ArrowUpRight,
  ArrowDownLeft,
  Utensils,
  BookOpen,
  Wallet,
  ChevronDown,
  Loader2,
} from "lucide-react";
import { useState } from "react";
import { MobileShell } from "@/components/MobileShell";
import { useSantri } from "@/contexts/SantriContext";
import { SantriSwitcherTrigger } from "@/components/SantriSwitcher";
import { useQuery } from "@tanstack/react-query";
import { fetchDashboard } from "@/lib/api";

export const Route = createFileRoute("/dashboard")({
  component: Dashboard,
  head: () => ({
    meta: [{ title: "Beranda — SantriPay" }],
  }),
});

const actions = [
  { label: "Topup Saldo", icon: Plus, accent: "from-primary to-primary-glow", to: "/topup" as const },
  { label: "Atur Limit", icon: Sliders, accent: "from-primary-deep to-primary", to: "/limit" as const },
  { label: "Riwayat", icon: History, accent: "from-primary-glow to-primary", to: "/riwayat" as const },
];

function Dashboard() {
  const navigate = useNavigate();
  const [hide, setHide] = useState(false);
  const { active, isLoading: isLoadingSantri } = useSantri();
  
  const { data: dashboard, isLoading: isLoadingDashboard } = useQuery({
    queryKey: ["dashboard", active?.id],
    queryFn: async () => {
      const res = await fetchDashboard();
      return res.data;
    },
    enabled: !!active,
  });

  const fmt = (n: number) =>
    new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(n);

  if (isLoadingSantri || (active && isLoadingDashboard)) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-background">
        <Loader2 className="animate-spin text-primary" size={40} />
      </div>
    );
  }

  if (!active) {
    return (
      <div className="min-h-screen flex flex-col items-center justify-center p-6 text-center space-y-4">
        <p className="text-muted-foreground">Belum ada santri yang tertaut.</p>
        <button 
          onClick={() => window.location.href = '/'}
          className="text-primary font-bold"
        >
          Kembali ke Portal Utama
        </button>
      </div>
    );
  }

  return (
    <MobileShell>
      {/* Header */}
      <header className="flex items-center justify-between px-6 pt-12 pb-4">
        <div className="flex items-center gap-3">
          <div className="w-11 h-11 rounded-2xl bg-[var(--gradient-card)] flex items-center justify-center text-primary-foreground font-bold shadow-[var(--shadow-soft)] overflow-hidden">
            {dashboard?.user?.avatar ? (
              <img src={`/${dashboard.user.avatar}`} alt="" className="w-full h-full object-cover" />
            ) : (
              dashboard?.user?.name?.substring(0, 2).toUpperCase() || "W"
            )}
          </div>
          <div>
            <p className="text-xs text-muted-foreground">Assalamualaikum,</p>
            <p className="text-sm font-bold text-foreground">{dashboard?.user?.name || "Wali Santri"}</p>
          </div>
        </div>
        <button className="relative w-11 h-11 rounded-2xl bg-secondary flex items-center justify-center">
          <Bell size={20} className="text-foreground" />
          <span className="absolute top-2.5 right-2.5 w-2 h-2 rounded-full bg-primary" />
        </button>
      </header>

      {/* Hero balance card */}
      <section className="px-6 mt-2">
        <div
          className="relative overflow-hidden rounded-3xl p-6 text-primary-foreground shadow-[var(--shadow-glow)]"
          style={{ background: "var(--gradient-hero)" }}
        >
          <div className="absolute -top-16 -right-10 w-56 h-56 rounded-full bg-white/10 blur-3xl" />
          <div className="absolute bottom-0 left-0 w-40 h-40 rounded-full bg-white/5 blur-2xl" />

          <div className="relative">
            <div className="flex items-center justify-between">
              <div className="min-w-0">
                <p className="text-xs uppercase tracking-widest text-white/70 font-semibold">
                  Saldo Santri
                </p>
                <p className="text-[11px] text-white/60 mt-0.5 truncate">
                  {active.name} · {active.classroom?.name || "Tanpa Kelas"}
                </p>
              </div>
              <SantriSwitcherTrigger>
                <span className="px-2.5 py-1 rounded-full bg-white/15 border border-white/20 text-[10px] font-semibold backdrop-blur flex items-center gap-1 cursor-pointer">
                  Ganti <ChevronDown size={12} />
                </span>
              </SantriSwitcherTrigger>
            </div>

            <div className="mt-5 flex items-end gap-3">
              <h2 className="text-3xl font-bold tracking-tight">
                {hide ? "Rp ••••••" : fmt(active.saldo)}
              </h2>
              <button onClick={() => setHide((h) => !h)} className="mb-1.5 text-white/80">
                {hide ? <EyeOff size={18} /> : <Eye size={18} />}
              </button>
            </div>

            <div className="mt-5 flex items-center justify-between text-xs">
              <div>
                <p className="text-white/60">Limit Harian</p>
                <p className="font-semibold">{fmt(active.daily_limit)}</p>
              </div>
              <div className="h-8 w-px bg-white/20" />
              <div>
                <p className="text-white/60">Tabungan</p>
                <p className="font-semibold">{fmt(active.saving)}</p>
              </div>
              <div className="h-8 w-px bg-white/20" />
              <div>
                <p className="text-white/60">NISN</p>
                <p className="font-semibold tracking-wider">{(active as any).nisn || "-"}</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Quick actions */}
      <section className="px-6 mt-6">
        <div className="grid grid-cols-3 gap-3">
          {actions.map(({ label, icon: Icon, accent, to }) => (
            <button
              key={label}
              onClick={() => navigate({ to })}
              className="flex flex-col items-center gap-2 p-4 rounded-2xl bg-card border border-border shadow-[var(--shadow-soft)] active:scale-95 transition"
            >
              <div className={`w-12 h-12 rounded-2xl bg-gradient-to-br ${accent} flex items-center justify-center shadow-[var(--shadow-soft)]`}>
                <Icon size={22} className="text-primary-foreground" />
              </div>
              <span className="text-[11px] font-semibold text-foreground text-center leading-tight">
                {label}
              </span>
            </button>
          ))}
        </div>
      </section>

      {/* Recent transactions */}
      <section className="px-6 mt-7 mb-10">
        <div className="flex items-center justify-between mb-3">
          <h3 className="text-base font-bold text-foreground">Transaksi Terbaru</h3>
          <button onClick={() => navigate({ to: "/riwayat" })} className="text-xs font-semibold text-primary">Lihat Semua</button>
        </div>

        <div className="bg-card rounded-3xl border border-border divide-y divide-border overflow-hidden shadow-[var(--shadow-soft)]">
          {dashboard?.recentTransactions?.map((t: any, i: number) => {
            const isIn = t.type === "IN";
            return (
              <div key={i} className="flex items-center gap-3 p-4">
                <div
                  className={`w-11 h-11 rounded-2xl flex items-center justify-center ${
                    isIn ? "bg-emerald-50 text-emerald-600" : "bg-blue-50 text-blue-600"
                  }`}
                >
                  {isIn ? <ArrowDownLeft size={18} /> : <Utensils size={18} />}
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-semibold text-foreground truncate">{t.note || (isIn ? "Saldo Masuk" : "Belanja Kantin")}</p>
                  <p className="text-[11px] text-muted-foreground">{new Date(t.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })}</p>
                </div>
                <div className="flex items-center gap-1">
                  <span
                    className={`text-sm font-bold ${
                      isIn ? "text-emerald-600" : "text-foreground"
                    }`}
                  >
                    {isIn ? "+" : "-"}
                    {fmt(t.amount)}
                  </span>
                </div>
              </div>
            );
          }) || (
            <div className="p-8 text-center text-xs text-muted-foreground">Belum ada transaksi.</div>
          )}
        </div>
      </section>
    </MobileShell>
  );
}
