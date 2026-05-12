import { r as reactExports, T as jsxRuntimeExports } from "./server-B6wkPDkD.js";
import { c as createLucideIcon, u as useNavigate, a as useSantri, e as useQuery, l as fetchDashboard } from "./router-CFPFE5wZ.js";
import { M as MobileShell } from "./MobileShell-Dd7LjuQz.js";
import { S as SantriSwitcherTrigger } from "./SantriSwitcher-8zeyOsw8.js";
import { L as LoaderCircle } from "./loader-circle-Nxd1j7ck.js";
import { B as Bell } from "./bell-B8JX_l6V.js";
import { C as ChevronDown } from "./chevron-down-CmeavGEV.js";
import { E as EyeOff, a as Eye } from "./eye-DoTsyshc.js";
import { P as Plus } from "./plus-C609R3R4.js";
import { S as SlidersVertical } from "./sliders-vertical-O5a65nFX.js";
import { A as ArrowDownLeft, U as Utensils } from "./utensils-Cwv43Bgc.js";
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
import "./receipt-CFDuUZ2Y.js";
import "./check-D3O2M5Eb.js";
const __iconNode = [
  ["path", { d: "M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8", key: "1357e3" }],
  ["path", { d: "M3 3v5h5", key: "1xhq8a" }],
  ["path", { d: "M12 7v5l4 2", key: "1fdv2h" }]
];
const History = createLucideIcon("history", __iconNode);
const actions = [{
  label: "Topup Saldo",
  icon: Plus,
  accent: "from-primary to-primary-glow",
  to: "/topup"
}, {
  label: "Atur Limit",
  icon: SlidersVertical,
  accent: "from-primary-deep to-primary",
  to: "/limit"
}, {
  label: "Riwayat",
  icon: History,
  accent: "from-primary-glow to-primary",
  to: "/riwayat"
}];
function Dashboard() {
  const navigate = useNavigate();
  const [hide, setHide] = reactExports.useState(false);
  const {
    active,
    isLoading: isLoadingSantri
  } = useSantri();
  const {
    data: dashboard,
    isLoading: isLoadingDashboard
  } = useQuery({
    queryKey: ["dashboard", active?.id],
    queryFn: async () => {
      const res = await fetchDashboard();
      return res.data;
    },
    enabled: !!active
  });
  const fmt = (n) => new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0
  }).format(n);
  if (isLoadingSantri || active && isLoadingDashboard) {
    return /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "min-h-screen flex items-center justify-center bg-background", children: /* @__PURE__ */ jsxRuntimeExports.jsx(LoaderCircle, { className: "animate-spin text-primary", size: 40 }) });
  }
  if (!active) {
    return /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "min-h-screen flex flex-col items-center justify-center p-6 text-center space-y-4", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-muted-foreground", children: "Belum ada santri yang tertaut." }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: () => window.location.href = "/", className: "text-primary font-bold", children: "Kembali ke Portal Utama" })
    ] });
  }
  return /* @__PURE__ */ jsxRuntimeExports.jsxs(MobileShell, { children: [
    /* @__PURE__ */ jsxRuntimeExports.jsxs("header", { className: "flex items-center justify-between px-6 pt-12 pb-4", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex items-center gap-3", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "w-11 h-11 rounded-2xl bg-[var(--gradient-card)] flex items-center justify-center text-primary-foreground font-bold shadow-[var(--shadow-soft)] overflow-hidden", children: dashboard?.user?.avatar ? /* @__PURE__ */ jsxRuntimeExports.jsx("img", { src: `/${dashboard.user.avatar}`, alt: "", className: "w-full h-full object-cover" }) : dashboard?.user?.name?.substring(0, 2).toUpperCase() || "W" }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-xs text-muted-foreground", children: "Assalamualaikum," }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-sm font-bold text-foreground", children: dashboard?.user?.name || "Wali Santri" })
        ] })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("button", { className: "relative w-11 h-11 rounded-2xl bg-secondary flex items-center justify-center", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx(Bell, { size: 20, className: "text-foreground" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "absolute top-2.5 right-2.5 w-2 h-2 rounded-full bg-primary" })
      ] })
    ] }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("section", { className: "px-6 mt-2", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative overflow-hidden rounded-3xl p-6 text-primary-foreground shadow-[var(--shadow-glow)]", style: {
      background: "var(--gradient-hero)"
    }, children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "absolute -top-16 -right-10 w-56 h-56 rounded-full bg-white/10 blur-3xl" }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "absolute bottom-0 left-0 w-40 h-40 rounded-full bg-white/5 blur-2xl" }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex items-center justify-between", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "min-w-0", children: [
            /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-xs uppercase tracking-widest text-white/70 font-semibold", children: "Saldo Santri" }),
            /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "text-[11px] text-white/60 mt-0.5 truncate", children: [
              active.name,
              " · ",
              active.classroom?.name || "Tanpa Kelas"
            ] })
          ] }),
          /* @__PURE__ */ jsxRuntimeExports.jsx(SantriSwitcherTrigger, { children: /* @__PURE__ */ jsxRuntimeExports.jsxs("span", { className: "px-2.5 py-1 rounded-full bg-white/15 border border-white/20 text-[10px] font-semibold backdrop-blur flex items-center gap-1 cursor-pointer", children: [
            "Ganti ",
            /* @__PURE__ */ jsxRuntimeExports.jsx(ChevronDown, { size: 12 })
          ] }) })
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-5 flex items-end gap-3", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("h2", { className: "text-3xl font-bold tracking-tight", children: hide ? "Rp ••••••" : fmt(active.saldo) }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: () => setHide((h) => !h), className: "mb-1.5 text-white/80", children: hide ? /* @__PURE__ */ jsxRuntimeExports.jsx(EyeOff, { size: 18 }) : /* @__PURE__ */ jsxRuntimeExports.jsx(Eye, { size: 18 }) })
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-5 flex items-center justify-between text-xs", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { children: [
            /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-white/60", children: "Limit Harian" }),
            /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "font-semibold", children: fmt(active.daily_limit) })
          ] }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "h-8 w-px bg-white/20" }),
          /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { children: [
            /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-white/60", children: "Tabungan" }),
            /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "font-semibold", children: fmt(active.saving) })
          ] }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "h-8 w-px bg-white/20" }),
          /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { children: [
            /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-white/60", children: "NISN" }),
            /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "font-semibold tracking-wider", children: active.nisn || "-" })
          ] })
        ] })
      ] })
    ] }) }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("section", { className: "px-6 mt-6", children: /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "grid grid-cols-3 gap-3", children: actions.map(({
      label,
      icon: Icon,
      accent,
      to
    }) => /* @__PURE__ */ jsxRuntimeExports.jsxs("button", { onClick: () => navigate({
      to
    }), className: "flex flex-col items-center gap-2 p-4 rounded-2xl bg-card border border-border shadow-[var(--shadow-soft)] active:scale-95 transition", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: `w-12 h-12 rounded-2xl bg-gradient-to-br ${accent} flex items-center justify-center shadow-[var(--shadow-soft)]`, children: /* @__PURE__ */ jsxRuntimeExports.jsx(Icon, { size: 22, className: "text-primary-foreground" }) }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "text-[11px] font-semibold text-foreground text-center leading-tight", children: label })
    ] }, label)) }) }),
    /* @__PURE__ */ jsxRuntimeExports.jsxs("section", { className: "px-6 mt-7 mb-10", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex items-center justify-between mb-3", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("h3", { className: "text-base font-bold text-foreground", children: "Transaksi Terbaru" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: () => navigate({
          to: "/riwayat"
        }), className: "text-xs font-semibold text-primary", children: "Lihat Semua" })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "bg-card rounded-3xl border border-border divide-y divide-border overflow-hidden shadow-[var(--shadow-soft)]", children: dashboard?.recentTransactions?.map((t, i) => {
        const isIn = t.type === "IN";
        return /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex items-center gap-3 p-4", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: `w-11 h-11 rounded-2xl flex items-center justify-center ${isIn ? "bg-emerald-50 text-emerald-600" : "bg-blue-50 text-blue-600"}`, children: isIn ? /* @__PURE__ */ jsxRuntimeExports.jsx(ArrowDownLeft, { size: 18 }) : /* @__PURE__ */ jsxRuntimeExports.jsx(Utensils, { size: 18 }) }),
          /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex-1 min-w-0", children: [
            /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-sm font-semibold text-foreground truncate", children: t.note || (isIn ? "Saldo Masuk" : "Belanja Kantin") }),
            /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-muted-foreground", children: new Date(t.created_at).toLocaleDateString("id-ID", {
              day: "numeric",
              month: "short",
              hour: "2-digit",
              minute: "2-digit"
            }) })
          ] }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "flex items-center gap-1", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("span", { className: `text-sm font-bold ${isIn ? "text-emerald-600" : "text-foreground"}`, children: [
            isIn ? "+" : "-",
            fmt(t.amount)
          ] }) })
        ] }, i);
      }) || /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "p-8 text-center text-xs text-muted-foreground", children: "Belum ada transaksi." }) })
    ] })
  ] });
}
export {
  Dashboard as component
};
