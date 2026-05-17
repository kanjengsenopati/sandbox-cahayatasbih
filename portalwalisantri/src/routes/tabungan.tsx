import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { PiggyBank, ArrowLeft, Loader2, ArrowUpRight, ArrowDownLeft, Calendar } from "lucide-react";
import { useQuery } from "@tanstack/react-query";
import { fetchSavingHistories } from "@/lib/api";
import { useSantri } from "@/contexts/SantriContext";
import { useState } from "react";

export const Route = createFileRoute("/tabungan")({
  component: Tabungan,
  head: () => ({ meta: [{ title: "Tabungan Santri — SantriPay" }] }),
});

type FilterType = "all" | "today" | "week" | "month";

function Tabungan() {
  const navigate = useNavigate();
  const [filter, setFilter] = useState<FilterType>("all");

  const { active: student, isLoading: isLoadingStudent } = useSantri();

  const { data: historyRes, isLoading: isLoadingHistory } = useQuery({
    queryKey: ["saving-histories", filter],
    queryFn: async () => {
      const params = filter !== "all" ? { filter } : {};
      const res = await fetchSavingHistories(params);
      return res.data;
    },
  });

  const savingAmount = student?.saving ?? 0;
  const histories = historyRes?.data ?? [];

  const fmt = (n: number) =>
    new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(n);

  return (
    <div className="min-h-screen w-full flex justify-center bg-secondary">
      <div className="relative w-full max-w-md min-h-screen bg-background pb-32">
        {/* Hero */}
        <div
          className="relative px-6 pt-12 pb-24 rounded-b-[2rem] overflow-hidden"
          style={{ background: "var(--gradient-hero)" }}
        >
          <div className="absolute -top-20 -right-10 w-56 h-56 rounded-full bg-primary-glow/30 blur-3xl" />
          <div
            className="absolute inset-0 opacity-[0.07]"
            style={{
              backgroundImage:
                "linear-gradient(rgba(255,255,255,0.6) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.6) 1px, transparent 1px)",
              backgroundSize: "26px 26px",
              maskImage: "radial-gradient(ellipse at top right, black 30%, transparent 70%)",
            }}
          />
          <div className="relative flex items-center gap-3">
            <button
              onClick={() => navigate({ to: "/dashboard" })}
              className="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center text-white"
            >
              <ArrowLeft size={18} />
            </button>
            <div>
              <p className="text-[11px] text-white/70 font-semibold uppercase tracking-wider">Tabungan</p>
              <p className="text-base font-bold text-white">Simpanan Santri</p>
            </div>
          </div>

          <div className="relative mt-6 text-white">
            <p className="text-xs text-white/70 uppercase tracking-widest font-semibold">Nama Santri</p>
            <p className="text-xl font-bold mt-1 tracking-tight">{student?.name ?? "Memuat..."}</p>
          </div>
        </div>

        {/* Balance Card */}
        <section className="px-6 -mt-10 relative z-10">
          <div 
            className="rounded-3xl p-6 text-white shadow-[var(--shadow-glow)] relative overflow-hidden"
            style={{ background: "var(--gradient-card)" }}
          >
            <div className="absolute -right-8 -bottom-8 text-white/10 pointer-events-none">
              <PiggyBank size={140} strokeWidth={1} />
            </div>
            
            <div className="flex items-center gap-2 mb-2 opacity-80">
              <PiggyBank size={16} />
              <p className="text-[10px] uppercase font-bold tracking-widest">Total Saldo Tabungan</p>
            </div>
            
            {isLoadingStudent ? (
              <Loader2 className="w-8 h-8 animate-spin text-white mb-2" />
            ) : (
              <h2 className="text-3xl font-extrabold tracking-tight">
                {fmt(savingAmount)}
              </h2>
            )}
            
            <div className="mt-4 pt-4 border-t border-white/10 flex justify-between items-center text-[11px] opacity-80">
              <span>Status Rekening</span>
              <span className="font-bold uppercase tracking-wider">Aktif · Terverifikasi</span>
            </div>
          </div>
        </section>

        {/* Filters */}
        <section className="px-6 mt-6">
          <div className="flex gap-2 overflow-x-auto pb-1 scrollbar-none">
            {(["all", "today", "week", "month"] as const).map((item) => (
              <button
                key={item}
                onClick={() => setFilter(item)}
                className={`px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap border transition ${
                  filter === item
                    ? "bg-primary text-primary-foreground border-transparent shadow-sm"
                    : "bg-card text-muted-foreground border-border hover:bg-secondary"
                }`}
              >
                {item === "all" ? "Semua" : item === "today" ? "Hari Ini" : item === "week" ? "7 Hari" : "30 Hari"}
              </button>
            ))}
          </div>
        </section>

        {/* History List */}
        <section className="px-6 mt-6">
          <div className="flex justify-between items-center mb-4">
            <h3 className="text-sm font-bold text-foreground">Riwayat Mutasi</h3>
            <span className="text-[10px] text-muted-foreground font-semibold flex items-center gap-1">
              <Calendar size={12} /> Real-time Sync
            </span>
          </div>

          {isLoadingHistory ? (
            <div className="bg-card rounded-3xl border border-border p-8 flex flex-col items-center justify-center shadow-[var(--shadow-soft)]">
              <Loader2 className="animate-spin text-primary mb-2" size={28} />
              <p className="text-xs font-semibold text-muted-foreground">Memuat mutasi...</p>
            </div>
          ) : histories.length === 0 ? (
            <div className="bg-card rounded-3xl border border-border p-8 text-center shadow-[var(--shadow-soft)]">
              <PiggyBank className="w-12 h-12 text-muted-foreground mx-auto mb-3" strokeWidth={1.5} />
              <p className="text-sm font-bold text-foreground">Belum Ada Mutasi</p>
              <p className="text-xs text-muted-foreground mt-1">
                Riwayat tabungan santri akan otomatis tercatat setelah setoran atau penarikan dilakukan.
              </p>
            </div>
          ) : (
            <div className="bg-card rounded-3xl border border-border shadow-[var(--shadow-soft)] overflow-hidden divide-y divide-border">
              {histories.map((log: any) => {
                const isIncome = log.type === "IN";
                return (
                  <div 
                    key={log.id} 
                    className="flex items-center justify-between p-4 active:bg-secondary transition"
                  >
                    <div className="flex items-center gap-3 min-w-0">
                      <div className={`w-10 h-10 rounded-xl flex items-center justify-center shrink-0 ${
                        isIncome ? "bg-emerald-500/10 text-emerald-600" : "bg-destructive/10 text-destructive"
                      }`}>
                        {isIncome ? <ArrowDownLeft size={18} /> : <ArrowUpRight size={18} />}
                      </div>
                      <div className="min-w-0">
                        <p className="text-sm font-bold text-foreground truncate">
                          {log.description || (isIncome ? "Penyetoran Mandiri" : "Penarikan Jajan")}
                        </p>
                        <p className="text-[10px] text-muted-foreground mt-0.5">
                          {new Date(log.created_at).toLocaleDateString("id-ID", {
                            day: "numeric",
                            month: "short",
                            year: "numeric",
                            hour: "2-digit",
                            minute: "2-digit"
                          })}
                        </p>
                      </div>
                    </div>
                    <div className="text-right shrink-0 ml-2">
                      <p className={`text-sm font-bold ${isIncome ? "text-success" : "text-foreground"}`}>
                        {isIncome ? "+" : "-"} {fmt(log.amount)}
                      </p>
                      <span className={`inline-block px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider ${
                        log.status === "SUCCESS" || log.status === "approved"
                          ? "bg-emerald-500/15 text-emerald-700"
                          : log.status === "PENDING"
                          ? "bg-amber-500/15 text-amber-700 animate-pulse"
                          : "bg-destructive/10 text-destructive"
                      }`}>
                        {log.status === "SUCCESS" ? "Sukses" : log.status}
                      </span>
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </section>
      </div>
    </div>
  );
}
