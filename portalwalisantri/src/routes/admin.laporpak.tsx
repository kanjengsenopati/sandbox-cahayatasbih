import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useEffect, useState, useMemo } from "react";
import {
  ArrowLeft,
  ShieldCheck,
  CheckCircle2,
  AlertTriangle,
  FileText,
  Filter,
  Search,
  RefreshCw,
  Trash2,
  ExternalLink,
  MessageSquareText
} from "lucide-react";
import {
  getLaporPakReports,
  subscribeLaporPak,
  toggleLaporPakStatus,
  deleteLaporPakReport,
  LaporPakReport,
  ReportStatus,
  KENDALA_OPTIONS
} from "@/data/laporpak";
import { Text } from "@/components/Text";

export const Route = createFileRoute("/admin/laporpak")({
  component: AdminLaporPakPage,
  head: () => ({ meta: [{ title: "Kelola Lapor Pak — Admin Module" }] }),
});

function AdminLaporPakPage() {
  const navigate = useNavigate();
  const [reports, setReports] = useState<LaporPakReport[]>([]);
  const [statusFilter, setStatusFilter] = useState<"all" | ReportStatus>("all");
  const [categoryFilter, setCategoryFilter] = useState<string>("all");
  const [searchQuery, setSearchQuery] = useState<string>("");

  useEffect(() => {
    const refresh = () => setReports(getLaporPakReports());
    refresh();
    return subscribeLaporPak(refresh);
  }, []);

  // Compute Summary Statistics
  const stats = useMemo(() => {
    const total = reports.length;
    const kendalaCount = reports.filter((r) => r.status === "Kendala").length;
    const teratasiCount = reports.filter((r) => r.status === "Teratasi").length;

    // Breakdown per jenis kendala
    const catBreakdown = KENDALA_OPTIONS.map((opt) => ({
      category: opt,
      count: reports.filter((r) => r.kendala === opt).length,
    }));

    return { total, kendalaCount, teratasiCount, catBreakdown };
  }, [reports]);

  // Filtered reports for data table
  const filteredReports = useMemo(() => {
    return reports.filter((r) => {
      const matchStatus = statusFilter === "all" || r.status === statusFilter;
      const matchCategory = categoryFilter === "all" || r.kendala === categoryFilter;
      const q = searchQuery.toLowerCase();
      const matchQuery =
        !q ||
        r.studentName.toLowerCase().includes(q) ||
        r.parentName.toLowerCase().includes(q) ||
        r.kendala.toLowerCase().includes(q) ||
        (r.keterangan && r.keterangan.toLowerCase().includes(q)) ||
        r.id.toLowerCase().includes(q);

      return matchStatus && matchCategory && matchQuery;
    });
  }, [reports, statusFilter, categoryFilter, searchQuery]);

  return (
    <div className="min-h-screen w-full flex justify-center bg-secondary">
      <div className="relative w-full max-w-4xl min-h-screen bg-background pb-16">
        {/* Header */}
        <div className="px-5 pt-10 pb-4 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <button
              onClick={() => navigate({ to: "/dashboard" })}
              className="w-10 h-10 rounded-[24px] bg-secondary border border-border flex items-center justify-center hover:bg-slate-100 transition active:scale-95"
            >
              <ArrowLeft size={18} className="text-slate-600" />
            </button>
            <div>
              <Text.Label className="block mb-0.5">Modul Kelola</Text.Label>
              <Text.H1>Modul UI Lapor Pak</Text.H1>
            </div>
          </div>
          <div className="flex items-center gap-2">
            <button
              onClick={() => navigate({ to: "/laporpak" })}
              className="px-3 py-1.5 rounded-[24px] bg-blue-600/10 text-blue-600 font-bold text-xs flex items-center gap-1.5 hover:bg-blue-600/20 active:scale-95 transition"
            >
              <ExternalLink size={14} /> Form Publik
            </button>
            <div className="w-10 h-10 rounded-[24px] bg-card border border-border flex items-center justify-center shadow-sm">
              <ShieldCheck size={20} className="text-blue-600" />
            </div>
          </div>
        </div>

        {/* SUMMARY CARDS (Top Section) */}
        <div className="px-5 pt-2 grid grid-cols-1 md:grid-cols-3 gap-4">
          {/* Card 1: Total Keluhan */}
          <div className="rounded-[24px] bg-card shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-5 relative overflow-hidden">
            <div className="flex items-start justify-between">
              <div>
                <Text.Label className="block mb-1">Total Keluhan</Text.Label>
                <div className="flex items-baseline gap-2">
                  <Text.H1 className="text-3xl font-bold text-slate-900">{stats.total}</Text.H1>
                  <Text.Caption className="text-slate-400 not-italic">Laporan masuk</Text.Caption>
                </div>
              </div>
              <div className="w-10 h-10 rounded-[24px] bg-blue-600/10 flex items-center justify-center">
                <FileText size={20} className="text-blue-600" />
              </div>
            </div>
            <div className="mt-4 pt-3 border-t border-border flex items-center justify-between text-xs">
              <span className="text-slate-500 font-medium">Memerlukan Penanganan</span>
              <span className="font-bold text-red-600 bg-red-600/10 px-2 py-0.5 rounded-full">
                {stats.kendalaCount} Pending
              </span>
            </div>
          </div>

          {/* Card 2: Status Keluhan */}
          <div className="rounded-[24px] bg-card shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-5">
            <div className="flex items-start justify-between">
              <div>
                <Text.Label className="block mb-1">Status Keluhan</Text.Label>
                <div className="flex items-center gap-3 mt-1">
                  <div>
                    <Text.Amount className="block text-red-600 text-lg">{stats.kendalaCount}</Text.Amount>
                    <Text.Caption className="block text-slate-400 not-italic text-[10px]">Kendala</Text.Caption>
                  </div>
                  <div className="h-6 w-px bg-border" />
                  <div>
                    <Text.Amount className="block text-emerald-600 text-lg">{stats.teratasiCount}</Text.Amount>
                    <Text.Caption className="block text-slate-400 not-italic text-[10px]">Teratasi</Text.Caption>
                  </div>
                </div>
              </div>
              <div className="w-10 h-10 rounded-[24px] bg-emerald-600/10 flex items-center justify-center">
                <CheckCircle2 size={20} className="text-emerald-600" />
              </div>
            </div>
            {/* Visual Progress Bar */}
            <div className="mt-4 pt-3 border-t border-border space-y-1">
              <div className="h-2 w-full bg-secondary rounded-full overflow-hidden flex">
                <div
                  className="bg-red-500 h-full transition-all duration-300"
                  style={{ width: `${stats.total ? (stats.kendalaCount / stats.total) * 100 : 0}%` }}
                />
                <div
                  className="bg-emerald-500 h-full transition-all duration-300"
                  style={{ width: `${stats.total ? (stats.teratasiCount / stats.total) * 100 : 0}%` }}
                />
              </div>
            </div>
          </div>

          {/* Card 3: Jenis Keluhan Breakdown */}
          <div className="rounded-[24px] bg-card shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-5">
            <div className="flex items-start justify-between mb-2">
              <div>
                <Text.Label className="block mb-1">Jenis Keluhan</Text.Label>
                <Text.H2 className="text-xs text-slate-600 font-normal">Kategori terbanyak</Text.H2>
              </div>
              <div className="w-10 h-10 rounded-[24px] bg-amber-600/10 flex items-center justify-center">
                <AlertTriangle size={20} className="text-amber-600" />
              </div>
            </div>
            <div className="space-y-1.5 max-h-24 overflow-y-auto pr-1">
              {stats.catBreakdown.map((cat) => (
                <div key={cat.category} className="flex justify-between items-center text-[11px]">
                  <span className="text-slate-600 font-medium truncate max-w-[170px]">{cat.category}</span>
                  <span className="font-bold text-slate-800 bg-secondary px-2 py-0.5 rounded-full">
                    {cat.count}
                  </span>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Filter & Search Bar */}
        <div className="px-5 pt-6 space-y-3">
          <div className="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            {/* Filter Tabs Status */}
            <div className="flex bg-secondary rounded-[24px] p-1 border border-border">
              {(["all", "Kendala", "Teratasi"] as const).map((st) => {
                const active = statusFilter === st;
                const count =
                  st === "all"
                    ? reports.length
                    : reports.filter((x) => x.status === st).length;
                return (
                  <button
                    key={st}
                    onClick={() => setStatusFilter(st)}
                    className={`px-3.5 py-1.5 rounded-[24px] text-xs font-bold transition capitalize ${
                      active
                        ? "bg-card text-blue-600 shadow-[0_8px_30px_rgb(0,0,0,0.04)]"
                        : "text-slate-500 hover:text-slate-800"
                    }`}
                  >
                    {st === "all" ? "Semua Status" : st}{" "}
                    <span className="opacity-70 font-normal">({count})</span>
                  </button>
                );
              })}
            </div>

            {/* Filter Category Dropdown */}
            <div className="flex items-center gap-2">
              <div className="relative flex-1 sm:w-48">
                <Filter size={14} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                <select
                  value={categoryFilter}
                  onChange={(e) => setCategoryFilter(e.target.value)}
                  className="w-full pl-8 pr-4 py-2 rounded-[24px] bg-card border border-border text-xs font-medium text-slate-700 focus:outline-none focus:border-blue-600 appearance-none"
                >
                  <option value="all">Semua Kendala</option>
                  {KENDALA_OPTIONS.map((opt) => (
                    <option key={opt} value={opt}>
                      {opt}
                    </option>
                  ))}
                </select>
              </div>
            </div>
          </div>

          {/* Search Box */}
          <div className="relative">
            <Search size={16} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Cari berdasarkan Nama Wali, Nama Siswa, Tiket, atau Keterangan..."
              className="w-full pl-9 pr-4 py-2.5 rounded-[24px] bg-card border border-border text-xs font-medium text-slate-800 focus:outline-none focus:border-blue-600"
            />
          </div>
        </div>

        {/* DATA TABLE SECTION */}
        <div className="px-5 pt-4">
          <div className="rounded-[24px] bg-card shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden border border-border/50">
            <div className="overflow-x-auto">
              <table className="w-full text-left border-collapse min-w-[700px]">
                <thead>
                  <tr className="bg-secondary/60 border-b border-border text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    <th className="py-3 px-4 w-12 text-center">No</th>
                    <th className="py-3 px-4">Nama Wali</th>
                    <th className="py-3 px-4">Nama Siswa</th>
                    <th className="py-3 px-4">Kendala</th>
                    <th className="py-3 px-4">Timestamp</th>
                    <th className="py-3 px-4 text-center">Status</th>
                    <th className="py-3 px-4 text-right">Aksi Status</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-border text-xs">
                  {filteredReports.length === 0 ? (
                    <tr>
                      <td colSpan={7} className="py-12 text-center">
                        <MessageSquareText size={32} className="mx-auto text-slate-300 mb-2" />
                        <Text.Body className="text-slate-400 font-normal">
                          Tidak ditemukan data pengaduan Lapor Pak.
                        </Text.Body>
                      </td>
                    </tr>
                  ) : (
                    filteredReports.map((report, idx) => (
                      <tr key={report.id} className="hover:bg-slate-50/80 transition">
                        {/* 1. No */}
                        <td className="py-3.5 px-4 text-center font-semibold text-slate-400">
                          {idx + 1}
                        </td>

                        {/* 2. Nama Wali */}
                        <td className="py-3.5 px-4">
                          <Text.H2 className="text-xs font-bold text-slate-800">{report.parentName}</Text.H2>
                          <Text.Caption className="block text-slate-400 font-mono not-italic text-[10px]">
                            {report.parentPhone}
                          </Text.Caption>
                          {report.isParentUpdated && (
                            <span className="mt-0.5 inline-block text-[9px] bg-blue-600/10 text-blue-600 px-1.5 py-0.2 rounded font-semibold">
                              Diupdate Wali
                            </span>
                          )}
                        </td>

                        {/* 3. Nama Siswa */}
                        <td className="py-3.5 px-4">
                          <Text.Body className="font-semibold text-slate-800 text-xs">
                            {report.studentName}
                          </Text.Body>
                          <Text.Caption className="block text-slate-400 not-italic text-[10px]">
                            {report.school} — {report.className}
                          </Text.Caption>
                        </td>

                        {/* 4. Kendala */}
                        <td className="py-3.5 px-4 max-w-xs">
                          <span className="inline-block font-semibold text-blue-600 bg-blue-600/10 px-2 py-0.5 rounded-full text-[10px] mb-1">
                            {report.kendala}
                          </span>
                          {report.keterangan ? (
                            <Text.Body className="text-slate-600 text-xs line-clamp-2 leading-relaxed">
                              {report.keterangan}
                            </Text.Body>
                          ) : (
                            <Text.Caption className="block text-slate-400 italic text-[11px]">
                              (Tanpa keterangan)
                            </Text.Caption>
                          )}
                        </td>

                        {/* 5. Timestamp */}
                        <td className="py-3.5 px-4 whitespace-nowrap text-slate-500 font-mono text-[11px]">
                          {report.timestamp}
                        </td>

                        {/* 6. Status */}
                        <td className="py-3.5 px-4 text-center whitespace-nowrap">
                          {report.status === "Kendala" ? (
                            <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-red-600/10 text-red-600 text-[10px] font-bold">
                              <AlertTriangle size={12} /> Kendala
                            </span>
                          ) : (
                            <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-600/10 text-emerald-600 text-[10px] font-bold">
                              <CheckCircle2 size={12} /> Teratasi
                            </span>
                          )}
                        </td>

                        {/* 7. Aksi Status */}
                        <td className="py-3.5 px-4 text-right whitespace-nowrap">
                          <div className="flex items-center justify-end gap-1.5">
                            <button
                              type="button"
                              onClick={() => toggleLaporPakStatus(report.id)}
                              className={`px-3 py-1.5 rounded-[24px] font-bold text-xs flex items-center gap-1 transition active:scale-95 ${
                                report.status === "Kendala"
                                  ? "bg-emerald-600 text-white hover:bg-emerald-700"
                                  : "bg-amber-600/10 text-amber-600 hover:bg-amber-600/20"
                              }`}
                            >
                              <RefreshCw size={12} />
                              <span>{report.status === "Kendala" ? "Tandai Teratasi" : "Tandai Kendala"}</span>
                            </button>
                            <button
                              type="button"
                              onClick={() => {
                                if (confirm(`Hapus laporan ${report.id}?`)) {
                                  deleteLaporPakReport(report.id);
                                }
                              }}
                              className="w-7 h-7 rounded-[24px] bg-red-600/10 text-red-600 flex items-center justify-center hover:bg-red-600/20 transition active:scale-95"
                              title="Hapus Pengaduan"
                            >
                              <Trash2 size={13} />
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
