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
  ChevronRight,
  Loader2,
  Newspaper,
  Calendar,
  ImageOff,
} from "lucide-react";
import { useState } from "react";
import { MobileShell } from "@/components/MobileShell";
import { useSantri } from "@/contexts/SantriContext";
import { SantriSwitcherTrigger } from "@/components/SantriSwitcher";
import { useQuery } from "@tanstack/react-query";
import { fetchDashboard, fetchInformations } from "@/lib/api";
import { resolveImageUrl } from "@/lib/utils";

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

  const [newsPage, setNewsPage] = useState(1);
  const [allNews, setAllNews] = useState<any[]>([]);

  const { data: newsData, isLoading: isLoadingNews, isFetching: isFetchingNews } = useQuery({
    queryKey: ["informations", newsPage],
    queryFn: async () => {
      const res = await fetchInformations({ page: newsPage, per_page: 3 });
      return res.data;
    },
    enabled: !!active,
  });

  // Accumulate news across pages
  const currentNews = newsData?.data || [];
  const displayedNews = newsPage === 1 ? currentNews : [...allNews, ...currentNews];
  const hasMoreNews = newsData?.next_page_url != null;

  const loadMoreNews = () => {
    setAllNews(displayedNews);
    setNewsPage((p) => p + 1);
  };

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
      <header className="px-5 pt-10 pb-2">
        <div className="bg-white rounded-[24px] p-4 flex items-center justify-between shadow-[0_8px_30px_rgb(0,0,0,0.06)] border border-slate-50">
          <div className="flex items-center gap-3">
            <div className="w-12 h-12 rounded-[16px] bg-gradient-to-br from-[#9b1de8] to-[#610a9c] flex items-center justify-center text-white font-bold shadow-md overflow-hidden">
              {dashboard?.user?.avatar ? (
                <img src={`/${dashboard.user.avatar}`} alt="" className="w-full h-full object-cover" />
              ) : (
                dashboard?.user?.name?.substring(0, 2).toUpperCase() || "W"
              )}
            </div>
            <div>
              <p className="text-[11px] font-bold text-slate-400 tracking-wide uppercase">Assalamualaikum,</p>
              <p className="text-[15px] font-extrabold text-slate-800 tracking-tight leading-tight mt-0.5">
                {dashboard?.user?.name || "Wali Santri"}
              </p>
            </div>
          </div>
          <button className="relative w-11 h-11 rounded-[16px] bg-slate-50 flex items-center justify-center hover:bg-slate-100 transition">
            <Bell size={20} className="text-slate-600" />
            <span className="absolute top-2.5 right-2.5 w-2.5 h-2.5 rounded-full bg-red-500 border-2 border-slate-50" />
          </button>
        </div>
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

      {/* Unit Transfer Promo */}
      {dashboard?.unit_transfer && (
        <section className="px-6 mt-6">
          <div className="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-3xl p-5 shadow-lg relative overflow-hidden flex items-center justify-between">
            <div className="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
            <div className="relative z-10 flex-1 pr-4">
              <h3 className="text-white font-bold text-sm mb-1">Pendaftaran {dashboard.unit_transfer.to_school?.name}</h3>
              <p className="text-white/80 text-xs">Lanjutkan pendidikan ananda ke jenjang berikutnya.</p>
            </div>
            <button 
              onClick={() => navigate({ to: '/lanjut-unit' })}
              className="relative z-10 bg-white text-blue-600 font-bold text-xs px-4 py-2 rounded-xl shadow-sm active:scale-95 transition whitespace-nowrap"
            >
              Daftar
            </button>
          </div>
        </section>
      )}

      {/* Transaksi Hari Ini */}
      <section className="px-6 mt-7 mb-10">
        <div className="flex items-center justify-between mb-1">
          <h3 className="text-base font-bold text-foreground">Transaksi Hari Ini</h3>
          <button onClick={() => navigate({ to: "/riwayat" })} className="text-xs font-semibold text-primary">Lihat Semua</button>
        </div>
        <p className="text-[11px] text-muted-foreground mb-3">
          {new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })}
        </p>

        {/* Summary chips */}
        {dashboard?.todaySummary && dashboard.todaySummary.count > 0 && (
          <div className="flex gap-2 mb-3">
            {dashboard.todaySummary.in > 0 && (
              <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold">
                <ArrowDownLeft size={10} /> +{fmt(dashboard.todaySummary.in)}
              </span>
            )}
            {dashboard.todaySummary.out > 0 && (
              <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-red-50 text-red-600 text-[10px] font-bold">
                <ArrowUpRight size={10} /> -{fmt(dashboard.todaySummary.out)}
              </span>
            )}
            <span className="inline-flex items-center px-2.5 py-1 rounded-full bg-secondary text-muted-foreground text-[10px] font-bold">
              {dashboard.todaySummary.count} transaksi
            </span>
          </div>
        )}

        <div className="bg-card rounded-3xl border border-border divide-y divide-border overflow-hidden shadow-[var(--shadow-soft)]">
          {(dashboard?.recentTransactions && Array.isArray(dashboard.recentTransactions) && dashboard.recentTransactions.length > 0) ? (
            dashboard.recentTransactions.map((t: any, i: number) => {
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
                    <p className="text-[11px] text-muted-foreground flex items-center gap-1.5 flex-wrap">
                      {t.status && t.status !== 'SUCCESS' && (
                        <span className={`px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider ${
                          t.status === 'FAILED' ? 'bg-destructive text-white' : 'bg-[oklch(0.78_0.16_75)] text-white'
                        }`}>
                          {t.status === 'FAILED' ? 'Cek Ulang' : t.status}
                        </span>
                      )}
                      {t.status === 'SUCCESS' && (
                        <span className="px-1.5 py-0.5 rounded bg-emerald-500/15 text-emerald-700 text-[9px] font-bold uppercase tracking-wider">Sukses</span>
                      )}
                      <span>{t.created_at ? new Date(t.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : "-"}</span>
                      {t.merchant && (
                        <>
                          <span className="text-border">·</span>
                          <span className="truncate">{t.merchant}</span>
                        </>
                      )}
                      {t.items_count > 0 && (
                        <>
                          <span className="text-border">·</span>
                          <span>{t.items_count} item</span>
                        </>
                      )}
                    </p>
                  </div>
                  <span
                    className={`text-sm font-bold tabular-nums whitespace-nowrap ${
                      isIn ? "text-emerald-600" : "text-foreground"
                    }`}
                  >
                    {isIn ? "+" : "-"}
                    {fmt(t.amount || 0)}
                  </span>
                </div>
              );
            })
          ) : (
            <div className="py-10 text-center">
              <div className="w-12 h-12 rounded-2xl bg-secondary flex items-center justify-center mx-auto mb-3">
                <Wallet size={20} className="text-muted-foreground" />
              </div>
              <p className="text-xs font-semibold text-muted-foreground">Belum ada transaksi hari ini</p>
              <p className="text-[10px] text-muted-foreground/70 mt-0.5">Transaksi belanja & topup akan muncul di sini</p>
            </div>
          )}
        </div>
      </section>

      {/* Berita Sekolah */}
      <section className="px-5 mt-2 mb-10">
        <div className="flex items-center justify-between mb-4">
          <div className="flex items-center gap-2">
            <div className="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
              <Newspaper size={16} className="text-blue-600" />
            </div>
            <h2 className="text-[16px] font-semibold text-slate-800">Berita Sekolah</h2>
          </div>
        </div>

        {isLoadingNews && newsPage === 1 ? (
          <div className="py-8 text-center">
            <Loader2 size={24} className="animate-spin text-blue-600 mx-auto mb-2" />
            <p className="text-xs text-slate-400">Memuat berita...</p>
          </div>
        ) : displayedNews.length > 0 ? (
          <div className="space-y-4">
            {displayedNews.map((info: any) => {
              const imageUrl = resolveImageUrl(info.image);

              return (
                <div
                  key={info.id}
                  onClick={() => navigate({ to: "/berita/$newsId", params: { newsId: info.id } })}
                  className="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden active:scale-[0.98] transition-all cursor-pointer flex flex-col"
                >
                  {/* Header Card / Image Section */}
                  <div className="relative h-[160px] w-full bg-slate-50">
                    {imageUrl ? (
                      <img
                        src={imageUrl}
                        alt={info.title}
                        className="w-full h-full object-cover"
                        onError={(e) => {
                          (e.target as HTMLImageElement).style.display = "none";
                          (e.target as HTMLImageElement).parentElement!.innerHTML =
                            '<div class="w-full h-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg></div>';
                        }}
                      />
                    ) : (
                      <div className="w-full h-full flex items-center justify-center">
                        <ImageOff size={32} className="text-slate-300" />
                      </div>
                    )}
                    {/* Top Right Action Cluster */}
                    <div className="absolute top-3 right-3 flex items-center gap-2">
                      <div className="w-8 h-8 rounded-full bg-white/80 backdrop-blur-md flex items-center justify-center shadow-sm">
                        <ChevronRight size={18} className="text-slate-400" />
                      </div>
                    </div>
                  </div>

                  {/* Content Section */}
                  <div className="p-4 flex flex-col">
                    <div className="flex items-center justify-between mb-2">
                      <span className="inline-block px-2.5 py-1 bg-blue-50 text-blue-600 text-[11px] font-bold uppercase tracking-widest rounded-lg">
                        {info.information_category?.name || "INFORMASI"}
                      </span>
                      <span className="text-[12px] italic text-slate-400">
                        {new Date(info.created_at).toLocaleDateString("id-ID", {
                          day: "numeric",
                          month: "short",
                          year: "numeric",
                        })}
                      </span>
                    </div>
                    
                    <h3 className="text-[14px] font-medium text-slate-800 line-clamp-2 leading-snug">
                      {info.title}
                    </h3>
                  </div>
                </div>
              );
            })}

            {/* Load More / Pagination */}
            {hasMoreNews && (
              <button
                onClick={loadMoreNews}
                disabled={isFetchingNews}
                className="w-full py-3.5 bg-white border border-slate-100 text-[14px] font-medium text-slate-600 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] active:bg-slate-50 transition-colors flex items-center justify-center gap-2 disabled:opacity-70 mt-2"
              >
                {isFetchingNews ? (
                  <>
                    <Loader2 size={16} className="animate-spin text-blue-600" />
                    <span>Memuat...</span>
                  </>
                ) : (
                  <span>Lihat Berita Lainnya</span>
                )}
              </button>
            )}
          </div>
        ) : (
          <div className="bg-white rounded-[24px] border border-slate-100 p-8 text-center shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
            <div className="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-3">
              <Newspaper size={20} className="text-slate-400" />
            </div>
            <p className="text-[14px] font-semibold text-slate-600">Belum ada berita</p>
            <p className="text-[12px] text-slate-400 mt-0.5">Berita sekolah akan tampil di sini</p>
          </div>
        )}
      </section>
    </MobileShell>
  );
}
