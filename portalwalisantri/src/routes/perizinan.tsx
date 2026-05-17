import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { ArrowLeft, Loader2, Calendar, ClipboardList, CheckCircle2, XCircle, Clock, ExternalLink, QrCode, Camera, Upload } from "lucide-react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { fetchPermits, postPermitRequest } from "@/lib/api";
import { useSantri } from "@/contexts/SantriContext";

export const Route = createFileRoute("/perizinan")({
  component: PerizinanPage,
  head: () => ({ meta: [{ title: "Izin Keluar Santri — CT-Mobile" }] }),
});

function PerizinanPage() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [tab, setTab] = useState<"list" | "request">("list");
  const [activeBarcode, setActiveBarcode] = useState<string | null>(null);
  const [activeBarcodeName, setActiveBarcodeName] = useState<string>("");

  // Form State
  const [permitType, setPermitType] = useState<"keluar_pondok" | "pulang_sementara" | "sakit">("keluar_pondok");
  const [reason, setReason] = useState("");
  const [plannedExit, setPlannedExit] = useState("");
  const [plannedReturn, setPlannedReturn] = useState("");
  const [formError, setFormError] = useState("");
  const [formSuccess, setFormSuccess] = useState("");
  const [attachmentPhoto, setAttachmentPhoto] = useState<string | null>(null);

  const handleAttachmentChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (event) => {
      const img = new Image();
      img.onload = () => {
        const canvas = document.createElement("canvas");
        let width = img.width;
        let height = img.height;

        // Downscale to 1200px max
        const maxDim = 1200;
        if (width > maxDim || height > maxDim) {
          if (width > height) {
            height = Math.round((height * maxDim) / width);
            width = maxDim;
          } else {
            width = Math.round((width * maxDim) / height);
            height = maxDim;
          }
        }

        canvas.width = width;
        canvas.height = height;

        const ctx = canvas.getContext("2d");
        if (ctx) {
          ctx.drawImage(img, 0, 0, width, height);
          const compressed = canvas.toDataURL("image/jpeg", 0.7); // 70% quality
          setAttachmentPhoto(compressed);
        } else {
          setAttachmentPhoto(event.target?.result as string);
        }
      };
      img.src = event.target?.result as string;
    };
    reader.readAsDataURL(file);
  };


  // DateTime Picker Modal States
  const [isPickerOpen, setIsPickerOpen] = useState(false);
  const [pickerCallback, setPickerCallback] = useState<((val: string) => void) | null>(null);
  
  // DateTime Picker Internal Temp States
  const [pickerDate, setPickerDate] = useState<Date>(new Date());
  const [pickerHour, setPickerHour] = useState(12);
  const [pickerMinute, setPickerMinute] = useState(0);
  const [pickerAmPm, setPickerAmPm] = useState<"AM" | "PM">("PM");
  const [pickerTab, setPickerTab] = useState<"date" | "time">("date");
  const [pickerMonthView, setPickerMonthView] = useState<Date>(new Date());
  const [pickerClockMode, setPickerClockMode] = useState<"hours" | "minutes">("hours");

  const openDateTimePicker = (initialValue: string, callback: (val: string) => void) => {
    const initDate = initialValue ? new Date(initialValue) : new Date();
    setPickerDate(initDate);
    let hr = initDate.getHours();
    const ampm = hr >= 12 ? "PM" : "AM";
    hr = hr % 12;
    if (hr === 0) hr = 12;
    setPickerHour(hr);
    setPickerMinute(initDate.getMinutes());
    setPickerAmPm(ampm);
    setPickerMonthView(new Date(initDate.getFullYear(), initDate.getMonth(), 1));
    setPickerTab("date");
    setPickerClockMode("hours");
    setPickerCallback(() => callback);
    setIsPickerOpen(true);
  };

  const saveDateTimePicker = () => {
    if (!pickerCallback) return;
    
    let hr24 = pickerHour % 12;
    if (pickerAmPm === "PM") {
      hr24 += 12;
    }
    
    const finalDate = new Date(
      pickerDate.getFullYear(),
      pickerDate.getMonth(),
      pickerDate.getDate(),
      hr24,
      pickerMinute
    );
    
    const pad = (n: number) => n.toString().padStart(2, '0');
    const formatted = `${finalDate.getFullYear()}-${pad(finalDate.getMonth() + 1)}-${pad(finalDate.getDate())}T${pad(hr24)}:${pad(pickerMinute)}`;
    
    pickerCallback(formatted);
    setIsPickerOpen(false);
  };

  const formatDateTimeIndo = (val: string) => {
    if (!val) return "Pilih Tanggal & Waktu...";
    try {
      const d = new Date(val);
      if (isNaN(d.getTime())) return "Pilih Tanggal & Waktu...";
      return d.toLocaleDateString("id-ID", {
        day: "numeric",
        month: "short",
        year: "numeric",
      }) + " - " + d.toLocaleTimeString("id-ID", {
        hour: "2-digit",
        minute: "2-digit",
      }) + " WIB";
    } catch {
      return "Pilih Tanggal & Waktu...";
    }
  };

  const getDaysInMonth = (monthDate: Date) => {
    const year = monthDate.getFullYear();
    const month = monthDate.getMonth();
    const firstDay = new Date(year, month, 1).getDay();
    const daysCount = new Date(year, month + 1, 0).getDate();
    
    const days: (Date | null)[] = [];
    
    for (let i = 0; i < firstDay; i++) {
      days.push(null);
    }
    
    for (let d = 1; d <= daysCount; d++) {
      days.push(new Date(year, month, d));
    }
    
    return days;
  };

  const getCircularStyle = (index: number, total: number, radiusPercent: number) => {
    const angle = ((index * 360) / total - 90) * (Math.PI / 180);
    const x = Math.cos(angle) * radiusPercent;
    const y = Math.sin(angle) * radiusPercent;
    return {
      left: `calc(50% + ${x}%)`,
      top: `calc(50% + ${y}%)`,
      transform: 'translate(-50%, -50%)',
    };
  };

  // Collective Mode states
  const [requestMode, setRequestMode] = useState<"individual" | "collective">("individual");
  const [selectedStudentIds, setSelectedStudentIds] = useState<string[]>([]);
  const [studentConfigs, setStudentConfigs] = useState<Record<string, {
    permitType: "keluar_pondok" | "pulang_sementara" | "sakit";
    plannedExit: string;
    plannedReturn: string;
  }>>({});
  const [submittingCollective, setSubmittingCollective] = useState(false);

  const { active: activeStudent, santri: allStudents = [], isLoading: isLoadingStudent } = useSantri();

  useEffect(() => {
    if (activeStudent && selectedStudentIds.length === 0) {
      setSelectedStudentIds([activeStudent.id]);
      setStudentConfigs({
        [activeStudent.id]: {
          permitType: "keluar_pondok",
          plannedExit: "",
          plannedReturn: ""
        }
      });
    }
  }, [activeStudent]);

  const toggleStudentSelection = (sId: string) => {
    if (selectedStudentIds.includes(sId)) {
      setSelectedStudentIds(prev => prev.filter(id => id !== sId));
    } else {
      setSelectedStudentIds(prev => [...prev, sId]);
      if (!studentConfigs[sId]) {
        setStudentConfigs(prev => ({
          ...prev,
          [sId]: {
            permitType: "keluar_pondok",
            plannedExit: "",
            plannedReturn: ""
          }
        }));
      }
    }
  };

  const updateStudentConfig = (sId: string, updates: Partial<typeof studentConfigs[string]>) => {
    setStudentConfigs(prev => ({
      ...prev,
      [sId]: {
        ...prev[sId],
        ...updates
      }
    }));
  };

  const { data: permitsRes, isLoading: isLoadingPermits } = useQuery({
    queryKey: ["permits", activeStudent?.id],
    queryFn: async () => {
      const res = await fetchPermits();
      return res.data;
    },
    enabled: !!activeStudent,
  });

  const permits = permitsRes?.permits ?? [];

  const permitMutation = useMutation({
    mutationFn: async (data: any) => {
      const res = await postPermitRequest(data);
      return res.data;
    },
    onSuccess: (data: any) => {
      setFormSuccess(data.message || "Perizinan berhasil diajukan!");
      setReason("");
      setPlannedExit("");
      setPlannedReturn("");
      setAttachmentPhoto(null);
      queryClient.invalidateQueries({ queryKey: ["permits"] });
      setTimeout(() => {
        setFormSuccess("");
        setTab("list");
      }, 2000);
    },
    onError: (err: any) => {
      setFormError(err.response?.data?.message || "Gagal mengajukan izin. Silakan periksa kembali formulir Anda.");
    },
  });

  const handleRequestSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setFormError("");
    setFormSuccess("");

    if (requestMode === "individual") {
      if (!activeStudent) {
        setFormError("Data santri aktif tidak ditemukan.");
        return;
      }

      if (!reason || !plannedExit || !plannedReturn) {
        setFormError("Semua kolom wajib diisi.");
        return;
      }

      permitMutation.mutate({
        student_id: activeStudent.id,
        permit_type: permitType,
        reason,
        planned_exit_date: plannedExit,
        planned_return_date: plannedReturn,
        attachment_photo: attachmentPhoto || undefined,
      });
    } else {
      // Collective Mode
      if (selectedStudentIds.length === 0) {
        setFormError("Harap pilih minimal satu santri.");
        return;
      }
      if (!reason) {
        setFormError("Alasan keperluan wajib diisi.");
        return;
      }

      // Validation
      for (const sId of selectedStudentIds) {
        const config = studentConfigs[sId];
        if (!config?.plannedExit || !config?.plannedReturn) {
          const studentObj = allStudents.find((s) => s.id === sId);
          setFormError(`Harap lengkapi tanggal keluar & kembali untuk ${studentObj?.name || 'santri'}.`);
          return;
        }
      }

      setSubmittingCollective(true);
      const results: string[] = [];
      const errors: string[] = [];

      for (const sId of selectedStudentIds) {
        const config = studentConfigs[sId];
        const studentObj = allStudents.find((s) => s.id === sId);
        try {
          await postPermitRequest({
            student_id: sId,
            permit_type: config.permitType,
            reason,
            planned_exit_date: config.plannedExit,
            planned_return_date: config.plannedReturn,
            attachment_photo: attachmentPhoto || undefined,
          });
          results.push(studentObj?.name || "Santri");
        } catch (err: any) {
          const errMsg = err.response?.data?.message || "Gagal memproses perizinan.";
          errors.push(`${studentObj?.name || 'Santri'}: ${errMsg}`);
        }
      }

      setSubmittingCollective(false);

      if (errors.length > 0) {
        if (results.length > 0) {
          setFormSuccess(`Berhasil diajukan untuk: ${results.join(", ")}.`);
        }
        setFormError(`Beberapa pengajuan gagal:\n${errors.join("\n")}`);
      } else {
        setFormSuccess("Semua pengajuan kolektif berhasil diajukan!");
        queryClient.invalidateQueries({ queryKey: ["permits"] });
        setReason("");
        setAttachmentPhoto(null);
        setTimeout(() => {
          setFormSuccess("");
          setTab("list");
        }, 2500);
      }
    }
  };

  return (
    <div className="min-h-screen w-full flex justify-center bg-secondary">
      <div className="relative w-full max-w-md min-h-screen bg-background pb-32">
        {/* Header */}
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
              <p className="text-[10px] text-white/70 font-extrabold uppercase tracking-wide">Layanan Perizinan</p>
              <h1 className="text-sm font-bold text-white leading-tight">Izin Keluar Santri</h1>
            </div>
          </div>

          <div className="relative mt-5 text-white">
            <p className="text-[10px] text-white/80 uppercase tracking-wide font-extrabold">Santri Aktif</p>
            <h2 className="text-xl font-extrabold mt-0.5 tracking-tight leading-tight">
              {isLoadingStudent ? "Memuat..." : activeStudent?.name ?? "Tanpa Nama"}
            </h2>
            <p className="text-[11px] text-white/75 mt-1 leading-snug">
              Gunakan fitur ini untuk mengajukan perizinan keluar pondok pesantren secara mandiri.
            </p>
          </div>
        </div>

        {/* Tab switcher */}
        <div className="px-6 -mt-8 relative z-10">
          <div className="flex bg-card rounded-[20px] p-1 border border-border shadow-[var(--shadow-soft)]">
            <button
              onClick={() => setTab("list")}
              className={`flex-1 py-3 rounded-[16px] text-xs font-bold transition flex items-center justify-center gap-2 ${
                tab === "list"
                  ? "bg-gradient-to-br from-[#9b1de8] to-[#610a9c] text-white shadow-md"
                  : "text-slate-500 hover:text-slate-800"
              }`}
            >
              <ClipboardList size={16} /> Riwayat Izin
            </button>
            <button
              onClick={() => setTab("request")}
              className={`flex-1 py-3 rounded-[16px] text-xs font-bold transition flex items-center justify-center gap-2 ${
                tab === "request"
                  ? "bg-gradient-to-br from-[#9b1de8] to-[#610a9c] text-white shadow-md"
                  : "text-slate-500 hover:text-slate-800"
              }`}
            >
              <Calendar size={16} /> Pengajuan Baru
            </button>
          </div>
        </div>

        {/* Tab Content */}
        <section className="px-6 mt-6 space-y-4">
          {tab === "list" ? (
            isLoadingPermits ? (
              <div className="bg-card rounded-3xl border border-border p-8 flex flex-col items-center justify-center shadow-[var(--shadow-soft)]">
                <Loader2 className="animate-spin text-[#9b1de8] mb-2" size={28} />
                <p className="text-xs font-semibold text-muted-foreground">Memuat riwayat perizinan...</p>
              </div>
            ) : permits.length === 0 ? (
              <div className="bg-card rounded-3xl border border-border p-8 text-center shadow-[var(--shadow-soft)]">
                <Calendar className="mx-auto text-muted-foreground mb-3" size={32} />
                <p className="text-sm font-bold text-foreground">Belum Ada Pengajuan</p>
                <p className="text-xs text-muted-foreground mt-1">
                  Semua perizinan keluar pondok ananda akan tampil di sini.
                </p>
              </div>
            ) : (
              permits.map((permit: any) => {
                const badgeColor = 
                  permit.status === "pending" ? "bg-amber-50 text-amber-700 border-amber-100" :
                  permit.status === "approved" ? "bg-emerald-50 text-emerald-700 border-emerald-100" :
                  permit.status === "rejected" ? "bg-rose-50 text-rose-700 border-rose-100" :
                  permit.status === "out" ? "bg-blue-50 text-blue-700 border-blue-100" :
                  "bg-slate-50 text-slate-500 border-slate-100";

                const badgeText = 
                  permit.status === "pending" ? "Menunggu Persetujuan" :
                  permit.status === "approved" ? "Disetujui (Siap Scan)" :
                  permit.status === "rejected" ? "Ditolak" :
                  permit.status === "out" ? "Sedang di Luar" :
                  "Telah Kembali";

                return (
                  <div
                    key={permit.id}
                    className="bg-card rounded-3xl border border-border p-5 shadow-[var(--shadow-soft)] hover:shadow-[var(--shadow-card)] transition duration-300"
                  >
                    <div className="flex items-start justify-between gap-3">
                      <div>
                        <span className={`inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border ${badgeColor}`}>
                          {badgeText}
                        </span>
                        <p className="text-sm font-bold text-slate-800 mt-2 capitalize">
                          {permit.permit_type.replace("_", " ")}
                        </p>
                      </div>
                      
                      {permit.status === "approved" && (
                        <button
                          onClick={() => {
                            setActiveBarcode(permit.barcode_token);
                            setActiveBarcodeName(permit.student?.name);
                          }}
                          className="p-2 rounded-xl bg-[#9b1de8]/10 text-[#9b1de8] hover:bg-[#9b1de8]/20 transition flex items-center gap-1 text-[11px] font-bold"
                        >
                          <QrCode size={15} /> Barcode
                        </button>
                      )}
                    </div>

                    <div className="mt-4 grid grid-cols-2 gap-3 text-xs border-t border-slate-100 pt-3">
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

                    {permit.reason && (
                      <div className="mt-3 bg-slate-50 rounded-2xl p-3 border border-slate-100">
                        <p className="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Alasan Perizinan</p>
                        <p className="text-xs text-slate-600 mt-1">{permit.reason}</p>
                      </div>
                    )}

                    {permit.attachment_photo && (
                      <div className="mt-3 bg-slate-50 rounded-2xl p-3 border border-slate-100 flex flex-col gap-1 animate-in fade-in">
                        <p className="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Dokumen Pendukung</p>
                        <a
                          href={`/${permit.attachment_photo}`}
                          target="_blank"
                          rel="noreferrer"
                          className="text-xs text-[#9b1de8] font-bold flex items-center gap-1.5 hover:underline mt-1"
                        >
                          <ExternalLink size={12} /> Lihat Lampiran Dokumen
                        </a>
                      </div>
                    )}

                    {permit.status === "rejected" && permit.rejection_reason && (
                      <div className="mt-3 bg-rose-50 rounded-2xl p-3 border border-rose-100 text-rose-800">
                        <p className="text-[10px] uppercase font-bold text-rose-500 tracking-wider">Alasan Penolakan</p>
                        <p className="text-xs mt-1">{permit.rejection_reason}</p>
                      </div>
                    )}

                    {permit.actual_exit_date && (
                      <div className="mt-3 text-[11px] text-slate-400 flex flex-col gap-1 border-t border-slate-100 pt-3">
                        <p>Keluar: {new Date(permit.actual_exit_date).toLocaleString("id-ID")}</p>
                        {permit.actual_return_date && (
                          <p>Kembali: {new Date(permit.actual_return_date).toLocaleString("id-ID")}</p>
                        )}
                      </div>
                    )}
                  </div>
                );
              })
            )
          ) : (
            // Request Form Tab
            <div className="bg-card rounded-3xl border border-border p-6 shadow-[var(--shadow-soft)] space-y-5">
              <div className="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 className="text-base font-bold text-slate-800">Formulir Pengajuan Izin</h3>
              </div>

              {/* Individual / Collective Switcher */}
              {allStudents.length >= 1 && (
                <div className="flex bg-slate-50 border border-slate-100 rounded-2xl p-1 gap-1">
                  <button
                    type="button"
                    onClick={() => setRequestMode("individual")}
                    className={`flex-1 py-2 text-xs font-bold rounded-xl transition ${
                      requestMode === "individual"
                        ? "bg-white text-slate-800 shadow-sm border border-slate-100"
                        : "text-slate-500 hover:text-slate-800"
                    }`}
                  >
                    Perorangan
                  </button>
                  <button
                    type="button"
                    onClick={() => setRequestMode("collective")}
                    className={`flex-1 py-2 text-xs font-bold rounded-xl transition ${
                      requestMode === "collective"
                        ? "bg-white text-slate-800 shadow-sm border border-slate-100"
                        : "text-slate-500 hover:text-slate-800"
                    }`}
                  >
                    Kolektif ({allStudents.length} Santri)
                  </button>
                </div>
              )}

              <form onSubmit={handleRequestSubmit} className="space-y-5">
                {formError && (
                  <div className="p-3 rounded-xl bg-red-50 text-red-600 border border-red-100 text-xs font-bold text-center whitespace-pre-line">
                    {formError}
                  </div>
                )}
                {formSuccess && (
                  <div className="p-3 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 text-xs font-bold text-center">
                    {formSuccess}
                  </div>
                )}

                {requestMode === "individual" ? (
                  /* --- Individual Form --- */
                  <div className="space-y-4">
                    <div>
                      <label className="text-[11px] font-extrabold text-slate-400/90 mb-1.5 uppercase tracking-wide block">Tipe Perizinan</label>
                      <select
                        value={permitType}
                        onChange={(e: any) => setPermitType(e.target.value)}
                        className="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3.5 outline-none text-slate-800 text-[14px] font-semibold focus:ring-2 focus:ring-[#9b1de8]/20 focus:bg-white transition"
                      >
                        <option value="keluar_pondok">Keluar Sebentar (Beli Buku/Keperluan)</option>
                        <option value="pulang_sementara">Pulang Sementara (Liburan/Sakit)</option>
                        <option value="sakit">Sakit Parah (Perawatan/Rujukan)</option>
                      </select>
                    </div>                     <div>
                      <label className="text-[11px] font-extrabold text-slate-400/90 mb-1.5 uppercase tracking-wide block">Rencana Tanggal Keluar</label>
                      <button
                        type="button"
                        onClick={() => openDateTimePicker(plannedExit, (val) => setPlannedExit(val))}
                        className="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3.5 outline-none text-slate-800 text-[14px] font-semibold flex items-center justify-between hover:bg-slate-100/50 transition text-left"
                      >
                        <span className={plannedExit ? "text-slate-800" : "text-slate-400"}>
                          {formatDateTimeIndo(plannedExit)}
                        </span>
                        <Calendar className="text-[#9b1de8]" size={18} />
                      </button>
                    </div>

                    <div>
                      <label className="text-[11px] font-extrabold text-slate-400/90 mb-1.5 uppercase tracking-wide block">Rencana Tanggal Kembali</label>
                      <button
                        type="button"
                        onClick={() => openDateTimePicker(plannedReturn, (val) => setPlannedReturn(val))}
                        className="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3.5 outline-none text-slate-800 text-[14px] font-semibold flex items-center justify-between hover:bg-slate-100/50 transition text-left"
                      >
                        <span className={plannedReturn ? "text-slate-800" : "text-slate-400"}>
                          {formatDateTimeIndo(plannedReturn)}
                        </span>
                        <Calendar className="text-[#9b1de8]" size={18} />
                      </button>
                    </div>
                  </div>
                ) : (
                  /* --- Collective Form --- */
                  <div className="space-y-5 animate-in fade-in duration-300">
                    {/* Student Selection Checklist */}
                    <div className="space-y-2">
                      <span className="text-[11px] font-extrabold text-slate-400/90 mb-1 block uppercase tracking-wide">
                        Pilih Santri yang Diajukan
                      </span>
                      <div className="grid grid-cols-1 gap-2">
                        {allStudents.map((s) => {
                          const isChecked = selectedStudentIds.includes(s.id);
                          return (
                            <button
                              type="button"
                              key={s.id}
                              onClick={() => toggleStudentSelection(s.id)}
                              className={`flex items-center justify-between p-3 rounded-2xl border transition text-left ${
                                isChecked
                                  ? "bg-[#9b1de8]/5 border-[#9b1de8]/30 shadow-sm"
                                  : "bg-slate-50/50 border-slate-100 hover:bg-slate-50"
                              }`}
                            >
                              <div className="flex items-center gap-3">
                                <div className="w-8 h-8 rounded-xl bg-gradient-to-br from-[#9b1de8] to-[#610a9c] text-white flex items-center justify-center font-bold text-xs">
                                  {s.initials}
                                </div>
                                <div>
                                  <p className="text-xs font-extrabold text-slate-800 leading-tight">{s.name}</p>
                                  <p className="text-[10px] text-slate-400 font-medium">Kelas: {s.className}</p>
                                </div>
                              </div>
                              <div className={`w-4 h-4 rounded-full border flex items-center justify-center transition-all ${
                                isChecked 
                                  ? "bg-[#9b1de8] border-[#9b1de8] text-white" 
                                  : "border-slate-300 bg-white"
                              }`}>
                                {isChecked && (
                                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={3} stroke="currentColor" className="w-2.5 h-2.5">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                  </svg>
                                )}
                              </div>
                            </button>
                          );
                        })}
                      </div>
                    </div>

                    {/* Student Config Cards */}
                    <div className="space-y-4">
                      {selectedStudentIds.map((sId) => {
                        const s = allStudents.find((student) => student.id === sId);
                        if (!s) return null;
                        const config = studentConfigs[sId] || { permitType: "keluar_pondok", plannedExit: "", plannedReturn: "" };
                        
                        return (
                          <div key={sId} className="bg-slate-50 border border-slate-100 rounded-3xl p-4 space-y-3.5 relative overflow-hidden animate-in slide-in-from-top-4 duration-300">
                            <div className="absolute left-0 top-0 bottom-0 w-1 bg-[#9b1de8]"></div>
                            
                            <div className="flex items-center gap-2 pb-2 border-b border-slate-100">
                              <div className="w-6 h-6 rounded-lg bg-[#9b1de8]/15 text-[#9b1de8] flex items-center justify-center font-bold text-[10px]">
                                {s.initials}
                              </div>
                              <span className="text-xs font-extrabold text-slate-800 leading-none">{s.name}</span>
                            </div>

                            <div>
                              <label className="text-[10px] font-extrabold text-slate-400 mb-1 uppercase tracking-wide block">Tipe Perizinan</label>
                              <select
                                value={config.permitType}
                                onChange={(e: any) => updateStudentConfig(sId, { permitType: e.target.value })}
                                className="w-full bg-white border border-slate-150 rounded-xl px-3 py-2 outline-none text-slate-800 text-[13px] font-semibold focus:ring-1 focus:ring-[#9b1de8]/20 transition"
                              >
                                <option value="keluar_pondok">Keluar Sebentar (Beli Buku/Keperluan)</option>
                                <option value="pulang_sementara">Pulang Sementara (Liburan/Sakit)</option>
                                <option value="sakit">Sakit Parah (Perawatan/Rujukan)</option>
                              </select>
                            </div>

                            <div className="grid grid-cols-1 gap-2.5">
                              <div>
                                <label className="text-[10px] font-extrabold text-slate-400 mb-1 uppercase tracking-wide block">Rencana Keluar</label>
                                <button
                                  type="button"
                                  onClick={() => openDateTimePicker(config.plannedExit, (val) => updateStudentConfig(sId, { plannedExit: val }))}
                                  className="w-full bg-white border border-slate-150 rounded-xl px-3 py-2 outline-none text-slate-800 text-[13px] font-semibold flex items-center justify-between hover:bg-slate-50 transition text-left"
                                >
                                  <span className={config.plannedExit ? "text-slate-800" : "text-slate-400"}>
                                    {formatDateTimeIndo(config.plannedExit)}
                                  </span>
                                  <Calendar className="text-[#9b1de8]" size={15} />
                                </button>
                              </div>

                              <div>
                                <label className="text-[10px] font-extrabold text-slate-400 mb-1 uppercase tracking-wide block">Rencana Kembali</label>
                                <button
                                  type="button"
                                  onClick={() => openDateTimePicker(config.plannedReturn, (val) => updateStudentConfig(sId, { plannedReturn: val }))}
                                  className="w-full bg-white border border-slate-150 rounded-xl px-3 py-2 outline-none text-slate-800 text-[13px] font-semibold flex items-center justify-between hover:bg-slate-50 transition text-left"
                                >
                                  <span className={config.plannedReturn ? "text-slate-800" : "text-slate-400"}>
                                    {formatDateTimeIndo(config.plannedReturn)}
                                  </span>
                                  <Calendar className="text-[#9b1de8]" size={15} />
                                </button>
                              </div>
                            </div>
                          </div>
                        );
                      })}
                    </div>
                  </div>
                )}

                <div>
                  <label className="text-[11px] font-extrabold text-slate-400/90 mb-1.5 uppercase tracking-wide block">Alasan Keperluan</label>
                  <textarea
                    rows={3}
                    value={reason}
                    onChange={(e) => setReason(e.target.value)}
                    placeholder="Tulis alasan izin keluar ananda secara jelas dan transparan..."
                    className="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3.5 outline-none text-slate-800 text-[14px] font-medium focus:ring-2 focus:ring-[#9b1de8]/20 focus:bg-white transition resize-none"
                  />
                </div>

                {/* Upload Dokumen Pendukung (Opsional) */}
                <div className="space-y-2">
                  <label className="text-[11px] font-extrabold text-slate-400/90 uppercase tracking-wide block">
                    Dokumen Pendukung (Opsional)
                  </label>
                  
                  {attachmentPhoto ? (
                    <div className="relative rounded-2xl border border-slate-100 overflow-hidden bg-slate-50 flex items-center justify-center p-3 animate-in fade-in duration-300">
                      <img
                        src={attachmentPhoto}
                        alt="Dokumen Pendukung"
                        className="max-h-40 rounded-xl object-contain shadow-sm border border-slate-150"
                      />
                      <button
                        type="button"
                        onClick={() => setAttachmentPhoto(null)}
                        className="absolute top-2 right-2 bg-red-600/90 text-white w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs hover:bg-red-700 transition"
                      >
                        ×
                      </button>
                    </div>
                  ) : (
                    <div className="grid grid-cols-2 gap-3">
                      {/* Capture with Camera Button */}
                      <label className="flex flex-col items-center justify-center p-4 border border-dashed border-slate-200 bg-slate-50 rounded-2xl hover:bg-slate-100/70 transition cursor-pointer text-center group active:scale-95">
                        <Camera className="text-slate-400 group-hover:text-[#9b1de8] transition mb-1" size={20} />
                        <span className="text-[10px] font-extrabold text-slate-500 uppercase tracking-wide">Ambil Foto</span>
                        <input
                          type="file"
                          accept="image/*"
                          capture="environment"
                          onChange={handleAttachmentChange}
                          className="hidden"
                        />
                      </label>

                      {/* Upload File Button */}
                      <label className="flex flex-col items-center justify-center p-4 border border-dashed border-slate-200 bg-slate-50 rounded-2xl hover:bg-slate-100/70 transition cursor-pointer text-center group active:scale-95">
                        <Upload className="text-slate-400 group-hover:text-[#9b1de8] transition mb-1" size={20} />
                        <span className="text-[10px] font-extrabold text-slate-500 uppercase tracking-wide">Pilih File</span>
                        <input
                          type="file"
                          accept="image/*"
                          onChange={handleAttachmentChange}
                          className="hidden"
                        />
                      </label>
                    </div>
                  )}
                  <p className="text-[10px] text-slate-400 italic">
                    *Foto surat keterangan dokter, undangan keluarga, dll. (Auto-kompresi 70% kualitas JPEG)
                  </p>
                </div>

                <button
                  type="submit"
                  disabled={permitMutation.isPending || submittingCollective}
                  className="w-full py-4 rounded-[20px] bg-gradient-to-r from-[#9b1de8] to-[#610a9c] text-white font-bold text-[14px] shadow-[0_8px_25px_rgba(155,29,232,0.3)] transition active:scale-[0.98] disabled:opacity-70 flex items-center justify-center"
                >
                  {permitMutation.isPending || submittingCollective ? (
                    <Loader2 className="animate-spin" size={18} />
                  ) : (
                    "Ajukan Perizinan"
                  )}
                </button>
              </form>
            </div>
          )}
        </section>

        {/* QR Code Modal Drawer */}
        {activeBarcode && (
          <div className="fixed inset-0 bg-black/60 z-50 flex items-end justify-center p-4 backdrop-blur-sm animate-fade-in">
            <div className="bg-card w-full max-w-md rounded-t-[2.5rem] p-6 shadow-2xl space-y-6 animate-slide-up pb-10">
              <div className="flex justify-between items-center">
                <div>
                  <p className="text-[11px] text-slate-400 font-bold uppercase tracking-widest">Pindai di Gerbang</p>
                  <p className="text-base font-bold text-slate-800">{activeBarcodeName}</p>
                </div>
                <button
                  onClick={() => setActiveBarcode(null)}
                  className="w-8 h-8 rounded-full bg-secondary border border-border flex items-center justify-center text-slate-500 font-bold hover:bg-slate-100 transition"
                >
                  ×
                </button>
              </div>

              <div className="bg-slate-50 border border-dashed border-slate-200 rounded-[2rem] p-8 flex flex-col items-center justify-center">
                {/* Dynamically generate QR code from unique permit token */}
                <img
                  src={`https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${activeBarcode}`}
                  alt="QR Code"
                  className="w-48 h-48 bg-white p-2 rounded-2xl border border-slate-100 shadow-md"
                />
                
                <div className="mt-4 text-center">
                  <p className="text-xs font-mono font-bold text-[#9b1de8] tracking-widest">{activeBarcode}</p>
                  <p className="text-[10px] text-slate-400 mt-1 font-semibold leading-relaxed">
                    Tunjukkan QR code ini kepada Ustadz atau Satpam di pos gerbang luar pondok pesantren.
                  </p>
                </div>
              </div>
            </div>
          </div>
        )}

        {/* Premium DateTime Picker Modal Drawer */}
        {isPickerOpen && (
          <div className="fixed inset-0 bg-black/60 z-50 flex items-end justify-center p-4 backdrop-blur-sm animate-fade-in">
            <div className="bg-card w-full max-w-md rounded-t-[2.5rem] p-6 shadow-2xl space-y-5 animate-slide-up pb-10">
              
              {/* Header */}
              <div className="flex justify-between items-center border-b border-slate-100 pb-3">
                <div>
                  <p className="text-[11px] text-slate-400 font-extrabold uppercase tracking-wide">Pengaturan Jadwal</p>
                  <p className="text-sm font-bold text-slate-800">Setel Tanggal & Waktu</p>
                </div>
                <button
                  type="button"
                  onClick={() => setIsPickerOpen(false)}
                  className="w-8 h-8 rounded-full bg-secondary border border-border flex items-center justify-center text-slate-500 font-bold hover:bg-slate-100 transition"
                >
                  ×
                </button>
              </div>

              {/* Selection Displays & Tabs Switcher */}
              <div className="grid grid-cols-2 bg-slate-50 border border-slate-100 rounded-2xl p-1 gap-1">
                <button
                  type="button"
                  onClick={() => setPickerTab("date")}
                  className={`py-3 px-2 rounded-xl flex flex-col items-center justify-center transition ${
                    pickerTab === "date"
                      ? "bg-white text-slate-800 shadow-sm border border-slate-100"
                      : "text-slate-500 hover:text-slate-800"
                  }`}
                >
                  <span className="text-[10px] font-extrabold uppercase tracking-wide text-slate-400">Tanggal</span>
                  <span className="text-xs font-bold mt-0.5 truncate max-w-full">
                    {pickerDate.toLocaleDateString("id-ID", { day: "numeric", month: "short", year: "numeric" })}
                  </span>
                </button>
                <button
                  type="button"
                  onClick={() => setPickerTab("time")}
                  className={`py-3 px-2 rounded-xl flex flex-col items-center justify-center transition ${
                    pickerTab === "time"
                      ? "bg-white text-slate-800 shadow-sm border border-slate-100"
                      : "text-slate-500 hover:text-slate-800"
                  }`}
                >
                  <span className="text-[10px] font-extrabold uppercase tracking-wide text-slate-400">Waktu</span>
                  <span className="text-xs font-bold mt-0.5">
                    {pickerHour.toString().padStart(2, '0')}:{pickerMinute.toString().padStart(2, '0')} {pickerAmPm}
                  </span>
                </button>
              </div>

              {/* Content Panels */}
              <div className="min-h-[280px]">
                {pickerTab === "date" ? (
                  /* --- CALENDAR PANEL --- */
                  <div className="space-y-3 animate-in fade-in duration-200">
                    <div className="flex justify-between items-center px-1">
                      <button
                        type="button"
                        onClick={() => setPickerMonthView(new Date(pickerMonthView.getFullYear(), pickerMonthView.getMonth() - 1, 1))}
                        className="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 font-bold active:scale-95 transition"
                      >
                        &larr;
                      </button>
                      <span className="text-xs font-extrabold uppercase tracking-wider text-slate-700">
                        {pickerMonthView.toLocaleDateString("id-ID", { month: "long", year: "numeric" })}
                      </span>
                      <button
                        type="button"
                        onClick={() => setPickerMonthView(new Date(pickerMonthView.getFullYear(), pickerMonthView.getMonth() + 1, 1))}
                        className="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 font-bold active:scale-95 transition"
                      >
                        &rarr;
                      </button>
                    </div>

                    <div className="grid grid-cols-7 gap-1 text-center">
                      {["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"].map((dayName) => (
                        <span key={dayName} className="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider py-1">
                          {dayName}
                        </span>
                      ))}
                      
                      {getDaysInMonth(pickerMonthView).map((day, idx) => {
                        if (!day) return <div key={`empty-${idx}`} />;
                        const isSelected = day.toDateString() === pickerDate.toDateString();
                        const isToday = day.toDateString() === new Date().toDateString();
                        return (
                          <button
                            key={day.toISOString()}
                            type="button"
                            onClick={() => setPickerDate(day)}
                            className={`aspect-square w-full rounded-xl flex items-center justify-center text-xs transition active:scale-95 ${
                              isSelected
                                ? "bg-gradient-to-br from-[#9b1de8] to-[#610a9c] text-white shadow-md font-bold scale-105"
                                : isToday
                                  ? "border border-[#9b1de8] text-[#9b1de8] font-bold"
                                  : "text-slate-700 hover:bg-slate-50 font-semibold"
                            }`}
                          >
                            {day.getDate()}
                          </button>
                        );
                      })}
                    </div>
                  </div>
                ) : (
                  /* --- ANALOG CLOCK PANEL --- */
                  <div className="space-y-4 animate-in fade-in duration-200">
                    
                    {/* Dial Selector (Hour vs Minute) & AM/PM Selector */}
                    <div className="flex justify-between items-center px-4">
                      <div className="flex bg-slate-100/80 border border-slate-200 rounded-xl p-0.5 text-xs font-bold">
                        <button
                          type="button"
                          onClick={() => setPickerClockMode("hours")}
                          className={`px-3 py-1 rounded-lg transition ${
                            pickerClockMode === "hours" ? "bg-white text-slate-800 shadow-sm" : "text-slate-500"
                          }`}
                        >
                          Jam
                        </button>
                        <button
                          type="button"
                          onClick={() => setPickerClockMode("minutes")}
                          className={`px-3 py-1 rounded-lg transition ${
                            pickerClockMode === "minutes" ? "bg-white text-slate-800 shadow-sm" : "text-slate-500"
                          }`}
                        >
                          Menit
                        </button>
                      </div>

                      <div className="flex bg-slate-100/80 border border-slate-200 rounded-xl p-0.5 text-xs font-bold">
                        <button
                          type="button"
                          onClick={() => setPickerAmPm("AM")}
                          className={`px-3 py-1 rounded-lg transition ${
                            pickerAmPm === "AM" ? "bg-[#9b1de8] text-white shadow-sm animate-none" : "text-slate-500"
                          }`}
                        >
                          AM
                        </button>
                        <button
                          type="button"
                          onClick={() => setPickerAmPm("PM")}
                          className={`px-3 py-1 rounded-lg transition ${
                            pickerAmPm === "PM" ? "bg-[#9b1de8] text-white shadow-sm animate-none" : "text-slate-500"
                          }`}
                        >
                          PM
                        </button>
                      </div>
                    </div>

                    {/* Circular Clock Face */}
                    <div className="relative w-52 h-52 mx-auto rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center shadow-[inset_0_4px_10px_rgba(0,0,0,0.03)]">
                      
                      {/* Hands */}
                      {/* Hour hand */}
                      <div
                        className="absolute w-1 bg-slate-700 rounded-full"
                        style={{
                          height: "30%",
                          bottom: "50%",
                          left: "calc(50% - 2px)",
                          transformOrigin: "bottom center",
                          transform: `rotate(${(pickerHour % 12) * 30 + pickerMinute * 0.5}deg)`,
                          transition: "transform 0.4s cubic-bezier(0.4, 0, 0.2, 1)",
                        }}
                      />
                      {/* Minute hand */}
                      <div
                        className="absolute w-0.5 bg-[#9b1de8] rounded-full"
                        style={{
                          height: "42%",
                          bottom: "50%",
                          left: "calc(50% - 1px)",
                          transformOrigin: "bottom center",
                          transform: `rotate(${pickerMinute * 6}deg)`,
                          transition: "transform 0.4s cubic-bezier(0.4, 0, 0.2, 1)",
                        }}
                      />
                      {/* Center cap */}
                      <div className="absolute w-3 h-3 rounded-full bg-[#9b1de8] border-2 border-white shadow-sm" />

                      {/* Render Clock Numbers */}
                      {pickerClockMode === "hours" ? (
                        [12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11].map((hr) => {
                          const isSelected = pickerHour === hr;
                          const style = getCircularStyle(hr, 12, 37);
                          return (
                            <button
                              key={`hr-${hr}`}
                              type="button"
                              onClick={() => {
                                setPickerHour(hr);
                                setPickerClockMode("minutes");
                              }}
                              style={style}
                              className={`absolute w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all ${
                                isSelected
                                  ? "bg-[#9b1de8] text-white shadow-md font-extrabold scale-110"
                                  : "text-slate-500 hover:bg-[#9b1de8]/10 hover:text-[#9b1de8]"
                              }`}
                            >
                              {hr}
                            </button>
                          );
                        })
                      ) : (
                        [0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55].map((min, idx) => {
                          const isSelected = pickerMinute === min;
                          const style = getCircularStyle(idx === 0 ? 12 : idx, 12, 37);
                          return (
                            <button
                              key={`min-${min}`}
                              type="button"
                              onClick={() => setPickerMinute(min)}
                              style={style}
                              className={`absolute w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold transition-all ${
                                isSelected
                                  ? "bg-[#9b1de8] text-white shadow-md font-extrabold scale-110"
                                  : "text-slate-500 hover:bg-[#9b1de8]/10 hover:text-[#9b1de8]"
                              }`}
                            >
                              {min.toString().padStart(2, '0')}
                            </button>
                          );
                        })
                      )}
                    </div>

                    {/* Exact Minute Precision Tuner */}
                    <div className="flex items-center justify-center gap-3 pt-3 border-t border-slate-100 mt-2">
                      <button
                        type="button"
                        onClick={() => setPickerMinute((prev) => (prev > 0 ? prev - 1 : 59))}
                        className="w-8 h-8 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center font-bold text-slate-600 hover:bg-slate-100 active:scale-95 transition"
                      >
                        -
                      </button>
                      <div className="text-center min-w-[70px]">
                        <span className="text-[9px] uppercase font-bold text-slate-400 block tracking-wider">Menit</span>
                        <span className="text-sm font-extrabold text-slate-700 font-mono">{pickerMinute.toString().padStart(2, '0')}</span>
                      </div>
                      <button
                        type="button"
                        onClick={() => setPickerMinute((prev) => (prev < 59 ? prev + 1 : 0))}
                        className="w-8 h-8 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center font-bold text-slate-600 hover:bg-slate-100 active:scale-95 transition"
                      >
                        +
                      </button>
                    </div>

                  </div>
                )}
              </div>

              {/* Footer Actions */}
              <div className="flex gap-3 pt-2">
                <button
                  type="button"
                  onClick={() => setIsPickerOpen(false)}
                  className="flex-1 py-3.5 rounded-2xl border border-slate-200 bg-white text-slate-600 text-xs font-bold hover:bg-slate-50 active:scale-[0.98] transition text-center shadow-sm"
                >
                  Batal
                </button>
                <button
                  type="button"
                  onClick={saveDateTimePicker}
                  className="flex-1 py-3.5 rounded-2xl bg-gradient-to-r from-[#9b1de8] to-[#610a9c] text-white text-xs font-bold hover:opacity-95 active:scale-[0.98] transition text-center shadow-md shadow-[#9b1de8]/20"
                >
                  Simpan Jadwal
                </button>
              </div>

            </div>
          </div>
        )}
      </div>
    </div>
  );
}
