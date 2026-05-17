import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { BookOpen, ArrowLeft, Loader2, BookMarked, Calendar, MessageSquareQuote } from "lucide-react";
import { MobileShell } from "@/components/MobileShell";
import { Text } from "@/components/Text";
import { useQuery } from "@tanstack/react-query";
import { fetchTahfidz } from "@/lib/api";

export const Route = createFileRoute("/tahfidz")({
  component: Tahfidz,
  head: () => ({ meta: [{ title: "Progress Tahfidz — CAHAYA TASBIH" }] }),
});

function Tahfidz() {
  const navigate = useNavigate();

  const { data: tahfidzRes, isLoading } = useQuery({
    queryKey: ["tahfidz"],
    queryFn: async () => {
      const res = await fetchTahfidz();
      return res.data;
    },
  });

  const studentName = tahfidzRes?.student_name ?? "Santri";
  const totalPages = tahfidzRes?.total_pages ?? 0;
  const depositLogs = tahfidzRes?.data ?? [];

  return (
    <MobileShell>
      <header className="px-5 pt-10 pb-4 flex items-center gap-3 sticky top-0 bg-white/80 backdrop-blur-md z-10">
        <button 
          onClick={() => navigate({ to: "/dashboard" })}
          className="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-50 active:scale-95 transition"
        >
          <ArrowLeft size={20} className="text-slate-600" />
        </button>
        <Text.H1>Tahfidz Al-Quran</Text.H1>
      </header>

      <section className="px-5 mt-4">
        {/* Tahfidz Summary Card */}
        <div className="bg-gradient-to-tr from-blue-600 to-blue-500 rounded-[24px] p-6 text-white shadow-[0_12px_40px_rgba(37,99,235,0.12)] relative overflow-hidden">
          <div className="absolute -right-8 -bottom-8 text-blue-400/20 pointer-events-none">
            <BookOpen size={140} strokeWidth={1} />
          </div>
          
          <div className="flex items-center gap-2 mb-2 opacity-90">
            <BookMarked size={18} />
            <Text.Label className="text-white">Total Setoran Hafalan</Text.Label>
          </div>
          
          {isLoading ? (
            <Loader2 className="w-6 h-6 animate-spin text-white mb-2" />
          ) : (
            <div className="flex items-baseline gap-1">
              <Text.Amount className="text-white text-3.5xl font-bold">
                {totalPages}
              </Text.Amount>
              <span className="text-sm opacity-90">Halaman</span>
            </div>
          )}
          
          <div className="mt-4 pt-4 border-t border-blue-400/30 flex justify-between items-center text-xs opacity-90">
            <span>Nama Santri</span>
            <span className="font-semibold">{studentName}</span>
          </div>
        </div>

        {/* Timeline Section */}
        <div className="mt-8">
          <div className="flex justify-between items-center mb-4">
            <Text.H2>Log Setoran Hafalan</Text.H2>
            <span className="text-xs text-slate-400 font-semibold flex items-center gap-1">
              <Calendar size={12} />
              Setoran Aktif
            </span>
          </div>

          {isLoading ? (
            <div className="flex flex-col items-center justify-center py-12">
              <Loader2 className="w-8 h-8 animate-spin text-blue-600 mb-2" />
              <Text.Caption>Memuat hafalan...</Text.Caption>
            </div>
          ) : depositLogs.length === 0 ? (
            <div className="bg-white rounded-[24px] p-8 text-center border border-slate-50 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
              <BookOpen className="w-12 h-12 text-slate-300 mx-auto mb-3" strokeWidth={1.5} />
              <Text.Body className="text-slate-500 font-medium mb-1">Belum Ada Setoran</Text.Body>
              <Text.Caption>Detail setoran hafalan Al-Quran dari ustadz pembimbing akan tampil di sini.</Text.Caption>
            </div>
          ) : (
            <div className="relative border-l-2 border-blue-100 ml-4 pl-6 flex flex-col gap-6 pb-12">
              {depositLogs.map((log: any) => (
                <div key={log.id} className="relative">
                  {/* Circle Dot indicator */}
                  <span className="absolute -left-[31px] top-1.5 w-4 h-4 rounded-full bg-blue-600 border-4 border-white shadow-sm"></span>
                  
                  <div className="bg-white rounded-[24px] p-5 border border-slate-50 shadow-[0_8px_30px_rgb(0,0,0,0.02)] transition hover:shadow-[0_12px_35px_rgba(0,0,0,0.04)]">
                    <div className="flex justify-between items-start mb-2">
                      <span className="text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                        {new Date(log.deposit_date).toLocaleDateString("id-ID", {
                          day: "numeric",
                          month: "long",
                          year: "numeric"
                        })}
                      </span>
                      <span className="px-2.5 py-0.5 bg-blue-50 text-blue-600 text-[10px] font-bold rounded-full">
                        {log.number_of_pages} Halaman
                      </span>
                    </div>

                    <Text.H2 className="text-slate-800 font-bold mb-2">
                      {log.note || "Setoran Hafalan"}
                    </Text.H2>

                    {log.feedback && (
                      <div className="mt-3 pt-3 border-t border-slate-50 flex gap-2">
                        <MessageSquareQuote size={16} className="text-slate-400 shrink-0 mt-0.5" />
                        <div className="bg-slate-50/50 rounded-xl p-3 flex-1">
                          <Text.Caption className="text-slate-400 font-bold block mb-1">Saran Ustadz:</Text.Caption>
                          <Text.Body className="text-slate-600 text-xs italic">
                            "{log.feedback}"
                          </Text.Body>
                        </div>
                      </div>
                    )}
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </section>
    </MobileShell>
  );
}
