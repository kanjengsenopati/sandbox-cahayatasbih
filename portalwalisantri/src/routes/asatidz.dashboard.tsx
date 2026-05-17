import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { ArrowLeft, Loader2, Calendar, ClipboardList, CheckCircle2, XCircle, Clock, ShieldAlert, Scan, LogOut, Phone } from "lucide-react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { fetchPendingPermits, fetchActivePermits, fetchOverduePermits, postPermitAction, postLogout } from "@/lib/api";

export const Route = createFileRoute("/asatidz/dashboard")({
  component: AsatidzDashboardPage,
  head: () => ({ meta: [{ title: "Dashboard Pengasuh Asrama — CT-Mobile" }] }),
});

function AsatidzDashboardPage() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [tab, setTab] = useState<"pending" | "active" | "overdue">("pending");
  const [rejectId, setRejectId] = useState<string | null>(null);
  const [rejectionReason, setRejectionReason] = useState("");

  const { data: pendingRes, isLoading: isLoadingPending } = useQuery({
    queryKey: ["pending-permits"],
    queryFn: async () => {
      const res = await fetchPendingPermits();
      return res.data;
    },
    refetchInterval: 10000, // Poll every 10 seconds for real-time approvals
  });

  const { data: activeRes, isLoading: isLoadingActive } = useQuery({
    queryKey: ["active-permits"],
    queryFn: async () => {
      const res = await fetchActivePermits();
      return res.data;
    },
  });

  const { data: overdueRes, isLoading: isLoadingOverdue } = useQuery({
    queryKey: ["overdue-permits"],
    queryFn: async () => {
      const res = await fetchOverduePermits();
      return res.data;
    },
    refetchInterval: 15000, // Poll for overdue alarms
  });

  const actionMutation = useMutation({
    mutationFn: async ({ id, action, reason }: { id: string; action: "approve" | "reject"; reason?: string }) => {
      const res = await postPermitAction(id, { action, rejection_reason: reason });
      return res.data;
    },
    onSuccess: (data: any) => {
      setRejectId(null);
      setRejectionReason("");
      queryClient.invalidateQueries({ queryKey: ["pending-permits"] });
      queryClient.invalidateQueries({ queryKey: ["active-permits"] });
      queryClient.invalidateQueries({ queryKey: ["overdue-permits"] });
    },
  });

  const handleApprove = (id: string) => {
    actionMutation.mutate({ id, action: "approve" });
  };

  const handleRejectSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!rejectId || !rejectionReason.trim()) return;
    actionMutation.mutate({ id: rejectId, action: "reject", reason: rejectionReason });
  };

  const handleLogout = async () => {
    try {
      await postLogout();
      navigate({ to: "/login" });
    } catch (e) {
      window.location.href = "/ct-mobile/login#/login";
    }
  };

  const pendingList = pendingRes?.permits ?? [];
  const activeList = activeRes?.permits ?? [];
  const overdueList = overdueRes?.permits ?? [];

  return (
    <div className="min-h-screen w-full flex justify-center bg-secondary">
      <div className="relative w-full max-w-md min-h-screen bg-background pb-32">
        {/* Header */}
        <div
          className="relative px-6 pt-12 pb-24 rounded-b-[2rem] overflow-hidden"
          style={{ background: "linear-gradient(135deg, #4f46e5 0%, #312e81 100%)" }}
        >
          <div className="absolute -top-20 -right-10 w-56 h-56 rounded-full bg-indigo-500/20 blur-3xl" />
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
              <div className="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-white">
                🛡️
              </div>
              <div>
                <p className="text-[11px] text-white/70 font-semibold uppercase tracking-wider">Akses Ustadz / Asrama</p>
                <p className="text-base font-bold text-white">Dashboard Perizinan</p>
              </div>
            </div>
            <button
              onClick={handleLogout}
              className="w-10 h-10 rounded-xl bg-white/15 border border-white/20 flex items-center justify-center text-white active:scale-95"
            >
              <LogOut size={16} />
            </button>
          </div>

          {/* Quick CTA to Scan */}
          <div className="mt-6 flex gap-3">
            <button
              onClick={() => navigate({ to: "/asatidz/scan" })}
              className="flex-1 py-3.5 bg-emerald-500 hover:bg-emerald-600 active:scale-[0.98] transition rounded-2xl text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-emerald-950/20"
            >
              <Scan size={16} /> Pindai Barcode Gerbang
            </button>
          </div>
        </div>

        {/* Tab switchers */}
        <div className="px-6 -mt-8 relative z-10">
          <div className="flex bg-card rounded-[20px] p-1 border border-border shadow-[var(--shadow-soft)]">
            <button
              onClick={() => setTab("pending")}
              className={`flex-1 py-3 rounded-[16px] text-xs font-bold transition flex flex-col items-center gap-0.5 ${
                tab === "pending"
                  ? "bg-indigo-600 text-white shadow-md"
                  : "text-slate-500 hover:text-slate-800"
              }`}
            >
              <span>Pending</span>
              <span className="text-[9px] opacity-75">({pendingList.length})</span>
            </button>
            <button
              onClick={() => setTab("active")}
              className={`flex-1 py-3 rounded-[16px] text-xs font-bold transition flex flex-col items-center gap-0.5 ${
                tab === "active"
                  ? "bg-indigo-600 text-white shadow-md"
                  : "text-slate-500 hover:text-slate-800"
              }`}
            >
              <span>Aktif</span>
              <span className="text-[9px] opacity-75">({activeList.length})</span>
            </button>
            <button
              onClick={() => setTab("overdue")}
              className={`flex-1 py-3 rounded-[16px] text-xs font-bold transition flex flex-col items-center gap-0.5 ${
                tab === "overdue"
                  ? "bg-indigo-600 text-white shadow-md"
                  : "text-slate-500 hover:text-slate-800"
              }`}
            >
              <span className="flex items-center gap-1">
                {overdueList.length > 0 && <span className="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>}
                Terlambat
              </span>
              <span className="text-[9px] opacity-75">({overdueList.length})</span>
            </button>
          </div>
        </div>

        {/* List Content */}
        <section className="px-6 mt-6 space-y-4">
          {tab === "pending" && (
            isLoadingPending ? (
              <div className="bg-card rounded-3xl border border-border p-8 flex flex-col items-center justify-center shadow-[var(--shadow-soft)]">
                <Loader2 className="animate-spin text-indigo-600 mb-2" size={28} />
                <p className="text-xs font-semibold text-muted-foreground">Memuat data pengajuan...</p>
              </div>
            ) : pendingList.length === 0 ? (
              <div className="bg-card rounded-3xl border border-border p-8 text-center shadow-[var(--shadow-soft)]">
                <CheckCircle2 className="mx-auto text-emerald-500 mb-3" size={32} />
                <p className="text-sm font-bold text-foreground">Semua Bersih!</p>
                <p className="text-xs text-muted-foreground mt-1">
                  Tidak ada pengajuan perizinan pending saat ini.
                </p>
              </div>
            ) : (
              pendingList.map((permit: any) => (
                <div
                  key={permit.id}
                  className="bg-card rounded-3xl border border-border p-5 shadow-[var(--shadow-soft)] space-y-4"
                >
                  <div className="flex items-start justify-between gap-3">
                    <div>
                      <p className="text-xs text-slate-400 font-bold uppercase tracking-wider">
                        {permit.permit_type.replace("_", " ")}
                      </p>
                      <p className="text-sm font-bold text-slate-800 mt-1">{permit.student?.name}</p>
                    </div>
                    <span className="px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-100 rounded-full text-[9px] font-bold">
                      Menunggu Approval
                    </span>
                  </div>

                  <div className="grid grid-cols-2 gap-3 text-xs bg-slate-50 rounded-2xl p-3 border border-slate-100">
                    <div>
                      <p className="text-slate-400">Rencana Keluar</p>
                      <p className="font-bold text-slate-700 mt-0.5">
                        {new Date(permit.planned_exit_date).toLocaleString("id-ID", { day: "numeric", month: "short", hour: "2-digit", minute: "2-digit" })}
                      </p>
                    </div>
                    <div>
                      <p className="text-slate-400">Rencana Kembali</p>
                      <p className="font-bold text-slate-700 mt-0.5">
                        {new Date(permit.planned_return_date).toLocaleString("id-ID", { day: "numeric", month: "short", hour: "2-digit", minute: "2-digit" })}
                      </p>
                    </div>
                  </div>

                  <div className="bg-slate-50 border border-slate-100 rounded-2xl p-3 text-xs space-y-1">
                    <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Wali / Pengaju</p>
                    <p className="font-bold text-slate-700">{permit.user?.name} · {permit.user?.phone}</p>
                    <p className="text-slate-500 mt-1 leading-relaxed">{permit.reason}</p>
                  </div>

                  {/* Actions buttons */}
                  <div className="flex gap-2">
                    <button
                      onClick={() => setRejectId(permit.id)}
                      className="flex-1 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl font-bold text-xs transition active:scale-[0.98]"
                    >
                      Tolak
                    </button>
                    <button
                      onClick={() => handleApprove(permit.id)}
                      className="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs transition active:scale-[0.98] shadow-sm shadow-indigo-900/10"
                    >
                      Setujui Izin
                    </button>
                  </div>
                </div>
              ))
            )
          )}

          {tab === "active" && (
            isLoadingActive ? (
              <div className="bg-card rounded-3xl border border-border p-8 flex flex-col items-center justify-center shadow-[var(--shadow-soft)]">
                <Loader2 className="animate-spin text-indigo-600 mb-2" size={28} />
                <p className="text-xs font-semibold text-muted-foreground">Memuat data aktif...</p>
              </div>
            ) : activeList.length === 0 ? (
              <div className="bg-card rounded-3xl border border-border p-8 text-center shadow-[var(--shadow-soft)]">
                <Calendar className="mx-auto text-muted-foreground mb-3" size={32} />
                <p className="text-sm font-bold text-foreground">Tidak Ada Santri Keluar</p>
                <p className="text-xs text-muted-foreground mt-1">
                  Saat ini tidak ada santri yang sedang keluar pondok.
                </p>
              </div>
            ) : (
              activeList.map((permit: any) => {
                const isOut = permit.status === "out";
                return (
                  <div
                    key={permit.id}
                    className="bg-card rounded-3xl border border-border p-5 shadow-[var(--shadow-soft)] space-y-3"
                  >
                    <div className="flex justify-between items-start">
                      <div>
                        <span className={`inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold border ${
                          isOut ? "bg-blue-50 text-blue-700 border-blue-100" : "bg-emerald-50 text-emerald-700 border-emerald-100"
                        }`}>
                          {isOut ? "Sedang Diluar" : "Disetujui Ustadz"}
                        </span>
                        <p className="text-sm font-bold text-slate-800 mt-2">{permit.student?.name}</p>
                      </div>
                      
                      <a
                        href={`tel:${permit.user?.phone}`}
                        className="p-2 rounded-xl bg-slate-50 border border-border text-slate-600 flex items-center justify-center"
                      >
                        <Phone size={14} />
                      </a>
                    </div>

                    <div className="text-xs space-y-1.5 border-t border-slate-100 pt-2.5 text-slate-500">
                      <p><span className="font-semibold text-slate-700">Wali:</span> {permit.user?.name}</p>
                      <p><span className="font-semibold text-slate-700">Rencana Keluar:</span> {new Date(permit.planned_exit_date).toLocaleString("id-ID")}</p>
                      <p><span className="font-semibold text-slate-700">Rencana Kembali:</span> {new Date(permit.planned_return_date).toLocaleString("id-ID")}</p>
                      {permit.exit_escort_name && (
                        <p><span className="font-semibold text-slate-700">Penjemput:</span> {permit.exit_escort_name} ({permit.exit_escort_relation})</p>
                      )}
                    </div>
                  </div>
                );
              })
            )
          )}

          {tab === "overdue" && (
            isLoadingOverdue ? (
              <div className="bg-card rounded-3xl border border-border p-8 flex flex-col items-center justify-center shadow-[var(--shadow-soft)]">
                <Loader2 className="animate-spin text-indigo-600 mb-2" size={28} />
                <p className="text-xs font-semibold text-muted-foreground">Memuat data terlambat...</p>
              </div>
            ) : overdueList.length === 0 ? (
              <div className="bg-card rounded-3xl border border-border p-8 text-center shadow-[var(--shadow-soft)]">
                <CheckCircle2 className="mx-auto text-emerald-500 mb-3" size={32} />
                <p className="text-sm font-bold text-foreground">Semua Kembali Tepat Waktu</p>
                <p className="text-xs text-muted-foreground mt-1">
                  Semua santri yang sedang keluar belum melewati rencana tanggal kepulangannya.
                </p>
              </div>
            ) : (
              overdueList.map((permit: any) => (
                <div
                  key={permit.id}
                  className="bg-card rounded-3xl border border-red-200 bg-red-50/10 p-5 shadow-lg relative overflow-hidden space-y-3"
                >
                  <div className="absolute right-0 top-0 w-24 h-24 bg-red-500/5 rounded-full blur-2xl"></div>
                  
                  <div className="flex justify-between items-start relative z-10">
                    <div>
                      <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-red-100 text-red-700 text-[9px] font-bold border border-red-200">
                        <ShieldAlert size={10} className="animate-pulse" /> Terlambat Kembali
                      </span>
                      <p className="text-sm font-bold text-red-950 mt-2">{permit.student?.name}</p>
                    </div>
                    
                    <a
                      href={`tel:${permit.user?.phone}`}
                      className="p-2 rounded-xl bg-red-100 text-red-700 border border-red-200 flex items-center justify-center"
                    >
                      <Phone size={14} />
                    </a>
                  </div>

                  <div className="text-xs space-y-1.5 border-t border-red-100/50 pt-2.5 text-slate-600 relative z-10">
                    <p><span className="font-semibold text-red-900">Wali:</span> {permit.user?.name}</p>
                    <p><span className="font-semibold text-red-900">Tenggat Kembali:</span> <span className="text-red-700 font-bold">{new Date(permit.planned_return_date).toLocaleString("id-ID")}</span></p>
                    {permit.exit_escort_name && (
                      <p><span className="font-semibold text-red-900">Penjemput:</span> {permit.exit_escort_name} ({permit.exit_escort_relation})</p>
                    )}
                  </div>
                </div>
              ))
            )
          )}
        </section>

        {/* Rejection Prompt Drawer */}
        {rejectId && (
          <div className="fixed inset-0 bg-black/60 z-50 flex items-end justify-center p-4 backdrop-blur-sm">
            <div className="bg-card w-full max-w-md rounded-t-[2.5rem] p-6 shadow-2xl space-y-4 animate-slide-up pb-10">
              <div className="flex justify-between items-center">
                <h3 className="text-base font-bold text-slate-800">Alasan Penolakan</h3>
                <button
                  onClick={() => setRejectId(null)}
                  className="w-8 h-8 rounded-full bg-secondary border border-border flex items-center justify-center text-slate-500 font-bold hover:bg-slate-100 transition"
                >
                  ×
                </button>
              </div>

              <form onSubmit={handleRejectSubmit} className="space-y-4">
                <textarea
                  rows={3}
                  required
                  value={rejectionReason}
                  onChange={(e) => setRejectionReason(e.target.value)}
                  placeholder="Tulis alasan penolakan agar wali santri dapat memahami keputusannya..."
                  className="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3.5 outline-none text-slate-800 text-[13px] focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition resize-none"
                />

                <button
                  type="submit"
                  disabled={actionMutation.isPending}
                  className="w-full py-3.5 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition active:scale-[0.98]"
                >
                  {actionMutation.isPending ? "Sedang memproses..." : "Tolak Pengajuan"}
                </button>
              </form>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
