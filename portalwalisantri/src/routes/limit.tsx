import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import {
  ArrowLeft,
  Sliders,
  CheckCircle2,
  Sparkles,
  Loader2,
  ShieldOff,
  X,
} from "lucide-react";
import { useSantri } from "@/contexts/SantriContext";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { fetchLimit, updateLimit as updateLimitApi } from "@/lib/api";
import { Text } from "@/components/Text";

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
  const [customInput, setCustomInput] = useState("");
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
      if (limitData.daily_limit > 0) {
        // Limit kustom Wali aktif
        setDaily(limitData.daily_limit);
        setCustomInput(new Intl.NumberFormat("id-ID").format(limitData.daily_limit));
        setEnabled(true);
      } else if (limitData.daily_limit === -1) {
        // Wali secara eksplisit minta No Limit
        setDaily(0);
        setCustomInput("");
        setEnabled(false);
      } else {
        // Wali belum set apapun, ikuti backoffice
        if (limitData.effective_limit > 0) {
          setDaily(limitData.effective_limit);
          setCustomInput(new Intl.NumberFormat("id-ID").format(limitData.effective_limit));
          setEnabled(true);
        } else {
          setDaily(0);
          setCustomInput("");
          setEnabled(false);
        }
      }
    }
  }, [limitData]);

  const mutation = useMutation({
    mutationFn: (newLimit: number) => updateLimitApi({ daily_limit: newLimit }),
    onSuccess: () => {
      setSaved(true);
      queryClient.invalidateQueries({ queryKey: ["limit"] });
      queryClient.invalidateQueries({ queryKey: ["active-student"] });
      queryClient.invalidateQueries({ queryKey: ["dashboard"] });
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

  if ((active as any)?.show_pwa_saldo === false) {
    return (
      <div className="min-h-screen w-full flex justify-center bg-secondary">
        <div className="relative w-full max-w-md min-h-screen bg-background p-6 flex flex-col items-center justify-center text-center">
          <div className="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mb-4 border border-amber-200 shadow-sm">
            <ShieldOff size={32} />
          </div>
          <Text.H2 className="text-slate-800">Layanan Limit Saldo Dinonaktifkan</Text.H2>
          <Text.Body className="text-slate-500 mt-2 max-w-xs">
            Pengaturan limit saldo saku dinonaktifkan untuk jenjang/kelas santri Anda ({active?.classroom?.name || active?.name}).
          </Text.Body>
          <button
            onClick={() => navigate({ to: "/dashboard" })}
            className="mt-6 px-6 py-3 rounded-2xl bg-primary text-primary-foreground font-bold text-sm shadow-md active:scale-95 transition"
          >
            Kembali ke Dashboard
          </button>
        </div>
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
              <p className="text-[11px] text-muted-foreground leading-tight mt-0.5">
                {limitData?.daily_limit === 0 && limitData?.effective_limit > 0 
                  ? "Limit saat ini diatur otomatis oleh sistem sekolah. Matikan untuk membatalkan limit." 
                  : "Transaksi ditolak otomatis jika pengeluaran harian melewati limit."}
              </p>
            </div>
            <button
              onClick={() => {
                const next = !enabled;
                setEnabled(next);
                if (!next) {
                  setDaily(0);
                  setCustomInput("");
                } else {
                  const defVal = (limitData?.daily_limit && limitData.daily_limit > 0) ? limitData.daily_limit : (limitData?.effective_limit || 50_000);
                  setDaily(defVal);
                  setCustomInput(new Intl.NumberFormat("id-ID").format(defVal));
                }
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

        {/* Slider & Custom Controls */}
        <section className="px-6 mt-5">
          <div className="bg-card rounded-3xl border border-border shadow-[var(--shadow-soft)] p-5">
            <div className="flex items-center justify-between">
              <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                Pilih Nominal Limit
              </p>
              <span className="text-sm font-bold text-primary">{fmt(daily)}</span>
            </div>

            <input
              type="range"
              min={0}
              max={500_000}
              step={10_000}
              value={Math.min(daily, 500_000)}
              onChange={(e) => {
                const val = parseInt(e.target.value, 10);
                setDaily(val);
                setCustomInput(val > 0 ? new Intl.NumberFormat("id-ID").format(val) : "");
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
                      setCustomInput(new Intl.NumberFormat("id-ID").format(p));
                      setEnabled(true);
                    }}
                    className={`py-2.5 rounded-xl text-[11px] font-bold border transition ${
                      active
                        ? "bg-[var(--gradient-card)] text-primary-foreground border-transparent"
                        : "bg-secondary text-foreground border-transparent hover:border-primary/20"
                    }`}
                  >
                    {p / 1000}rb
                  </button>
                );
              })}
            </div>

            {/* Input Custom Nominal */}
            <div className="pt-4 border-t border-border mt-4">
              <div className="flex items-center justify-between mb-1.5">
                <span className="text-[10px] uppercase font-bold text-muted-foreground tracking-wider">
                  Atau Masukkan Nominal Sendiri
                </span>
                {customInput && (
                  <span className="text-[10px] text-primary font-bold">Kustom Aktif</span>
                )}
              </div>
              <div className="flex items-center gap-2 bg-secondary/80 rounded-2xl px-3.5 py-2.5 border border-border focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition">
                <span className="text-xs font-bold text-primary">Rp</span>
                <input
                  type="text"
                  inputMode="numeric"
                  value={customInput}
                  onChange={(e) => {
                    const raw = e.target.value.replace(/\D/g, "");
                    if (!raw) {
                      setCustomInput("");
                      setDaily(0);
                      return;
                    }
                    const val = parseInt(raw, 10);
                    setCustomInput(new Intl.NumberFormat("id-ID").format(val));
                    setDaily(val);
                    setEnabled(true);
                  }}
                  placeholder="Contoh: 35.000"
                  className="bg-transparent flex-1 outline-none text-foreground text-xs font-bold placeholder:text-muted-foreground/60 placeholder:font-normal"
                />
                {customInput && (
                  <button
                    type="button"
                    onClick={() => {
                      setCustomInput("");
                      setDaily(50_000);
                    }}
                    className="w-5 h-5 rounded-full bg-muted flex items-center justify-center text-muted-foreground hover:text-foreground text-[10px] transition"
                    title="Hapus nominal custom"
                  >
                    <X size={12} />
                  </button>
                )}
              </div>
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
            onClick={() => {
              const payload = enabled ? (daily > 0 ? daily : 0) : -1;
              mutation.mutate(payload);
            }}
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
