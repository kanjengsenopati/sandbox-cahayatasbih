import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { ArrowLeft, Loader2, Phone, MessageCircle, HelpCircle } from "lucide-react";
import { useQuery } from "@tanstack/react-query";
import { fetchOfficers } from "@/lib/api";

export const Route = createFileRoute("/petugas")({
  component: PetugasPage,
  head: () => ({ meta: [{ title: "Hubungi Petugas — SantriPay" }] }),
});

function PetugasPage() {
  const navigate = useNavigate();

  const { data: officersRes, isLoading } = useQuery({
    queryKey: ["officers"],
    queryFn: async () => {
      const res = await fetchOfficers();
      return res.data;
    },
  });

  const studentName = officersRes?.student_name ?? "Santri";
  const officers = officersRes?.data ?? [];

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
              <p className="text-[11px] text-white/70 font-semibold uppercase tracking-wider">Layanan Wali</p>
              <p className="text-base font-bold text-white">Hubungi Petugas Pesantren</p>
            </div>
          </div>

          <div className="relative mt-6 text-white">
            <p className="text-xs text-white/70 uppercase tracking-widest font-semibold">Wali dari</p>
            <p className="text-xl font-bold mt-1 tracking-tight">{studentName}</p>
            <p className="text-[11px] text-white/70 mt-1">
              Hubungi pengurus untuk konsultasi, perizinan, atau administrasi santri.
            </p>
          </div>
        </div>

        {/* Officers list */}
        <section className="px-6 -mt-10 relative z-10 space-y-4">
          {isLoading ? (
            <div className="bg-card rounded-3xl border border-border p-8 flex flex-col items-center justify-center shadow-[var(--shadow-card)]">
              <Loader2 className="animate-spin text-primary mb-2" size={28} />
              <p className="text-xs font-semibold text-muted-foreground">Memuat data petugas...</p>
            </div>
          ) : officers.length === 0 ? (
            <div className="bg-card rounded-3xl border border-border p-8 text-center shadow-[var(--shadow-card)]">
              <HelpCircle className="mx-auto text-muted-foreground mb-3" size={32} />
              <p className="text-sm font-bold text-foreground">Tidak Ada Petugas</p>
              <p className="text-xs text-muted-foreground mt-1">
                Data petugas belum dikonfigurasi di panel admin.
              </p>
            </div>
          ) : (
            officers.map((officer: any) => {
              // Extract initials if photo is absent
              const officerName = officer?.name || "Petugas Pesantren";
              const initials = officerName
                .split(" ")
                .filter((n: string) => !n.includes(".") && n.length > 0)
                .slice(0, 2)
                .map((n: string) => n[0])
                .join("")
                .toUpperCase() || "ST";

              // WA link preparation (using international format)
              const cleanWa = officer.phone.replace(/[^0-9]/g, "");

              return (
                <div 
                  key={officer.id}
                  className="bg-card rounded-3xl border border-border shadow-[var(--shadow-soft)] p-5 hover:shadow-[var(--shadow-card)] transition-all duration-300"
                >
                  <div className="flex items-start gap-4">
                    {officer.photo ? (
                      <img 
                        src={`/${officer.photo}`} 
                        alt={officer.name} 
                        className="w-12 h-12 rounded-2xl object-cover shrink-0 border border-border"
                      />
                    ) : (
                      <div className="w-12 h-12 rounded-2xl bg-primary/10 text-primary font-bold text-sm flex items-center justify-center shrink-0 border border-primary/20">
                        {initials}
                      </div>
                    )}
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-bold text-foreground leading-tight truncate">{officer.name}</p>
                      <span className="inline-flex mt-1.5 px-2 py-0.5 rounded-md bg-primary/5 text-primary text-[10px] font-bold uppercase tracking-wider">
                        {officer.position}
                      </span>
                      <p className="text-xs text-muted-foreground mt-2 leading-relaxed">
                        {officer.duty}
                      </p>
                    </div>
                  </div>

                  <a 
                    href={`https://wa.me/${cleanWa}`}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="mt-4 w-full py-3 rounded-2xl font-bold text-xs flex items-center justify-center gap-2 transition active:scale-[0.98] shadow-sm hover:opacity-90"
                    style={{ 
                      background: "linear-gradient(135deg, #25D366 0%, #128C7E 100%)", 
                      color: "#FFFFFF" 
                    }}
                  >
                    <MessageCircle size={16} /> Hubungi via WhatsApp
                  </a>
                </div>
              );
            })
          )}
        </section>
      </div>
    </div>
  );
}
