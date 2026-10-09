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
  ChevronLeft,
  Loader2,
  Newspaper,
  Calendar,
  ImageOff,
  ShieldOff,
  PiggyBank,
  Heart,
  Trophy,
  GraduationCap,
  Users,
  Receipt,
  X,
  Sparkles,
  AlertCircle,
} from "lucide-react";
import { useState, useEffect, useMemo } from "react";
import { MobileShell } from "@/components/MobileShell";
import { useSantri } from "@/contexts/SantriContext";
import { SantriSwitcherTrigger } from "@/components/SantriSwitcher";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { fetchDashboard, fetchInformations, fetchLimit, updateLimit as updateLimitApi } from "@/lib/api";
import { resolveImageUrl, safeParseDate } from "@/lib/utils";
import { Text } from "@/components/Text";
import { toast } from "sonner";

export const Route = createFileRoute("/dashboard")({
  component: Dashboard,
  head: () => ({
    meta: [{ title: "Beranda — SantriPay" }],
  }),
});

const menuMapping: Record<string, { label: string; icon: any; accent: string; to: any }> = {
  "topup": { label: "Topup Saldo", icon: Plus, accent: "from-primary to-primary-glow", to: "/topup" as const },
  "saldo": { label: "Topup Saldo", icon: Plus, accent: "from-primary to-primary-glow", to: "/topup" as const },
  "topup saldo": { label: "Topup Saldo", icon: Plus, accent: "from-primary to-primary-glow", to: "/topup" as const },
  "atur limit": { label: "Atur Limit", icon: Sliders, accent: "from-[#6366f1] to-[#4f46e5]", to: "/limit" as const },
  "atur_limit": { label: "Atur Limit", icon: Sliders, accent: "from-[#6366f1] to-[#4f46e5]", to: "/limit" as const },
  "blokir saldo": { label: "Blokir Saldo", icon: ShieldOff, accent: "from-[#ef4444] to-[#dc2626]", to: "/blokir-saldo" as const },
  "blokir_saldo": { label: "Blokir Saldo", icon: ShieldOff, accent: "from-[#ef4444] to-[#dc2626]", to: "/blokir-saldo" as const },
  "tabungan": { label: "Tabungan", icon: PiggyBank, accent: "from-[#10b981] to-[#059669]", to: "/tabungan" as const },
  "tahfidz": { label: "Tahfidz", icon: BookOpen, accent: "from-[#3b82f6] to-[#2563eb]", to: "/tahfidz" as const },
  "perilaku": { label: "Perilaku", icon: Heart, accent: "from-[#f43f5e] to-[#e11d48]", to: "/perilaku" as const },
  "prestasi": { label: "Prestasi", icon: Trophy, accent: "from-[#f59e0b] to-[#d97706]", to: "/prestasi" as const },
  "nilai": { label: "Nilai", icon: GraduationCap, accent: "from-[#8b5cf6] to-[#7c3aed]", to: "/nilai" as const },
  "petugas": { label: "Hubungi Petugas", icon: Users, accent: "from-[#0284c7] to-[#0369a1]", to: "/petugas" as const },
  "perizinan": { label: "Izin Keluar", icon: Calendar, accent: "from-[#ec4899] to-[#db2777]", to: "/perizinan" as const },
};

const STATUS_MAP: Record<string, string> = {
  SUCCESS: "Sukses",
  approved: "Lunas",
  PAID: "Lunas",
  PENDING: "Menunggu Bukti Bayar",
  PENDING_PAYMENT: "Menunggu Bukti Bayar",
  PENDING_CONFIRMATION: "Menunggu Verifikasi",
  CANCELLED: "Dibatalkan",
  cancelled: "Dibatalkan",
  rejected: "Ditolak",
  REJECTED: "Ditolak",
  EXPIRED: "Kedaluwarsa",
  expired: "Kedaluwarsa",
  FAILED: "Gagal",
  failed: "Gagal",
};

const ITEMS_PER_PAGE = 4;

function Dashboard() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [hide, setHide] = useState(false);
  const [showLimitModal, setShowLimitModal] = useState(false);
  const [limitDaily, setLimitDaily] = useState<number>(0);
  const [limitCustomInput, setLimitCustomInput] = useState<string>("");
  const [limitEnabled, setLimitEnabled] = useState<boolean>(true);
  const [txPage, setTxPage] = useState<number>(1);
  const { active, isLoading: isLoadingSantri } = useSantri();

  useEffect(() => {
    if (window.location.hash === "#transaksi-terkini") {
      queryClient.invalidateQueries({ queryKey: ["dashboard"] });
      queryClient.refetchQueries({ queryKey: ["dashboard"] });
      const scrollTimer = setTimeout(() => {
        const el = document.getElementById("transaksi-terkini");
        if (el) {
          el.scrollIntoView({ behavior: "smooth", block: "start" });
        }
      }, 250);
      return () => clearTimeout(scrollTimer);
    }
  }, [queryClient]);
  
  const { data: limitData, isLoading: isLoadingLimit } = useQuery({
    queryKey: ["limit", active?.id],
    queryFn: async () => {
      const res = await fetchLimit();
      return res.data;
    },
    enabled: !!active && showLimitModal,
  });

  useEffect(() => {
    if (limitData) {
      if (limitData.daily_limit > 0) {
        setLimitDaily(limitData.daily_limit);
        setLimitCustomInput(new Intl.NumberFormat("id-ID").format(limitData.daily_limit));
        setLimitEnabled(true);
      } else if (limitData.daily_limit === -1) {
        setLimitDaily(0);
        setLimitCustomInput("");
        setLimitEnabled(false);
      } else {
        if (limitData.effective_limit > 0) {
          setLimitDaily(limitData.effective_limit);
          setLimitCustomInput(new Intl.NumberFormat("id-ID").format(limitData.effective_limit));
          setLimitEnabled(true);
        } else {
          setLimitDaily(0);
          setLimitCustomInput("");
          setLimitEnabled(false);
        }
      }
    } else if (active) {
      const def = (active.daily_limit && active.daily_limit > 0) ? active.daily_limit : (active.effective_daily_limit || 0);
      setLimitDaily(def);
      setLimitCustomInput(def > 0 ? new Intl.NumberFormat("id-ID").format(def) : "");
      setLimitEnabled(def > 0);
    }
  }, [limitData, active, showLimitModal]);

  const updateLimitMutation = useMutation({
    mutationFn: (newLimit: number) => updateLimitApi({ daily_limit: newLimit }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["limit"] });
      queryClient.invalidateQueries({ queryKey: ["active-student"] });
      queryClient.invalidateQueries({ queryKey: ["dashboard"] });
      toast.success("Limit jajan harian berhasil diperbarui");
      setShowLimitModal(false);
    },
    onError: () => {
      toast.error("Gagal memperbarui limit harian.");
    },
  });
  
  const { data: dashboard, isLoading: isLoadingDashboard } = useQuery({
    queryKey: ["dashboard", active?.id],
    queryFn: async () => {
      const res = await fetchDashboard();
      return res.data;
    },
    enabled: !!active,
    staleTime: 0,
    refetchOnMount: "always",
    refetchOnWindowFocus: true,
  });

  const hasUnpaidBills = dashboard?.has_unpaid_bills || false;

  useEffect(() => {
    setTxPage(1);
  }, [active?.id]);

  const validTransactions = useMemo(() => {
    if (!dashboard?.recentTransactions || !Array.isArray(dashboard.recentTransactions)) return [];
    return dashboard.recentTransactions.filter(
      (t: any) => !["CANCELLED", "cancelled", "EXPIRED", "expired", "failed", "FAILED"].includes(t.status)
    );
  }, [dashboard?.recentTransactions]);

  const totalTxPages = Math.max(1, Math.ceil(validTransactions.length / ITEMS_PER_PAGE));

  useEffect(() => {
    if (txPage > totalTxPages) {
      setTxPage(totalTxPages);
    }
  }, [totalTxPages, txPage]);

  const paginatedTransactions = useMemo(() => {
    const currentPage = Math.min(txPage, totalTxPages);
    const start = (currentPage - 1) * ITEMS_PER_PAGE;
    return validTransactions.slice(start, start + ITEMS_PER_PAGE);
  }, [validTransactions, txPage, totalTxPages]);

  useEffect(() => {
    if (hasUnpaidBills) {
      toast.custom(
        (t) => (
          <div className="w-full max-w-sm bg-white/95 backdrop-blur-md rounded-[24px] border border-red-100 shadow-[0_10px_30px_rgba(239,68,68,0.08)] p-5 flex gap-4 items-start animate-in fade-in slide-in-from-top-4 duration-300">
            <div className="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 border border-red-100 bg-red-50 text-red-500">
              <Bell size={22} className="animate-bounce" />
            </div>
            <div className="flex-1 min-w-0">
              <div className="flex justify-between items-baseline">
                <span className="text-[10px] font-extrabold uppercase tracking-widest text-red-600">
                  Tagihan Aktif
                </span>
                <span className="text-[10px] text-slate-400">Penting</span>
              </div>
              <p className="text-[14px] font-bold text-slate-900 mt-1">
                Ada Tagihan Belum Lunas
              </p>
              <p className="text-[12px] text-slate-500 mt-1.5 leading-relaxed">
                Anda memiliki tagihan aktif bulan berjalan yang belum dibayar. Harap segera melakukan pembayaran.
              </p>
              <div className="mt-4 flex gap-2">
                <button
                  onClick={() => {
                    toast.dismiss(t);
                    navigate({ to: "/tagihan" });
                  }}
                  className="px-4 py-2 rounded-xl bg-red-600 text-white text-[11px] font-bold hover:opacity-90 active:scale-95 transition-all shadow-sm"
                >
                  Bayar Sekarang
                </button>
                <button
                  onClick={() => toast.dismiss(t)}
                  className="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-[11px] font-bold hover:bg-slate-200"
                >
                  Tutup
                </button>
              </div>
            </div>
          </div>
        ),
        { id: "unpaid-bill-notification", duration: 10000 }
      );
    }
  }, [hasUnpaidBills, navigate]);

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

  if (isLoadingSantri) {
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

  // Check PWA permission flags
  const isSaldoVisible = dashboard?.pwa_permissions?.show_pwa_saldo ?? (active as any)?.show_pwa_saldo ?? true;

  // Resolve dynamic actions from database menus
  const dynamicActions: Array<{ label: string; icon: any; accent: string; to: any }> = [];
  const dbMenus = dashboard?.menus || [];
  
  dbMenus.forEach((menu: any) => {
    const flagKey = (menu.flag || "").toLowerCase().trim();
    const nameKey = (menu.name || "").toLowerCase().trim();
    const mapped = menuMapping[flagKey] || menuMapping[nameKey];
    if (mapped && mapped.label !== "Atur Limit" && mapped.label !== "Blokir Saldo") {
      // If saldo UI is hidden, filter out Topup Saldo action
      if (!isSaldoVisible && (mapped.label === "Topup Saldo" || mapped.label === "Top Up")) {
        return;
      }
      if (!dynamicActions.some(a => a.label === mapped.label)) {
        dynamicActions.push(mapped);
      }
    }
  });

  const finalActions = dynamicActions.length > 0 ? [
    ...dynamicActions.filter(a => a.label !== "Atur Limit" && a.label !== "Blokir Saldo"),
    { label: "Riwayat", icon: History, accent: "from-primary-glow to-primary", to: "/riwayat" as const }
  ] : (isSaldoVisible ? [
    { label: "Topup Saldo", icon: Plus, accent: "from-primary to-primary-glow", to: "/topup" as const },
    { label: "Riwayat", icon: History, accent: "from-primary-glow to-primary", to: "/riwayat" as const },
  ] : [
    { label: "Tagihan", icon: Receipt, accent: "from-primary to-primary-glow", to: "/tagihan" as const },
    { label: "Riwayat", icon: History, accent: "from-primary-glow to-primary", to: "/riwayat" as const },
  ]);

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
              <Text.Label>Assalamualaikum,</Text.Label>
              <Text.H2 className="mt-0.5 leading-tight">
                {dashboard?.user?.name || "Wali Santri"}
              </Text.H2>
            </div>
          </div>
          <button className="relative w-11 h-11 rounded-[16px] bg-slate-50 flex items-center justify-center hover:bg-slate-100 transition">
            <Bell size={20} className="text-slate-600" />
            {hasUnpaidBills && (
              <span className="absolute top-2.5 right-2.5 w-2.5 h-2.5 rounded-full bg-red-500 border-2 border-slate-50" />
            )}
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
                <div className="flex items-center gap-2 flex-wrap">
                  <Text.Label className="text-white/90">
                    {isSaldoVisible ? "Saldo Santri" : "Profil Santri"}
                  </Text.Label>
                  {(active as any).nisn && (
                    <span className="px-1.5 py-0.5 rounded-md bg-white/15 text-[10px] font-extrabold tracking-wide uppercase text-white/95 border border-white/10 shrink-0">
                      {(active as any).nisn}
                    </span>
                  )}
                </div>
                <Text.Body className="text-white font-bold mt-1 truncate">
                  {active.name} <span className="text-white/85 font-semibold">· {active.classroom?.name || "Tanpa Kelas"}</span>
                </Text.Body>
              </div>
              <SantriSwitcherTrigger>
                <span className="px-2.5 py-1 rounded-full bg-white/15 border border-white/20 text-[10px] font-semibold backdrop-blur flex items-center gap-1 cursor-pointer shrink-0">
                  Ganti <ChevronDown size={12} />
                </span>
              </SantriSwitcherTrigger>
            </div>

            {isSaldoVisible ? (
              <div className="flex items-center justify-between mt-5">
                <div className="flex items-end gap-3">
                  <h2 className="text-3xl font-bold tracking-tight">
                    {hide ? "Rp ••••••" : fmt(dashboard?.activeStudent?.saldo ?? active.saldo)}
                  </h2>
                  <button onClick={() => setHide((h) => !h)} className="mb-1.5 text-white/80">
                    {hide ? <EyeOff size={18} /> : <Eye size={18} />}
                  </button>
                </div>
                <button
                  type="button"
                  onClick={() => setShowLimitModal(true)}
                  className="text-right bg-white/15 hover:bg-white/25 active:scale-95 transition-all px-3 py-1.5 rounded-2xl border border-white/20 backdrop-blur-md shadow-sm shrink-0 flex flex-col items-end group cursor-pointer"
                  title="Klik untuk atur limit harian"
                >
                  <div className="flex items-center gap-1">
                    <Text.Label className="text-white/85 font-bold block text-[10px] tracking-wider uppercase group-hover:text-white transition">
                      Limit Harian
                    </Text.Label>
                    <Sliders size={11} className="text-white/80 group-hover:text-white transition" />
                  </div>
                  <span className="text-[14px] font-black tracking-tight text-white mt-0.5 block leading-tight drop-shadow-sm">
                    {fmt(active.effective_daily_limit)}
                  </span>
                </button>
              </div>
            ) : (
              <div className="flex flex-col gap-2.5 mt-4 bg-white/10 p-3.5 rounded-2xl border border-white/15 backdrop-blur-md shadow-sm">
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-2.5">
                    <div className="w-8 h-8 rounded-xl bg-emerald-500/25 border border-emerald-400/40 flex items-center justify-center text-emerald-300 font-bold shrink-0">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" className="text-emerald-300">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="m9 12 2 2 4-4"/>
                      </svg>
                    </div>
                    <div>
                      <span className="text-[10px] font-extrabold uppercase tracking-widest text-emerald-300 block leading-none">Status Santri</span>
                      <span className="text-xs font-bold text-white block mt-0.5">Santri Terdaftar Aktif</span>
                    </div>
                  </div>
                  <div className="text-right">
                    <span className="px-2.5 py-1 rounded-lg bg-white/15 text-[10px] font-extrabold uppercase text-white border border-white/15">
                      {active.school?.name || "Pondok"}
                    </span>
                  </div>
                </div>

                {/* Custom Hero Notice */}
                <div className="pt-2 border-t border-white/15 flex items-start gap-2">
                  <div className="mt-0.5 w-4 h-4 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                    <span className="text-[10px] font-bold text-white leading-none">ℹ</span>
                  </div>
                  <p className="text-[11px] leading-snug text-white/90 font-medium">
                    {dashboard?.hero_saldo_off_message || "Layanan uang saku & belanja santri dikelola melalui sistem kartu utama / aplikasi lama."}
                  </p>
                </div>
              </div>
            )}

            <div className="mt-6 flex items-center justify-between text-xs gap-3">
              <div className="min-w-0" style={{ width: "30%" }}>
                <Text.Label className="text-white/85 block text-[10px]">Kamar</Text.Label>
                <Text.Caption className="text-white not-italic font-bold truncate mt-0.5 block">{(active as any).asrama_name || "-"}</Text.Caption>
              </div>
              <div className="h-8 w-px bg-white/20 shrink-0" />
              <div className="min-w-0" style={{ width: "70%" }}>
                <Text.Label className="text-white/85 block text-[10px]">Penanggung Jawab</Text.Label>
                {((active as any).asrama_host || (active as any).asramaHost) ? (
                  <div className="flex flex-col">
                    <a
                      href={`https://wa.me/${((active as any).asrama_host || (active as any).asramaHost).phone}`}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex items-center justify-start gap-1 mt-0.5 text-emerald-300 font-bold hover:text-emerald-200 transition-all text-[12px] max-w-full"
                    >
                      <span className="truncate max-w-[170px]">{((active as any).asrama_host || (active as any).asramaHost).name}</span>
                      <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse shrink-0" />
                    </a>
                    <Text.Caption className="text-white/70 italic mt-0.5 leading-none block">
                      Klik nama untuk chat WA langsung
                    </Text.Caption>
                  </div>
                ) : (
                  <Text.Caption className="text-white/50 not-italic font-bold mt-0.5 block">-</Text.Caption>
                )}
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Quick actions */}
      <section className="px-5 mt-6">
        <div className="grid grid-cols-4 gap-2">
          {finalActions.map(({ label, icon: Icon, accent, to }) => (
            <button
              key={label}
              onClick={() => navigate({ to })}
              className="flex flex-col items-center gap-2 p-2 rounded-[20px] bg-card border border-border shadow-[var(--shadow-soft)] active:scale-95 transition"
            >
              <div className={`w-10 h-10 rounded-[14px] bg-gradient-to-br ${accent} flex items-center justify-center shadow-sm`}>
                <Icon size={18} className="text-primary-foreground" />
              </div>
              <span className="text-[10px] font-bold text-slate-700 text-center leading-tight">
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

      {/* Transaksi Terkini */}
      <section id="transaksi-terkini" className="px-5 mt-7 mb-10 scroll-mt-20">
        <div className="flex items-center justify-between mb-3">
          <Text.H2 className="text-base font-bold text-foreground">Transaksi Terkini</Text.H2>
          <button onClick={() => navigate({ to: "/riwayat" })} className="text-xs font-semibold text-primary bg-transparent shadow-none">Lihat Semua</button>
        </div>

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

        <div className="bg-card rounded-[24px] border border-border divide-y divide-border overflow-hidden shadow-[var(--shadow-soft)]">
          {isLoadingDashboard ? (
            <div className="divide-y divide-border">
              {[1, 2, 3, 4].map((i) => (
                <div key={i} className="flex items-center gap-3 p-4 animate-pulse">
                  <div className="w-11 h-11 rounded-2xl bg-slate-100 shrink-0" />
                  <div className="flex-1 space-y-2">
                    <div className="h-4 w-32 bg-slate-100 rounded" />
                    <div className="h-3 w-16 bg-slate-100 rounded animate-pulse" />
                  </div>
                  <div className="h-4 w-16 bg-slate-100 rounded" />
                </div>
              ))}
            </div>
          ) : validTransactions.length > 0 ? (
            <>
              {paginatedTransactions.map((t: any, i: number) => {
                const isIn = t.type === "IN";
                const isBill = t.category === "BILL";
                const isPending = t.id && ["PENDING", "PENDING_PAYMENT", "PENDING_CONFIRMATION"].includes(t.status);
                const isRejected = t.status === "REJECTED" || t.status === "rejected";
                const isClickable = Boolean(t.id && (isBill || isPending || isRejected));

                return (
                  <div
                    key={t.id || i}
                    onClick={isClickable ? () => navigate({ to: "/pembayaran/$payId", params: { payId: String(t.id) } }) : undefined}
                    className={`flex items-start gap-3 p-4 transition-all ${
                      isClickable 
                        ? "cursor-pointer hover:bg-slate-50 active:bg-slate-100/80" 
                        : ""
                    }`}
                  >
                    <div
                      className={`w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 mt-0.5 ${
                        isRejected
                          ? "bg-red-50 text-red-600"
                          : isIn 
                          ? "bg-emerald-50 text-emerald-600" 
                          : isBill 
                          ? "bg-purple-50 text-purple-600" 
                          : "bg-blue-50 text-blue-600"
                      }`}
                    >
                      {isIn ? (
                        <ArrowDownLeft size={18} />
                      ) : isBill ? (
                        <Receipt size={18} />
                      ) : (
                        <Utensils size={18} />
                      )}
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-semibold text-foreground truncate">{t.note || (isIn ? "Saldo Masuk" : isBill ? "Pembayaran Tagihan" : "Belanja Kantin")}</p>
                      <p className="text-[11px] text-muted-foreground flex items-center gap-1.5 flex-wrap">
                        {t.status && (
                          <span className={`px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider ${
                            t.status === "SUCCESS" || t.status === "approved" || t.status === "PAID"
                              ? "bg-emerald-100 text-emerald-800"
                              : t.status === "FAILED" || isRejected
                              ? "bg-red-100 text-red-700"
                              : t.status === "PENDING_CONFIRMATION"
                              ? "bg-blue-100 text-blue-700"
                              : "bg-amber-100 text-amber-800"
                          }`}>
                            {STATUS_MAP[t.status] || t.status}
                          </span>
                        )}
                        <span>
                          {t.created_at ? (
                            <>
                              {safeParseDate(t.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}
                              {' · '}
                              {safeParseDate(t.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}
                            </>
                          ) : "-"}
                        </span>
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
                      {isRejected && t.rejection_note && (
                        <div className="mt-2 flex items-start gap-1.5 p-2 rounded-xl bg-red-50 border border-red-200/80 text-[11px] text-red-900 leading-tight w-full">
                          <AlertCircle size={13} className="shrink-0 text-red-600 mt-0.5" />
                          <span>
                            <span className="font-bold text-red-950">Alasan Penolakan:</span> {t.rejection_note}
                          </span>
                        </div>
                      )}
                    </div>
                    <div className="flex items-center gap-2 mt-0.5">
                      <span
                        className={`text-sm font-bold tabular-nums whitespace-nowrap ${
                          isIn ? "text-emerald-600" : "text-red-600"
                        }`}
                      >
                        {isIn ? "+" : "-"}
                        {fmt(t.amount || 0)}
                      </span>
                      {isClickable && (
                        <ChevronRight size={14} className="text-slate-400 shrink-0" />
                      )}
                    </div>
                  </div>
                );
              })}

              {totalTxPages > 1 && (
                <div className="flex items-center justify-between px-4 py-3 bg-slate-50/70 border-t border-border">
                  <Text.Caption className="not-italic text-slate-500 font-medium">
                    Hal {txPage} dari {totalTxPages} ({validTransactions.length} transaksi)
                  </Text.Caption>
                  <div className="flex items-center gap-1.5">
                    <button
                      type="button"
                      onClick={() => setTxPage((p) => Math.max(1, p - 1))}
                      disabled={txPage === 1}
                      className="w-8 h-8 rounded-xl flex items-center justify-center bg-white border border-slate-200 text-slate-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-slate-100 active:scale-95 transition shadow-sm"
                      title="Halaman Sebelumnya"
                    >
                      <ChevronLeft size={16} />
                    </button>
                    {Array.from({ length: totalTxPages }).map((_, idx) => {
                      const pageNum = idx + 1;
                      const isActive = pageNum === txPage;
                      return (
                        <button
                          key={pageNum}
                          type="button"
                          onClick={() => setTxPage(pageNum)}
                          className={`w-8 h-8 rounded-xl text-xs font-bold transition flex items-center justify-center ${
                            isActive
                              ? "bg-blue-600 text-white shadow-sm"
                              : "bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 active:scale-95"
                          }`}
                        >
                          {pageNum}
                        </button>
                      );
                    })}
                    <button
                      type="button"
                      onClick={() => setTxPage((p) => Math.min(totalTxPages, p + 1))}
                      disabled={txPage === totalTxPages}
                      className="w-8 h-8 rounded-xl flex items-center justify-center bg-white border border-slate-200 text-slate-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-slate-100 active:scale-95 transition shadow-sm"
                      title="Halaman Selanjutnya"
                    >
                      <ChevronRight size={16} />
                    </button>
                  </div>
                </div>
              )}
            </>
          ) : (
            <div className="py-10 text-center">
              <div className="w-12 h-12 rounded-2xl bg-secondary flex items-center justify-center mx-auto mb-3">
                <Wallet size={20} className="text-muted-foreground" />
              </div>
              <p className="text-xs font-semibold text-muted-foreground">Belum ada transaksi</p>
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
                        {safeParseDate(info.created_at).toLocaleDateString("id-ID", {
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

      {/* Modal Pengaturan Limit Harian */}
      {showLimitModal && (
        <div className="fixed inset-0 bg-slate-950/60 backdrop-blur-md z-50 flex items-center justify-center p-5 animate-in fade-in duration-200">
          <div className="bg-white w-full max-w-sm rounded-[24px] border border-slate-100 shadow-[0_20px_50px_rgba(0,0,0,0.15)] p-6 relative overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            {/* Decorative top background blur */}
            <div className="absolute -top-16 -right-16 w-32 h-32 rounded-full bg-blue-500/10 blur-2xl pointer-events-none" />

            {/* Header */}
            <div className="flex items-center justify-between pb-3.5 border-b border-slate-100">
              <div className="flex items-center gap-2.5">
                <div className="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                  <Sliders size={18} />
                </div>
                <div>
                  <Text.H2 className="leading-none text-slate-800">Atur Limit Harian</Text.H2>
                  <Text.Caption className="text-slate-400 not-italic text-[11px] mt-0.5 block">
                    Kontrol belanja santri di kantin digital
                  </Text.Caption>
                </div>
              </div>
              <button
                type="button"
                onClick={() => setShowLimitModal(false)}
                className="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-400 hover:text-slate-600 transition active:scale-90"
              >
                <X size={16} />
              </button>
            </div>

            {/* Status Toggle */}
            <div className="mt-4 bg-slate-50 rounded-2xl p-3.5 border border-slate-100 flex items-center justify-between">
              <div className="flex-1 mr-3">
                <p className="text-xs font-bold text-slate-800">Aktifkan Limit Harian</p>
                <p className="text-[10px] text-slate-500 leading-tight mt-0.5">
                  {limitDaily === 0 && (active?.effective_daily_limit || 0) > 0
                    ? "Limit otomatis oleh sekolah. Nonaktifkan untuk batal."
                    : "Membatasi pengeluaran jajan harian santri agar hemat."}
                </p>
              </div>
              <button
                type="button"
                onClick={() => {
                  const next = !limitEnabled;
                  setLimitEnabled(next);
                  if (!next) {
                    setLimitDaily(0);
                    setLimitCustomInput("");
                  } else {
                    const defVal = (active?.daily_limit && active.daily_limit > 0) ? active.daily_limit : (active?.effective_daily_limit || 50_000);
                    setLimitDaily(defVal);
                    setLimitCustomInput(new Intl.NumberFormat("id-ID").format(defVal));
                  }
                }}
                className={`relative w-11 h-6 rounded-full transition-colors duration-200 ${
                  limitEnabled ? "bg-blue-600" : "bg-slate-300"
                }`}
              >
                <div
                  className={`absolute top-0.5 w-5 h-5 rounded-full bg-white shadow-sm transition-transform duration-200 ${
                    limitEnabled ? "translate-x-5.5" : "translate-x-0.5"
                  }`}
                />
              </button>
            </div>

            {/* Nominal Controls */}
            {limitEnabled && (
              <div className="mt-4 space-y-3">
                <div className="flex items-center justify-between">
                  <Text.Label className="text-slate-400">Nominal Limit</Text.Label>
                  <span className="text-base font-extrabold text-blue-600">
                    {fmt(limitDaily)}
                  </span>
                </div>

                {/* Preset Chips */}
                <div className="grid grid-cols-4 gap-1.5">
                  {[15_000, 25_000, 50_000, 100_000].map((p) => {
                    const isSelected = limitDaily === p;
                    return (
                      <button
                        key={p}
                        type="button"
                        onClick={() => {
                          setLimitDaily(p);
                          setLimitCustomInput(new Intl.NumberFormat("id-ID").format(p));
                        }}
                        className={`py-2 rounded-xl text-[11px] font-bold border transition active:scale-95 ${
                          isSelected
                            ? "bg-blue-600 text-white border-blue-600 shadow-sm"
                            : "bg-slate-50 text-slate-700 border-slate-200 hover:border-slate-300"
                        }`}
                      >
                        {p >= 1000 ? `${p / 1000}rb` : p}
                      </button>
                    );
                  })}
                </div>

                {/* Custom Input */}
                <div className="pt-0.5">
                  <div className="flex items-center gap-2 bg-slate-50 rounded-2xl px-3.5 py-2.5 border border-slate-200 focus-within:border-blue-600 focus-within:ring-2 focus-within:ring-blue-100 transition">
                    <span className="text-xs font-bold text-blue-600">Rp</span>
                    <input
                      type="text"
                      inputMode="numeric"
                      value={limitCustomInput}
                      onChange={(e) => {
                        const raw = e.target.value.replace(/\D/g, "");
                        if (!raw) {
                          setLimitCustomInput("");
                          setLimitDaily(0);
                          return;
                        }
                        const val = parseInt(raw, 10);
                        setLimitCustomInput(new Intl.NumberFormat("id-ID").format(val));
                        setLimitDaily(val);
                      }}
                      placeholder="Atau ketik sendiri: misal 20.000"
                      className="bg-transparent flex-1 outline-none text-slate-800 text-xs font-bold placeholder:text-slate-400 placeholder:font-normal"
                    />
                    {limitCustomInput && (
                      <button
                        type="button"
                        onClick={() => {
                          setLimitCustomInput("");
                          setLimitDaily(0);
                        }}
                        className="w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 hover:text-slate-700 text-[10px] transition"
                      >
                        <X size={12} />
                      </button>
                    )}
                  </div>
                </div>
              </div>
            )}

            {/* Info tips */}
            <div className="mt-3.5 rounded-xl bg-blue-50/70 p-2.5 border border-blue-100/70 flex items-start gap-2">
              <Sparkles size={14} className="text-blue-600 shrink-0 mt-0.5" />
              <p className="text-[10px] text-slate-600 leading-normal">
                Transaksi jajan otomatis dibatasi sesuai nominal ini setiap harinya.
              </p>
            </div>

            {/* Actions */}
            <div className="mt-4 flex gap-2">
              <button
                type="button"
                onClick={() => setShowLimitModal(false)}
                className="flex-1 py-3 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-600 transition active:scale-95"
              >
                Batal
              </button>
              <button
                type="button"
                onClick={() => {
                  const payload = limitEnabled ? (limitDaily > 0 ? limitDaily : 0) : -1;
                  updateLimitMutation.mutate(payload);
                }}
                disabled={updateLimitMutation.isPending}
                className="flex-1 py-3 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md active:scale-95 disabled:opacity-50 transition flex items-center justify-center gap-1.5"
              >
                {updateLimitMutation.isPending ? (
                  <Loader2 size={14} className="animate-spin" />
                ) : (
                  "Simpan Limit"
                )}
              </button>
            </div>

            {/* Detailed page shortcut */}
            <div className="mt-3 text-center">
              <button
                type="button"
                onClick={() => {
                  setShowLimitModal(false);
                  navigate({ to: "/limit" });
                }}
                className="text-[11px] font-semibold text-blue-600 hover:underline inline-flex items-center gap-1 active:scale-95 transition"
              >
                Buka halaman pengaturan limit lengkap →
              </button>
            </div>
          </div>
        </div>
      )}
    </MobileShell>
  );
}
