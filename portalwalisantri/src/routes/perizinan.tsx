import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { ArrowLeft, Loader2, Calendar, ClipboardList, CheckCircle2, XCircle, Clock, ExternalLink, QrCode, Camera, Upload, ArrowLeftCircle } from "lucide-react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { fetchPermits, postPermitRequest, postReportReturn } from "@/lib/api";
import { useSantri } from "@/contexts/SantriContext";
import { Text } from "@/components/Text";

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
  
  // Expandable and Pagination States
  const [expandedPermitId, setExpandedPermitId] = useState<string | number | null>(null);
  const [currentPage, setCurrentPage] = useState(1);

  // Form State
  const [permitType, setPermitType] = useState<"keluar_pondok" | "pulang_sementara" | "sakit">("keluar_pondok");
  const [reason, setReason] = useState("");
  const [plannedExit, setPlannedExit] = useState("");
  const [plannedReturn, setPlannedReturn] = useState("");
  const [formError, setFormError] = useState("");
  const [formSuccess, setFormSuccess] = useState("");
  const [attachmentPhoto, setAttachmentPhoto] = useState<string | null>(null);
  const [viewingDocumentUrl, setViewingDocumentUrl] = useState<string | null>(null);

  // Return Reporting States
  const [reportingPermitId, setReportingPermitId] = useState<string | number | null>(null);
  const [returnPhotoSantri, setReturnPhotoSantri] = useState<string | null>(null);
  const [returnPhotoEscort, setReturnPhotoEscort] = useState<string | null>(null);
  const [isReportingSubmit, setIsReportingSubmit] = useState(false);
  const [reportError, setReportError] = useState("");
  const [successDialog, setSuccessDialog] = useState<{ open: boolean; message: string }>({ open: false, message: "" });

  const handlePhotoUpload = (file: File, callback: (base64: string) => void) => {
    const reader = new FileReader();
    reader.onload = (event) => {
      const img = new Image();
      img.onload = () => {
        const canvas = document.createElement("canvas");
        let width = img.width;
        let height = img.height;
        const maxDim = 800; // Small size for return photos to save server storage
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
          const compressed = canvas.toDataURL("image/jpeg", 0.7);
          callback(compressed);
        } else {
          callback(event.target?.result as string);
        }
      };
      img.src = event.target?.result as string;
    };
    reader.readAsDataURL(file);
  };

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

  const formatHeaderDate = (dateStr: string) => {
    if (!dateStr) return "-";
    try {
      const d = new Date(dateStr);
      if (isNaN(d.getTime())) return "-";
      const dateFormatted = d.toLocaleDateString("id-ID", {
        day: "numeric",
        month: "short"
      });
      const timeFormatted = d.toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" });
      return `${dateFormatted}, ${timeFormatted}`;
    } catch {
      return "-";
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

  useEffect(() => {
    setCurrentPage(1);
    setExpandedPermitId(null);
  }, [activeStudent, tab]);

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
    refetchInterval: 5000, // Auto refresh permits status every 5 seconds
  });

  const permits = permitsRes?.permits ?? [];

  // Calculate statistics
  const totalPermits = permits.length;
  
  const onTimeCount = permits.filter((p: any) => {
    if (!p.actual_return_date) return false;
    const actual = new Date(p.actual_return_date).getTime();
    const planned = new Date(p.planned_return_date).getTime();
    return actual <= planned;
  }).length;
  
  const lateCount = permits.filter((p: any) => {
    if (!p.actual_return_date) {
      if (p.status === "out") {
        const planned = new Date(p.planned_return_date).getTime();
        return Date.now() > planned;
      }
      return false;
    }
    const actual = new Date(p.actual_return_date).getTime();
    const planned = new Date(p.planned_return_date).getTime();
    return actual > planned;
  }).length;

  const itemsPerPage = 3;
  const totalPages = Math.ceil(permits.length / itemsPerPage);
  const paginatedPermits = permits.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);

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
          className="relative px-5 pt-12 pb-24 rounded-b-[2rem] overflow-hidden"
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
        <div className="px-5 -mt-8 relative z-10">
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
        <section className="px-5 mt-4 space-y-3">
          {tab === "list" ? (
            isLoadingPermits ? (
              <div className="bg-card rounded-[24px] border-none p-8 flex flex-col items-center justify-center shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                <Loader2 className="animate-spin text-[#2563EB] mb-2" size={28} />
                <Text.Caption className="font-semibold text-slate-500">Memuat riwayat perizinan...</Text.Caption>
              </div>
            ) : permits.length === 0 ? (
              <div className="bg-card rounded-[24px] border-none p-8 text-center shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                <Calendar className="mx-auto text-slate-400 mb-3" size={32} />
                <Text.H2 className="text-slate-800">Belum Ada Pengajuan</Text.H2>
                <Text.Body className="text-slate-500 mt-1">
                  Semua perizinan keluar pondok ananda akan tampil di sini.
                </Text.Body>
              </div>
            ) : (
              <div className="space-y-3">
                {/* Summary Cards Grid */}
                <div className="grid grid-cols-3 gap-2">
                  <div className="bg-card rounded-[24px] p-3 flex flex-col items-center justify-center shadow-[0_8px_30px_rgb(0,0,0,0.04)] border-none">
                    <Text.Label className="text-slate-400 text-[10px] !normal-case !tracking-normal font-semibold">Total Izin</Text.Label>
                    <Text.H1 className="text-slate-800 mt-1">{totalPermits}</Text.H1>
                  </div>
                  <div className="bg-card rounded-[24px] p-3 flex flex-col items-center justify-center shadow-[0_8px_30px_rgb(0,0,0,0.04)] border-none">
                    <Text.Label className="text-emerald-600 text-[10px] !normal-case !tracking-normal font-semibold">Tepat Waktu</Text.Label>
                    <Text.Amount className="text-[22px] mt-1">{onTimeCount}</Text.Amount>
                  </div>
                  <div className="bg-card rounded-[24px] p-3 flex flex-col items-center justify-center shadow-[0_8px_30px_rgb(0,0,0,0.04)] border-none">
                    <Text.Label className="text-red-600 text-[10px] !normal-case !tracking-normal font-semibold">Terlambat</Text.Label>
                    <Text.Amount className="text-[22px] text-red-600 mt-1">{lateCount}</Text.Amount>
                  </div>
                </div>

                {/* Simple Data Table / Expandable Panel */}
                <div className="bg-card rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden divide-y divide-slate-100">
                  {/* Table Body */}
                  {paginatedPermits.map((permit: any) => {
                    const isExpanded = expandedPermitId === permit.id;
                    
                    const badgeColor = 
                      permit.status === "pending" ? "bg-amber-50 text-amber-700 border-amber-100" :
                      permit.status === "approved" ? "bg-emerald-50 text-emerald-700 border-emerald-100" :
                      permit.status === "rejected" ? "bg-rose-50 text-rose-700 border-rose-100" :
                      permit.status === "out" ? "bg-blue-50 text-blue-700 border-blue-100" :
                      permit.status === "pending_return" ? "bg-purple-50 text-purple-700 border-purple-100" :
                      "bg-slate-50 text-slate-500 border-slate-100";

                    const badgeText = 
                      permit.status === "pending" ? "Pending" :
                      permit.status === "approved" ? "Disetujui" :
                      permit.status === "rejected" ? "Ditolak" :
                      permit.status === "out" ? "Keluar" :
                      permit.status === "pending_return" ? "Proses Kembali" :
                      "Kembali";

                    const exitText = formatHeaderDate(permit.planned_exit_date);
                    const returnText = formatHeaderDate(permit.planned_return_date);
                    const actualExitText = permit.actual_exit_date ? formatHeaderDate(permit.actual_exit_date) : null;
                    const actualReturnText = permit.actual_return_date ? formatHeaderDate(permit.actual_return_date) : null;

                    const getReturnStatus = () => {
                      if (permit.status === "returned" || permit.status === "pending_return") {
                        const actualReturn = new Date(permit.actual_return_date);
                        const plannedReturn = new Date(permit.planned_return_date);
                        if (!isNaN(actualReturn.getTime()) && !isNaN(plannedReturn.getTime())) {
                          return actualReturn <= plannedReturn ? "TEPAT WAKTU" : "TERLAMBAT";
                        }
                      }
                      if (permit.status === "out") {
                        const now = new Date();
                        const plannedReturn = new Date(permit.planned_return_date);
                        if (!isNaN(plannedReturn.getTime()) && now > plannedReturn) {
                          return "TERLAMBAT";
                        }
                      }
                      return null;
                    };
                    const returnStatus = getReturnStatus();

                    return (
                      <div key={permit.id} className="transition duration-200">
                        {/* Row Header - Clickable */}
                        <button
                          type="button"
                          onClick={() => setExpandedPermitId(isExpanded ? null : permit.id)}
                          className="w-full flex items-center justify-between gap-3 px-5 py-3 hover:bg-slate-50/40 active:bg-slate-50 text-left transition"
                        >
                          <div className="min-w-0 flex-1 space-y-1">
                            <Text.Body className="font-semibold text-slate-800 capitalize leading-tight truncate">
                              {permit.permit_type.replace("_", " ")}
                            </Text.Body>
                            
                            {/* Rencana */}
                            <div className="flex items-center gap-1.5 text-[9px] text-slate-400 font-semibold flex-wrap">
                              <span className="bg-slate-100 text-slate-500 px-1 py-0.5 rounded text-[8px] font-extrabold uppercase leading-none">Rencana</span>
                              <span className="text-slate-600 font-extrabold">{exitText}</span>
                              <span className="text-slate-300">➔</span>
                              <span className="text-slate-600 font-extrabold">{returnText}</span>
                            </div>

                            {/* Realisasi */}
                            {(permit.status === "out" || permit.status === "returned" || permit.status === "pending_return" || !!permit.actual_exit_date || !!permit.actual_return_date) && (
                              <div className="flex items-center gap-1.5 text-[9px] text-slate-400 font-semibold flex-wrap">
                                <span className={`px-1 py-0.5 rounded text-[8px] font-extrabold uppercase leading-none ${
                                  permit.status === "returned" ? "bg-emerald-50 text-emerald-600" : "bg-blue-50 text-blue-600"
                                }`}>
                                  Realisasi
                                </span>
                                <span className="text-slate-600 font-extrabold">{actualExitText || "-"}</span>
                                <span className="text-slate-300">➔</span>
                                {actualReturnText ? (
                                  <span className="text-slate-600 font-extrabold">{actualReturnText}</span>
                                ) : permit.status === "returned" ? (
                                  <span className="text-slate-600 font-extrabold">-</span>
                                ) : (
                                  <span className="text-blue-600 font-extrabold animate-pulse">Sedang Diluar</span>
                                )}
                              </div>
                            )}

                            {/* Return Status Badge */}
                            {returnStatus && (
                              <div className="pt-0.5 flex">
                                <span className={`inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase border ${
                                  returnStatus === "TEPAT WAKTU" 
                                    ? "bg-emerald-50 text-emerald-600 border-emerald-100 bg-opacity-80" 
                                    : "bg-red-50 text-red-600 border-red-100 bg-opacity-80"
                                }`}>
                                  {returnStatus === "TEPAT WAKTU" ? (
                                    <>
                                      <span className="w-1 h-1 rounded-full bg-emerald-500 animate-pulse" />
                                      Tepat Waktu
                                    </>
                                  ) : (
                                    <>
                                      <span className="w-1 h-1 rounded-full bg-red-500 animate-pulse" />
                                      Terlambat
                                    </>
                                  )}
                                </span>
                              </div>
                            )}
                          </div>

                          {/* Status & Chevron */}
                          <div className="flex items-center gap-2 shrink-0">
                            <span className={`inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border ${badgeColor}`}>
                              {badgeText}
                            </span>
                            <span className="text-slate-400">
                              {isExpanded ? (
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={2.5} stroke="currentColor" className="w-3.5 h-3.5">
                                  <path strokeLinecap="round" strokeLinejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                                </svg>
                              ) : (
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={2.5} stroke="currentColor" className="w-3.5 h-3.5">
                                  <path strokeLinecap="round" strokeLinejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                              )}
                            </span>
                          </div>
                        </button>

                        {/* Row Expanded Details */}
                        {isExpanded && (
                          <div className="px-5 pb-5 pt-1 bg-slate-50/40 border-t border-slate-100/50 space-y-4 animate-in fade-in duration-200">
                            <div className="grid grid-cols-2 gap-3 text-xs pt-3">
                              <div>
                                <Text.Label className="text-[10px] text-slate-400 block">Rencana Keluar</Text.Label>
                                <Text.Body className="font-bold text-slate-700 mt-1">
                                  {new Date(permit.planned_exit_date).toLocaleString("id-ID", { day: "numeric", month: "short", hour: "2-digit", minute: "2-digit" })}
                                </Text.Body>
                              </div>
                              <div>
                                <Text.Label className="text-[10px] text-slate-400 block">Rencana Kembali</Text.Label>
                                <Text.Body className="font-bold text-slate-700 mt-1">
                                  {new Date(permit.planned_return_date).toLocaleString("id-ID", { day: "numeric", month: "short", hour: "2-digit", minute: "2-digit" })}
                                </Text.Body>
                              </div>
                            </div>

                            {permit.reason && (
                              <div className="bg-white rounded-[16px] p-3 shadow-[0_4px_20px_rgb(0,0,0,0.02)] border border-slate-100">
                                <Text.Label className="text-[9px] uppercase font-bold text-slate-400 tracking-wider">Alasan Perizinan</Text.Label>
                                <Text.Body className="text-slate-600 mt-1">{permit.reason}</Text.Body>
                              </div>
                            )}

                            {permit.attachment_photo && (
                              <div className="bg-white rounded-[16px] p-3 shadow-[0_4px_20px_rgb(0,0,0,0.02)] border border-slate-100 flex flex-col gap-1">
                                <Text.Label className="text-[9px] uppercase font-bold text-slate-400 tracking-wider">Dokumen Pendukung</Text.Label>
                                <button
                                  type="button"
                                  onClick={(e) => {
                                    e.stopPropagation();
                                    setViewingDocumentUrl(`/${permit.attachment_photo}`);
                                  }}
                                  className="text-xs text-[#2563EB] hover:text-blue-750 font-bold flex items-center gap-1.5 hover:underline mt-1 text-left w-fit"
                                >
                                  <ExternalLink size={12} /> Lihat Lampiran Dokumen
                                </button>
                              </div>
                            )}

                            {permit.status === "rejected" && permit.rejection_reason && (
                              <div className="bg-rose-50/50 rounded-[16px] p-3 border border-rose-100 text-rose-800">
                                <Text.Label className="text-[9px] uppercase font-bold text-rose-500 tracking-wider">Alasan Penolakan</Text.Label>
                                <Text.Body className="mt-1 text-rose-750">{permit.rejection_reason}</Text.Body>
                              </div>
                            )}

                            {permit.actual_exit_date && (
                              <div className="text-[11px] text-slate-400 flex flex-col gap-1 border-t border-slate-100 pt-3">
                                <Text.Caption>
                                  Keluar: {new Date(permit.actual_exit_date).toLocaleString("id-ID")}
                                </Text.Caption>
                                {permit.actual_return_date && (
                                  <Text.Caption>
                                    Kembali: {new Date(permit.actual_return_date).toLocaleString("id-ID")}
                                  </Text.Caption>
                                )}
                              </div>
                            )}

                            {permit.status === "approved" && (
                              <div className="pt-2 space-y-2">
                                <button
                                  type="button"
                                  onClick={(e) => {
                                    e.stopPropagation();
                                    setActiveBarcode(permit.barcode_token);
                                    setActiveBarcodeName(permit.student?.name);
                                  }}
                                  className="w-full py-2.5 rounded-[16px] bg-gradient-to-br from-[#9b1de8] to-[#610a9c] text-white flex items-center justify-center gap-2 text-xs font-bold shadow-md active:scale-98 transition"
                                >
                                  <QrCode size={15} /> Tampilkan Barcode Izin
                                </button>
                                <button
                                  type="button"
                                  onClick={(e) => {
                                    e.stopPropagation();
                                    setReportingPermitId(permit.id);
                                  }}
                                  className="w-full py-2.5 rounded-[16px] bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-700 hover:to-emerald-600 text-white flex items-center justify-center gap-2 text-xs font-bold shadow-md active:scale-98 transition"
                                >
                                  <ArrowLeftCircle size={15} /> Lapor Kembali
                                </button>
                              </div>
                            )}

                            {permit.status === "out" && (
                              <div className="pt-2">
                                <button
                                  type="button"
                                  onClick={(e) => {
                                    e.stopPropagation();
                                    setReportingPermitId(permit.id);
                                  }}
                                  className="w-full py-2.5 rounded-[16px] bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-700 hover:to-emerald-600 text-white flex items-center justify-center gap-2 text-xs font-bold shadow-md active:scale-98 transition"
                                >
                                  <ArrowLeftCircle size={15} /> Lapor Kembali
                                </button>
                              </div>
                            )}
                          </div>
                        )}
                      </div>
                    );
                  })}
                </div>

                {/* Pagination Controls */}
                {totalPages > 1 && (
                  <div className="flex items-center justify-between mt-4 px-2">
                    <button
                      type="button"
                      disabled={currentPage === 1}
                      onClick={() => setCurrentPage(prev => Math.max(prev - 1, 1))}
                      className="px-4 py-2 text-xs font-bold text-slate-600 bg-card rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] disabled:opacity-50 disabled:pointer-events-none hover:bg-slate-50 transition"
                    >
                      Sebelumnya
                    </button>
                    
                    <Text.Caption className="text-slate-500 font-semibold">
                      Halaman {currentPage} dari {totalPages}
                    </Text.Caption>
                    
                    <button
                      type="button"
                      disabled={currentPage === totalPages}
                      onClick={() => setCurrentPage(prev => Math.min(prev + 1, totalPages))}
                      className="px-4 py-2 text-xs font-bold text-slate-600 bg-card rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] disabled:opacity-50 disabled:pointer-events-none hover:bg-slate-50 transition"
                    >
                      Selanjutnya
                    </button>
                  </div>
                )}
              </div>
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
          <div className="fixed inset-0 bg-slate-900/60 z-50 flex items-end justify-center p-4 backdrop-blur-md animate-fade-in">
            <div className="bg-white/95 w-full max-w-md rounded-t-[2.5rem] p-6 border border-white/20 shadow-[0_20px_60px_rgba(155,29,232,0.18)] space-y-6 animate-slide-up pb-10">
              
              {/* Header */}
              <div className="flex justify-between items-center border-b border-slate-100/80 pb-3">
                <div>
                  <p className="text-[11px] text-slate-400 font-extrabold uppercase tracking-wide">Pengaturan Jadwal</p>
                  <p className="text-sm font-bold text-slate-800">Setel Tanggal & Waktu</p>
                </div>
                <button
                  type="button"
                  onClick={() => setIsPickerOpen(false)}
                  className="w-8 h-8 rounded-full bg-secondary border border-border flex items-center justify-center text-slate-500 font-bold hover:bg-slate-100 active:scale-95 transition"
                >
                  ×
                </button>
              </div>

              {/* Premium Hero Display Board */}
              <div className="relative overflow-hidden rounded-[24px] bg-gradient-to-br from-[#9b1de8] to-[#5a0c91] text-white p-5 shadow-[0_12px_30px_rgba(155,29,232,0.22)] flex flex-col justify-between min-h-[110px]">
                <div className="absolute top-0 right-0 w-28 h-28 bg-white/5 rounded-full blur-3xl -mr-4 -mt-4"></div>
                <div className="absolute bottom-0 left-0 w-36 h-36 bg-white/5 rounded-full blur-3xl -ml-10 -mb-10"></div>
                
                <div className="relative z-10">
                  <span className="text-[9px] font-extrabold uppercase tracking-widest text-purple-200 block">Jadwal Terpilih</span>
                  <span className="text-sm font-extrabold mt-1 block">
                    {pickerDate.toLocaleDateString("id-ID", { weekday: "long", day: "numeric", month: "long", year: "numeric" })}
                  </span>
                </div>
                
                <div className="relative z-10 mt-3 flex items-baseline gap-2">
                  <span className="text-3xl font-extrabold font-mono tracking-tight text-white drop-shadow-[0_2px_8px_rgba(255,255,255,0.15)]">
                    {pickerHour.toString().padStart(2, '0')}:{pickerMinute.toString().padStart(2, '0')}
                  </span>
                  <span className="text-xs font-bold px-2 py-0.5 rounded-lg bg-white/15 text-purple-100 font-sans tracking-wide">
                    {pickerAmPm} WIB
                  </span>
                </div>
              </div>

              {/* Premium Prominent Tabs */}
              <div className="grid grid-cols-2 bg-slate-100/80 border border-slate-200/50 rounded-2xl p-1 gap-1">
                <button
                  type="button"
                  onClick={() => setPickerTab("date")}
                  className={`py-3 px-2 rounded-xl flex items-center justify-center gap-2 font-extrabold text-xs transition duration-300 ${
                    pickerTab === "date"
                      ? "bg-white text-[#9b1de8] shadow-sm border border-slate-200/60"
                      : "text-slate-500 hover:text-slate-800"
                  }`}
                >
                  <Calendar size={14} /> Set Tanggal
                </button>
                <button
                  type="button"
                  onClick={() => setPickerTab("time")}
                  className={`py-3 px-2 rounded-xl flex items-center justify-center gap-2 font-extrabold text-xs transition duration-300 ${
                    pickerTab === "time"
                      ? "bg-white text-[#9b1de8] shadow-sm border border-slate-200/60"
                      : "text-slate-500 hover:text-slate-800"
                  }`}
                >
                  <Clock size={14} /> Set Waktu
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
                                ? "bg-gradient-to-br from-[#9b1de8] to-[#610a9c] text-white shadow-[0_4px_12px_rgba(155,29,232,0.3)] font-bold scale-105"
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
                  /* --- PREMIUM DIGITAL TIME PICKER PANEL --- */
                  <div className="space-y-4 animate-in fade-in duration-200">
                    {/* Glowing Digital Time Banner */}
                    <div className="bg-gradient-to-r from-[#9b1de8]/10 to-[#5a0c91]/10 border border-[#9b1de8]/20 rounded-2xl p-4 flex items-center justify-between shadow-sm">
                      <div className="flex flex-col">
                        <span className="text-[10px] uppercase font-bold text-[#9b1de8]/80 tracking-widest">Waktu Terpilih</span>
                        <div className="flex items-baseline gap-1 mt-1">
                          <span className="text-2xl font-black text-slate-800 tracking-tight font-mono">
                            {pickerHour.toString().padStart(2, '0')}:{pickerMinute.toString().padStart(2, '0')}
                          </span>
                          <span className="text-sm font-extrabold text-[#9b1de8]">{pickerAmPm}</span>
                        </div>
                      </div>
                      
                      {/* AM / PM Segmented Capsule Selector */}
                      <div className="flex bg-slate-100/80 border border-slate-200/50 rounded-xl p-0.5 text-[11px] font-extrabold">
                        <button
                          type="button"
                          onClick={() => setPickerAmPm("AM")}
                          className={`px-3 py-1.5 rounded-lg transition active:scale-95 ${
                            pickerAmPm === "AM" 
                              ? "bg-gradient-to-r from-[#9b1de8] to-[#5a0c91] text-white shadow-sm" 
                              : "text-slate-500 hover:text-slate-800"
                          }`}
                        >
                          AM
                        </button>
                        <button
                          type="button"
                          onClick={() => setPickerAmPm("PM")}
                          className={`px-3 py-1.5 rounded-lg transition active:scale-95 ${
                            pickerAmPm === "PM" 
                              ? "bg-gradient-to-r from-[#9b1de8] to-[#5a0c91] text-white shadow-sm" 
                              : "text-slate-500 hover:text-slate-800"
                          }`}
                        >
                          PM
                        </button>
                      </div>
                    </div>

                    {/* Interactive Tab Switcher (Jam vs Menit) */}
                    <div className="flex bg-slate-100/60 border border-slate-200/40 rounded-xl p-1 gap-1">
                      <button
                        type="button"
                        onClick={() => setPickerClockMode("hours")}
                        className={`flex-1 py-2 text-xs font-bold rounded-lg transition ${
                          pickerClockMode === "hours"
                            ? "bg-white text-slate-800 shadow-sm border border-slate-200/20"
                            : "text-slate-500 hover:text-slate-800"
                        }`}
                      >
                        Pilih Jam ({pickerHour})
                      </button>
                      <button
                        type="button"
                        onClick={() => setPickerClockMode("minutes")}
                        className={`flex-1 py-2 text-xs font-bold rounded-lg transition ${
                          pickerClockMode === "minutes"
                            ? "bg-white text-slate-800 shadow-sm border border-slate-200/20"
                            : "text-slate-500 hover:text-slate-800"
                        }`}
                      >
                        Pilih Menit ({pickerMinute.toString().padStart(2, '0')})
                      </button>
                    </div>

                    {/* Selector Grid */}
                    <div className="min-h-[160px] flex items-center justify-center">
                      {pickerClockMode === "hours" ? (
                        /* Hours 4x3 Grid */
                        <div className="grid grid-cols-4 gap-2 w-full max-w-sm">
                          {[12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11].map((hr) => {
                            const isSelected = pickerHour === hr;
                            return (
                              <button
                                type="button"
                                key={`hr-${hr}`}
                                onClick={() => {
                                  setPickerHour(hr);
                                  setPickerClockMode("minutes"); // Auto switch to minutes for speed
                                }}
                                className={`py-3 rounded-2xl text-xs font-extrabold transition duration-200 active:scale-95 border ${
                                  isSelected
                                    ? "bg-gradient-to-br from-[#9b1de8] to-[#5a0c91] text-white border-transparent shadow-[0_6px_15px_rgba(155,29,232,0.25)] scale-105"
                                    : "bg-slate-50 text-slate-700 border-slate-100 hover:bg-slate-100/50"
                                }`}
                              >
                                {hr}
                              </button>
                            );
                          })}
                        </div>
                      ) : (
                        /* Minutes 4x3 Grid (increments of 5) */
                        <div className="w-full max-w-sm space-y-3">
                          <div className="grid grid-cols-4 gap-2">
                            {[0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55].map((min) => {
                              const isSelected = pickerMinute === min;
                              return (
                                <button
                                  type="button"
                                  key={`min-${min}`}
                                  onClick={() => setPickerMinute(min)}
                                  className={`py-2.5 rounded-2xl text-xs font-extrabold transition duration-200 active:scale-95 border ${
                                    isSelected
                                      ? "bg-gradient-to-br from-[#9b1de8] to-[#5a0c91] text-white border-transparent shadow-[0_6px_15px_rgba(155,29,232,0.25)] scale-105"
                                      : "bg-slate-50 text-slate-700 border-slate-100 hover:bg-slate-100/50"
                                  }`}
                                >
                                  {min.toString().padStart(2, '0')}
                                </button>
                              );
                            })}
                          </div>
                          
                          {/* Precise Minute Fine-Tuning controls (+ / -) */}
                          <div className="flex items-center justify-between px-2 pt-2 border-t border-slate-100">
                            <span className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sesuaikan Presisi:</span>
                            <div className="flex items-center gap-2">
                              <button
                                type="button"
                                onClick={() => setPickerMinute((prev) => (prev > 0 ? prev - 1 : 59))}
                                className="w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center font-bold text-slate-600 hover:bg-slate-100 active:scale-95 transition"
                              >
                                -
                              </button>
                              <div className="w-12 text-center bg-slate-100 border border-slate-200/50 rounded-xl py-1">
                                <span className="text-xs font-black text-slate-700 font-mono">{pickerMinute.toString().padStart(2, '0')}</span>
                              </div>
                              <button
                                type="button"
                                onClick={() => setPickerMinute((prev) => (prev < 59 ? prev + 1 : 0))}
                                className="w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center font-bold text-slate-600 hover:bg-slate-100 active:scale-95 transition"
                              >
                                +
                              </button>
                            </div>
                          </div>
                        </div>
                      )}
                    </div>
                  </div>
                )}
              </div>

              {/* Footer Actions */}
              <div className="flex gap-3 pt-2">
                <button
                  type="button"
                  onClick={() => setIsPickerOpen(false)}
                  className="flex-1 py-4 rounded-2xl border border-slate-200 bg-white text-slate-500 text-xs font-bold hover:bg-slate-50 active:scale-[0.98] transition text-center shadow-sm"
                >
                  Batal
                </button>
                <button
                  type="button"
                  onClick={saveDateTimePicker}
                  className="flex-1 py-4 rounded-2xl bg-gradient-to-r from-[#9b1de8] to-[#5a0c91] text-white text-xs font-extrabold hover:brightness-110 active:scale-[0.98] transition text-center shadow-[0_8px_25px_rgba(155,29,232,0.3)]"
                >
                  Simpan Jadwal
                </button>
              </div>

            </div>
          </div>
        )}

        {/* Premium Document Preview Modal */}
        {viewingDocumentUrl && (
          <div className="fixed inset-0 bg-slate-900/70 z-50 flex items-center justify-center p-4 backdrop-blur-md animate-fade-in">
            <div className="bg-white/95 w-full max-w-md rounded-[32px] p-6 border border-white/20 shadow-[0_20px_50px_rgba(155,29,232,0.15)] flex flex-col space-y-4 animate-slide-up relative overflow-hidden">
              <div className="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-[#9b1de8]/10 to-[#5a0c91]/0 rounded-full blur-2xl"></div>
              
              {/* Header */}
              <div className="flex justify-between items-center pb-2 border-b border-slate-100">
                <div>
                  <p className="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest">Lampiran</p>
                  <p className="text-sm font-bold text-slate-800">Dokumen Pendukung</p>
                </div>
                <button
                  type="button"
                  onClick={() => setViewingDocumentUrl(null)}
                  className="w-8 h-8 rounded-full bg-secondary border border-border flex items-center justify-center text-slate-500 font-bold hover:bg-slate-100 active:scale-95 transition"
                >
                  ×
                </button>
              </div>

              {/* Image Preview Container */}
              <div className="relative rounded-2xl overflow-hidden bg-slate-50 border border-slate-100 flex items-center justify-center min-h-[300px] max-h-[450px]">
                <img
                  src={viewingDocumentUrl}
                  alt="Dokumen Pendukung"
                  className="w-full h-full object-contain max-h-[450px]"
                />
              </div>

              {/* Action Button */}
              <button
                type="button"
                onClick={() => setViewingDocumentUrl(null)}
                className="w-full py-3.5 rounded-2xl bg-gradient-to-r from-[#9b1de8] to-[#5a0c91] text-white text-xs font-extrabold hover:brightness-110 active:scale-[0.98] transition text-center shadow-[0_6px_20px_rgba(155,29,232,0.25)]"
              >
                Tutup Tampilan
              </button>
            </div>
          </div>
        )}
        {/* Premium Lapor Kembali Modal Drawer */}
        {reportingPermitId && (
          <div className="fixed inset-0 bg-slate-900/60 z-50 flex items-end justify-center p-4 backdrop-blur-md animate-fade-in">
            <div className="bg-white w-full max-w-md rounded-t-[2.5rem] p-6 shadow-2xl space-y-5 animate-slide-up pb-10 max-h-[90vh] overflow-y-auto">
              
              {/* Header */}
              <div className="flex justify-between items-center border-b border-slate-100 pb-3">
                <div>
                  <Text.Label className="text-[11px] text-slate-400 font-extrabold uppercase tracking-wide">Konfirmasi Kedatangan</Text.Label>
                  <Text.H2 className="text-sm font-bold text-slate-800">Lapor Kembali ke Pondok</Text.H2>
                </div>
                <button
                  type="button"
                  onClick={() => {
                    setReportingPermitId(null);
                    setReturnPhotoSantri(null);
                    setReturnPhotoEscort(null);
                    setReportError("");
                  }}
                  className="w-8 h-8 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 font-bold hover:bg-slate-100 active:scale-95 transition"
                >
                  ×
                </button>
              </div>

              {reportError && (
                <div className="p-3 bg-red-50 border border-red-100 rounded-2xl text-xs text-red-600 font-medium">
                  {reportError}
                </div>
              )}

              {/* Photo Uploaders */}
              <div className="space-y-4">
                {/* 1. Foto Santri Kembali */}
                <div>
                  <Text.Label className="text-slate-500 mb-1.5 block">Foto Santri (Saat Tiba/Kembali)</Text.Label>
                  {returnPhotoSantri ? (
                    <div className="relative rounded-[20px] overflow-hidden border border-slate-150 shadow-sm aspect-video bg-slate-50">
                      <img src={returnPhotoSantri} alt="Santri Kembali" className="w-full h-full object-cover" />
                      <button
                        type="button"
                        onClick={() => setReturnPhotoSantri(null)}
                        className="absolute top-2 right-2 p-1.5 rounded-full bg-red-600 text-white shadow-md active:scale-95 transition"
                      >
                        <XCircle size={16} />
                      </button>
                    </div>
                  ) : (
                    <label className="flex flex-col items-center justify-center border-2 border-dashed border-slate-200 rounded-[20px] p-6 hover:bg-slate-50 cursor-pointer transition select-none">
                      <Camera className="text-slate-400 mb-2" size={24} />
                      <span className="text-xs font-semibold text-slate-600">Ambil / Unggah Foto Santri</span>
                      <span className="text-[10px] text-slate-400 mt-1">Gunakan kamera HP atau pilih file</span>
                      <input
                        type="file"
                        accept="image/*"
                        capture="environment"
                        onChange={(e) => {
                          const file = e.target.files?.[0];
                          if (file) handlePhotoUpload(file, setReturnPhotoSantri);
                        }}
                        className="hidden"
                      />
                    </label>
                  )}
                </div>

                {/* 2. Foto Wali / Pengantar */}
                <div>
                  <Text.Label className="text-slate-500 mb-1.5 block">Foto Pengantar / Wali Santri</Text.Label>
                  {returnPhotoEscort ? (
                    <div className="relative rounded-[20px] overflow-hidden border border-slate-150 shadow-sm aspect-video bg-slate-50">
                      <img src={returnPhotoEscort} alt="Pengantar" className="w-full h-full object-cover" />
                      <button
                        type="button"
                        onClick={() => setReturnPhotoEscort(null)}
                        className="absolute top-2 right-2 p-1.5 rounded-full bg-red-600 text-white shadow-md active:scale-95 transition"
                      >
                        <XCircle size={16} />
                      </button>
                    </div>
                  ) : (
                    <label className="flex flex-col items-center justify-center border-2 border-dashed border-slate-200 rounded-[20px] p-6 hover:bg-slate-50 cursor-pointer transition select-none">
                      <Upload className="text-slate-400 mb-2" size={24} />
                      <span className="text-xs font-semibold text-slate-600">Ambil / Unggah Foto Pengantar</span>
                      <span className="text-[10px] text-slate-400 mt-1">Gunakan kamera HP atau pilih file</span>
                      <input
                        type="file"
                        accept="image/*"
                        capture="user"
                        onChange={(e) => {
                          const file = e.target.files?.[0];
                          if (file) handlePhotoUpload(file, setReturnPhotoEscort);
                        }}
                        className="hidden"
                      />
                    </label>
                  )}
                </div>
              </div>

              {/* Submit Button */}
              <button
                type="button"
                disabled={isReportingSubmit || !returnPhotoSantri || !returnPhotoEscort}
                onClick={async () => {
                  if (!returnPhotoSantri || !returnPhotoEscort || !reportingPermitId) return;
                  setIsReportingSubmit(true);
                  setReportError("");

                  // Get location coords first (with 3-second timeout fallback)
                  let latitude = "";
                  let longitude = "";
                  try {
                    const position: any = await new Promise((resolve, reject) => {
                      navigator.geolocation.getCurrentPosition(resolve, reject, {
                        enableHighAccuracy: true,
                        timeout: 3000
                      });
                    });
                    latitude = position.coords.latitude.toString();
                    longitude = position.coords.longitude.toString();
                  } catch (err) {
                    console.warn("Could not get geolocation", err);
                  }

                  try {
                    const res = await postReportReturn(reportingPermitId, {
                      return_photo_santri: returnPhotoSantri,
                      return_photo_escort: returnPhotoEscort,
                      latitude,
                      longitude
                    });

                    if (res.data?.success) {
                      queryClient.invalidateQueries({ queryKey: ["permits"] });
                      setReportingPermitId(null);
                      setReturnPhotoSantri(null);
                      setReturnPhotoEscort(null);
                      setSuccessDialog({
                        open: true,
                        message: "Laporan kepulangan berhasil dikirim! Silakan hubungi Ustadz untuk persetujuan."
                      });
                    } else {
                      setReportError(res.data?.message || "Gagal mengirim laporan kepulangan.");
                    }
                  } catch (err: any) {
                    setReportError(err.response?.data?.message || "Terjadi kesalahan sistem saat mengirim laporan.");
                  } finally {
                    setIsReportingSubmit(false);
                  }
                }}
                className="w-full py-4 rounded-[20px] bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-700 hover:to-emerald-600 text-white font-bold text-[14px] shadow-lg transition active:scale-[0.98] disabled:opacity-50 disabled:active:scale-100 flex items-center justify-center gap-2"
              >
                {isReportingSubmit ? (
                  <Loader2 className="animate-spin" size={18} />
                ) : (
                  "Kirim Laporan Kepulangan"
                )}
              </button>
            </div>
          </div>
        )}
      </div>

      {/* Premium Success Modal */}
      {successDialog.open && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center p-5 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-200">
          <div className="bg-white rounded-[24px] shadow-[0_12px_40px_rgba(0,0,0,0.12)] max-w-sm w-full p-6 text-center animate-in zoom-in-95 duration-200 border border-slate-100">
            <div className="mx-auto w-12 h-12 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 mb-4 animate-bounce">
              <CheckCircle2 size={24} strokeWidth={2.5} />
            </div>
            <Text.H2 className="text-slate-800 font-extrabold text-base leading-tight">
              Laporan Terkirim
            </Text.H2>
            <Text.Body className="text-slate-600 text-xs font-semibold mt-2.5 leading-relaxed">
              {successDialog.message}
            </Text.Body>
            <button
              type="button"
              onClick={() => setSuccessDialog({ open: false, message: "" })}
              className="w-full mt-5 py-3 rounded-2xl bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-700 hover:to-emerald-600 text-white font-bold text-xs shadow-md active:scale-95 transition"
            >
              Selesai
            </button>
          </div>
        </div>
      )}
    </div>
  );
}
