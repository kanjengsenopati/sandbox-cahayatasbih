import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useState, useMemo } from "react";
import {
  ArrowLeft,
  Headphones,
  Search,
  CheckCircle2,
  UserCheck,
  Edit3,
  Send,
  AlertCircle,
  HelpCircle,
  ShieldCheck,
  Check
} from "lucide-react";
import {
  KENDALA_OPTIONS,
  KendalaType,
  MOCK_STUDENTS,
  StudentMaster,
  addLaporPakReport,
} from "@/data/laporpak";
import { Text } from "@/components/Text";

export const Route = createFileRoute("/laporpak")({
  component: LaporPakPublicPage,
  head: () => ({ meta: [{ title: "Lapor Pak — Layanan Pengaduan Wali Santri" }] }),
});

function LaporPakPublicPage() {
  const navigate = useNavigate();

  // Form State
  const [selectedKendala, setSelectedKendala] = useState<KendalaType | "">("");
  const [keterangan, setKeterangan] = useState("");

  // Student Search State
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedStudent, setSelectedStudent] = useState<StudentMaster | null>(null);
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);

  // Wali Info & Update Mode State
  const [parentName, setParentName] = useState("");
  const [parentPhone, setParentPhone] = useState("");
  const [isEditParent, setIsEditParent] = useState(false);

  // Submission State
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submittedReportId, setSubmittedReportId] = useState<string | null>(null);

  // Filter students based on search query
  const filteredStudents = useMemo(() => {
    if (!searchQuery.trim()) return MOCK_STUDENTS;
    const q = searchQuery.toLowerCase();
    return MOCK_STUDENTS.filter(
      (s) =>
        s.name.toLowerCase().includes(q) ||
        s.school.toLowerCase().includes(q) ||
        s.class.toLowerCase().includes(q) ||
        s.displayLabel.toLowerCase().includes(q)
    );
  }, [searchQuery]);

  const handleSelectStudent = (student: StudentMaster) => {
    setSelectedStudent(student);
    setSearchQuery(student.displayLabel);
    setParentName(student.parentName);
    setParentPhone(student.parentPhone);
    setIsEditParent(false);
    setIsDropdownOpen(false);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedKendala) {
      alert("Harap pilih salah satu jenis kendala.");
      return;
    }
    if (!selectedStudent) {
      alert("Harap pilih data siswa dari kotak pencarian.");
      return;
    }
    if (!parentName.trim() || !parentPhone.trim()) {
      alert("Harap lengkapi nama wali dan nomor WhatsApp.");
      return;
    }

    setIsSubmitting(true);

    setTimeout(() => {
      const created = addLaporPakReport({
        studentId: selectedStudent.id,
        studentName: selectedStudent.name,
        school: selectedStudent.school,
        className: selectedStudent.class,
        parentName: parentName.trim(),
        parentPhone: parentPhone.trim(),
        isParentUpdated: isEditParent,
        kendala: selectedKendala as KendalaType,
        keterangan: keterangan.trim() || undefined,
      });

      setIsSubmitting(false);
      setSubmittedReportId(created.id);
    }, 600);
  };

  const handleResetForm = () => {
    setSelectedKendala("");
    setKeterangan("");
    setSearchQuery("");
    setSelectedStudent(null);
    setParentName("");
    setParentPhone("");
    setIsEditParent(false);
    setSubmittedReportId(null);
  };

  return (
    <div className="min-h-screen w-full flex justify-center bg-secondary">
      <div className="relative w-full max-w-md min-h-screen bg-background pb-12">
        {/* Header */}
        <div className="px-5 pt-12 pb-3 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <button
              type="button"
              onClick={() => navigate({ to: "/dashboard" })}
              className="w-10 h-10 rounded-[24px] bg-secondary border border-border flex items-center justify-center hover:bg-slate-100 transition active:scale-95"
            >
              <ArrowLeft size={18} className="text-slate-600" />
            </button>
            <div>
              <Text.Label className="block mb-0.5">Layanan Publik</Text.Label>
              <Text.H1>Lapor Pak</Text.H1>
            </div>
          </div>
          <div className="flex items-center gap-2">
            <div className="w-9 h-9 rounded-[24px] bg-blue-600/10 flex items-center justify-center">
              <Headphones size={18} className="text-blue-600" />
            </div>
          </div>
        </div>

        {/* Banner Info */}
        <div className="px-5 pt-1">
          <div className="rounded-[24px] bg-blue-600 text-white p-4 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative overflow-hidden">
            <div className="relative z-10">
              <Text.H2 className="text-white mb-1">Pusat Pengaduan Kendala</Text.H2>
              <Text.Body className="text-blue-100 text-xs leading-relaxed">
                Sampaikan masalah login, aplikasi, tagihan, atau saldo Anda. Tim kami akan segera menindaklanjuti.
              </Text.Body>
            </div>
            <ShieldCheck className="absolute -right-3 -bottom-3 text-white/10 w-24 h-24 pointer-events-none" />
          </div>
        </div>

        {/* Modal / Overlay Terkirim Sukses */}
        {submittedReportId ? (
          <div className="px-5 pt-6">
            <div className="rounded-[24px] bg-card p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] text-center space-y-4">
              <div className="w-14 h-14 bg-emerald-600/10 text-emerald-600 rounded-full flex items-center justify-center mx-auto">
                <CheckCircle2 size={32} />
              </div>

              <div>
                <Text.Label className="block mb-1 text-emerald-600">Laporan Berhasil Terkirim</Text.Label>
                <Text.H1 className="text-slate-900 mb-1">Nomor Tiket: {submittedReportId}</Text.H1>
                <Text.Body className="text-slate-500 text-xs">
                  Laporan Anda telah terdaftar di sistem admin Lapor Pak. Tim petugas kami akan segera memproses kendala Anda.
                </Text.Body>
              </div>

              <div className="bg-secondary rounded-[24px] p-4 text-left space-y-2">
                <div className="flex justify-between items-center text-xs">
                  <Text.Label>Nama Siswa</Text.Label>
                  <Text.Body className="text-slate-800 font-semibold">{selectedStudent?.displayLabel}</Text.Body>
                </div>
                <div className="flex justify-between items-center text-xs">
                  <Text.Label>Nama Wali</Text.Label>
                  <Text.Body className="text-slate-800 font-semibold">{parentName}</Text.Body>
                </div>
                <div className="flex justify-between items-center text-xs">
                  <Text.Label>Jenis Kendala</Text.Label>
                  <span className="px-2 py-0.5 rounded-full bg-red-600/10 text-red-600 text-[10px] font-bold">
                    {selectedKendala}
                  </span>
                </div>
              </div>

              <div className="pt-2 space-y-2">
                <button
                  type="button"
                  onClick={handleResetForm}
                  className="w-full py-3 rounded-[24px] bg-blue-600 text-white font-bold text-xs shadow-md active:scale-95 transition"
                >
                  Buat Laporan Baru
                </button>
                <button
                  type="button"
                  onClick={() => navigate({ to: "/admin/laporpak" })}
                  className="w-full py-2.5 rounded-[24px] bg-secondary text-blue-600 font-semibold text-xs border border-border hover:bg-slate-100 active:scale-95 transition"
                >
                  Lihat Modul Admin (Lapor Pak)
                </button>
              </div>
            </div>
          </div>
        ) : (
          /* Form Utama */
          <form onSubmit={handleSubmit} className="px-5 pt-4 space-y-5">
            {/* Step 1: Opsi Kendala */}
            <div className="rounded-[24px] bg-card p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] space-y-3">
              <div className="flex items-center justify-between">
                <div>
                  <Text.Label className="block mb-0.5 text-blue-600">Langkah 1</Text.Label>
                  <Text.H2>Pilih Jenis Kendala</Text.H2>
                </div>
                <AlertCircle size={18} className="text-slate-400" />
              </div>

              <div className="space-y-2 pt-1">
                {KENDALA_OPTIONS.map((opt, idx) => {
                  const isSelected = selectedKendala === opt;
                  return (
                    <button
                      key={opt}
                      type="button"
                      onClick={() => setSelectedKendala(opt)}
                      className={`w-full text-left p-3.5 rounded-[24px] border transition flex items-center justify-between ${
                        isSelected
                          ? "border-blue-600 bg-blue-600/5 shadow-sm"
                          : "border-border bg-card hover:border-slate-300"
                      }`}
                    >
                      <div className="flex items-center gap-3">
                        <span
                          className={`w-6 h-6 rounded-full text-[11px] font-bold flex items-center justify-center ${
                            isSelected ? "bg-blue-600 text-white" : "bg-secondary text-slate-500"
                          }`}
                        >
                          {idx + 1}
                        </span>
                        <Text.Body className={`font-semibold ${isSelected ? "text-blue-600" : "text-slate-700"}`}>
                          {opt}
                        </Text.Body>
                      </div>
                      {isSelected && <Check size={16} className="text-blue-600" />}
                    </button>
                  );
                })}
              </div>

              {/* Keterangan Entry (Opsional) - Appears when option is selected */}
              {selectedKendala && (
                <div className="pt-2 animate-fadeIn">
                  <Text.Label className="block mb-1 text-slate-500">
                    Keterangan Tambahan (Opsional)
                  </Text.Label>
                  <textarea
                    rows={3}
                    value={keterangan}
                    onChange={(e) => setKeterangan(e.target.value)}
                    placeholder="Jelaskan detail kendala yang dialami secara singkat..."
                    className="w-full rounded-[24px] bg-secondary border border-border p-3.5 text-xs text-slate-800 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 resize-none"
                  />
                </div>
              )}
            </div>

            {/* Step 2: Pencarian Nama Siswa */}
            <div className="rounded-[24px] bg-card p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] space-y-3 relative">
              <div className="flex items-center justify-between">
                <div>
                  <Text.Label className="block mb-0.5 text-blue-600">Langkah 2</Text.Label>
                  <Text.H2>Cari Nama Siswa</Text.H2>
                </div>
                <Search size={18} className="text-slate-400" />
              </div>

              <div className="relative">
                <div className="relative flex items-center">
                  <Search size={16} className="absolute left-3.5 text-slate-400 pointer-events-none" />
                  <input
                    type="text"
                    value={searchQuery}
                    onChange={(e) => {
                      setSearchQuery(e.target.value);
                      setIsDropdownOpen(true);
                      if (selectedStudent && e.target.value !== selectedStudent.displayLabel) {
                        setSelectedStudent(null);
                        setParentName("");
                        setParentPhone("");
                      }
                    }}
                    onFocus={() => setIsDropdownOpen(true)}
                    placeholder="Ketik nama siswa..."
                    className="w-full pl-9 pr-4 py-3 rounded-[24px] bg-secondary border border-border text-xs font-medium text-slate-800 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600"
                  />
                </div>

                {/* Dropdown Hasil Pencarian Output (Nama - Sekolah - Kelas) */}
                {isDropdownOpen && filteredStudents.length > 0 && (
                  <div className="absolute left-0 right-0 top-full mt-2 bg-card border border-border rounded-[24px] shadow-lg z-20 max-h-48 overflow-y-auto divide-y divide-border">
                    {filteredStudents.map((s) => (
                      <button
                        key={s.id}
                        type="button"
                        onClick={() => handleSelectStudent(s)}
                        className="w-full text-left p-3 hover:bg-blue-600/5 transition flex items-center justify-between group"
                      >
                        <div>
                          <Text.Body className="font-semibold text-slate-800 group-hover:text-blue-600">
                            {s.name}
                          </Text.Body>
                          <Text.Caption className="block text-slate-400">
                            {s.school} — {s.class}
                          </Text.Caption>
                        </div>
                        <span className="text-[10px] bg-secondary px-2 py-1 rounded-full text-slate-500 font-mono">
                          {s.id}
                        </span>
                      </button>
                    ))}
                  </div>
                )}
              </div>

              {/* Display Selected Student Badge */}
              {selectedStudent && (
                <div className="p-3 rounded-[24px] bg-emerald-600/10 border border-emerald-600/20 flex items-center gap-3">
                  <UserCheck size={18} className="text-emerald-600 shrink-0" />
                  <div className="min-w-0 flex-1">
                    <Text.Label className="block text-emerald-600 mb-0.5">Siswa Terpilih</Text.Label>
                    <Text.Body className="font-bold text-slate-800 text-xs truncate">
                      {selectedStudent.displayLabel}
                    </Text.Body>
                  </div>
                </div>
              )}
            </div>

            {/* Step 3: Auto-Populate Data Orang Tua (Wali) + Opsi Update */}
            {selectedStudent && (
              <div className="rounded-[24px] bg-card p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] space-y-3 animate-fadeIn">
                <div className="flex items-center justify-between">
                  <div>
                    <Text.Label className="block mb-0.5 text-blue-600">Langkah 3</Text.Label>
                    <Text.H2>Data Wali Siswa</Text.H2>
                  </div>
                  <button
                    type="button"
                    onClick={() => setIsEditParent(!isEditParent)}
                    className="flex items-center gap-1 text-xs font-semibold text-blue-600 hover:underline"
                  >
                    <Edit3 size={14} />
                    {isEditParent ? "Batal Update" : "Opsi Update"}
                  </button>
                </div>

                {!isEditParent ? (
                  <div className="bg-secondary rounded-[24px] p-4 space-y-2 border border-border">
                    <div className="flex justify-between items-center">
                      <Text.Label>Nama Wali</Text.Label>
                      <Text.Body className="font-bold text-slate-800">{parentName}</Text.Body>
                    </div>
                    <div className="flex justify-between items-center">
                      <Text.Label>No. WhatsApp</Text.Label>
                      <Text.Body className="font-bold text-slate-800 font-mono">{parentPhone}</Text.Body>
                    </div>
                    <Text.Caption className="block pt-1 text-slate-400 italic">
                      *Data wali terhubung otomatis dari sistem perwalian.
                    </Text.Caption>
                  </div>
                ) : (
                  <div className="space-y-3 bg-blue-600/5 p-4 rounded-[24px] border border-blue-600/20">
                    <div className="flex items-center gap-1.5 text-amber-600 text-xs font-medium">
                      <HelpCircle size={14} />
                      <span>Silakan perbarui nama atau nomor WA jika tidak sesuai:</span>
                    </div>

                    <div>
                      <Text.Label className="block mb-1 text-slate-600">Nama Wali / Orang Tua</Text.Label>
                      <input
                        type="text"
                        value={parentName}
                        onChange={(e) => setParentName(e.target.value)}
                        className="w-full px-3.5 py-2.5 rounded-[24px] bg-card border border-border text-xs font-semibold text-slate-800 focus:outline-none focus:border-blue-600"
                        placeholder="Nama Lengkap Wali"
                      />
                    </div>

                    <div>
                      <Text.Label className="block mb-1 text-slate-600">No. WhatsApp Aktif</Text.Label>
                      <input
                        type="text"
                        value={parentPhone}
                        onChange={(e) => setParentPhone(e.target.value)}
                        className="w-full px-3.5 py-2.5 rounded-[24px] bg-card border border-border text-xs font-semibold text-slate-800 focus:outline-none focus:border-blue-600 font-mono"
                        placeholder="Contoh: 081234567890"
                      />
                    </div>
                  </div>
                )}
              </div>
            )}

            {/* Submit Action */}
            <div className="pt-2">
              <button
                type="submit"
                disabled={isSubmitting || !selectedKendala || !selectedStudent}
                className="w-full py-3.5 rounded-[24px] bg-blue-600 text-white font-bold text-sm shadow-[0_8px_30px_rgb(37,99,235,0.25)] hover:bg-blue-700 active:scale-95 transition flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {isSubmitting ? (
                  <span>Mengirim Laporan...</span>
                ) : (
                  <>
                    <Send size={16} />
                    <span>Kirim Pengaduan Lapor Pak</span>
                  </>
                )}
              </button>
            </div>
          </form>
        )}
      </div>
    </div>
  );
}
