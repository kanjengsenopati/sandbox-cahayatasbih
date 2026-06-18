import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { ArrowLeft, Loader2, Calendar, Clock, MapPin, CheckCircle2, AlertCircle, RefreshCw, Sparkles } from "lucide-react";
import { useQuery } from "@tanstack/react-query";
import { fetchAttendances } from "@/lib/api";
import { format } from "date-fns";
import { id } from "date-fns/locale";

export const Route = createFileRoute("/presensi" as any)({
  component: AttendancePage,
  head: () => ({ meta: [{ title: "Riwayat Kehadiran — SantriPay" }] }),
});

function AttendancePage() {
  const navigate = useNavigate();

  const { data: attendanceRes, isLoading, refetch, isFetching } = useQuery({
    queryKey: ["attendances"],
    queryFn: async () => {
      const res = await fetchAttendances();
      return res.data;
    },
  });

  const studentName = attendanceRes?.student_name ?? "Santri";
  const stats = attendanceRes?.stats ?? { present: 0, late: 0, permit: 0, absent: 0 };
  const logs = attendanceRes?.data ?? [];

  const getStatusBadge = (status: string) => {
    switch (status) {
      case "present":
        return (
          <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-600">
            <CheckCircle2 size={12} /> Hadir
          </span>
        );
      case "late":
        return (
          <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-500/10 text-amber-600">
            <Clock size={12} /> Terlambat
          </span>
        );
      case "permit":
      case "sick":
        return (
          <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-500/10 text-blue-600">
            <AlertCircle size={12} /> Izin/Sakit
          </span>
        );
      case "absent":
      default:
        return (
          <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-500/10 text-red-600">
            <AlertCircle size={12} /> Alpa
          </span>
        );
    }
  };

  const getActivityIcon = (type: string) => {
    switch (type) {
      case "school":
        return "🏫";
      case "prayer":
        return "🕌";
      case "kajian":
        return "📖";
      case "work":
        return "💼";
      default:
        return "📝";
    }
  };

  const getActivityLabel = (type: string) => {
    switch (type) {
      case "school":
        return "Sekolah";
      case "prayer":
        return "Sholat Jamaah";
      case "kajian":
        return "Kajian/Halaqoh";
      case "work":
        return "Jam Kerja";
      default:
        return "Kegiatan";
    }
  };

  return (
    <div className="min-h-screen w-full flex justify-center bg-secondary">
      <div className="relative w-full max-w-md min-h-screen bg-background pb-32">
        {/* Header Hero */}
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
          <div className="relative flex items-center justify-between">
            <div className="flex items-center gap-3">
              <button
                onClick={() => navigate({ to: "/dashboard" })}
                className="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center text-white"
              >
                <ArrowLeft size={18} />
              </button>
              <div>
                <p className="text-[11px] text-white/70 font-semibold uppercase tracking-wider">Aktivitas Harian</p>
                <p className="text-base font-bold text-white">Kehadiran Santri & Siswa</p>
              </div>
            </div>
            <button
              onClick={() => refetch()}
              disabled={isFetching}
              className="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center text-white active:scale-95 transition-transform"
            >
              <RefreshCw size={16} className={isFetching ? "animate-spin" : ""} />
            </button>
          </div>

          <div className="relative mt-6 text-white">
            <p className="text-xs text-white/70 uppercase tracking-widest font-semibold">Nama Santri</p>
            <p className="text-xl font-bold mt-1 tracking-tight">{studentName}</p>
          </div>
        </div>

        {/* Stats Grid */}
        <section className="px-6 -mt-10 relative z-10 grid grid-cols-4 gap-2">
          <div className="bg-card rounded-3xl p-3 border border-border shadow-[var(--shadow-soft)] text-center">
            <p className="text-[10px] text-muted-foreground font-semibold">Hadir</p>
            <p className="text-lg font-bold text-emerald-600 mt-1">{stats.present}</p>
          </div>
          <div className="bg-card rounded-3xl p-3 border border-border shadow-[var(--shadow-soft)] text-center">
            <p className="text-[10px] text-muted-foreground font-semibold">Telat</p>
            <p className="text-lg font-bold text-amber-600 mt-1">{stats.late}</p>
          </div>
          <div className="bg-card rounded-3xl p-3 border border-border shadow-[var(--shadow-soft)] text-center">
            <p className="text-[10px] text-muted-foreground font-semibold">Izin</p>
            <p className="text-lg font-bold text-blue-600 mt-1">{stats.permit}</p>
          </div>
          <div className="bg-card rounded-3xl p-3 border border-border shadow-[var(--shadow-soft)] text-center">
            <p className="text-[10px] text-muted-foreground font-semibold">Alpa</p>
            <p className="text-lg font-bold text-red-600 mt-1">{stats.absent}</p>
          </div>
        </section>

        {/* History Timeline */}
        <section className="px-6 mt-6">
          <div className="flex justify-between items-center mb-4">
            <h3 className="text-sm font-bold text-foreground">Log Kehadiran Terkini</h3>
            <span className="text-[10px] text-muted-foreground font-semibold flex items-center gap-1">
              <Sparkles size={12} /> Terhubung ke Biometrik
            </span>
          </div>

          {isLoading ? (
            <div className="bg-card rounded-[24px] border border-border p-12 flex flex-col items-center justify-center shadow-[var(--shadow-card)]">
              <Loader2 className="animate-spin text-primary mb-2" size={28} />
              <p className="text-xs font-semibold text-muted-foreground">Memuat data absensi...</p>
            </div>
          ) : logs.length === 0 ? (
            <div className="bg-card rounded-[24px] border border-border p-12 text-center shadow-[var(--shadow-card)]">
              <Calendar className="mx-auto text-muted-foreground mb-3" size={32} />
              <p className="text-sm font-bold text-foreground">Belum Ada Kehadiran</p>
              <p className="text-xs text-muted-foreground mt-1">
                Data kehadiran santri belum tercatat hari ini.
              </p>
            </div>
          ) : (
            <div className="space-y-3">
              {logs.map((log: any) => (
                <div 
                  key={log.id}
                  className="bg-card rounded-[24px] border border-border shadow-[var(--shadow-soft)] p-4 flex justify-between items-center"
                >
                  <div className="flex items-center gap-3">
                    <span className="text-2xl p-2 rounded-2xl bg-secondary/50 flex items-center justify-center">
                      {getActivityIcon(log.activity_type)}
                    </span>
                    <div>
                      <p className="text-[10px] text-slate-400 uppercase font-bold tracking-widest">
                        {getActivityLabel(log.activity_type)}
                      </p>
                      <h4 className="text-[14px] font-bold text-slate-800 mt-0.5">{log.activity_name}</h4>
                      <p className="text-[12px] text-slate-500 mt-0.5 flex items-center gap-1">
                        <Clock size={12} /> 
                        {format(new Date(log.check_in), "d MMM yyyy, HH:mm", { locale: id })}
                        {log.late_minutes > 0 && (
                          <span className="text-red-500 font-semibold">(+{log.late_minutes}m)</span>
                        )}
                      </p>
                      {log.latitude && (
                        <p className="text-[11px] text-slate-400 mt-0.5 flex items-center gap-0.5">
                          <MapPin size={10} /> Presensi Mandiri (GPS)
                        </p>
                      )}
                    </div>
                  </div>
                  <div>
                    {getStatusBadge(log.status)}
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
