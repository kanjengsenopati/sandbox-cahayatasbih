import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { Heart, ArrowLeft } from "lucide-react";
import { MobileShell } from "@/components/MobileShell";
import { Text } from "@/components/Text";

export const Route = createFileRoute("/perilaku")({
  component: Perilaku,
  head: () => ({ meta: [{ title: "Catatan Perilaku — SantriPay" }] }),
});

function Perilaku() {
  const navigate = useNavigate();

  return (
    <MobileShell>
      <header className="px-5 pt-10 pb-4 flex items-center gap-3">
        <button 
          onClick={() => navigate({ to: "/dashboard" })}
          className="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-50 active:scale-95 transition"
        >
          <ArrowLeft size={20} className="text-slate-600" />
        </button>
        <Text.H1>Perilaku</Text.H1>
      </header>

      <section className="px-5 mt-6 flex-1 flex flex-col items-center justify-center py-20 text-center">
        <div className="w-20 h-20 rounded-[24px] bg-rose-50 flex items-center justify-center text-rose-600 shadow-[0_8px_30px_rgb(225,29,72,0.06)] mb-6 animate-pulse">
          <Heart size={40} strokeWidth={1.5} />
        </div>
        
        <div className="bg-white rounded-[24px] p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-50/50 max-w-sm w-full">
          <Text.Label className="text-rose-500 mb-2 block">Karakter & Akhlak</Text.Label>
          <Text.H2 className="mb-3">Modul Penilaian Perilaku</Text.H2>
          <Text.Body className="text-slate-500 mb-4">
            Catatan kedisiplinan, ibadah harian, dan akhlakul karimah santri yang tercatat secara real-time dari pengasuhan pondok pesantren.
          </Text.Body>
          <span className="inline-flex px-3 py-1 bg-slate-50 rounded-full text-slate-400 text-xs font-semibold">
            Segera Hadir di Versi Berikutnya
          </span>
        </div>
      </section>
    </MobileShell>
  );
}
