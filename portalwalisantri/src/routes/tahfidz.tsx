import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { BookOpen, ArrowLeft, Loader2, BookMarked, Calendar, MessageSquare } from "lucide-react";
import { useQuery } from "@tanstack/react-query";
import { fetchTahfidz } from "@/lib/api";

export const Route = createFileRoute("/tahfidz")({
  component: Tahfidz,
  head: () => ({ meta: [{ title: "Progress Tahfidz — SantriPay" }] }),
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
              <p className="text-[11px] text-white/70 font-semibold uppercase tracking-wider">Kurikulum</p>
              <p className="text-base font-bold text-white">Setoran Tahfidz Quran</p>
            </div>
          </div>

          <div className="relative mt-6 text-white">
            <p className="text-xs text-white/70 uppercase tracking-widest font-semibold">Nama Santri</p>
            <p className="text-xl font-bold mt-1 tracking-tight">{studentName}</p>
          </div>
        </div>

        {/* Total Setoran Card */}
        <section className="px-6 -mt-10 relative z-10">
          <div 
            className="rounded-3xl p-6 text-white shadow-[var(--shadow-glow)] relative overflow-hidden"
            style={{ background: "var(--gradient-card)" }}
          >
            <div className="absolute -right-8 -bottom-8 text-white/10 pointer-events-none">
              <BookOpen size={140} strokeWidth={1} />
            </div>
            
            <div className="flex items-center gap-2 mb-2 opacity-80">
              <BookMarked size={16} />
              <p className="text-[10px] uppercase font-bold tracking-widest">Total Halaman Dihafal</p>
            </div>
            
            {isLoading ? (
              <Loader2 className="w-8 h-8 animate-spin text-white mb-2" />
            ) : (
              <div className="flex items-baseline gap-1.5">
                <h2 className="text-3xl font-extrabold tracking-tight">{totalPages}</h2>
                <span className="text-sm font-semibold opacity-80">Halaman</span>
              </div>
            )}
            
            <div className="mt-4 pt-4 border-t border-white/10 flex justify-between items-center text-[11px] opacity-80">
              <span>Metode Setoran</span>
              <span className="font-bold uppercase tracking-wider">Bin-Nadhar & Bil-Ghaib</span>
            </div>
          </div>
        </section>

        {/* Timeline Logs */}
        <section className="px-6 mt-6">
          <div className="flex justify-between items-center mb-4">
            <h3 className="text-sm font-bold text-foreground">Log Setoran Hafalan</h3>
            <span className="text-[10px] text-muted-foreground font-semibold flex items-center gap-1">
              <Calendar size={12} /> Progress Halaqah
            </span>
          </div>

          {isLoading ? (
            <div className="bg-card rounded-3xl border border-border p-8 flex flex-col items-center justify-center shadow-[var(--shadow-soft)]">
              <Loader2 className="animate-spin text-primary mb-2" size={28} />
              <p className="text-xs font-semibold text-muted-foreground">Memuat log setoran...</p>
            </div>
          ) : depositLogs.length === 0 ? (
            <div className="bg-card rounded-3xl border border-border p-8 text-center shadow-[var(--shadow-soft)]">
              <BookOpen className="w-12 h-12 text-muted-foreground mx-auto mb-3" strokeWidth={1.5} />
              <p className="text-sm font-bold text-foreground">Belum Ada Setoran</p>
              <p className="text-xs text-muted-foreground mt-1">
                Data setoran hafalan Al-Quran dari ustadz pembimbing akan tampil secara berkala di sini.
              </p>
            </div>
          ) : (
            <div className="relative border-l-2 border-primary/20 ml-4 pl-6 flex flex-col gap-5 pb-8">
              {depositLogs.map((log: any) => (
                <div key={log.id} className="relative">
                  {/* Dot indicator */}
                  <span className="absolute -left-[31px] top-1.5 w-4 h-4 rounded-full bg-primary border-4 border-background shadow-sm" />
                  
                  <div className="bg-card rounded-3xl border border-border shadow-[var(--shadow-soft)] p-5">
                    <div className="flex justify-between items-start gap-2 mb-2">
                      <span className="text-[10px] uppercase font-bold text-muted-foreground tracking-wider">
                        {new Date(log.deposit_date).toLocaleDateString("id-ID", {
                          day: "numeric",
                          month: "long",
                          year: "numeric"
                        })}
                      </span>
                      <span className="px-2.5 py-0.5 bg-primary/10 text-primary text-[10px] font-bold rounded-full">
                        {log.number_of_pages} Halaman
                      </span>
                    </div>

                    <h4 className="font-bold text-sm text-foreground leading-snug">
                      {log.note || "Murojaah & Setoran Baru"}
                    </h4>

                    {log.feedback && (
                      <div className="mt-3 pt-3 border-t border-border flex gap-2">
                        <MessageSquare size={14} className="text-primary shrink-0 mt-0.5" />
                        <div className="bg-secondary/50 rounded-xl p-3 flex-1">
                          <span className="text-[9px] font-bold text-muted-foreground block mb-0.5">Saran Ustadz:</span>
                          <p className="text-xs text-foreground font-medium italic">
                            "{log.feedback}"
                          </p>
                        </div>
                      </div>
                    )}
                  </div>
                </div>
              ))}
            </div>
          )}
        </section>
      </div>
    </div>
  );
}
