import { createFileRoute, Link } from "@tanstack/react-router";
import { useMemo, useState } from "react";
import {
  ArrowLeft,
  Search,
  CheckCircle2,
  ChevronRight,
  Receipt,
  Loader2,
  ChevronDown,
} from "lucide-react";
import { useNavigate } from "@tanstack/react-router";
import { MobileShell } from "@/components/MobileShell";
import { useSantri } from "@/contexts/SantriContext";
import { SantriSwitcherTrigger } from "@/components/SantriSwitcher";
import { useQuery } from "@tanstack/react-query";
import { fetchBills } from "@/lib/api";
import { safeParseDate } from "@/lib/utils";

export const Route = createFileRoute("/tagihan")({
  component: Tagihan,
  head: () => ({ meta: [{ title: "Tagihan — SantriPay" }] }),
});

const fmtIDR = (n: number) =>
  new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(n);

function Tagihan() {
  const navigate = useNavigate();
  const { active, isLoading: isLoadingSantri } = useSantri();
  const [tab, setTab] = useState<"due" | "paid">("due");
  const [q, setQ] = useState("");
  const [selectedYear, setSelectedYear] = useState("");

  const { data: billsData, isLoading: isLoadingBills } = useQuery({
    queryKey: ["bills", active?.id],
    queryFn: async () => {
      const res = await fetchBills();
      return res.data;
    },
    enabled: !!active,
  });

  const bills = useMemo(() => {
    if (!billsData) return [];
    const allBills = [...(billsData.unpaid || []), ...(billsData.paid || [])];
    return allBills.map((b: any) => ({
      id: b.bill_type_id,
      name: b.bill_type_name,
      category: b.academic_year || "Lainnya",
      total: b.total,
      paid: b.paid,
      due: b.due_date ? `Jatuh tempo: ${safeParseDate(b.due_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}` : "",
      payments: b.payments || [],
      payment_input_type: b.payment_input_type || "FIXED",
    }));
  }, [billsData]);

  const academicYears = useMemo(() => {
    const years = bills.map((b: any) => b.category);
    return Array.from(new Set(years)).filter(Boolean).sort().reverse();
  }, [bills]);

  const filtered = useMemo(() => {
    return bills.filter((b: any) => {
      const isPaid = b.paid >= b.total;
      if (tab === "due" && isPaid) return false;
      if (tab === "paid" && !isPaid) return false;
      if (q && !b.name.toLowerCase().includes(q.toLowerCase())) return false;
      if (selectedYear && b.category !== selectedYear) return false;
      return true;
    });
  }, [bills, tab, q, selectedYear]);

  const grouped = useMemo(() => {
    const map = new Map<string, any[]>();
    for (const b of filtered) {
      map.set(b.category, [...(map.get(b.category) ?? []), b]);
    }
    return Array.from(map.entries());
  }, [filtered]);

  if (isLoadingSantri || isLoadingBills) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-background">
        <Loader2 className="animate-spin text-primary" size={40} />
      </div>
    );
  }

  const totalDue = bills.filter((b: any) => b.paid < b.total).reduce((acc: number, b: any) => acc + (b.total - b.paid), 0);

  return (
    <MobileShell>
      {/* Header */}
      <div className="px-6 pt-12 pb-4 flex items-center gap-3">
        <button
          onClick={() => navigate({ to: "/dashboard" })}
          className="w-10 h-10 rounded-xl bg-secondary border border-border flex items-center justify-center text-foreground active:scale-95 transition"
        >
          <ArrowLeft size={18} />
        </button>
        <div>
          <p className="text-[11px] text-muted-foreground font-semibold uppercase tracking-wider">
            Tagihan
          </p>
          <p className="text-base font-bold text-foreground">Pembayaran Santri</p>
        </div>
      </div>

      {/* Profile card */}
      <div className="px-4 mt-2 relative z-10">
        <div
          className="rounded-2xl p-5 text-primary-foreground shadow-[var(--shadow-glow)]"
          style={{ background: "var(--gradient-card)" }}
        >
          <div className="flex items-start justify-between gap-3">
            <div className="min-w-0">
              <p className="text-[11px] text-white/70">Nama Siswa / Santri</p>
              <p className="text-base font-bold tracking-tight mt-0.5 truncate">
                {active.name.toUpperCase()}
              </p>
              <p className="text-[11px] text-white/70 mt-0.5">
                {active.jenjang} · Kelas {active.className}
              </p>
            </div>
            <SantriSwitcherTrigger variant="subtle">Ganti Santri</SantriSwitcherTrigger>
          </div>

          <div className="mt-5">
            <p className="text-[11px] text-white/70">Tagihan Saat ini</p>
            <p className="text-2xl font-extrabold tracking-tight mt-0.5">{fmtIDR(totalDue)}</p>
          </div>
        </div>
      </div>

      {/* Tabs */}
      <div className="px-4 mt-5">
        <div className="flex border-b border-border">
          {([
            { id: "due", label: "Tagihan" },
            { id: "paid", label: "Lunas" },
          ] as const).map((t) => {
            const active = tab === t.id;
            return (
              <button
                key={t.id}
                onClick={() => setTab(t.id)}
                className={`flex-1 pb-3 pt-2 text-sm font-bold relative transition bg-transparent ${
                  active ? "text-primary" : "text-muted-foreground"
                }`}
              >
                {t.label}
                {active && (
                  <span className="absolute bottom-0 left-1/2 -translate-x-1/2 w-12 h-1 rounded-full bg-primary" />
                )}
              </button>
            );
          })}
        </div>
      </div>

      {/* Search & Filter */}
      <div className="px-4 mt-4 flex items-center gap-2">
        <div className="flex-1 flex items-center gap-2 bg-secondary rounded-full px-4 py-3 border border-transparent focus-within:border-primary transition">
          <Search size={16} className="text-muted-foreground" />
          <input
            value={q}
            onChange={(e) => setQ(e.target.value)}
            placeholder="Cari Data"
            className="bg-transparent flex-1 outline-none text-sm font-medium text-foreground placeholder:text-muted-foreground"
          />
        </div>
        
        {/* Academic Year Filter */}
        <div className="relative shrink-0">
          <select
            value={selectedYear}
            onChange={(e) => setSelectedYear(e.target.value)}
            className="appearance-none bg-secondary text-foreground text-xs font-bold pl-4 pr-9 py-3.5 rounded-full border border-transparent focus:border-primary outline-none transition cursor-pointer"
          >
            <option value="">Semua TA</option>
            {academicYears.map((yr) => (
              <option key={yr} value={yr}>
                {yr}
              </option>
            ))}
          </select>
          <ChevronDown
            size={14}
            className="absolute right-3.5 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none"
          />
        </div>
      </div>

      {/* Bills grouped by category */}
      <div className="px-4 mt-5 space-y-6">
        {grouped.length === 0 && (
          <div className="text-center py-12">
            <div className="w-14 h-14 rounded-2xl bg-secondary mx-auto flex items-center justify-center text-muted-foreground">
              <Receipt size={26} />
            </div>
            <p className="mt-3 text-sm font-bold text-foreground">
              {tab === "paid" ? "Belum ada tagihan lunas" : "Tidak ada tagihan"}
            </p>
            <p className="text-xs text-muted-foreground">
              Coba ubah kata kunci pencarian.
            </p>
          </div>
        )}

        {grouped.map(([cat, items]) => (
          <section key={cat}>
            <div className="rounded-2xl bg-primary px-4 py-2.5 flex items-center justify-between text-primary-foreground shadow-[var(--shadow-soft)] mt-4 mb-3">
              <span className="text-xs font-extrabold tracking-widest uppercase text-white">
                Tahun Ajaran {cat}
              </span>
              <span className="px-2.5 py-0.5 rounded-full bg-white/20 text-[10px] font-bold uppercase tracking-wider text-white">
                {items.length} item
              </span>
            </div>

            <div className="space-y-3">
              {items.map((b) => (
                <BillCard key={b.id} bill={b} />
              ))}
            </div>
          </section>
        ))}
      </div>
    </MobileShell>
  );
}

function BillCard({ bill }: { bill: any }) {
  const remaining = Math.max(0, bill.total - bill.paid);
  const isPaid = remaining === 0;
  const pct = Math.min(100, Math.round((bill.paid / bill.total) * 100));
  const [expanded, setExpanded] = useState(false);

  return (
    <div className="relative rounded-2xl bg-card border border-border shadow-[var(--shadow-soft)] overflow-hidden">
      {/* Left accent bar */}
      <span
        className={`absolute left-0 top-3 bottom-3 w-1 rounded-r-full ${
          isPaid ? "bg-emerald-500" : "bg-primary"
        }`}
      />

      <div className="p-4 pl-5">
        <div className="flex items-start justify-between gap-3">
          <div className="min-w-0">
            <p className="text-[13px] font-bold text-foreground leading-snug">
              {bill.name}
            </p>
            <p className="text-base font-extrabold text-foreground tracking-tight mt-1">
              {fmtIDR(bill.total)}
            </p>
            {!isPaid && bill.due && (
              <p className="text-[10px] text-muted-foreground mt-0.5">{bill.due}</p>
            )}
          </div>

          {isPaid ? (
            <span className="shrink-0 inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-600 text-[11px] font-bold">
              <CheckCircle2 size={13} /> Lunas
            </span>
          ) : (
            <Link
              to="/tagihan/$billId"
              params={{ billId: bill.id }}
              className="shrink-0 px-4 py-2.5 rounded-xl text-primary-foreground text-xs font-bold shadow-[var(--shadow-soft)] active:scale-95 transition flex items-center gap-1"
              style={{ background: "var(--gradient-card)" }}
            >
              Bayar <ChevronRight size={14} />
            </Link>
          )}
        </div>

        {/* Pills */}
        <div className="mt-4 grid grid-cols-2 gap-2">
          <div>
            <p className="text-[10px] text-slate-400 font-medium mb-1.5 uppercase tracking-wide">Sudah Dibayarkan</p>
            <div className="rounded-xl bg-emerald-50 text-emerald-600 text-[13px] font-extrabold px-3 py-2 text-center truncate border border-emerald-100">
              {fmtIDR(bill.paid)}
            </div>
          </div>
          <div>
            <p className="text-[10px] text-slate-400 font-medium mb-1.5 uppercase tracking-wide text-right">
              {isPaid ? "Status" : "Kekurangan"}
            </p>
            <div
              className={`rounded-xl text-[13px] font-extrabold px-3 py-2 text-center truncate border ${
                isPaid ? "bg-emerald-50 text-emerald-600 border-emerald-100" : "bg-red-50 text-red-600 border-red-100"
              }`}
            >
              {isPaid ? "Lunas" : fmtIDR(remaining)}
            </div>
          </div>
        </div>

        {/* Progress */}
        {!isPaid && (
          <div className="mt-4">
            <div className="flex justify-between items-center mb-1.5 px-1">
              <span className="text-[10px] font-bold text-slate-400">Progress Pembayaran</span>
              <span className="text-[10px] font-extrabold text-[#9b1de8]">{pct}%</span>
            </div>
            <div className="h-2 rounded-full bg-slate-100 overflow-hidden">
              <div
                className="h-full rounded-full bg-gradient-to-r from-[#9b1de8] to-[#610a9c]"
                style={{ width: `${pct}%` }}
              />
            </div>
          </div>
        )}

        {/* Expandable Payments History Panel */}
        {bill.payment_input_type === "FREE" && bill.payments && bill.payments.length > 0 && (
          <div className="mt-4 border-t border-border pt-4">
            <button
              onClick={() => setExpanded(!expanded)}
              className="w-full flex items-center justify-between text-xs font-bold text-primary active:opacity-70 transition bg-transparent border-0 outline-none p-0 cursor-pointer"
            >
              <span>Lihat Riwayat Angsuran ({bill.payments.length})</span>
              <ChevronDown
                size={14}
                className={`transition-transform duration-200 ${expanded ? "rotate-180" : ""}`}
              />
            </button>
            
            {expanded && (
              <div className="mt-3 space-y-2.5">
                {bill.payments.map((p: any) => (
                  <div key={p.id} className="flex items-center justify-between p-3 rounded-2xl bg-secondary/40 border border-border/50 text-[11px]">
                    <div className="min-w-0">
                      <p className="font-bold text-foreground truncate">
                        {p.method}
                      </p>
                      <p className="text-[9px] text-muted-foreground mt-0.5">
                        {safeParseDate(p.date).toLocaleDateString("id-ID", {
                          day: "numeric",
                          month: "short",
                          year: "numeric",
                        })} · Petugas: {p.cashier}
                      </p>
                    </div>
                    <span className="font-bold text-emerald-600 shrink-0 ml-2">
                      +{fmtIDR(p.amount)}
                    </span>
                  </div>
                ))}
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  );
}
