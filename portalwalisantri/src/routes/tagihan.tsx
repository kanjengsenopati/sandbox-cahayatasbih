import { createFileRoute, Link } from "@tanstack/react-router";
import { useMemo, useState } from "react";
import {
  ArrowLeft,
  Search,
  CheckCircle2,
  ChevronRight,
  Receipt,
  Loader2,
} from "lucide-react";
import { useNavigate } from "@tanstack/react-router";
import { MobileShell } from "@/components/MobileShell";
import { useSantri } from "@/contexts/SantriContext";
import { SantriSwitcherTrigger } from "@/components/SantriSwitcher";
import { useQuery } from "@tanstack/react-query";
import { fetchBills } from "@/lib/api";

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
      due: b.due_date ? `Jatuh tempo: ${new Date(b.due_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}` : "",
    }));
  }, [billsData]);

  const filtered = useMemo(() => {
    return bills.filter((b: any) => {
      const isPaid = b.paid >= b.total;
      if (tab === "due" && isPaid) return false;
      if (tab === "paid" && !isPaid) return false;
      if (q && !b.name.toLowerCase().includes(q.toLowerCase())) return false;
      return true;
    });
  }, [bills, tab, q]);

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
                className={`flex-1 pb-3 pt-2 text-sm font-bold relative transition ${
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

      {/* Search */}
      <div className="px-4 mt-4">
        <div className="flex items-center gap-2 bg-secondary rounded-full px-4 py-3 border border-transparent focus-within:border-primary transition">
          <Search size={16} className="text-muted-foreground" />
          <input
            value={q}
            onChange={(e) => setQ(e.target.value)}
            placeholder="Cari Data"
            className="bg-transparent flex-1 outline-none text-sm font-medium text-foreground placeholder:text-muted-foreground"
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
            <div className="flex items-center justify-between mb-2 px-1">
              <h3 className="text-[11px] font-bold text-muted-foreground uppercase tracking-widest">
                {cat}
              </h3>
              <span className="text-[10px] font-semibold text-muted-foreground">
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
      </div>
    </div>
  );
}
