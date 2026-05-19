import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { ArrowLeft, Loader2, Calendar, ClipboardList, CheckCircle2, XCircle, Clock, ShieldAlert, Scan, LogOut, Phone, RefreshCw, ChevronDown, ChevronUp, Image as ImageIcon } from "lucide-react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { fetchPendingPermits, fetchActivePermits, fetchOverduePermits, postPermitAction, postLogout, fetchAsatidzStats, fetchMyStudents, fetchStudentHistory, fetchPendingReturnPermits, postPermitReturnAction } from "@/lib/api";
import { resolveImageUrl } from "@/lib/utils";

export const Route = createFileRoute("/asatidz/dashboard")({
  component: AsatidzDashboardPage,
  head: () => ({ meta: [{ title: "Dashboard Pengasuh Asrama — CT-Mobile" }] }),
});

function AsatidzDashboardPage() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [tab, setTab] = useState<"pending" | "active" | "overdue" | "my-students" | "pending_return">("pending");
  const [rejectId, setRejectId] = useState<string | null>(null);
  const [rejectionReason, setRejectionReason] = useState("");
  const [historyStudentId, setHistoryStudentId] = useState<string | null>(null);
  const [expandedPermitId, setExpandedPermitId] = useState<string | null>(null);
  const [expandedHistoryId, setExpandedHistoryId] = useState<string | null>(null);
  const [zoomPhotoUrl, setZoomPhotoUrl] = useState<string | null>(null);
  
  // Return Flow States
  const [rejectReturnId, setRejectReturnId] = useState<string | null>(null);
  const [rejectionReturnReason, setRejectionReturnReason] = useState("");

  const formatFullDate = (dateStr: string) => {
    if (!dateStr) return "-";
    try {
      const date = new Date(dateStr);
      return date.toLocaleDateString("id-ID", {
        weekday: "long",
        day: "numeric",
        month: "long",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit"
      }) + " WIB";
    } catch (e) {
      return dateStr;
    }
  };

  const { data: statsRes } = useQuery({
    queryKey: ["asatidz-stats"],
    queryFn: async () => {
      const res = await fetchAsatidzStats();
      return res.data;
    },
    refetchInterval: 10000,
  });

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

  const { data: myStudentsRes, isLoading: isLoadingMyStudents } = useQuery({
    queryKey: ["my-students"],
    queryFn: async () => {
      const res = await fetchMyStudents();
      return res.data;
    },
    enabled: tab === "my-students",
  });

  const { data: historyRes, isLoading: isLoadingHistory } = useQuery({
    queryKey: ["student-history", historyStudentId],
    queryFn: async () => {
      if (!historyStudentId) return null;
      const res = await fetchStudentHistory(historyStudentId);
      return res.data;
    },
    enabled: !!historyStudentId,
  });

  const { data: pendingReturnRes, isLoading: isLoadingPendingReturn } = useQuery({
    queryKey: ["pending-return-permits"],
    queryFn: async () => {
      const res = await fetchPendingReturnPermits();
      return res.data;
    },
    refetchInterval: 10000,
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
      queryClient.invalidateQueries({ queryKey: ["asatidz-stats"] });
    },
  });

  const actionReturnMutation = useMutation({
    mutationFn: async ({ id, action, reason }: { id: string; action: "approve" | "reject"; reason?: string }) => {
      const res = await postPermitReturnAction(id, { action, rejection_reason: reason });
      return res.data;
    },
    onSuccess: () => {
      setRejectReturnId(null);
      setRejectionReturnReason("");
      queryClient.invalidateQueries({ queryKey: ["pending-return-permits"] });
      queryClient.invalidateQueries({ queryKey: ["active-permits"] });
      queryClient.invalidateQueries({ queryKey: ["overdue-permits"] });
      queryClient.invalidateQueries({ queryKey: ["asatidz-stats"] });
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

  const handleApproveReturn = (id: string) => {
    actionReturnMutation.mutate({ id, action: "approve" });
  };

  const handleRejectReturnSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!rejectReturnId || !rejectionReturnReason.trim()) return;
    actionReturnMutation.mutate({ id: rejectReturnId, action: "reject", reason: rejectionReturnReason });
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
  const myStudentsList = myStudentsRes?.students ?? [];
  const pendingReturnList = pendingReturnRes?.permits ?? [];

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
              <div className="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur border border-white/15 flex items-center justify-center text-white font-extrabold text-lg shrink-0">
                {statsRes?.host_name ? statsRes.host_name.substring(0, 2).toUpperCase() : "AZ"}
              </div>
              <div>
                <p className="text-[10px] text-indigo-200 font-extrabold uppercase tracking-widest leading-none">
                  {statsRes?.asrama_name || "Asrama Binaan"}
                </p>
                <p className="text-base font-extrabold text-white leading-tight mt-1">
                  {statsRes?.host_name || "Ustadz / Ustadzah"}
                </p>
                <p className="text-[10px] text-indigo-300 font-medium mt-1 leading-none">
                  Supervisi: <span className="text-white font-bold">{statsRes?.total_students || 0} Santri Saya</span>
                </p>
              </div>
            </div>
            <button
              onClick={handleLogout}
              className="w-10 h-10 rounded-xl bg-white/15 border border-white/20 flex items-center justify-center text-white active:scale-95 shrink-0"
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
          <div className="flex bg-card rounded-[20px] p-1 border border-border shadow-[var(--shadow-soft)] overflow-x-auto scrollbar-none gap-1">
            <button
              onClick={() => setTab("pending")}
              className={`flex-1 py-3 rounded-[16px] text-xs font-bold transition flex flex-col items-center gap-0.5 shrink-0 min-w-[70px] ${
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
              className={`flex-1 py-3 rounded-[16px] text-xs font-bold transition flex flex-col items-center gap-0.5 shrink-0 min-w-[70px] ${
                tab === "active"
                  ? "bg-indigo-600 text-white shadow-md"
                  : "text-slate-500 hover:text-slate-800"
              }`}
            >
              <span>Aktif</span>
              <span className="text-[9px] opacity-75">({activeList.length})</span>
            </button>
            <button
              onClick={() => setTab("pending_return")}
              className={`flex-1 py-3 rounded-[16px] text-xs font-bold transition flex flex-col items-center gap-0.5 shrink-0 min-w-[70px] ${
                tab === "pending_return"
                  ? "bg-indigo-600 text-white shadow-md"
                  : "text-slate-500 hover:text-slate-800"
              }`}
            >
              <span>Kembali</span>
              <span className="text-[9px] opacity-75">({pendingReturnList.length})</span>
            </button>
            <button
              onClick={() => setTab("overdue")}
              className={`flex-1 py-3 rounded-[16px] text-xs font-bold transition flex flex-col items-center gap-0.5 shrink-0 min-w-[70px] ${
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
            <button
              onClick={() => setTab("my-students")}
              className={`flex-1 py-3 rounded-[16px] text-xs font-bold transition flex flex-col items-center gap-0.5 shrink-0 min-w-[80px] ${
                tab === "my-students"
                  ? "bg-indigo-600 text-white shadow-md"
                  : "text-slate-500 hover:text-slate-800"
              }`}
            >
              <span>Santri Saya</span>
              <span className="text-[9px] opacity-75">({statsRes?.total_students || 0})</span>
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
              <div className="bg-card rounded-[24px] border border-border overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                <div className="divide-y divide-slate-100">
                  {pendingList.map((permit: any) => {
                    const isExpanded = expandedPermitId === permit.id;
                    return (
                      <div key={permit.id} className="transition-all">
                        {/* Table Row / Accordion Header */}
                        <div 
                          onClick={() => setExpandedPermitId(isExpanded ? null : permit.id)}
                          className={`flex items-center justify-between p-4 cursor-pointer hover:bg-slate-50/50 transition-colors ${
                            isExpanded ? "bg-slate-50/50" : ""
                          }`}
                        >
                          <div className="flex items-center gap-3">
                            <div className="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center font-bold text-slate-500 shrink-0">
                              {permit.student?.avatar ? (
                                <img src={resolveImageUrl(permit.student.avatar) || ""} alt="" className="w-full h-full object-cover" />
                              ) : (
                                permit.student?.name?.substring(0, 2).toUpperCase() || "S"
                              )}
                            </div>
                            <div>
                              <p className="text-[13px] font-bold text-slate-800 leading-tight">
                                {permit.student?.name}
                              </p>
                              <p className="text-[10px] font-semibold text-slate-400 mt-1">
                                {permit.student?.classroom_name || "-"}
                              </p>
                            </div>
                          </div>

                          <div className="flex items-center gap-3 text-right">
                            <div>
                              <span className="inline-block px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-100 uppercase tracking-wide">
                                {permit.permit_type.replace("_", " ")}
                              </span>
                              <p className="text-[10px] text-slate-400 mt-1">
                                {new Date(permit.planned_return_date).toLocaleString("id-ID", { day: "numeric", month: "short" })}
                              </p>
                            </div>
                            <div className="text-slate-400">
                              {isExpanded ? (
                                <ChevronUp size={16} strokeWidth={2.5} />
                              ) : (
                                <ChevronDown size={16} strokeWidth={2.5} />
                              )}
                            </div>
                          </div>
                        </div>

                        {/* Expandable Panel */}
                        {isExpanded && (
                          <div className="px-4 pb-5 pt-2 bg-slate-50/30 border-t border-slate-100/50 space-y-4">
                            <div className="grid grid-cols-2 gap-3 text-xs bg-white rounded-2xl p-3 border border-slate-100/80 shadow-[0_2px_8px_rgba(0,0,0,0.01)]">
                              <div>
                                <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Rencana Keluar</p>
                                <p className="font-bold text-slate-700 mt-0.5">
                                  {new Date(permit.planned_exit_date).toLocaleString("id-ID", { day: "numeric", month: "short", hour: "2-digit", minute: "2-digit" })}
                                </p>
                              </div>
                              <div>
                                <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Rencana Kembali</p>
                                <p className="font-bold text-slate-700 mt-0.5">
                                  {new Date(permit.planned_return_date).toLocaleString("id-ID", { day: "numeric", month: "short", hour: "2-digit", minute: "2-digit" })}
                                </p>
                              </div>
                            </div>

                            <div className="bg-white border border-slate-100/80 rounded-2xl p-3 text-xs space-y-2 shadow-[0_2px_8px_rgba(0,0,0,0.01)]">
                              <div>
                                <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Wali / Pengaju</p>
                                <p className="font-bold text-slate-700 mt-0.5">{permit.user?.name} · {permit.user?.phone}</p>
                              </div>
                              <div>
                                <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Alasan Perizinan</p>
                                <p className="text-slate-600 mt-0.5 leading-relaxed font-medium">{permit.reason || "-"}</p>
                              </div>
                            </div>

                            {/* Foto Pendukung / Attachment */}
                            <div className="bg-white border border-slate-100/80 rounded-2xl p-3 text-xs space-y-2.5 shadow-[0_2px_8px_rgba(0,0,0,0.01)]">
                              <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Foto Pendukung</p>
                              {permit.attachment_photo ? (
                                <div 
                                  onClick={(e) => {
                                    e.stopPropagation();
                                    setZoomPhotoUrl(resolveImageUrl(permit.attachment_photo));
                                  }}
                                  className="relative rounded-xl overflow-hidden border border-slate-100 max-h-[220px] bg-slate-50 flex items-center justify-center group cursor-pointer active:scale-[0.99] transition-all shadow-sm"
                                >
                                  <img 
                                    src={resolveImageUrl(permit.attachment_photo) || ""} 
                                    alt="Foto Pendukung" 
                                    className="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                                  />
                                  <div className="absolute inset-0 bg-black/10 group-hover:bg-black/25 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <span className="text-white text-[10px] font-bold bg-black/60 px-2.5 py-1 rounded-full backdrop-blur-sm shadow-md">Klik untuk perbesar</span>
                                  </div>
                                </div>
                              ) : (
                                <div className="py-4 text-center rounded-xl bg-slate-50 border border-dashed border-slate-200 text-slate-400 font-medium">
                                  Tidak ada lampiran foto
                                </div>
                              )}
                            </div>

                            {/* Action Buttons */}
                            <div className="flex gap-2.5">
                              <button
                                onClick={() => setRejectId(permit.id)}
                                className="flex-1 py-3 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl font-bold text-xs transition active:scale-[0.98] border border-rose-100"
                              >
                                Tolak
                              </button>
                              <button
                                onClick={() => handleApprove(permit.id)}
                                className="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs transition active:scale-[0.98] shadow-sm shadow-indigo-900/10"
                              >
                                Setujui Izin
                              </button>
                            </div>
                          </div>
                        )}
                      </div>
                    );
                  })}
                </div>
              </div>
            )
          )}

          {tab === "pending_return" && (
            isLoadingPendingReturn ? (
              <div className="bg-card rounded-3xl border border-border p-8 flex flex-col items-center justify-center shadow-[var(--shadow-soft)]">
                <Loader2 className="animate-spin text-indigo-600 mb-2" size={28} />
                <p className="text-xs font-semibold text-muted-foreground">Memuat data kepulangan...</p>
              </div>
            ) : pendingReturnList.length === 0 ? (
              <div className="bg-card rounded-3xl border border-border p-8 text-center shadow-[var(--shadow-soft)]">
                <CheckCircle2 className="mx-auto text-emerald-500 mb-3" size={32} />
                <p className="text-sm font-bold text-foreground">Semua Selesai!</p>
                <p className="text-xs text-muted-foreground mt-1">
                  Tidak ada laporan kepulangan yang menunggu konfirmasi.
                </p>
              </div>
            ) : (
              <div className="bg-card rounded-[24px] border border-border overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                <div className="divide-y divide-slate-100">
                  {pendingReturnList.map((permit: any) => {
                    const isExpanded = expandedPermitId === permit.id;
                    return (
                      <div key={permit.id} className="transition-all">
                        {/* Accordion Header */}
                        <div 
                          onClick={() => setExpandedPermitId(isExpanded ? null : permit.id)}
                          className={`flex items-center justify-between p-4 cursor-pointer hover:bg-slate-50/50 transition-colors ${
                            isExpanded ? "bg-slate-50/50" : ""
                          }`}
                        >
                          <div className="flex items-center gap-3">
                            <div className="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center font-bold text-slate-500 shrink-0">
                              {permit.student?.avatar ? (
                                <img src={resolveImageUrl(permit.student.avatar) || ""} alt="" className="w-full h-full object-cover" />
                              ) : (
                                permit.student?.name?.substring(0, 2).toUpperCase() || "S"
                              )}
                            </div>
                            <div>
                              <p className="text-[13px] font-bold text-slate-800 leading-tight">
                                {permit.student?.name}
                              </p>
                              <p className="text-[10px] font-semibold text-slate-400 mt-1">
                                {permit.student?.classroom_name || "-"}
                              </p>
                            </div>
                          </div>

                          <div className="flex items-center gap-3 text-right">
                            <div>
                              <span className="inline-block px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-purple-50 text-purple-700 border border-purple-100 uppercase tracking-wide">
                                Lapor Kembali
                              </span>
                              <p className="text-[10px] text-slate-400 mt-1">
                                {permit.actual_return_date ? new Date(permit.actual_return_date).toLocaleDateString("id-ID", { day: "numeric", month: "short" }) : "-"}
                              </p>
                            </div>
                            <div className="text-slate-400">
                              {isExpanded ? (
                                <ChevronUp size={16} strokeWidth={2.5} />
                              ) : (
                                <ChevronDown size={16} strokeWidth={2.5} />
                              )}
                            </div>
                          </div>
                        </div>

                        {/* Accordion Details */}
                        {isExpanded && (
                          <div className="px-4 pb-5 pt-2 bg-slate-50/30 border-t border-slate-100/50 space-y-4">
                            <div className="grid grid-cols-2 gap-3 text-xs bg-white rounded-2xl p-3 border border-slate-100/80 shadow-[0_2px_8px_rgba(0,0,0,0.01)]">
                              <div>
                                <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Keluar</p>
                                <p className="font-bold text-slate-700 mt-0.5">
                                  {permit.actual_exit_date ? new Date(permit.actual_exit_date).toLocaleString("id-ID", { day: "numeric", month: "short", hour: "2-digit", minute: "2-digit" }) : "-"}
                                </p>
                              </div>
                              <div>
                                <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Dilaporkan Kembali</p>
                                <p className="font-bold text-slate-700 mt-0.5">
                                  {permit.actual_return_date ? new Date(permit.actual_return_date).toLocaleString("id-ID", { day: "numeric", month: "short", hour: "2-digit", minute: "2-digit" }) : "-"}
                                </p>
                              </div>
                            </div>

                            <div className="bg-white border border-slate-100/80 rounded-2xl p-3 text-xs space-y-2 shadow-[0_2px_8px_rgba(0,0,0,0.01)]">
                              <div>
                                <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Wali / Pelapor</p>
                                <p className="font-bold text-slate-700 mt-0.5">{permit.user?.name} · {permit.user?.phone}</p>
                              </div>
                              {permit.return_latitude && (
                                <div>
                                  <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Lokasi GPS</p>
                                  <a 
                                    href={`https://www.google.com/maps/search/?api=1&query=${permit.return_latitude},${permit.return_longitude}`}
                                    target="_blank" 
                                    rel="noopener noreferrer"
                                    className="text-xs text-blue-600 font-bold hover:underline mt-0.5 inline-block animate-pulse"
                                  >
                                    Lihat di Google Maps ({permit.return_latitude.substring(0,8)}, {permit.return_longitude.substring(0,8)})
                                  </a>
                                </div>
                              )}
                            </div>

                            {/* Return Verification Photos */}
                            <div className="grid grid-cols-2 gap-3">
                              <div className="bg-white border border-slate-100/80 rounded-2xl p-3 text-xs space-y-2.5 shadow-[0_2px_8px_rgba(0,0,0,0.01)]">
                                <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Foto Santri</p>
                                {permit.return_photo_santri ? (
                                  <div 
                                    onClick={(e) => {
                                      e.stopPropagation();
                                      setZoomPhotoUrl(resolveImageUrl(permit.return_photo_santri));
                                    }}
                                    className="relative rounded-xl overflow-hidden border border-slate-100 aspect-video bg-slate-50 flex items-center justify-center group cursor-pointer shadow-sm"
                                  >
                                    <img 
                                      src={resolveImageUrl(permit.return_photo_santri) || ""} 
                                      alt="Foto Santri" 
                                      className="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                                    />
                                    <div className="absolute inset-0 bg-black/10 group-hover:bg-black/25 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                      <span className="text-white text-[9px] font-bold bg-black/60 px-2 py-0.5 rounded-full">Zoom</span>
                                    </div>
                                  </div>
                                ) : (
                                  <div className="py-3 text-center rounded-xl bg-slate-50 text-slate-400">Tidak ada foto</div>
                                )}
                              </div>

                              <div className="bg-white border border-slate-100/80 rounded-2xl p-3 text-xs space-y-2.5 shadow-[0_2px_8px_rgba(0,0,0,0.01)]">
                                <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Foto Pengantar</p>
                                {permit.return_photo_escort ? (
                                  <div 
                                    onClick={(e) => {
                                      e.stopPropagation();
                                      setZoomPhotoUrl(resolveImageUrl(permit.return_photo_escort));
                                    }}
                                    className="relative rounded-xl overflow-hidden border border-slate-100 aspect-video bg-slate-50 flex items-center justify-center group cursor-pointer shadow-sm"
                                  >
                                    <img 
                                      src={resolveImageUrl(permit.return_photo_escort) || ""} 
                                      alt="Foto Pengantar" 
                                      className="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                                    />
                                    <div className="absolute inset-0 bg-black/10 group-hover:bg-black/25 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                      <span className="text-white text-[9px] font-bold bg-black/60 px-2 py-0.5 rounded-full">Zoom</span>
                                    </div>
                                  </div>
                                ) : (
                                  <div className="py-3 text-center rounded-xl bg-slate-50 text-slate-400">Tidak ada foto</div>
                                )}
                              </div>
                            </div>

                            {/* Action Buttons */}
                            <div className="flex gap-2.5">
                              <button
                                onClick={() => setRejectReturnId(permit.id)}
                                className="flex-1 py-3 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl font-bold text-xs transition active:scale-[0.98] border border-rose-100"
                              >
                                Tolak Laporan
                              </button>
                              <button
                                onClick={() => handleApproveReturn(permit.id)}
                                className="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs transition active:scale-[0.98] shadow-sm shadow-emerald-950/10"
                              >
                                Konfirmasi Kembali
                              </button>
                            </div>
                          </div>
                        )}
                      </div>
                    );
                  })}
                </div>
              </div>
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

          {tab === "my-students" && (
            isLoadingMyStudents ? (
              <div className="bg-card rounded-3xl border border-border p-8 flex flex-col items-center justify-center shadow-[var(--shadow-soft)]">
                <Loader2 className="animate-spin text-indigo-600 mb-2" size={28} />
                <p className="text-xs font-semibold text-muted-foreground">Memuat data santri...</p>
              </div>
            ) : myStudentsList.length === 0 ? (
              <div className="bg-card rounded-3xl border border-border p-8 text-center shadow-[var(--shadow-soft)]">
                <p className="text-sm font-bold text-foreground">Tidak Ada Santri</p>
                <p className="text-xs text-muted-foreground mt-1">
                  Anda belum ditunjuk sebagai Host Asrama untuk santri manapun.
                </p>
              </div>
            ) : (
              myStudentsList.map((student: any) => {
                let statusColor = "bg-slate-50 text-slate-700 border-slate-200";
                if (student.status === "Sedang Keluar") {
                  statusColor = "bg-blue-50 text-blue-700 border-blue-200";
                } else if (student.status === "Terlambat") {
                  statusColor = "bg-rose-50 text-rose-700 border-rose-200";
                } else if (student.status === "Di Pondok") {
                  statusColor = "bg-emerald-50 text-emerald-700 border-emerald-200";
                }

                const isHistoryOpen = historyStudentId === student.id;

                return (
                  <div
                    key={student.id}
                    className="w-full bg-card rounded-[24px] border border-border shadow-[var(--shadow-soft)] overflow-hidden transition-all duration-300"
                  >
                    {/* Student Header Button */}
                    <button
                      onClick={() => {
                        setHistoryStudentId(isHistoryOpen ? null : student.id);
                        setExpandedHistoryId(null);
                      }}
                      className="w-full text-left p-4 flex items-center justify-between gap-3 active:bg-slate-50/50 transition-colors"
                    >
                      <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 font-bold overflow-hidden border border-slate-200 shrink-0">
                          {student.avatar ? (
                            <img src={resolveImageUrl(student.avatar) || ""} alt="" className="w-full h-full object-cover" />
                          ) : (
                            student.name.substring(0, 2).toUpperCase()
                          )}
                        </div>
                        <div>
                          <p className="text-sm font-bold text-slate-800 leading-tight">{student.name}</p>
                          <p className="text-[11px] text-slate-400 font-semibold mt-1">NIS: {student.nis} · {student.classroom_name}</p>
                        </div>
                      </div>
                      <div className="flex items-center gap-2 shrink-0">
                        <span className={`px-2.5 py-0.5 rounded-full text-[9px] font-extrabold tracking-wide border uppercase ${statusColor}`}>
                          {student.status}
                        </span>
                        <ChevronDown 
                          size={18} 
                          className={`text-slate-400 transition-transform duration-200 ${isHistoryOpen ? 'rotate-180' : ''}`} 
                        />
                      </div>
                    </button>

                    {/* Expandable History Table Panel */}
                    {isHistoryOpen && (
                      <div className="border-t border-slate-100 bg-slate-50/40 p-4 space-y-3">
                        <div className="flex items-center justify-between">
                          <span className="text-[10px] font-bold text-indigo-600 tracking-normal">Riwayat Perizinan</span>
                          <span className="text-[10px] font-semibold text-slate-400">
                            {isLoadingHistory ? "Memuat..." : `${historyRes?.history?.length || 0} Pengajuan`}
                          </span>
                        </div>

                        {isLoadingHistory ? (
                          <div className="py-8 flex flex-col items-center justify-center">
                            <Loader2 className="animate-spin text-indigo-600 mb-1.5" size={22} />
                            <p className="text-[10px] font-medium text-slate-400">Memuat riwayat...</p>
                          </div>
                        ) : !historyRes?.history || historyRes.history.length === 0 ? (
                          <div className="py-8 text-center bg-white rounded-2xl border border-dashed border-slate-200 p-4">
                            <p className="text-[11px] font-bold text-slate-400">Belum ada riwayat perizinan santri ini.</p>
                          </div>
                        ) : (
                          <div className="rounded-2xl border border-slate-200/60 bg-white overflow-hidden divide-y divide-slate-100 shadow-[var(--shadow-soft)]">
                            {historyRes.history.map((h: any) => {
                              const isRowExpanded = expandedHistoryId === h.id;
                              let statusText = "Pending";
                              let badgeStyle = "bg-amber-50 text-amber-700 border-amber-100 bg-opacity-80";
                              if (h.status === "approved") {
                                statusText = "Disetujui";
                                badgeStyle = "bg-indigo-50 text-indigo-700 border-indigo-100 bg-opacity-80";
                              } else if (h.status === "rejected") {
                                statusText = "Ditolak";
                                badgeStyle = "bg-rose-50 text-rose-700 border-rose-100 bg-opacity-80";
                              } else if (h.status === "out") {
                                statusText = "Keluar";
                                badgeStyle = "bg-blue-50 text-blue-700 border-blue-100 bg-opacity-80";
                              } else if (h.status === "returned") {
                                statusText = "Kembali";
                                badgeStyle = "bg-emerald-50 text-emerald-700 border-emerald-100 bg-opacity-80";
                              }

                              const photos = [];
                              if (h.attachment_photo) photos.push({ url: h.attachment_photo, label: "Lampiran Wali" });
                              if (h.exit_photo_santri) photos.push({ url: h.exit_photo_santri, label: "Foto Keluar (Santri)" });
                              if (h.exit_photo_escort) photos.push({ url: h.exit_photo_escort, label: "Foto Keluar (Penjemput)" });
                              if (h.return_photo_santri) photos.push({ url: h.return_photo_santri, label: "Foto Kembali (Santri)" });
                              if (h.return_photo_escort) photos.push({ url: h.return_photo_escort, label: "Foto Kembali (Pengantar)" });

                              const simpleDate = h.planned_exit_date 
                                ? new Date(h.planned_exit_date).toLocaleDateString("id-ID", {
                                    day: "numeric",
                                    month: "short",
                                    year: "numeric"
                                  })
                                : "-";

                              return (
                                <div key={h.id} className="transition-colors">
                                  {/* Table Row Header (Clickable) */}
                                  <button
                                    onClick={() => setExpandedHistoryId(isRowExpanded ? null : h.id)}
                                    className="w-full text-left py-3.5 px-4 flex items-center justify-between gap-3 hover:bg-slate-50 transition active:bg-slate-100/50"
                                  >
                                    <div className="min-w-0 flex-1">
                                      <p className="text-xs font-bold text-slate-700 truncate uppercase tracking-wide">
                                        {h.permit_type.replace(/_/g, " ")}
                                      </p>
                                      <p className="text-[10px] text-slate-400 font-semibold mt-0.5">{simpleDate}</p>
                                    </div>
                                    <div className="flex items-center gap-2 shrink-0">
                                      <span className={`px-2 py-0.5 rounded-full text-[8px] font-extrabold tracking-wide border uppercase ${badgeStyle}`}>
                                        {statusText}
                                      </span>
                                      <ChevronDown 
                                        size={14} 
                                        className={`text-slate-400 transition-transform duration-200 ${isRowExpanded ? 'rotate-180' : ''}`} 
                                      />
                                    </div>
                                  </button>

                                  {/* Expandable nested details panel */}
                                  {isRowExpanded && (
                                    <div className="bg-slate-50/50 p-4 border-t border-slate-100 space-y-3.5 text-xs animate-slide-down">
                                      <div className="space-y-1">
                                        <p className="text-[9px] font-bold text-slate-400 tracking-normal">Keperluan / Alasan</p>
                                        <p className="text-xs font-semibold text-slate-600 leading-relaxed bg-white rounded-xl p-3 border border-slate-100">
                                          {h.reason}
                                        </p>
                                      </div>

                                      {h.status === "rejected" && h.rejection_reason && (
                                        <div className="space-y-1">
                                          <p className="text-[9px] font-bold text-rose-400 tracking-normal">Alasan Penolakan</p>
                                          <p className="text-xs font-semibold text-rose-700 leading-relaxed bg-rose-50/50 rounded-xl p-3 border border-rose-100/50">
                                            {h.rejection_reason}
                                          </p>
                                        </div>
                                      )}

                                      <div className="grid grid-cols-2 gap-3 pt-3 border-t border-slate-200/60">
                                        <div>
                                          <p className="text-[9px] font-bold text-slate-400 tracking-normal mb-0.5">Rencana Keluar</p>
                                          <p className="font-semibold text-slate-600 text-[11px] leading-snug">{formatFullDate(h.planned_exit_date)}</p>
                                        </div>
                                        <div>
                                          <p className="text-[9px] font-bold text-slate-400 tracking-normal mb-0.5">Rencana Kembali</p>
                                          <p className="font-semibold text-slate-600 text-[11px] leading-snug">{formatFullDate(h.planned_return_date)}</p>
                                        </div>
                                        {h.actual_return_date && (
                                          <div className="col-span-2 bg-emerald-50/50 border border-emerald-100/50 rounded-xl p-2.5 flex justify-between items-center">
                                            <div>
                                              <p className="text-[8px] font-bold text-emerald-600 tracking-normal">Waktu Kembali Aktual</p>
                                              <p className="font-bold text-emerald-700 text-[11px] mt-0.5">{formatFullDate(h.actual_return_date)}</p>
                                            </div>
                                            <span className="text-[9px] font-extrabold uppercase bg-emerald-600 text-white px-2 py-0.5 rounded-md">Tepat Waktu</span>
                                          </div>
                                        )}
                                      </div>

                                      {photos.length > 0 && (
                                        <div className="pt-3 border-t border-slate-200/60 space-y-2">
                                          <p className="text-[9px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1">
                                            <ImageIcon size={11} className="text-slate-400" /> Dokumentasi Foto ({photos.length})
                                          </p>
                                          <div className="grid grid-cols-3 gap-2">
                                            {photos.map((ph, idx) => (
                                              <div
                                                key={idx}
                                                onClick={(e) => {
                                                  e.stopPropagation();
                                                  setZoomPhotoUrl(resolveImageUrl(ph.url));
                                                }}
                                                className="group relative aspect-square rounded-2xl overflow-hidden border border-slate-200/60 bg-slate-50 flex flex-col justify-end cursor-pointer active:scale-95 transition-all shadow-sm"
                                              >
                                                <img
                                                  src={resolveImageUrl(ph.url) || ""}
                                                  alt={ph.label}
                                                  className="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform"
                                                />
                                                <div className="absolute inset-x-0 bottom-0 bg-black/60 py-1 px-1.5 text-[8px] font-extrabold text-white text-center truncate select-none leading-none">
                                                  {ph.label}
                                                </div>
                                              </div>
                                            ))}
                                          </div>
                                        </div>
                                      )}
                                    </div>
                                  )}
                                </div>
                              );
                            })}
                          </div>
                        )}
                      </div>
                    )}
                  </div>
                );
              })
            )
          )}
        </section>

        {/* Rejection Prompt Drawer */}
        {rejectId && (
          <div className="fixed inset-0 bg-slate-900/60 z-50 flex items-end justify-center p-4 backdrop-blur-md transition-all duration-300">
            <div className="bg-card w-full max-w-md rounded-t-[32px] p-6 shadow-[0_-10px_40px_rgba(220,38,38,0.1)] space-y-5 animate-slide-up pb-10 relative overflow-hidden">
              {/* Decorative top red glow */}
              <div className="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-rose-500 to-rose-600"></div>
              
              <div className="flex justify-between items-start pt-1">
                <div className="flex gap-3 items-center">
                  <div className="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 shadow-inner shadow-rose-200">
                    <XCircle size={22} strokeWidth={2.5} />
                  </div>
                  <div>
                    <h3 className="text-[17px] font-extrabold text-slate-800 leading-tight">Penolakan Izin</h3>
                    <p className="text-[11px] font-medium text-slate-500 mt-0.5">Wajib berikan alasan yang jelas</p>
                  </div>
                </div>
                <button
                  onClick={() => setRejectId(null)}
                  className="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 text-lg hover:bg-slate-200 transition active:scale-95 border border-slate-200 font-medium pb-1"
                >
                  &times;
                </button>
              </div>

              <form onSubmit={handleRejectSubmit} className="space-y-5">
                <textarea
                  rows={4}
                  required
                  value={rejectionReason}
                  onChange={(e) => setRejectionReason(e.target.value)}
                  placeholder="Contoh: Santri bersangkutan masih dalam masa sanksi kedisiplinan..."
                  className="w-full bg-slate-50 border border-slate-200 rounded-[20px] px-5 py-4 outline-none text-slate-700 text-[13px] font-medium focus:ring-4 focus:ring-rose-500/10 focus:border-rose-300 focus:bg-white transition resize-none placeholder:text-slate-400"
                />

                <button
                  type="submit"
                  disabled={actionMutation.isPending}
                  className="w-full py-4 rounded-[18px] bg-gradient-to-r from-rose-600 to-rose-500 hover:from-rose-700 hover:to-rose-600 text-white font-extrabold text-[13px] transition-all active:scale-[0.98] shadow-lg shadow-rose-600/25 flex items-center justify-center gap-2"
                >
                  {actionMutation.isPending ? (
                    <>
                      <Loader2 className="animate-spin" size={18} /> Memproses...
                    </>
                  ) : (
                    "Konfirmasi Penolakan"
                  )}
                </button>
              </form>
            </div>
          </div>
        )}

        {/* Return Rejection Prompt Drawer */}
        {rejectReturnId && (
          <div className="fixed inset-0 bg-slate-900/60 z-50 flex items-end justify-center p-4 backdrop-blur-md transition-all duration-300">
            <div className="bg-card w-full max-w-md rounded-t-[32px] p-6 shadow-[0_-10px_40px_rgba(220,38,38,0.1)] space-y-5 animate-slide-up pb-10 relative overflow-hidden">
              {/* Decorative top red glow */}
              <div className="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-rose-500 to-rose-600"></div>
              
              <div className="flex justify-between items-start pt-1">
                <div className="flex gap-3 items-center">
                  <div className="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 shadow-inner shadow-rose-200">
                    <XCircle size={22} strokeWidth={2.5} />
                  </div>
                  <div>
                    <h3 className="text-[17px] font-extrabold text-slate-800 leading-tight">Tolak Laporan Kembali</h3>
                    <p className="text-[11px] font-medium text-slate-500 mt-0.5">Wajib berikan alasan penolakan</p>
                  </div>
                </div>
                <button
                  onClick={() => setRejectReturnId(null)}
                  className="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 text-lg hover:bg-slate-200 transition active:scale-95 border border-slate-200 font-medium pb-1"
                >
                  &times;
                </button>
              </div>

              <form onSubmit={handleRejectReturnSubmit} className="space-y-5">
                <textarea
                  rows={4}
                  required
                  value={rejectionReturnReason}
                  onChange={(e) => setRejectionReturnReason(e.target.value)}
                  placeholder="Contoh: Foto santri tidak jelas atau posisi pengantar tidak sesuai..."
                  className="w-full bg-slate-50 border border-slate-200 rounded-[20px] px-5 py-4 outline-none text-slate-700 text-[13px] font-medium focus:ring-4 focus:ring-rose-500/10 focus:border-rose-300 focus:bg-white transition resize-none placeholder:text-slate-400"
                />

                <button
                  type="submit"
                  disabled={actionReturnMutation.isPending}
                  className="w-full py-4 rounded-[18px] bg-gradient-to-r from-rose-600 to-rose-500 hover:from-rose-700 hover:to-rose-600 text-white font-extrabold text-[13px] transition-all active:scale-[0.98] shadow-lg shadow-rose-600/25 flex items-center justify-center gap-2"
                >
                  {actionReturnMutation.isPending ? (
                    <>
                      <Loader2 className="animate-spin" size={18} /> Memproses...
                    </>
                  ) : (
                    "Konfirmasi Penolakan Laporan"
                  )}
                </button>
              </form>
            </div>
          </div>
        )}



        {/* Photo Zoom Viewer Modal */}
        {zoomPhotoUrl && (
          <div
            onClick={() => setZoomPhotoUrl(null)}
            className="fixed inset-0 z-[60] bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in"
          >
            <button
              onClick={() => setZoomPhotoUrl(null)}
              className="absolute top-5 right-5 w-10 h-10 rounded-full bg-white/10 text-white flex items-center justify-center backdrop-blur active:scale-95 border border-white/20"
            >
              &times;
            </button>
            <img
              src={zoomPhotoUrl}
              alt="Pratinjau Foto"
              className="max-h-[85vh] max-w-full rounded-2xl shadow-2xl object-contain animate-in zoom-in-95 duration-200"
              onClick={(e) => e.stopPropagation()}
            />
          </div>
        )}
      </div>
    </div>
  );
}
