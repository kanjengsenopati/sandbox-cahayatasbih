import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { PiggyBank, ArrowLeft, Loader2, ArrowUpRight, ArrowDownLeft, Calendar } from "lucide-react";
import { MobileShell } from "@/components/MobileShell";
import { Text } from "@/components/Text";
import { useQuery } from "@tanstack/react-query";
import { fetchActiveStudent, fetchSavingHistories } from "@/lib/api";
import { useState } from "react";

export const Route = createFileRoute("/tabungan")({
  component: Tabungan,
  head: () => ({ meta: [{ title: "Tabungan Santri — CAHAYA TASBIH" }] }),
});

type FilterType = "all" | "today" | "week" | "month";

function Tabungan() {
  const navigate = useNavigate();
  const [filter, setFilter] = useState<FilterType>("all");

  const { data: studentRes, isLoading: isLoadingStudent } = useQuery({
    queryKey: ["active-student"],
    queryFn: async () => {
      const res = await fetchActiveStudent();
      return res.data;
    },
  });

  const { data: historyRes, isLoading: isLoadingHistory } = useQuery({
    queryKey: ["saving-histories", filter],
    queryFn: async () => {
      const params = filter !== "all" ? { filter } : {};
      const res = await fetchSavingHistories(params);
      return res.data;
    },
  });

  const student = studentRes?.data;
  const savingAmount = student?.saving ?? 0;
  const histories = historyRes?.data ?? [];

  return (
    <MobileShell>
      <header className="px-5 pt-10 pb-4 flex items-center gap-3 sticky top-0 bg-white/80 backdrop-blur-md z-10">
        <button 
          onClick={() => navigate({ to: "/dashboard" })}
          className="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-50 active:scale-95 transition"
        >
          <ArrowLeft size={20} className="text-slate-600" />
        </button>
        <Text.H1>Tabungan Santri</Text.H1>
      </header>

      <section className="px-5 mt-4">
        {/* Balance Card */}
        <div className="bg-gradient-to-tr from-emerald-600 to-emerald-500 rounded-[24px] p-6 text-white shadow-[0_12px_40px_rgba(16,185,129,0.12)] relative overflow-hidden">
          <div className="absolute -right-8 -bottom-8 text-emerald-400/20 pointer-events-none">
            <PiggyBank size={140} strokeWidth={1} />
          </div>
          
          <div className="flex items-center gap-2 mb-2 opacity-90">
            <PiggyBank size={18} />
            <Text.Label className="text-white">Total Saldo Tabungan</Text.Label>
          </div>
          
          {isLoadingStudent ? (
            <Loader2 className="w-6 h-6 animate-spin text-white mb-2" />
          ) : (
            <Text.Amount className="text-white text-3xl font-bold">
              Rp {savingAmount.toLocaleString("id-ID")}
            </Text.Amount>
          )}
          
          <div className="mt-4 pt-4 border-t border-emerald-400/30 flex justify-between items-center text-xs opacity-90">
            <span>Nama Santri</span>
            <span className="font-semibold">{student?.name ?? "Memuat..."}</span>
          </div>
        </div>

        {/* Quick Filter Buttons */}
        <div className="mt-6 flex gap-2 overflow-x-auto pb-1 scrollbar-none">
          {(["all", "today", "week", "month"] as const).map((item) => (
            <button
              key={item}
              onClick={() => setFilter(item)}
              className={`px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all duration-300 ${
                filter === item
                  ? "bg-slate-900 text-white shadow-[0_4px_12px_rgba(0,0,0,0.1)]"
                  : "bg-slate-50 text-slate-500 hover:bg-slate-100"
              }`}
            >
              {item === "all" ? "Semua" : item === "today" ? "Hari Ini" : item === "week" ? "Minggu Ini" : "Bulan Ini"}
            </button>
          ))}
        </div>

        {/* Transaction History Section */}
        <div className="mt-6">
          <div className="flex justify-between items-center mb-4">
            <Text.H2>Riwayat Transaksi</Text.H2>
            <span className="text-xs text-slate-400 font-semibold flex items-center gap-1">
              <Calendar size={12} />
              Database Driven
            </span>
          </div>

          {isLoadingHistory ? (
            <div className="flex flex-col items-center justify-center py-12">
              <Loader2 className="w-8 h-8 animate-spin text-emerald-600 mb-2" />
              <Text.Caption>Memuat riwayat...</Text.Caption>
            </div>
          ) : histories.length === 0 ? (
            <div className="bg-white rounded-[24px] p-8 text-center border border-slate-50 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
              <PiggyBank className="w-12 h-12 text-slate-300 mx-auto mb-3" strokeWidth={1.5} />
              <Text.Body className="text-slate-500 font-medium mb-1">Belum Ada Transaksi</Text.Body>
              <Text.Caption>Riwayat tabungan santri akan tampil di sini setelah dilakukan penyetoran atau penarikan.</Text.Caption>
            </div>
          ) : (
            <div className="flex flex-col gap-3 pb-8">
              {histories.map((log: any) => {
                const isIncome = log.type === "IN";
                return (
                  <div 
                    key={log.id} 
                    className="bg-white rounded-[24px] p-4 flex items-center justify-between border border-slate-50 shadow-[0_8px_30px_rgb(0,0,0,0.02)] active:scale-99 transition"
                  >
                    <div className="flex items-center gap-3">
                      <div className={`w-10 h-10 rounded-full flex items-center justify-center ${
                        isIncome ? "bg-emerald-50 text-emerald-600" : "bg-red-50 text-red-600"
                      }`}>
                        {isIncome ? <ArrowDownLeft size={18} /> : <ArrowUpRight size={18} />}
                      </div>
                      <div>
                        <Text.Body className="font-semibold text-slate-800">
                          {log.description || (isIncome ? "Tabungan Masuk" : "Penarikan Tabungan")}
                        </Text.Body>
                        <Text.Caption className="text-slate-400">
                          {new Date(log.created_at).toLocaleDateString("id-ID", {
                            day: "numeric",
                            month: "long",
                            year: "numeric",
                            hour: "2-digit",
                            minute: "2-digit"
                          })}
                        </Text.Caption>
                      </div>
                    </div>
                    <div className="text-right">
                      <Text.Amount className={`font-bold ${isIncome ? "text-emerald-600" : "text-red-600"}`}>
                        {isIncome ? "+" : "-"} Rp {log.amount.toLocaleString("id-ID")}
                      </Text.Amount>
                      <span className={`inline-block px-2 py-0.5 rounded-full text-[9px] font-bold ${
                        log.status === "SUCCESS" 
                          ? "bg-emerald-50 text-emerald-600" 
                          : log.status === "PENDING"
                          ? "bg-amber-50 text-amber-600 animate-pulse"
                          : "bg-red-50 text-red-600"
                      }`}>
                        {log.status}
                      </span>
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </div>
      </section>
    </MobileShell>
  );
}
