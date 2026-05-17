import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { GraduationCap, ArrowLeft, Loader2, Sparkles, AlertCircle, CheckCircle } from "lucide-react";
import { MobileShell } from "@/components/MobileShell";
import { Text } from "@/components/Text";
import { useQuery } from "@tanstack/react-query";
import { fetchStudyGrades, fetchSemesters } from "@/lib/api";
import { useState, useEffect } from "react";

export const Route = createFileRoute("/nilai")({
  component: Nilai,
  head: () => ({ meta: [{ title: "Nilai Akademik — CAHAYA TASBIH" }] }),
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
    <MobileShell>
      <header className="px-5 pt-10 pb-4 flex items-center gap-3 sticky top-0 bg-white/80 backdrop-blur-md z-10">
        <button 
          onClick={() => navigate({ to: "/dashboard" })}
          className="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-50 active:scale-95 transition"
        >
          <ArrowLeft size={20} className="text-slate-600" />
        </button>
        <Text.H1>Hasil Belajar (Rapor)</Text.H1>
      </header>

      <section className="px-5 mt-4">
        {/* GPA Summary Card */}
        <div className="bg-gradient-to-tr from-indigo-600 to-indigo-500 rounded-[24px] p-6 text-white shadow-[0_12px_40px_rgba(79,70,229,0.15)] relative overflow-hidden">
          <div className="absolute -right-8 -bottom-8 text-indigo-400/20 pointer-events-none">
            <GraduationCap size={140} strokeWidth={1} />
          </div>

          <div className="flex items-center gap-2 mb-2 opacity-95">
            <Sparkles size={18} />
            <Text.Label className="text-white">Rata-Rata Nilai Rapor</Text.Label>
          </div>

          {isLoadingGrades ? (
            <Loader2 className="w-6 h-6 animate-spin text-white mb-2" />
          ) : (
            <div className="flex items-baseline justify-between">
              <div>
                <Text.Amount className="text-white text-4xl font-black">
                  {averageGrade}
                </Text.Amount>
                <Text.Caption className="text-white/80 mt-1 block">
                  Dari total {grades.length} mata pelajaran
                </Text.Caption>
              </div>
            </div>
          )}

          <div className="mt-4 pt-4 border-t border-indigo-400/30 flex justify-between items-center text-xs opacity-90">
            <span>Nama Santri</span>
            <span className="font-semibold">{studentName}</span>
          </div>
        </div>

        {/* Semester Tab Selector */}
        {isLoadingSemesters ? (
          <div className="mt-6 flex justify-center">
            <Loader2 className="w-6 h-6 animate-spin text-indigo-600" />
          </div>
        ) : (
          <div className="mt-6 flex gap-2 overflow-x-auto pb-1 scrollbar-none">
            {semesters.map((sem: any) => (
              <button
                key={sem.id}
                onClick={() => setSelectedSemesterId(sem.id)}
                className={`px-4 py-2 rounded-full text-xs font-bold whitespace-nowrap transition-all duration-300 ${
                  selectedSemesterId === sem.id
                    ? "bg-slate-900 text-white shadow-[0_4px_12px_rgba(0,0,0,0.1)]"
                    : "bg-slate-50 text-slate-500 hover:bg-slate-100"
                }`}
              >
                {sem.name}
              </button>
            ))}
          </div>
        )}

        {/* Grades List Section */}
        <div className="mt-6">
          <Text.H2 className="mb-4">Daftar Mata Pelajaran</Text.H2>

          {isLoadingGrades ? (
            <div className="flex flex-col items-center justify-center py-12">
              <Loader2 className="w-8 h-8 animate-spin text-indigo-600 mb-2" />
              <Text.Caption>Memuat nilai...</Text.Caption>
            </div>
          ) : grades.length === 0 ? (
            <div className="bg-white rounded-[24px] p-8 text-center border border-slate-50 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
              <GraduationCap className="w-12 h-12 text-slate-300 mx-auto mb-3" strokeWidth={1.5} />
              <Text.Body className="text-slate-500 font-medium mb-1">Rapor Belum Terbit</Text.Body>
              <Text.Caption>Nilai hasil belajar santri untuk semester yang dipilih belum diterbitkan oleh bagian kurikulum.</Text.Caption>
            </div>
          ) : (
            <div className="flex flex-col gap-3 pb-8">
              {grades.map((item: any) => {
                const isPassed = Number(item.grade || 0) >= Number(item.kkm || 75);
                return (
                  <div 
                    key={item.id} 
                    className="bg-white rounded-[24px] p-4 flex items-center justify-between border border-slate-50 shadow-[0_8px_30px_rgb(0,0,0,0.02)]"
                  >
                    <div className="flex items-center gap-3">
                      <div className={`w-10 h-10 rounded-full flex items-center justify-center ${
                        isPassed ? "bg-emerald-50 text-emerald-600" : "bg-red-50 text-red-600"
                      }`}>
                        {isPassed ? <CheckCircle size={18} /> : <AlertCircle size={18} />}
                      </div>
                      <div>
                        <Text.Body className="font-bold text-slate-800">
                          {item.study?.name || "Mata Pelajaran"}
                        </Text.Body>
                        <Text.Caption className="text-slate-400">
                          KKM: <span className="font-semibold">{item.kkm || 75}</span>
                        </Text.Caption>
                      </div>
                    </div>

                    <div className="flex items-center gap-4 text-right">
                      <div>
                        <span className="text-lg font-black text-slate-800 block leading-tight">
                          {item.grade}
                        </span>
                        <span className="text-[10px] font-bold text-slate-400">
                          Predikat: <span className="text-indigo-600 uppercase font-black">{item.letter_grade || "-"}</span>
                        </span>
                      </div>
                      
                      <span className={`px-2.5 py-1 text-[9px] font-black rounded-full uppercase tracking-wider ${
                        isPassed ? "bg-emerald-50 text-emerald-600" : "bg-red-50 text-red-600 animate-pulse"
                      }`}>
                        {isPassed ? "TUNTAS" : "TIDAK TUNTAS"}
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
