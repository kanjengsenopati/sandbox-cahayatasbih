import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { ShieldAlert, ShieldCheck, ArrowLeft, Loader2 } from "lucide-react";
import { MobileShell } from "@/components/MobileShell";
import { Text } from "@/components/Text";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { fetchBlockStatus, toggleBlock } from "@/lib/api";
import { toast } from "sonner";

export const Route = createFileRoute("/blokir-saldo")({
  component: BlokirSaldo,
  head: () => ({ meta: [{ title: "Blokir Saldo — CAHAYA TASBIH" }] }),
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
    <MobileShell>
      <header className="px-5 pt-10 pb-4 flex items-center gap-3">
        <button 
          onClick={() => navigate({ to: "/dashboard" })}
          className="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-50 active:scale-95 transition"
        >
          <ArrowLeft size={20} className="text-slate-600" />
        </button>
        <Text.H1>Blokir Saldo</Text.H1>
      </header>

      <section className="px-5 mt-6 flex-1 flex flex-col items-center justify-center py-10 text-center">
        {isLoading ? (
          <div className="flex flex-col items-center justify-center">
            <Loader2 className="w-10 h-10 animate-spin text-blue-600 mb-2" />
            <Text.Body className="text-slate-400">Memuat status kartu...</Text.Body>
          </div>
        ) : (
          <>
            <div className={`w-28 h-28 rounded-[24px] flex items-center justify-center shadow-[0_12px_40px_rgba(0,0,0,0.08)] mb-8 transition-all duration-500 scale-105 ${
              isBlocked 
                ? "bg-red-50 text-red-600 shadow-red-100/50" 
                : "bg-emerald-50 text-emerald-600 shadow-emerald-100/50"
            }`}>
              {isBlocked ? (
                <ShieldAlert size={56} strokeWidth={1.5} className="animate-pulse" />
              ) : (
                <ShieldCheck size={56} strokeWidth={1.5} />
              )}
            </div>
            
            <div className="bg-white rounded-[24px] p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-50/50 max-w-sm w-full mb-8">
              <Text.Label className={`mb-2 block font-bold tracking-widest ${isBlocked ? "text-red-500" : "text-emerald-500"}`}>
                {isBlocked ? "KARTU NONAKTIF" : "KARTU AKTIF"}
              </Text.Label>
              <Text.H2 className="mb-3 text-slate-800">Status Kartu {studentName}</Text.H2>
              
              <Text.Body className="text-slate-500 leading-relaxed mb-2">
                {isBlocked 
                  ? "Kartu fisik santri saat ini diblokir. Segala jenis transaksi belanja di POS Kantin akan otomatis ditolak demi keamanan."
                  : "Kartu fisik santri saat ini aktif dan dapat digunakan untuk belanja di POS Kantin sesuai limit harian."
                }
              </Text.Body>
            </div>

            <button
              onClick={() => toggleMutation.mutate()}
              disabled={toggleMutation.isPending}
              className={`w-full max-w-sm py-4 rounded-[24px] font-bold text-white shadow-[0_8px_30px_rgba(0,0,0,0.08)] transition-all duration-300 active:scale-98 flex items-center justify-center gap-2 ${
                isBlocked 
                  ? "bg-emerald-600 hover:bg-emerald-500 shadow-emerald-200" 
                  : "bg-red-600 hover:bg-red-500 shadow-red-200"
              }`}
            >
              {toggleMutation.isPending ? (
                <Loader2 className="w-5 h-5 animate-spin" />
              ) : isBlocked ? (
                "Aktifkan Kembali Kartu"
              ) : (
                "Blokir Sementara Kartu"
              )}
            </button>
          </>
        )}
      </section>
    </MobileShell>
  );
}
