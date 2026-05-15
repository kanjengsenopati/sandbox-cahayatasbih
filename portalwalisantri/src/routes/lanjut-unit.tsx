import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { ArrowLeft, CheckCircle2, GraduationCap, Loader2 } from "lucide-react";
import { MobileShell } from "@/components/MobileShell";
import { useSantri } from "@/contexts/SantriContext";
import { useQuery, useMutation } from "@tanstack/react-query";
import { fetchDashboard, api } from "@/lib/api";


export const Route = createFileRoute("/lanjut-unit")({
  component: LanjutUnitPage,
  head: () => ({
    meta: [{ title: "Lanjut Pendidikan — SantriPay" }],
  }),
});

function LanjutUnitPage() {
  const navigate = useNavigate();
  const { active } = useSantri();

  const { data: dashboard, isLoading } = useQuery({
    queryKey: ["dashboard", active?.id],
    queryFn: async () => {
      const res = await fetchDashboard();
      return res.data;
    },
    enabled: !!active,
  });

  const generateBillMutation = useMutation({
    mutationFn: async () => {
      if (!active?.id || !dashboard?.unit_transfer?.id) throw new Error("Data tidak lengkap");
      const response = await api.post("/unit-transfer/continue", {
        student_id: active.id,
        config_id: dashboard.unit_transfer.id,
      });
      return response.data;
    },
    onSuccess: (data) => {
      // Success handled by navigation
      if (data.data?.bill?.id) {
        navigate({ to: `/tagihan/${data.data.bill.id}` });
      } else {
        navigate({ to: "/tagihan" });
      }
    },
    onError: (error: any) => {
      alert(error.response?.data?.message || "Gagal membuat tagihan");
    },
  });

  const fmt = (n: number) =>
    new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(n);

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-background">
        <Loader2 className="animate-spin text-primary" size={40} />
      </div>
    );
  }

  const transferConfig = dashboard?.unit_transfer;

  if (!transferConfig) {
    return (
      <MobileShell>
        <div className="flex flex-col h-screen">
          <header className="flex items-center gap-4 p-5">
            <button onClick={() => navigate({ to: "/dashboard" })} className="p-2 -ml-2 text-foreground active:bg-secondary rounded-xl">
              <ArrowLeft size={24} />
            </button>
            <h1 className="text-lg font-bold text-foreground">Pendaftaran</h1>
          </header>
          <div className="flex-1 flex flex-col items-center justify-center p-6 text-center text-muted-foreground">
            <GraduationCap size={48} className="mb-4 text-primary/30" />
            <p>Jalur pendaftaran untuk jenjang berikutnya belum tersedia saat ini.</p>
          </div>
        </div>
      </MobileShell>
    );
  }

  return (
    <MobileShell>
      <div className="flex flex-col min-h-screen bg-background">
        <header className="flex items-center gap-4 p-5 bg-card border-b border-border z-10 sticky top-0">
          <button onClick={() => navigate({ to: "/dashboard" })} className="p-2 -ml-2 text-foreground active:bg-secondary rounded-xl">
            <ArrowLeft size={24} />
          </button>
          <h1 className="text-lg font-bold text-foreground">Pendaftaran Lanjutan</h1>
        </header>

        <div className="p-6">
          <div className="bg-gradient-to-br from-primary to-primary-deep rounded-3xl p-6 text-white shadow-xl mb-6 text-center relative overflow-hidden">
             <div className="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -mr-10 -mt-10" />
             <GraduationCap size={48} className="mx-auto mb-4 text-white/90" />
             <h2 className="text-xl font-bold mb-2">Selamat!</h2>
             <p className="text-white/80 text-sm">Ananda {active?.name} berkesempatan untuk melanjutkan pendidikan ke jenjang {transferConfig.to_school?.name}.</p>
          </div>

          <h3 className="text-base font-bold text-foreground mb-3">Informasi Pendaftaran</h3>
          <div className="bg-card rounded-2xl p-4 border border-border shadow-sm mb-6 space-y-4">
             <div className="flex justify-between items-center">
               <span className="text-sm text-muted-foreground">Unit Asal</span>
               <span className="text-sm font-semibold">{transferConfig.from_school?.name}</span>
             </div>
             <div className="flex justify-between items-center">
               <span className="text-sm text-muted-foreground">Unit Tujuan</span>
               <span className="text-sm font-semibold text-primary">{transferConfig.to_school?.name}</span>
             </div>
             <div className="flex justify-between items-center">
               <span className="text-sm text-muted-foreground">Nama Tagihan</span>
               <span className="text-sm font-semibold">{transferConfig.bill_type?.name}</span>
             </div>
             <div className="pt-4 border-t border-border flex justify-between items-center">
               <span className="text-sm font-bold text-foreground">Total Biaya</span>
               <span className="text-lg font-bold text-emerald-600">{fmt(transferConfig.amount || 0)}</span>
             </div>
          </div>

          <div className="bg-blue-50 text-blue-800 rounded-2xl p-4 flex gap-3 items-start border border-blue-100">
             <CheckCircle2 size={20} className="shrink-0 mt-0.5 text-blue-600" />
             <p className="text-xs leading-relaxed">
               Dengan menekan tombol di bawah, Anda menyetujui untuk mendaftarkan Ananda ke jenjang berikutnya. Sistem akan membuatkan tagihan daftar ulang. Setelah lunas, kelas dan unit Ananda akan otomatis diperbarui.
             </p>
          </div>
        </div>

        <div className="mt-auto p-6">
          <button
            onClick={() => generateBillMutation.mutate()}
            disabled={generateBillMutation.isPending}
            className="w-full h-14 rounded-2xl bg-primary text-primary-foreground font-bold text-sm shadow-lg shadow-primary/30 flex items-center justify-center gap-2 active:scale-[0.98] transition disabled:opacity-70 disabled:cursor-not-allowed"
          >
            {generateBillMutation.isPending ? <Loader2 size={20} className="animate-spin" /> : "Buat Tagihan Pendaftaran"}
          </button>
        </div>
      </div>
    </MobileShell>
  );
}
