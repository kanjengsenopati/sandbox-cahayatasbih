import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { Trophy, ArrowLeft, Loader2, Award, Calendar, Gift } from "lucide-react";
import { MobileShell } from "@/components/MobileShell";
import { Text } from "@/components/Text";
import { useQuery } from "@tanstack/react-query";
import { fetchAchievements } from "@/lib/api";

export const Route = createFileRoute("/prestasi")({
  component: Prestasi,
  head: () => ({ meta: [{ title: "Prestasi Santri — CAHAYA TASBIH" }] }),
});

function Prestasi() {
  const navigate = useNavigate();

  const { data: achievementsRes, isLoading } = useQuery({
    queryKey: ["achievements"],
    queryFn: async () => {
      const res = await fetchAchievements();
      return res.data;
    },
  });

  const studentName = achievementsRes?.student_name ?? "Santri";
  const totalAchievements = achievementsRes?.total_achievements ?? 0;
  const achievements = achievementsRes?.data ?? [];

  return (
    <MobileShell>
      <header className="px-5 pt-10 pb-4 flex items-center gap-3 sticky top-0 bg-white/80 backdrop-blur-md z-10">
        <button 
          onClick={() => navigate({ to: "/dashboard" })}
          className="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-50 active:scale-95 transition"
        >
          <ArrowLeft size={20} className="text-slate-600" />
        </button>
        <Text.H1>Prestasi & Bakat</Text.H1>
      </header>

      <section className="px-5 mt-4">
        {/* Achievements Card */}
        <div className="bg-gradient-to-tr from-amber-500 to-amber-400 rounded-[24px] p-6 text-white shadow-[0_12px_40px_rgba(245,158,11,0.15)] relative overflow-hidden">
          <div className="absolute -right-8 -bottom-8 text-amber-300/20 pointer-events-none">
            <Trophy size={140} strokeWidth={1} />
          </div>

          <div className="flex items-center gap-2 mb-2 opacity-95">
            <Award size={18} />
            <Text.Label className="text-white">Piala & Penghargaan</Text.Label>
          </div>

          {isLoading ? (
            <Loader2 className="w-6 h-6 animate-spin text-white mb-2" />
          ) : (
            <div className="flex items-baseline gap-1">
              <Text.Amount className="text-white text-3.5xl font-bold">
                {totalAchievements}
              </Text.Amount>
              <span className="text-sm opacity-90">Prestasi</span>
            </div>
          )}

          <div className="mt-4 pt-4 border-t border-amber-300/30 flex justify-between items-center text-xs opacity-90">
            <span>Nama Santri</span>
            <span className="font-semibold">{studentName}</span>
          </div>
        </div>

        {/* Gallery / List Section */}
        <div className="mt-8">
          <Text.H2 className="mb-4">Daftar Penghargaan</Text.H2>

          {isLoading ? (
            <div className="flex flex-col items-center justify-center py-12">
              <Loader2 className="w-8 h-8 animate-spin text-amber-600 mb-2" />
              <Text.Caption>Memuat prestasi...</Text.Caption>
            </div>
          ) : achievements.length === 0 ? (
            <div className="bg-white rounded-[24px] p-8 text-center border border-slate-50 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
              <Trophy className="w-12 h-12 text-slate-300 mx-auto mb-3" strokeWidth={1.5} />
              <Text.Body className="text-slate-500 font-medium mb-1">Belum Ada Penghargaan</Text.Body>
              <Text.Caption>Piagam, tropi, dan prestasi membanggakan yang diraih santri akan terbit di sini.</Text.Caption>
            </div>
          ) : (
            <div className="flex flex-col gap-4 pb-12">
              {achievements.map((item: any) => (
                <div 
                  key={item.id} 
                  className="bg-white rounded-[24px] p-5 border border-slate-50 shadow-[0_8px_30px_rgb(0,0,0,0.02)] relative overflow-hidden transition hover:shadow-[0_12px_35px_rgba(0,0,0,0.04)]"
                >
                  <div className="flex justify-between items-start mb-2">
                    <span className="px-2 py-0.5 bg-amber-50 text-amber-600 text-[10px] font-bold rounded-full uppercase tracking-wider">
                      Tingkat {item.level || "Sekolah"}
                    </span>
                    <span className="text-[10px] text-slate-400 font-semibold flex items-center gap-1">
                      <Calendar size={12} />
                      {item.academic_year?.name || "Tahun Ajaran"}
                    </span>
                  </div>

                  <Text.H2 className="text-slate-800 font-bold mb-2 pr-12">
                    {item.title}
                  </Text.H2>
                  
                  {item.champion && (
                    <Text.Body className="text-amber-600 font-bold text-sm mb-3 block">
                      🏅 Juara {item.champion}
                    </Text.Body>
                  )}

                  {item.reward && (
                    <div className="bg-slate-50/50 rounded-xl p-3 border border-slate-50 flex items-center gap-2">
                      <Gift size={16} className="text-amber-500 shrink-0" />
                      <Text.Caption className="text-slate-600 font-medium">
                        Penghargaan: <span className="font-bold text-slate-800">{item.reward}</span>
                      </Text.Caption>
                    </div>
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
