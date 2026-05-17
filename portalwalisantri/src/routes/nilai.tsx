import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { GraduationCap, ArrowLeft, Loader2, Sparkles, AlertCircle, CheckCircle, Calendar } from "lucide-react";
import { useQuery } from "@tanstack/react-query";
import { fetchStudyGrades, fetchSemesters } from "@/lib/api";
import { useState, useEffect } from "react";

export const Route = createFileRoute("/nilai")({
  component: Nilai,
  head: () => ({ meta: [{ title: "Nilai Akademik — SantriPay" }] }),
});

function Nilai() {
  const navigate = useNavigate();
  const [selectedSemesterId, setSelectedSemesterId] = useState<string | number>("");

  const { data: semestersRes, isLoading: isLoadingSemesters } = useQuery({
    queryKey: ["semesters"],
    queryFn: async () => {
      const res = await fetchSemesters();
      return res.data;
    },
  });

  const semesters = semestersRes?.data ?? [];

  // Set default semester once loaded
  useEffect(() => {
    if (semesters.length > 0 && !selectedSemesterId) {
      setSelectedSemesterId(semesters[0].id);
    }
  }, [semesters, selectedSemesterId]);

  const { data: gradesRes, isLoading: isLoadingGrades } = useQuery({
    queryKey: ["study-grades", selectedSemesterId],
    queryFn: async () => {
      const res = await fetchStudyGrades({ semester_id: selectedSemesterId });
      return res.data;
    },
    enabled: !!selectedSemesterId,
  });

  const studentName = gradesRes?.student_name ?? "Santri";
  const grades = gradesRes?.data ?? [];

  // Calculate average score
  const averageGrade = grades.length > 0 
    ? (grades.reduce((sum: number, g: any) => sum + Number(g.grade || 0), 0) / grades.length).toFixed(1)
    : "0.0";

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
              <p className="text-[11px] text-white/70 font-semibold uppercase tracking-wider">Hasil Belajar</p>
              <p className="text-base font-bold text-white">Rapor Nilai Santri</p>
            </div>
          </div>

          <div className="relative mt-6 text-white">
            <p className="text-xs text-white/70 uppercase tracking-widest font-semibold">Nama Santri</p>
            <p className="text-xl font-bold mt-1 tracking-tight">{studentName}</p>
          </div>
        </div>

        {/* GPA / Average Card */}
        <section className="px-6 -mt-10 relative z-10">
          <div 
            className="rounded-3xl p-6 text-white shadow-[var(--shadow-glow)] relative overflow-hidden"
            style={{ background: "var(--gradient-card)" }}
          >
            <div className="absolute -right-8 -bottom-8 text-white/10 pointer-events-none">
              <GraduationCap size={140} strokeWidth={1} />
            </div>

            <div className="flex items-center gap-2 mb-2 opacity-85">
              <Sparkles size={16} />
              <p className="text-[10px] uppercase font-bold tracking-widest">Rata-Rata Nilai Rapor</p>
            </div>

            {isLoadingGrades ? (
              <Loader2 className="w-8 h-8 animate-spin text-white mb-2" />
            ) : (
              <div className="flex items-baseline justify-between gap-2">
                <div>
                  <h2 className="text-3xl font-extrabold tracking-tight">{averageGrade}</h2>
                  <p className="text-[10px] text-white/80 mt-1">
                    Berdasarkan {grades.length} mata pelajaran terisi
                  </p>
                </div>
              </div>
            )}

            <div className="mt-4 pt-4 border-t border-white/10 flex justify-between items-center text-[11px] opacity-80">
              <span>Sistem Penilaian</span>
              <span className="font-bold uppercase tracking-wider">KTSP & Kurikulum Merdeka</span>
            </div>
          </div>
        </section>

        {/* Semesters Selector */}
        <section className="px-6 mt-6">
          {isLoadingSemesters ? (
            <div className="flex justify-center">
              <Loader2 className="w-6 h-6 animate-spin text-primary" />
            </div>
          ) : (
            <div className="flex gap-2 overflow-x-auto pb-1 scrollbar-none">
              {semesters.map((sem: any) => (
                <button
                  key={sem.id}
                  onClick={() => setSelectedSemesterId(sem.id)}
                  className={`px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap border transition ${
                    selectedSemesterId === sem.id
                      ? "bg-primary text-primary-foreground border-transparent shadow-sm"
                      : "bg-card text-muted-foreground border-border hover:bg-secondary"
                  }`}
                >
                  {sem.name}
                </button>
              ))}
            </div>
          )}
        </section>

        {/* Grades Table */}
        <section className="px-6 mt-6">
          <div className="flex justify-between items-center mb-4">
            <h3 className="text-sm font-bold text-foreground">Daftar Nilai Pelajaran</h3>
            <span className="text-[10px] text-muted-foreground font-semibold flex items-center gap-1">
              <Calendar size={12} /> Rapor Digital
            </span>
          </div>

          {isLoadingGrades ? (
            <div className="bg-card rounded-3xl border border-border p-8 flex flex-col items-center justify-center shadow-[var(--shadow-soft)]">
              <Loader2 className="animate-spin text-primary mb-2" size={28} />
              <p className="text-xs font-semibold text-muted-foreground">Memuat nilai...</p>
            </div>
          ) : grades.length === 0 ? (
            <div className="bg-card rounded-3xl border border-border p-8 text-center shadow-[var(--shadow-soft)]">
              <GraduationCap className="w-12 h-12 text-muted-foreground mx-auto mb-3" strokeWidth={1.5} />
              <p className="text-sm font-bold text-foreground">Nilai Belum Terbit</p>
              <p className="text-xs text-muted-foreground mt-1">
                Data nilai hasil belajar belum dirilis oleh bagian kurikulum pesantren.
              </p>
            </div>
          ) : (
            <div className="bg-card rounded-3xl border border-border shadow-[var(--shadow-soft)] overflow-hidden divide-y divide-border pb-2">
              {grades.map((item: any) => {
                const isPassed = Number(item.grade || 0) >= Number(item.kkm || 75);
                return (
                  <div 
                    key={item.id} 
                    className="flex items-center justify-between p-4 active:bg-secondary transition"
                  >
                    <div className="flex items-center gap-3 min-w-0">
                      <div className={`w-10 h-10 rounded-xl flex items-center justify-center shrink-0 ${
                        isPassed ? "bg-emerald-500/10 text-emerald-600" : "bg-destructive/10 text-destructive"
                      }`}>
                        {isPassed ? <CheckCircle size={18} /> : <AlertCircle size={18} />}
                      </div>
                      <div className="min-w-0">
                        <p className="text-sm font-bold text-foreground truncate">
                          {item.study?.name || "Mata Pelajaran"}
                        </p>
                        <p className="text-[10px] text-muted-foreground mt-0.5">
                          KKM Kriteria Kelulusan: <span className="font-semibold text-foreground/80">{item.kkm || 75}</span>
                        </p>
                      </div>
                    </div>

                    <div className="flex items-center gap-3 shrink-0 ml-2">
                      <div className="text-right">
                        <span className="text-base font-extrabold text-foreground block leading-none">
                          {item.grade}
                        </span>
                        <span className="text-[9px] font-bold text-muted-foreground">
                          Predikat: <span className="text-primary font-black uppercase">{item.letter_grade || "-"}</span>
                        </span>
                      </div>
                      
                      <span className={`px-2 py-0.5 text-[9px] font-bold rounded uppercase tracking-wider ${
                        isPassed ? "bg-emerald-500/15 text-emerald-700" : "bg-destructive/10 text-destructive animate-pulse"
                      }`}>
                        {isPassed ? "LULUS" : "REMIDI"}
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
