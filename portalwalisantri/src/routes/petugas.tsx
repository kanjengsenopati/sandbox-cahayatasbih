import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { ArrowLeft, Loader2, MessageCircle, HelpCircle, Search, X } from "lucide-react";
import { useQuery } from "@tanstack/react-query";
import { fetchOfficers } from "@/lib/api";
import { useState, useMemo } from "react";
import { Text } from "@/components/Text";

export const Route = createFileRoute("/petugas")({
  component: PetugasPage,
  head: () => ({ meta: [{ title: "Hubungi Petugas — SantriPay" }] }),
});

function PetugasPage() {
  const navigate = useNavigate();
  const [searchQuery, setSearchQuery] = useState("");

  const { data: officersRes, isLoading } = useQuery({
    queryKey: ["officers"],
    queryFn: async () => {
      const res = await fetchOfficers();
      return res.data;
    },
  });

  const studentName = officersRes?.student_name ?? "Santri";
  const officers: any[] = officersRes?.data ?? [];

  // Filter officers based on search query (name or position or duty)
  const filteredOfficers = useMemo(() => {
    if (!searchQuery.trim()) return officers;
    const q = searchQuery.toLowerCase().trim();
    return officers.filter(
      (officer) =>
        (officer?.name || "").toLowerCase().includes(q) ||
        (officer?.position || "").toLowerCase().includes(q) ||
        (officer?.duty || "").toLowerCase().includes(q)
    );
  }, [officers, searchQuery]);

  return (
    <div className="min-h-screen w-full flex justify-center bg-secondary">
      <div className="relative w-full max-w-4xl min-h-screen bg-background pb-32">
        {/* Hero Header */}
        <div
          className="relative px-5 pt-10 pb-20 rounded-b-[24px] overflow-hidden"
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
              className="w-10 h-10 rounded-2xl bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center text-white hover:bg-white/25 transition-all"
            >
              <ArrowLeft size={18} />
            </button>
            <div>
              <Text.Label className="text-white/70">Layanan Wali</Text.Label>
              <Text.H1 className="text-white text-lg sm:text-xl">Hubungi Petugas Pesantren dan Sekolah</Text.H1>
            </div>
          </div>

          <div className="relative mt-5 text-white">
            <Text.Label className="text-white/70">Wali dari</Text.Label>
            <p className="text-lg sm:text-xl font-bold mt-0.5 tracking-tight text-white">{studentName}</p>
            <Text.Caption className="text-white/80 not-italic mt-1 block">
              Hubungi pengurus untuk konsultasi, perizinan, atau administrasi santri.
            </Text.Caption>
          </div>
        </div>

        {/* Search Box & Header Toolbar */}
        <div className="relative -mt-7 px-5 z-20 mb-6">
          <div className="bg-white/90 backdrop-blur-md rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-2.5 px-4 flex items-center gap-3 border border-slate-100 transition-all focus-within:ring-2 focus-within:ring-blue-500/30">
            <Search className="w-5 h-5 text-slate-400 shrink-0" strokeWidth={2} />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Cari berdasarkan nama atau jabatan..."
              className="w-full bg-transparent text-sm text-slate-800 placeholder-slate-400 focus:outline-none font-medium"
            />
            {searchQuery && (
              <button
                onClick={() => setSearchQuery("")}
                className="p-1 rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors"
                title="Hapus pencarian"
              >
                <X className="w-4 h-4" strokeWidth={2} />
              </button>
            )}
          </div>

          {/* Result Count metadata */}
          {!isLoading && officers.length > 0 && (
            <div className="flex items-center justify-between px-2 mt-3">
              <Text.Label className="text-slate-400">
                {searchQuery ? `Hasil Pencarian (${filteredOfficers.length})` : `Semua Petugas (${officers.length})`}
              </Text.Label>
            </div>
          )}
        </div>

        {/* Officers Grid (2 Columns) */}
        <section className="px-5 relative z-10">
          {isLoading ? (
            <div className="bg-white rounded-[24px] p-8 flex flex-col items-center justify-center shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
              <Loader2 className="animate-spin text-blue-600 mb-2" size={28} />
              <Text.Body className="text-slate-500 text-xs font-semibold">Memuat data petugas...</Text.Body>
            </div>
          ) : filteredOfficers.length === 0 ? (
            <div className="bg-white rounded-[24px] p-8 text-center shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
              <HelpCircle className="mx-auto text-slate-400 mb-2" size={32} />
              <Text.H2 className="text-slate-800 text-sm">
                {searchQuery ? "Petugas tidak ditemukan" : "Tidak Ada Petugas"}
              </Text.H2>
              <Text.Caption className="text-slate-400 mt-1 block">
                {searchQuery
                  ? `Tidak ada nama atau jabatan yang cocok dengan "${searchQuery}"`
                  : "Data petugas belum dikonfigurasi di panel admin."}
              </Text.Caption>
              {searchQuery && (
                <button
                  onClick={() => setSearchQuery("")}
                  className="mt-4 px-4 py-2 rounded-xl bg-blue-50 text-blue-600 font-semibold text-xs hover:bg-blue-100 transition-colors"
                >
                  Reset Pencarian
                </button>
              )}
            </div>
          ) : (
            <div className="grid grid-cols-2 gap-3 sm:gap-3.5">
              {filteredOfficers.map((officer: any) => {
                // Extract initials if photo is absent
                const officerName = officer?.name || "Petugas Pesantren";
                const initials =
                  officerName
                    .split(" ")
                    .filter((n: string) => !n.includes(".") && n.length > 0)
                    .slice(0, 2)
                    .map((n: string) => n[0])
                    .join("")
                    .toUpperCase() || "ST";

                // WA link preparation
                const cleanWa = (officer?.phone || "").replace(/[^0-9]/g, "");

                return (
                  <div
                    key={officer.id}
                    className="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-3.5 sm:p-4 flex flex-col justify-between hover:shadow-[0_12px_36px_rgb(0,0,0,0.08)] transition-all duration-200 group border-0"
                  >
                    <div>
                      {/* Top Row: Avatar & Position Badge */}
                      <div className="flex items-start justify-between gap-1.5 mb-3">
                        {officer.photo ? (
                          <img
                            src={`/${officer.photo}`}
                            alt={officerName}
                            className="w-11 h-11 rounded-[16px] object-cover shrink-0 border border-slate-100 shadow-xs"
                          />
                        ) : (
                          <div className="w-11 h-11 rounded-[16px] bg-blue-600/10 text-blue-600 font-bold text-xs flex items-center justify-center shrink-0 border border-blue-600/20 shadow-xs">
                            {initials}
                          </div>
                        )}

                        {/* Top-Right Action / Tag Cluster */}
                        {officer.position && (
                          <span className="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-700 text-[10px] font-bold uppercase tracking-wider shrink-0 max-w-[70%] truncate text-right">
                            {officer.position}
                          </span>
                        )}
                      </div>

                      {/* Content: Name & Duty */}
                      <div className="space-y-1 mb-3">
                        <Text.H2 className="text-slate-900 font-bold text-xs sm:text-sm leading-tight line-clamp-2 min-h-[2.25rem] group-hover:text-blue-600 transition-colors">
                          {officer.name}
                        </Text.H2>
                        {officer.duty && (
                          <Text.Body className="text-slate-500 text-[11px] sm:text-xs leading-snug line-clamp-2 font-normal">
                            {officer.duty}
                          </Text.Body>
                        )}
                      </div>
                    </div>

                    {/* WhatsApp Action CTA */}
                    {cleanWa ? (
                      <a
                        href={`https://wa.me/${cleanWa}`}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="w-full py-2.5 px-2.5 rounded-[16px] bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] sm:text-xs flex items-center justify-center gap-1.5 shadow-sm active:scale-[0.97] transition-all shrink-0"
                      >
                        <MessageCircle size={15} strokeWidth={2.2} className="shrink-0" />
                        <span className="truncate">Hubungi via WA</span>
                      </a>
                    ) : (
                      <button
                        disabled
                        className="w-full py-2.5 px-2.5 rounded-[16px] bg-slate-100 text-slate-400 font-bold text-[11px] sm:text-xs flex items-center justify-center gap-1.5 cursor-not-allowed shrink-0"
                      >
                        <MessageCircle size={15} strokeWidth={2.2} className="shrink-0" />
                        <span className="truncate">No. WA Tdk Ada</span>
                      </button>
                    )}
                  </div>
                );
              })}
            </div>
          )}
        </section>
      </div>
    </div>
  );
}

