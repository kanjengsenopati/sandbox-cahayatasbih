import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import {
  ArrowLeft,
  Sliders,
  CheckCircle2,
  Sparkles,
  Loader2,
} from "lucide-react";
import { useSantri } from "@/contexts/SantriContext";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { fetchLimit, updateLimit as updateLimitApi } from "@/lib/api";

export const Route = createFileRoute("/limit")({
  component: LimitPage,
  head: () => ({ meta: [{ title: "Atur Limit — SantriPay" }] }),
});

const PRESETS = [50_000, 100_000, 150_000, 250_000];

const fmt = (n: number) =>
  new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(n);

function LimitPage() {
  const navigate = useNavigate();
  const { active, isLoading: isLoadingSantri } = useSantri();
  const queryClient = useQueryClient();
  const [daily, setDaily] = useState(0);
  const [enabled, setEnabled] = useState(true);
  const [saved, setSaved] = useState(false);

  const { data: limitData, isLoading: isLoadingLimit } = useQuery({
    queryKey: ["limit", active?.id],
    queryFn: async () => {
      const res = await fetchLimit();
      return res.data;
    },
    enabled: !!active,
  });

  useEffect(() => {
    if (limitData) {
      setDaily(limitData.daily_limit || 0);
      setEnabled(limitData.daily_limit > 0);
    }
  }, [limitData]);

  const mutation = useMutation({
    mutationFn: (newLimit: number) => updateLimitApi({ daily_limit: newLimit }),
    onSuccess: () => {
      setSaved(true);
      queryClient.invalidateQueries({ queryKey: ["limit"] });
      queryClient.invalidateQueries({ queryKey: ["active-student"] });
      setTimeout(() => navigate({ to: "/dashboard" }), 800);
    },
  });

  if (isLoadingSantri || isLoadingLimit) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-background">
        <Loader2 className="animate-spin text-primary" size={40} />
      </div>
    );
  }

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
              <p className="text-[11px] text-white/70 font-semibold uppercase tracking-wider">Limit</p>
              <p className="text-base font-bold text-white">Atur Pengeluaran Harian</p>
            </div>
          </div>

          <div className="relative mt-6 text-white">
            <p className="text-xs text-white/70 uppercase tracking-widest font-semibold">Limit Harian</p>
            <p className="text-3xl font-bold mt-1 tracking-tight">{fmt(daily)}</p>
            <p className="text-[11px] text-white/70 mt-1">
              Santri tidak dapat menghabiskan lebih dari ini per hari.
            </p>
          </div>
        </div>

        {/* Toggle card */}
        <div className="px-6 -mt-14 relative z-10">
          <div className="bg-card rounded-3xl border border-border shadow-[var(--shadow-card)] p-5 flex items-center gap-3">
            <div className="w-11 h-11 rounded-xl bg-[var(--gradient-card)] flex items-center justify-center text-primary-foreground">
              <Sliders size={20} />
            </div>
            <div className="flex-1">
              <p className="text-sm font-bold text-foreground">Aktifkan Limit Harian</p>
              <p className="text-[11px] text-muted-foreground">
                Transaksi ditolak otomatis jika melewati limit.
              </p>
            </div>
            <button
              onClick={() => {
                setEnabled(!enabled);
                if (enabled) setDaily(0);
              }}
              className={`relative w-12 h-7 rounded-full transition ${
                enabled ? "bg-primary" : "bg-muted"
              }`}
            >
              <div
                className={`absolute top-0.5 w-6 h-6 rounded-full bg-white shadow transition-transform ${
                  enabled ? "translate-x-5" : "translate-x-0.5"
                }`}
              />
            </button>
          </div>
        </div>

        {/* Slider */}
        <section className="px-6 mt-5">
          <div className="bg-card rounded-3xl border border-border shadow-[var(--shadow-soft)] p-5">
            <div className="flex items-center justify-between">
              <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                Slider Limit
              </p>
              <span className="text-sm font-bold text-primary">{fmt(daily)}</span>
            </div>

            <input
              type="range"
              min={0}
              max={500_000}
              step={10_000}
              value={daily}
              onChange={(e) => {
                const val = parseInt(e.target.value, 10);
                setDaily(val);
                setEnabled(val > 0);
              }}
              className="w-full mt-4 accent-primary disabled:opacity-40"
            />

            <div className="flex justify-between text-[10px] text-muted-foreground mt-1">
              <span>Rp 0</span>
              <span>Rp 500rb</span>
            </div>

            <div className="grid grid-cols-4 gap-2 mt-4">
              {PRESETS.map((p) => {
                const active = daily === p;
                return (
                  <button
                    key={p}
                    onClick={() => {
                      setDaily(p);
                      setEnabled(true);
                    }}
                    className={`py-2.5 rounded-xl text-[11px] font-bold border transition ${
                      active
                        ? "bg-[var(--gradient-card)] text-primary-foreground border-transparent"
                        : "bg-secondary text-foreground border-transparent"
                    }`}
                  >
                    {p / 1000}rb
                  </button>
                );
              })}
            </div>
          </div>
        </section>

        {/* Smart tip */}
        <section className="px-6 mt-5">
          <div className="rounded-2xl p-4 flex items-center gap-3 border border-border bg-accent">
            <Sparkles size={18} className="text-primary shrink-0" />
            <p className="text-[11px] text-foreground leading-relaxed">
              Limit harian membantu mengontrol jajan santri di kantin dan toko pondok agar tetap hemat.
            </p>
          </div>
        </section>

        {/* Save bar */}
        <div className="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md px-4 pb-4 pt-3 bg-gradient-to-t from-background via-background to-background/0 z-40">
          <button
            onClick={() => mutation.mutate(daily)}
            disabled={mutation.isPending}
            className="w-full py-4 rounded-2xl text-primary-foreground font-semibold text-sm shadow-[var(--shadow-glow)] flex items-center justify-center gap-2 disabled:opacity-50 transition active:scale-[0.98]"
            style={{ background: "var(--gradient-card)" }}
          >
            {mutation.isPending ? (
              <Loader2 className="animate-spin" size={18} />
            ) : saved ? (
              <>
                <CheckCircle2 size={18} /> Tersimpan
              </>
            ) : (
              "Simpan Pengaturan Limit"
            )}
          </button>
        </div>
      </div>
    </div>
  );
}
