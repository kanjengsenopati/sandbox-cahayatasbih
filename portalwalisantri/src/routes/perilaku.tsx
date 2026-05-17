import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { Heart, ArrowLeft, Loader2, Sparkles, AlertTriangle, ShieldCheck, HelpCircle } from "lucide-react";
import { MobileShell } from "@/components/MobileShell";
import { Text } from "@/components/Text";
import { useQuery } from "@tanstack/react-query";
import { fetchCounseling } from "@/lib/api";

export const Route = createFileRoute("/perilaku")({
  component: Perilaku,
  head: () => ({ meta: [{ title: "Catatan Perilaku — CAHAYA TASBIH" }] }),
});

function Perilaku() {
  const navigate = useNavigate();

  const { data: counselingRes, isLoading } = useQuery({
    queryKey: ["counseling"],
    queryFn: async () => {
      const res = await fetchCounseling();
      return res.data;
    },
  });

  const studentName = counselingRes?.student_name ?? "Santri";
  const averageScore = counselingRes?.average_score ?? 100;
  const counselingLogs = counselingRes?.data ?? [];

  // Determine status color and text based on counseling score
  let statusText = "Sangat Baik";
  let statusColor = "from-emerald-600 to-emerald-500 shadow-emerald-200/50";
  let statusIcon = <ShieldCheck size={18} />;

  if (averageScore < 75) {
    statusText = "Butuh Perhatian";
    statusColor = "from-red-600 to-red-500 shadow-red-200/50";
    statusIcon = <AlertTriangle size={18} />;
  } else if (averageScore < 90) {
    statusText = "Baik";
    statusColor = "from-blue-600 to-blue-500 shadow-blue-200/50";
    statusIcon = <Sparkles size={18} />;
  }

  return (
    <MobileShell>
      <header className="px-5 pt-10 pb-4 flex items-center gap-3 sticky top-0 bg-white/80 backdrop-blur-md z-10">
        <button 
          onClick={() => navigate({ to: "/dashboard" })}
          className="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-50 active:scale-95 transition"
        >
          <ArrowLeft size={20} className="text-slate-600" />
        </button>
        <Text.H1>Catatan Perilaku</Text.H1>
      </header>

      <section className="px-5 mt-4">
        {/* Status Perilaku Card */}
        <div className={`bg-gradient-to-tr ${statusColor} rounded-[24px] p-6 text-white shadow-[0_12px_40px_rgba(0,0,0,0.1)] relative overflow-hidden`}>
          <div className="absolute -right-8 -bottom-8 text-white/10 pointer-events-none">
            <Heart size={140} strokeWidth={1} />
          </div>

          <div className="flex items-center gap-2 mb-2 opacity-95">
            {statusIcon}
            <Text.Label className="text-white">Status Karakter Santri</Text.Label>
          </div>

          {isLoading ? (
            <Loader2 className="w-6 h-6 animate-spin text-white mb-2" />
          ) : (
            <div className="flex items-baseline justify-between">
              <div>
                <Text.Amount className="text-white text-3xl font-bold">
                  {statusText}
                </Text.Amount>
                <Text.Caption className="text-white/85 mt-1 block">
                  Berdasarkan penilaian pengasuhan pondok
                </Text.Caption>
              </div>
              <div className="text-right">
                <span className="text-[10px] uppercase font-bold tracking-widest text-white/80 block">Skor</span>
                <span className="text-2xl font-black">{averageScore}</span>
              </div>
            </div>
          )}

          <div className="mt-4 pt-4 border-t border-white/20 flex justify-between items-center text-xs opacity-90">
            <span>Nama Santri</span>
            <span className="font-semibold">{studentName}</span>
          </div>
        </div>

        {/* Log List Section */}
        <div className="mt-8">
          <Text.H2 className="mb-4">Daftar Catatan Perilaku</Text.H2>

          {isLoading ? (
            <div className="flex flex-col items-center justify-center py-12">
              <Loader2 className="w-8 h-8 animate-spin text-rose-600 mb-2" />
              <Text.Caption>Memuat perilaku...</Text.Caption>
            </div>
          ) : counselingLogs.length === 0 ? (
            <div className="bg-white rounded-[24px] p-8 text-center border border-slate-50 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
              <Heart className="w-12 h-12 text-slate-300 mx-auto mb-3" strokeWidth={1.5} />
              <Text.Body className="text-slate-500 font-medium mb-1">Catatan Bersih</Text.Body>
              <Text.Caption>Santri memiliki track record perilaku yang baik. Belum ada catatan khusus dari pengasuhan.</Text.Caption>
            </div>
          ) : (
            <div className="flex flex-col gap-3 pb-8">
              {counselingLogs.map((log: any) => (
                <div 
                  key={log.id} 
                  className="bg-white rounded-[24px] p-5 border border-slate-50 shadow-[0_8px_30px_rgb(0,0,0,0.02)] active:scale-99 transition"
                >
                  <div className="flex justify-between items-start mb-2">
                    <span className="text-[10px] uppercase font-bold text-slate-400">
                      {new Date(log.created_at).toLocaleDateString("id-ID", {
                        day: "numeric",
                        month: "long",
                        year: "numeric"
                      })}
                    </span>
                    <span className={`px-2 py-0.5 rounded-full text-[10px] font-bold ${
                      log.score < 0 ? "bg-red-50 text-red-600" : "bg-emerald-50 text-emerald-600"
                    }`}>
                      {log.score < 0 ? "" : "+"}{log.score} Poin
                    </span>
                  </div>

                  <Text.H2 className="text-slate-800 font-bold mb-2">
                    {log.violation || "Catatan Akhlak / Kegiatan"}
                  </Text.H2>

                  {log.action && (
                    <div className="bg-rose-50/20 rounded-xl p-3 border border-rose-50/50 mt-3 flex items-start gap-2">
                      <AlertTriangle size={14} className="text-rose-500 shrink-0 mt-0.5" />
                      <div>
                        <span className="text-[9px] uppercase font-bold text-rose-500 block mb-0.5">Tindakan Pembinaan:</span>
                        <Text.Body className="text-slate-600 text-xs">
                          {log.action}
                        </Text.Body>
                      </div>
                    </div>
                  )}

                  {log.note && (
                    <p className="text-xs text-slate-400 mt-2 italic">
                      Catatan: {log.note}
                    </p>
                  )}
                </div>
              ))}
            </div>
          )}
        </div>
      </section>
    </MobileShell>
  );
}
