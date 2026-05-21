import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { ArrowLeft, CreditCard, Wallet, Plus, ChevronRight, Loader2 } from "lucide-react";
import { useQuery } from "@tanstack/react-query";
import { fetchPaymentMethods } from "@/lib/api";

export const Route = createFileRoute("/profil_/pembayaran")({
  component: PembayaranPage,
  head: () => ({ meta: [{ title: "Metode Pembayaran — SantriPay" }] }),
});

function PembayaranPage() {
  const navigate = useNavigate();
  
  const { data, isLoading } = useQuery({
    queryKey: ["payment-methods"],
    queryFn: async () => {
      const res = await fetchPaymentMethods();
      return res.data?.data || [];
    }
  });

  return (
    <div className="min-h-screen w-full flex justify-center bg-secondary">
      <div className="relative w-full max-w-md min-h-screen bg-background pb-24">
        {/* Hero */}
        <div
          className="relative px-6 pt-12 pb-20 rounded-b-[2rem] overflow-hidden"
          style={{ background: "var(--gradient-hero)" }}
        >
          <div className="absolute -top-16 -right-12 w-56 h-56 rounded-full bg-primary-glow/30 blur-3xl" />
          <div className="relative flex items-center gap-3">
            <button
              onClick={() => navigate({ to: "/profil" })}
              className="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center text-white"
            >
              <ArrowLeft size={18} />
            </button>
            <div>
              <p className="text-[11px] text-white/70 font-semibold uppercase tracking-wider">Profil</p>
              <p className="text-base font-bold text-white">Metode Pembayaran</p>
            </div>
          </div>
        </div>

        {/* Content */}
        <div className="px-6 -mt-12 relative z-10 space-y-4">
          <div className="bg-card rounded-3xl border border-border shadow-[var(--shadow-card)] p-4 divide-y divide-border">
            {isLoading ? (
              <div className="flex flex-col items-center justify-center py-6">
                <Loader2 className="w-8 h-8 animate-spin text-primary" />
                <p className="text-sm font-semibold mt-2 text-foreground">Memuat metode pembayaran...</p>
              </div>
            ) : data && data.length > 0 ? (
              data.map((pm: any, idx: number) => (
                <button key={idx} className="w-full flex items-center gap-3 py-3 active:opacity-70 transition text-left">
                  <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                    {pm.payment_type === 'ewallet' ? <Wallet size={18} /> : <CreditCard size={18} />}
                  </div>
                  <div className="flex-1">
                    <p className="text-sm font-bold text-foreground">{pm.name || pm.payment_type}</p>
                    <p className="text-[11px] text-muted-foreground mt-0.5">{pm.provider || pm.payment_type_label}</p>
                  </div>
                  <ChevronRight size={18} className="text-muted-foreground" />
                </button>
              ))
            ) : (
              <div className="text-center py-6 text-muted-foreground text-sm font-medium">
                Belum ada metode pembayaran.
              </div>
            )}
          </div>
          
          <button className="w-full mt-4 flex items-center justify-center gap-2 p-4 rounded-2xl border-2 border-dashed border-primary/40 text-primary font-bold active:bg-primary/5 transition">
            <Plus size={18} /> Tambah Metode Baru
          </button>
        </div>
      </div>
    </div>
  );
}
