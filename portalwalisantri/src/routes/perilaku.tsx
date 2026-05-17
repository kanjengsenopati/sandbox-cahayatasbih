import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { Heart, ArrowLeft, Loader2, Sparkles, AlertTriangle, ShieldCheck, Calendar } from "lucide-react";
import { useQuery } from "@tanstack/react-query";
import { fetchCounseling } from "@/lib/api";

export const Route = createFileRoute("/perilaku")({
  component: Perilaku,
  head: () => ({ meta: [{ title: "Catatan Perilaku — SantriPay" }] }),
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
              <p className="text-[11px] text-white/70 font-semibold uppercase tracking-wider">Kedisiplinan</p>
              <p className="text-base font-bold text-white">Catatan Perilaku</p>
            </div>
          </div>

          <div className="relative mt-6 text-white">
            <p className="text-xs text-white/70 uppercase tracking-widest font-semibold">Nama Santri</p>
            <p className="text-xl font-bold mt-1 tracking-tight">{studentName}</p>
          </div>
        </div>

        {/* Status Perilaku Card */}
        <section className="px-6 -mt-10 relative z-10">
          <div 
            className="rounded-3xl p-6 text-white shadow-[var(--shadow-glow)] relative overflow-hidden"
            style={{ background: "var(--gradient-card)" }}
          >
            <div className="absolute -right-8 -bottom-8 text-white/10 pointer-events-none">
              <Heart size={140} strokeWidth={1} />
            </div>

            <div className="flex items-center gap-2 mb-2 opacity-85">
              {statusIcon}
              <p className="text-[10px] uppercase font-bold tracking-widest">Karakter Akhlak Santri</p>
            </div>

            {isLoading ? (
              <Loader2 className="w-8 h-8 animate-spin text-white mb-2" />
            ) : (
              <div className="flex items-baseline justify-between gap-2">
                <div>
                  <h2 className="text-2xl font-extrabold tracking-tight">
                    {statusText}
                  </h2>
                  <p className="text-[10px] text-white/80 mt-1">
                    Berdasarkan asesmen pembimbing asrama
                  </p>
                </div>
                <div className="text-right">
                  <span className="text-[9px] uppercase font-bold tracking-widest text-white/80 block">Skor Kelakuan</span>
                  <span className="text-2xl font-black">{averageScore}</span>
                </div>
              </div>
            )}

            <div className="mt-4 pt-4 border-t border-white/10 flex justify-between items-center text-[11px] opacity-80">
              <span>Buku Kendali Santri</span>
              <span className="font-bold uppercase tracking-wider">Sinkronisasi Harian</span>
            </div>
          </div>
        </section>

        {/* Logs */}
        <section className="px-6 mt-6">
          <div className="flex justify-between items-center mb-4">
            <h3 className="text-sm font-bold text-foreground">Log Aktivitas & Pelanggaran</h3>
            <span className="text-[10px] text-muted-foreground font-semibold flex items-center gap-1">
              <Calendar size={12} /> Konseling Pesantren
            </span>
          </div>

          {isLoading ? (
            <div className="bg-card rounded-3xl border border-border p-8 flex flex-col items-center justify-center shadow-[var(--shadow-soft)]">
              <Loader2 className="animate-spin text-primary mb-2" size={28} />
              <p className="text-xs font-semibold text-muted-foreground">Memuat catatan...</p>
            </div>
          ) : counselingLogs.length === 0 ? (
            <div className="bg-card rounded-3xl border border-border p-8 text-center shadow-[var(--shadow-soft)]">
              <Heart className="w-12 h-12 text-muted-foreground mx-auto mb-3" strokeWidth={1.5} />
              <p className="text-sm font-bold text-foreground">Catatan Bersih</p>
              <p className="text-xs text-muted-foreground mt-1">
                Alhamdulillah, ananda santri memiliki rekam perilaku yang bersih dan berakhlak mulia.
              </p>
            </div>
          ) : (
            <div className="flex flex-col gap-3 pb-8">
              {counselingLogs.map((log: any) => (
                <div 
                  key={log.id} 
                  className="bg-card rounded-3xl border border-border shadow-[var(--shadow-soft)] p-5"
                >
                  <div className="flex justify-between items-start gap-2 mb-2">
                    <span className="text-[10px] uppercase font-bold text-muted-foreground">
                      {new Date(log.created_at).toLocaleDateString("id-ID", {
                        day: "numeric",
                        month: "long",
                        year: "numeric"
                      })}
                    </span>
                    <span className={`px-2 py-0.5 rounded text-[9px] font-bold ${
                      log.score < 0 ? "bg-red-500/15 text-red-700" : "bg-emerald-500/15 text-emerald-700"
                    }`}>
                      {log.score < 0 ? "" : "+"}{log.score} Poin
                    </span>
                  </div>

                  <h4 className="font-bold text-sm text-foreground leading-snug">
                    {log.violation || "Catatan Pembinaan Akhlak"}
                  </h4>

                  {log.action && (
                    <div className="bg-destructive/5 rounded-xl p-3 border border-destructive/10 mt-3 flex items-start gap-2">
                      <AlertTriangle size={14} className="text-destructive shrink-0 mt-0.5" />
                      <div>
                        <span className="text-[9px] uppercase font-bold text-destructive block mb-0.5">Tindakan Takzir/Binaan:</span>
                        <p className="text-xs text-foreground font-medium">
                          {log.action}
                        </p>
                      </div>
                    </div>
                  )}

                  {log.note && (
                    <p className="text-[11px] text-muted-foreground mt-2 italic">
                      Keterangan tambahan: {log.note}
                    </p>
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
