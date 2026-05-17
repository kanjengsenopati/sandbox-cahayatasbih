import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { Trophy, ArrowLeft, Loader2, Award, Calendar, Gift } from "lucide-react";
import { useQuery } from "@tanstack/react-query";
import { fetchAchievements } from "@/lib/api";

export const Route = createFileRoute("/prestasi")({
  component: Prestasi,
  head: () => ({ meta: [{ title: "Prestasi Santri — SantriPay" }] }),
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
              <p className="text-[11px] text-white/70 font-semibold uppercase tracking-wider">Apresiasi</p>
              <p className="text-base font-bold text-white">Prestasi & Penghargaan</p>
            </div>
          </div>

          <div className="relative mt-6 text-white">
            <p className="text-xs text-white/70 uppercase tracking-widest font-semibold">Nama Santri</p>
            <p className="text-xl font-bold mt-1 tracking-tight">{studentName}</p>
          </div>
        </div>

        {/* Total Trophy Card */}
        <section className="px-6 -mt-10 relative z-10">
          <div 
            className="rounded-3xl p-6 text-white shadow-[var(--shadow-glow)] relative overflow-hidden"
            style={{ background: "var(--gradient-card)" }}
          >
            <div className="absolute -right-8 -bottom-8 text-white/10 pointer-events-none">
              <Trophy size={140} strokeWidth={1} />
            </div>

            <div className="flex items-center gap-2 mb-2 opacity-85">
              <Award size={16} />
              <p className="text-[10px] uppercase font-bold tracking-widest">Tropi & Piagam Diraih</p>
            </div>

            {isLoading ? (
              <Loader2 className="w-8 h-8 animate-spin text-white mb-2" />
            ) : (
              <div className="flex items-baseline gap-1.5">
                <h2 className="text-3xl font-extrabold tracking-tight">{totalAchievements}</h2>
                <span className="text-sm font-semibold opacity-80">Prestasi Resmi</span>
              </div>
            )}

            <div className="mt-4 pt-4 border-t border-white/10 flex justify-between items-center text-[11px] opacity-80">
              <span>Portofolio Santri</span>
              <span className="font-bold uppercase tracking-wider">Tingkat Sekolah s/d Nasional</span>
            </div>
          </div>
        </section>

        {/* Achievement List */}
        <section className="px-6 mt-6">
          <div className="flex justify-between items-center mb-4">
            <h3 className="text-sm font-bold text-foreground">Daftar Penghargaan</h3>
            <span className="text-[10px] text-muted-foreground font-semibold flex items-center gap-1">
              <Calendar size={12} /> Database Terverifikasi
            </span>
          </div>

          {isLoading ? (
            <div className="bg-card rounded-3xl border border-border p-8 flex flex-col items-center justify-center shadow-[var(--shadow-soft)]">
              <Loader2 className="animate-spin text-primary mb-2" size={28} />
              <p className="text-xs font-semibold text-muted-foreground">Memuat prestasi...</p>
            </div>
          ) : achievements.length === 0 ? (
            <div className="bg-card rounded-3xl border border-border p-8 text-center shadow-[var(--shadow-soft)]">
              <Trophy className="w-12 h-12 text-muted-foreground mx-auto mb-3" strokeWidth={1.5} />
              <p className="text-sm font-bold text-foreground">Belum Ada Prestasi</p>
              <p className="text-xs text-muted-foreground mt-1">
                Catatan prestasi membanggakan dari akademik atau lomba pondok pesantren belum tercatat.
              </p>
            </div>
          ) : (
            <div className="flex flex-col gap-4 pb-8">
              {achievements.map((item: any) => (
                <div 
                  key={item.id} 
                  className="bg-card rounded-3xl border border-border shadow-[var(--shadow-soft)] p-5 hover:shadow-[var(--shadow-card)] transition"
                >
                  <div className="flex justify-between items-start gap-2 mb-3">
                    <span className="px-2.5 py-0.5 bg-primary/10 text-primary text-[9px] font-bold rounded-full uppercase tracking-wider">
                      Tingkat {item.level || "Pesantren"}
                    </span>
                    <span className="text-[10px] text-muted-foreground font-semibold flex items-center gap-1">
                      <Calendar size={12} />
                      {item.academic_year?.name || "Tahun Ajaran"}
                    </span>
                  </div>

                  <h4 className="font-bold text-sm text-foreground leading-snug">
                    {item.title}
                  </h4>
                  
                  {item.champion && (
                    <span className="inline-block text-amber-500 font-extrabold text-xs mt-2">
                      🏆 Juara {item.champion}
                    </span>
                  )}

                  {item.reward && (
                    <div className="bg-secondary/50 rounded-2xl p-3 border border-border mt-3 flex items-center gap-2">
                      <Gift size={14} className="text-amber-500 shrink-0" />
                      <p className="text-[11px] text-foreground font-semibold">
                        Apresiasi: <span className="text-primary font-bold">{item.reward}</span>
                      </p>
                    </div>
                  )}
                </div>
              ))}
            </div>
          )}
        </section>
      </div>
    </div>
  );
}
