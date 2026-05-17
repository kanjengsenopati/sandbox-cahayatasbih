import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { ShieldAlert, ShieldCheck, ArrowLeft, Loader2 } from "lucide-react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { fetchBlockStatus, toggleBlock } from "@/lib/api";
import { toast } from "sonner";

export const Route = createFileRoute("/blokir-saldo")({
  component: BlokirSaldo,
  head: () => ({ meta: [{ title: "Blokir Saldo — SantriPay" }] }),
});

function BlokirSaldo() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();

  const { data: statusData, isLoading } = useQuery({
    queryKey: ["block-status"],
    queryFn: async () => {
      const res = await fetchBlockStatus();
      return res.data;
    },
  });

  const toggleMutation = useMutation({
    mutationFn: toggleBlock,
    onSuccess: (res) => {
      queryClient.invalidateQueries({ queryKey: ["block-status"] });
      queryClient.invalidateQueries({ queryKey: ["dashboard"] });
      toast.success(res.data.message || "Status kartu berhasil diubah");
    },
    onError: () => {
      toast.error("Gagal mengubah status kartu. Silakan coba lagi.");
    },
  });

  const isBlocked = statusData?.is_blocked ?? false;
  const studentName = statusData?.student_name ?? "Santri";

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
              <p className="text-[11px] text-white/70 font-semibold uppercase tracking-wider">Keamanan Kartu</p>
              <p className="text-base font-bold text-white">Blokir Sementara Kartu</p>
            </div>
          </div>

          <div className="relative mt-6 text-white">
            <p className="text-xs text-white/70 uppercase tracking-widest font-semibold">Nama Santri</p>
            <p className="text-xl font-bold mt-1 tracking-tight">{studentName}</p>
          </div>
        </div>

        {/* Content */}
        <section className="px-6 -mt-10 relative z-10 flex flex-col items-center justify-center text-center">
          {isLoading ? (
            <div className="bg-card rounded-3xl border border-border p-8 flex flex-col items-center justify-center shadow-[var(--shadow-card)] w-full">
              <Loader2 className="animate-spin text-primary mb-2" size={28} />
              <p className="text-xs font-semibold text-muted-foreground">Memuat status kartu...</p>
            </div>
          ) : (
            <div className="w-full space-y-5">
              {/* Shield Status Icon */}
              <div className="flex justify-center">
                <div className={`w-28 h-28 rounded-3xl flex items-center justify-center shadow-[var(--shadow-soft)] transition-all duration-500 ${
                  isBlocked 
                    ? "bg-destructive/10 text-destructive border border-destructive/20" 
                    : "bg-emerald-500/10 text-emerald-600 border border-emerald-500/20"
                }`}>
                  {isBlocked ? (
                    <ShieldAlert size={56} strokeWidth={1.5} className="animate-pulse" />
                  ) : (
                    <ShieldCheck size={56} strokeWidth={1.5} />
                  )}
                </div>
              </div>
              
              {/* Details card */}
              <div className="bg-card rounded-3xl border border-border shadow-[var(--shadow-soft)] p-6">
                <span className={`inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider ${
                  isBlocked ? "bg-destructive/10 text-destructive" : "bg-emerald-500/10 text-emerald-700"
                }`}>
                  {isBlocked ? "KARTU NONAKTIF" : "KARTU AKTIF"}
                </span>
                
                <h3 className="mt-3 text-base font-bold text-foreground">Status Jajan Kartu Santri</h3>
                
                <p className="text-xs text-muted-foreground leading-relaxed mt-2">
                  {isBlocked 
                    ? "Kartu fisik santri saat ini diblokir secara manual oleh wali santri. Seluruh transaksi belanja di POS Kantin akan otomatis ditolak secara real-time demi keamanan."
                    : "Kartu fisik santri saat ini aktif dan dapat digunakan untuk belanja di POS Kantin Pesantren sesuai limit jajan harian yang Anda atur."
                  }
                </p>
              </div>

              {/* Action Button */}
              <button
                onClick={() => toggleMutation.mutate()}
                disabled={toggleMutation.isPending}
                className="w-full py-4 rounded-3xl font-bold text-xs flex items-center justify-center gap-2 transition active:scale-[0.98] shadow-sm text-white"
                style={{ 
                  background: isBlocked 
                    ? "linear-gradient(135deg, #10b981 0%, #059669 100%)" 
                    : "linear-gradient(135deg, #ef4444 0%, #dc2626 100%)"
                }}
              >
                {toggleMutation.isPending ? (
                  <Loader2 className="w-5 h-5 animate-spin" />
                ) : isBlocked ? (
                  "Aktifkan Kembali Kartu"
                ) : (
                  "Blokir Sementara Kartu"
                )}
              </button>
            </div>
          )}
        </section>
      </div>
    </div>
  );
}
