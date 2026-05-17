import { r as reactExports, T as jsxRuntimeExports } from "./server-B6wkPDkD.js";
import { c as createLucideIcon, u as useNavigate, a as useSantri, b as useQueryClient, e as useQuery, j as fetchLimit, k as updateLimit } from "./router-CFPFE5wZ.js";
import { u as useMutation } from "./useMutation-BYL_0E1C.js";
import { L as LoaderCircle } from "./loader-circle-Nxd1j7ck.js";
import { A as ArrowLeft } from "./arrow-left-CU_-rGeT.js";
import { S as SlidersVertical } from "./sliders-vertical-O5a65nFX.js";
import { C as CircleCheck } from "./circle-check-C791vGyw.js";
import "node:async_hooks";
import "node:stream/web";
import "node:stream";
import "util";
import "stream";
import "path";
import "http";
import "https";
import "url";
import "fs";
import "crypto";
import "http2";
import "assert";
import "./worker-entry-zXm__Ov7.js";
import "node:events";
import "os";
import "zlib";
import "events";
const __iconNode = [
  [
    "path",
    {
      d: "M11.017 2.814a1 1 0 0 1 1.966 0l1.051 5.558a2 2 0 0 0 1.594 1.594l5.558 1.051a1 1 0 0 1 0 1.966l-5.558 1.051a2 2 0 0 0-1.594 1.594l-1.051 5.558a1 1 0 0 1-1.966 0l-1.051-5.558a2 2 0 0 0-1.594-1.594l-5.558-1.051a1 1 0 0 1 0-1.966l5.558-1.051a2 2 0 0 0 1.594-1.594z",
      key: "1s2grr"
    }
  ],
  ["path", { d: "M20 2v4", key: "1rf3ol" }],
  ["path", { d: "M22 4h-4", key: "gwowj6" }],
  ["circle", { cx: "4", cy: "20", r: "2", key: "6kqj1y" }]
];
const Sparkles = createLucideIcon("sparkles", __iconNode);
const PRESETS = [5e4, 1e5, 15e4, 25e4];
const fmt = (n) => new Intl.NumberFormat("id-ID", {
  style: "currency",
  currency: "IDR",
  minimumFractionDigits: 0
}).format(n);
function LimitPage() {
  const navigate = useNavigate();
  const {
    active,
    isLoading: isLoadingSantri
  } = useSantri();
  const queryClient = useQueryClient();
  const [daily, setDaily] = reactExports.useState(0);
  const [enabled, setEnabled] = reactExports.useState(true);
  const [saved, setSaved] = reactExports.useState(false);
  const {
    data: limitData,
    isLoading: isLoadingLimit
  } = useQuery({
    queryKey: ["limit", active?.id],
    queryFn: async () => {
      const res = await fetchLimit();
      return res.data;
    },
    enabled: !!active
  });
  reactExports.useEffect(() => {
    if (limitData) {
      setDaily(limitData.daily_limit || 0);
      setEnabled(limitData.daily_limit > 0);
    }
  }, [limitData]);
  const mutation = useMutation({
    mutationFn: (newLimit) => updateLimit({
      daily_limit: newLimit
    }),
    onSuccess: () => {
      setSaved(true);
      queryClient.invalidateQueries({
        queryKey: ["limit"]
      });
      queryClient.invalidateQueries({
        queryKey: ["active-student"]
      });
      setTimeout(() => navigate({
        to: "/dashboard"
      }), 800);
    }
  });
  if (isLoadingSantri || isLoadingLimit) {
    return /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "min-h-screen flex items-center justify-center bg-background", children: /* @__PURE__ */ jsxRuntimeExports.jsx(LoaderCircle, { className: "animate-spin text-primary", size: 40 }) });
  }
  return /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "min-h-screen w-full flex justify-center bg-secondary", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative w-full max-w-md min-h-screen bg-background pb-32", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative px-6 pt-12 pb-24 rounded-b-[2rem] overflow-hidden", style: {
      background: "var(--gradient-hero)"
    }, children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "absolute -top-20 -right-10 w-56 h-56 rounded-full bg-primary-glow/30 blur-3xl" }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "absolute inset-0 opacity-[0.07]", style: {
        backgroundImage: "linear-gradient(rgba(255,255,255,0.6) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.6) 1px, transparent 1px)",
        backgroundSize: "26px 26px",
        maskImage: "radial-gradient(ellipse at top right, black 30%, transparent 70%)"
      } }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative flex items-center gap-3", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: () => navigate({
          to: "/dashboard"
        }), className: "w-10 h-10 rounded-xl bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center text-white", children: /* @__PURE__ */ jsxRuntimeExports.jsx(ArrowLeft, { size: 18 }) }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-white/70 font-semibold uppercase tracking-wider", children: "Limit" }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-base font-bold text-white", children: "Atur Pengeluaran Harian" })
        ] })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative mt-6 text-white", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-xs text-white/70 uppercase tracking-widest font-semibold", children: "Limit Harian" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-3xl font-bold mt-1 tracking-tight", children: fmt(daily) }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-white/70 mt-1", children: "Santri tidak dapat menghabiskan lebih dari ini per hari." })
      ] })
    ] }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "px-6 -mt-14 relative z-10", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "bg-card rounded-3xl border border-border shadow-[var(--shadow-card)] p-5 flex items-center gap-3", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "w-11 h-11 rounded-xl bg-[var(--gradient-card)] flex items-center justify-center text-primary-foreground", children: /* @__PURE__ */ jsxRuntimeExports.jsx(SlidersVertical, { size: 20 }) }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex-1", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-sm font-bold text-foreground", children: "Aktifkan Limit Harian" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-muted-foreground", children: "Transaksi ditolak otomatis jika melewati limit." })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: () => {
        setEnabled(!enabled);
        if (enabled) setDaily(0);
      }, className: `relative w-12 h-7 rounded-full transition ${enabled ? "bg-primary" : "bg-muted"}`, children: /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: `absolute top-0.5 w-6 h-6 rounded-full bg-white shadow transition-transform ${enabled ? "translate-x-5" : "translate-x-0.5"}` }) })
    ] }) }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("section", { className: "px-6 mt-5", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "bg-card rounded-3xl border border-border shadow-[var(--shadow-soft)] p-5", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex items-center justify-between", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-xs font-semibold text-muted-foreground uppercase tracking-wider", children: "Slider Limit" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "text-sm font-bold text-primary", children: fmt(daily) })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("input", { type: "range", min: 0, max: 5e5, step: 1e4, value: daily, onChange: (e) => {
        const val = parseInt(e.target.value, 10);
        setDaily(val);
        setEnabled(val > 0);
      }, className: "w-full mt-4 accent-primary disabled:opacity-40" }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex justify-between text-[10px] text-muted-foreground mt-1", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("span", { children: "Rp 0" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("span", { children: "Rp 500rb" })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "grid grid-cols-4 gap-2 mt-4", children: PRESETS.map((p) => {
        const active2 = daily === p;
        return /* @__PURE__ */ jsxRuntimeExports.jsxs("button", { onClick: () => {
          setDaily(p);
          setEnabled(true);
        }, className: `py-2.5 rounded-xl text-[11px] font-bold border transition ${active2 ? "bg-[var(--gradient-card)] text-primary-foreground border-transparent" : "bg-secondary text-foreground border-transparent"}`, children: [
          p / 1e3,
          "rb"
        ] }, p);
      }) })
    ] }) }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("section", { className: "px-6 mt-5", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "rounded-2xl p-4 flex items-center gap-3 border border-border bg-accent", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx(Sparkles, { size: 18, className: "text-primary shrink-0" }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-foreground leading-relaxed", children: "Limit harian membantu mengontrol jajan santri di kantin dan toko pondok agar tetap hemat." })
    ] }) }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md px-4 pb-4 pt-3 bg-gradient-to-t from-background via-background to-background/0 z-40", children: /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: () => mutation.mutate(daily), disabled: mutation.isPending, className: "w-full py-4 rounded-2xl text-primary-foreground font-semibold text-sm shadow-[var(--shadow-glow)] flex items-center justify-center gap-2 disabled:opacity-50 transition active:scale-[0.98]", style: {
      background: "var(--gradient-card)"
    }, children: mutation.isPending ? /* @__PURE__ */ jsxRuntimeExports.jsx(LoaderCircle, { className: "animate-spin", size: 18 }) : saved ? /* @__PURE__ */ jsxRuntimeExports.jsxs(jsxRuntimeExports.Fragment, { children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx(CircleCheck, { size: 18 }),
      " Tersimpan"
    ] }) : "Simpan Pengaturan Limit" }) })
  ] }) });
}
export {
  LimitPage as component
};
