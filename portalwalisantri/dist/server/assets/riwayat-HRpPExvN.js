import { r as reactExports, T as jsxRuntimeExports } from "./server-B6wkPDkD.js";
import { c as createLucideIcon, u as useNavigate, a as useSantri, e as useQuery, g as fetchSaldoHistories, h as fetchPosTransactions, X } from "./router-CFPFE5wZ.js";
import { L as LoaderCircle } from "./loader-circle-Nxd1j7ck.js";
import { A as ArrowLeft } from "./arrow-left-CU_-rGeT.js";
import { A as ArrowDownLeft, U as Utensils } from "./utensils-Cwv43Bgc.js";
import { S as Search } from "./search-Dxuaf2Zh.js";
import { R as Receipt } from "./receipt-CFDuUZ2Y.js";
import { W as Wallet } from "./wallet-UASjtD00.js";
import { C as ChevronDown } from "./chevron-down-CmeavGEV.js";
import { C as Clock } from "./clock-Bt-K3S50.js";
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
const __iconNode$6 = [
  ["path", { d: "M7 7h10v10", key: "1tivn9" }],
  ["path", { d: "M7 17 17 7", key: "1vkiza" }]
];
const ArrowUpRight = createLucideIcon("arrow-up-right", __iconNode$6);
const __iconNode$5 = [
  ["path", { d: "M12 7v14", key: "1akyts" }],
  [
    "path",
    {
      d: "M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z",
      key: "ruj8y"
    }
  ]
];
const BookOpen = createLucideIcon("book-open", __iconNode$5);
const __iconNode$4 = [
  ["path", { d: "M8 2v4", key: "1cmpym" }],
  ["path", { d: "M16 2v4", key: "4m81vk" }],
  ["rect", { width: "18", height: "18", x: "3", y: "4", rx: "2", key: "1hopcy" }],
  ["path", { d: "M3 10h18", key: "8toen8" }]
];
const Calendar = createLucideIcon("calendar", __iconNode$4);
const __iconNode$3 = [
  ["path", { d: "M10 2v2", key: "7u0qdc" }],
  ["path", { d: "M14 2v2", key: "6buw04" }],
  [
    "path",
    {
      d: "M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1",
      key: "pwadti"
    }
  ],
  ["path", { d: "M6 2v2", key: "colzsn" }]
];
const Coffee = createLucideIcon("coffee", __iconNode$3);
const __iconNode$2 = [
  ["line", { x1: "4", x2: "20", y1: "9", y2: "9", key: "4lhtct" }],
  ["line", { x1: "4", x2: "20", y1: "15", y2: "15", key: "vyu0kd" }],
  ["line", { x1: "10", x2: "8", y1: "3", y2: "21", key: "1ggp8o" }],
  ["line", { x1: "16", x2: "14", y1: "3", y2: "21", key: "weycgp" }]
];
const Hash = createLucideIcon("hash", __iconNode$2);
const __iconNode$1 = [
  ["path", { d: "M16 10a4 4 0 0 1-8 0", key: "1ltviw" }],
  ["path", { d: "M3.103 6.034h17.794", key: "awc11p" }],
  [
    "path",
    {
      d: "M3.4 5.467a2 2 0 0 0-.4 1.2V20a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6.667a2 2 0 0 0-.4-1.2l-2-2.667A2 2 0 0 0 17 2H7a2 2 0 0 0-1.6.8z",
      key: "o988cm"
    }
  ]
];
const ShoppingBag = createLucideIcon("shopping-bag", __iconNode$1);
const __iconNode = [
  ["path", { d: "M15 21v-5a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v5", key: "slp6dd" }],
  [
    "path",
    {
      d: "M17.774 10.31a1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.451 0 1.12 1.12 0 0 0-1.548 0 2.5 2.5 0 0 1-3.452 0 1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.77-3.248l2.889-4.184A2 2 0 0 1 7 2h10a2 2 0 0 1 1.653.873l2.895 4.192a2.5 2.5 0 0 1-3.774 3.244",
      key: "o0xfot"
    }
  ],
  ["path", { d: "M4 10.95V19a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8.05", key: "wn3emo" }]
];
const Store = createLucideIcon("store", __iconNode);
const CAT_META = {
  topup: {
    label: "Top Up",
    icon: Wallet,
    tone: "bg-success/15 text-success"
  },
  kantin: {
    label: "Kantin",
    icon: Utensils,
    tone: "bg-secondary text-primary"
  },
  minuman: {
    label: "Minuman",
    icon: Coffee,
    tone: "bg-secondary text-primary"
  },
  alat: {
    label: "Alat Tulis",
    icon: BookOpen,
    tone: "bg-secondary text-primary"
  },
  spp: {
    label: "SPP",
    icon: Receipt,
    tone: "bg-secondary text-primary"
  },
  mart: {
    label: "Pondok Mart",
    icon: Store,
    tone: "bg-secondary text-primary"
  }
};
const fmt = (n) => new Intl.NumberFormat("id-ID", {
  style: "currency",
  currency: "IDR",
  minimumFractionDigits: 0
}).format(n);
const fmtDate = (iso) => {
  const d = new Date(iso);
  return d.toLocaleDateString("id-ID", {
    day: "numeric",
    month: "short",
    year: "numeric"
  });
};
const fmtTime = (iso) => new Date(iso).toLocaleTimeString("id-ID", {
  hour: "2-digit",
  minute: "2-digit"
});
const TYPE_TABS = [{
  id: "all",
  label: "Semua"
}, {
  id: "in",
  label: "Masuk"
}, {
  id: "out",
  label: "Keluar"
}];
const CAT_FILTERS = [{
  id: "all",
  label: "Semua"
}, {
  id: "minuman",
  label: "Minuman"
}, {
  id: "alat",
  label: "Alat Tulis"
}, {
  id: "spp",
  label: "SPP"
}, {
  id: "topup",
  label: "Top Up"
}];
const DATE_FILTERS = [{
  id: "all",
  label: "Semua"
}, {
  id: "today",
  label: "Hari Ini"
}, {
  id: "7d",
  label: "7 Hari"
}, {
  id: "30d",
  label: "30 Hari"
}];
function RiwayatPage() {
  const navigate = useNavigate();
  const {
    active
  } = useSantri();
  const [type, setType] = reactExports.useState("all");
  const [cat, setCat] = reactExports.useState("all");
  const [range, setRange] = reactExports.useState("all");
  const [q, setQ] = reactExports.useState("");
  const [openId, setOpenId] = reactExports.useState(null);
  const {
    data: saldoHistories = [],
    isLoading: isLoadingSaldo
  } = useQuery({
    queryKey: ["saldo-histories", active?.id, range],
    queryFn: async () => {
      const res = await fetchSaldoHistories({
        filter: range
      });
      return res.data.data || [];
    },
    enabled: !!active
  });
  const {
    data: posTransactions = [],
    isLoading: isLoadingPos
  } = useQuery({
    queryKey: ["pos-transactions", active?.id, range],
    queryFn: async () => {
      const res = await fetchPosTransactions({
        filter: range
      });
      return res.data.data || [];
    },
    enabled: !!active
  });
  const allTxs = reactExports.useMemo(() => {
    const saldoMapped = saldoHistories.map((s) => ({
      id: s.id,
      name: s.type === "IN" ? "Top Up Saldo" : "Pengeluaran Saldo",
      category: s.type === "IN" ? "topup" : "kantin",
      type: s.type === "IN" ? "in" : "out",
      amount: s.amount,
      date: s.created_at,
      note: s.note,
      status: s.status
    }));
    const posMapped = posTransactions.map((p) => ({
      id: p.id,
      name: p.merchant_name || "Kantin Pondok",
      category: "kantin",
      type: "out",
      amount: p.pay_amount,
      date: p.created_at,
      merchant: p.merchant_name,
      cashier: p.admins?.name,
      ref: p.payment_code,
      items: p.point_of_sale_transaction_details?.map((d) => ({
        name: d.item?.name || "Item",
        qty: d.qty,
        price: d.price
      }))
    }));
    return [...saldoMapped, ...posMapped].sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());
  }, [saldoHistories, posTransactions]);
  const filtered = reactExports.useMemo(() => {
    return allTxs.filter((t) => {
      if (type !== "all" && t.type !== type) return false;
      if (cat !== "all" && t.category !== cat) return false;
      if (q.trim()) {
        const s = q.toLowerCase();
        const hay = `${t.name} ${t.merchant ?? ""} ${t.ref ?? ""} ${t.id} ${(t.items ?? []).map((i) => i.name).join(" ")}`.toLowerCase();
        if (!hay.includes(s)) return false;
      }
      return true;
    });
  }, [allTxs, type, cat, q]);
  const totalIn = filtered.filter((t) => t.type === "in").reduce((a, b) => a + b.amount, 0);
  const totalOut = filtered.filter((t) => t.type === "out").reduce((a, b) => a + b.amount, 0);
  const groups = reactExports.useMemo(() => {
    const m = /* @__PURE__ */ new Map();
    for (const t of filtered) {
      const k = fmtDate(t.date);
      m.set(k, [...m.get(k) ?? [], t]);
    }
    return Array.from(m.entries());
  }, [filtered]);
  const activeFilters = (type !== "all" ? 1 : 0) + (cat !== "all" ? 1 : 0) + (range !== "all" ? 1 : 0) + (q ? 1 : 0);
  if (isLoadingSaldo || isLoadingPos) {
    return /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "min-h-screen flex items-center justify-center bg-background", children: /* @__PURE__ */ jsxRuntimeExports.jsx(LoaderCircle, { className: "animate-spin text-primary", size: 40 }) });
  }
  return /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "min-h-screen w-full flex justify-center bg-secondary", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative w-full max-w-md min-h-screen bg-background pb-24", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative px-6 pt-12 pb-20 rounded-b-[2rem] overflow-hidden", style: {
      background: "var(--gradient-hero)"
    }, children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "absolute -top-16 -right-12 w-56 h-56 rounded-full bg-primary-glow/30 blur-3xl" }),
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
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-white/70 font-semibold uppercase tracking-wider", children: "Riwayat" }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-base font-bold text-white", children: "Transaksi Santri" })
        ] })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative mt-5 grid grid-cols-2 gap-3 text-white", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "rounded-2xl bg-white/10 backdrop-blur border border-white/15 p-3", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex items-center gap-1.5 text-white/70 text-[10px] font-semibold uppercase tracking-wider", children: [
            /* @__PURE__ */ jsxRuntimeExports.jsx(ArrowDownLeft, { size: 12 }),
            " Pemasukan"
          ] }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-base font-bold mt-1", children: fmt(totalIn) })
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "rounded-2xl bg-white/10 backdrop-blur border border-white/15 p-3", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex items-center gap-1.5 text-white/70 text-[10px] font-semibold uppercase tracking-wider", children: [
            /* @__PURE__ */ jsxRuntimeExports.jsx(ArrowUpRight, { size: 12 }),
            " Pengeluaran"
          ] }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-base font-bold mt-1", children: fmt(Math.abs(totalOut)) })
        ] })
      ] })
    ] }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "px-6 -mt-12 relative z-10", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "bg-card rounded-3xl border border-border shadow-[var(--shadow-card)] p-4", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex items-center gap-2 bg-secondary rounded-2xl px-4 py-3 border border-transparent focus-within:border-primary transition", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx(Search, { size: 16, className: "text-primary" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("input", { value: q, onChange: (e) => setQ(e.target.value), placeholder: "Cari transaksi, item, atau ID…", className: "bg-transparent flex-1 outline-none text-sm font-medium text-foreground placeholder:text-muted-foreground" }),
        q && /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: () => setQ(""), className: "text-muted-foreground", children: /* @__PURE__ */ jsxRuntimeExports.jsx(X, { size: 14 }) })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "mt-3 flex bg-secondary rounded-2xl p-1", children: TYPE_TABS.map((t) => {
        const active2 = type === t.id;
        return /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: () => setType(t.id), className: `flex-1 py-2 rounded-xl text-xs font-bold transition ${active2 ? "bg-card text-primary shadow-[var(--shadow-soft)]" : "text-muted-foreground"}`, children: t.label }, t.id);
      }) }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "mt-3 flex gap-2 overflow-x-auto -mx-4 px-4 pb-1 scrollbar-none", children: CAT_FILTERS.map((c) => {
        const active2 = cat === c.id;
        return /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: () => setCat(c.id), className: `shrink-0 px-3 py-1.5 rounded-full text-[11px] font-bold border transition ${active2 ? "bg-[var(--gradient-card)] text-primary-foreground border-transparent" : "bg-secondary text-foreground border-transparent"}`, children: c.label }, c.id);
      }) }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-3 flex items-center gap-2", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx(Calendar, { size: 14, className: "text-muted-foreground shrink-0" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "flex gap-1.5 flex-1 overflow-x-auto scrollbar-none", children: DATE_FILTERS.map((d) => {
          const active2 = range === d.id;
          return /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: () => setRange(d.id), className: `shrink-0 px-3 py-1.5 rounded-lg text-[11px] font-bold border transition ${active2 ? "bg-primary text-primary-foreground border-transparent" : "bg-secondary text-muted-foreground border-transparent"}`, children: d.label }, d.id);
        }) })
      ] }),
      activeFilters > 0 && /* @__PURE__ */ jsxRuntimeExports.jsxs("button", { onClick: () => {
        setType("all");
        setCat("all");
        setRange("all");
        setQ("");
      }, className: "mt-3 w-full py-2 rounded-xl bg-accent text-primary text-[11px] font-bold flex items-center justify-center gap-1.5", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx(X, { size: 12 }),
        " Hapus ",
        activeFilters,
        " filter"
      ] })
    ] }) }),
    /* @__PURE__ */ jsxRuntimeExports.jsxs("section", { className: "px-6 mt-6 space-y-5", children: [
      groups.length === 0 && /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "text-center py-12", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "w-16 h-16 rounded-2xl bg-secondary mx-auto flex items-center justify-center text-muted-foreground", children: /* @__PURE__ */ jsxRuntimeExports.jsx(Receipt, { size: 28 }) }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "mt-3 text-sm font-bold text-foreground", children: "Tidak ada transaksi" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-xs text-muted-foreground", children: "Coba ubah filter atau kata kunci pencarian." })
      ] }),
      groups.map(([day, items]) => /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-2 px-1", children: day }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "bg-card rounded-3xl border border-border shadow-[var(--shadow-soft)] overflow-hidden divide-y divide-border", children: items.map((t) => /* @__PURE__ */ jsxRuntimeExports.jsx(TxRow, { tx: t, open: openId === t.id, onToggle: () => setOpenId(openId === t.id ? null : t.id) }, t.id)) })
      ] }, day))
    ] })
  ] }) });
}
function TxRow({
  tx,
  open,
  onToggle
}) {
  const meta = CAT_META[tx.category];
  const Icon = meta.icon;
  const isIn = tx.type === "in";
  const itemTotal = tx.items?.reduce((a, b) => a + b.qty * b.price, 0) ?? 0;
  return /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { children: [
    /* @__PURE__ */ jsxRuntimeExports.jsxs("button", { onClick: onToggle, className: "w-full flex items-center gap-3 p-4 active:bg-secondary transition text-left", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: `w-11 h-11 rounded-2xl flex items-center justify-center ${meta.tone}`, children: /* @__PURE__ */ jsxRuntimeExports.jsx(Icon, { size: 18 }) }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex-1 min-w-0", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-sm font-bold text-foreground truncate", children: tx.name }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "text-[11px] text-muted-foreground flex items-center gap-1.5 flex-wrap", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "px-1.5 py-0.5 rounded bg-secondary text-[9px] font-bold uppercase tracking-wider", children: meta.label }),
          tx.status && /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: `px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider text-white ${tx.status === "approved" ? "bg-success" : tx.status === "rejected" ? "bg-destructive" : "bg-[oklch(0.78_0.16_75)]"}`, children: tx.status }),
          fmtTime(tx.date)
        ] })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex items-center gap-1.5", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "text-right", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: `text-sm font-bold ${isIn ? "text-success" : "text-foreground"}`, children: [
            isIn ? "+" : "",
            fmt(tx.amount)
          ] }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[10px] text-muted-foreground", children: tx.items ? `${tx.items.length} item` : tx.method ?? "" })
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsx(ChevronDown, { size: 16, className: `text-muted-foreground transition-transform ${open ? "rotate-180" : ""}` })
      ] })
    ] }),
    open && /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "px-4 pb-4 pt-1 bg-secondary/40 border-t border-border", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "grid grid-cols-2 gap-3 mt-3 mb-3", children: [
        tx.merchant && /* @__PURE__ */ jsxRuntimeExports.jsx(Mini, { icon: Store, k: "Merchant", v: tx.merchant }),
        tx.cashier && /* @__PURE__ */ jsxRuntimeExports.jsx(Mini, { icon: Receipt, k: "Kasir", v: tx.cashier }),
        tx.ref && /* @__PURE__ */ jsxRuntimeExports.jsx(Mini, { icon: Hash, k: "Ref", v: tx.ref }),
        /* @__PURE__ */ jsxRuntimeExports.jsx(Mini, { icon: Clock, k: "Waktu", v: `${fmtDate(tx.date)} · ${fmtTime(tx.date)}` })
      ] }),
      tx.items && tx.items.length > 0 && /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "rounded-2xl bg-card border-2 border-primary/20 overflow-hidden shadow-[var(--shadow-card)]", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "px-4 py-3 flex items-center gap-2.5 text-primary-foreground", style: {
          background: "var(--gradient-card)"
        }, children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "w-8 h-8 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center", children: /* @__PURE__ */ jsxRuntimeExports.jsx(ShoppingBag, { size: 15 }) }),
          /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex-1 min-w-0", children: [
            /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[13px] font-bold leading-tight", children: "Detail Belanja" }),
            /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "text-[10px] opacity-80", children: [
              tx.items.length,
              " item dibeli"
            ] })
          ] }),
          /* @__PURE__ */ jsxRuntimeExports.jsxs("span", { className: "px-2 py-1 rounded-full bg-white/20 backdrop-blur text-[10px] font-bold", children: [
            tx.items.reduce((a, b) => a + b.qty, 0),
            "× pcs"
          ] })
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "divide-y divide-border", children: tx.items.map((it, i) => /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "px-3.5 py-3 flex items-start gap-3 text-xs hover:bg-secondary/40 transition", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsxs("span", { className: "w-8 h-8 rounded-lg bg-primary/10 text-primary text-[11px] font-bold flex items-center justify-center shrink-0 ring-1 ring-primary/20", children: [
            it.qty,
            "×"
          ] }),
          /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex-1 min-w-0", children: [
            /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "font-bold text-foreground text-[13px] truncate", children: it.name }),
            /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "text-[10px] text-muted-foreground mt-0.5", children: [
              "@ ",
              /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "font-semibold text-foreground/70", children: fmt(it.price) }),
              " / pcs"
            ] })
          ] }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "font-bold text-foreground text-[13px] tabular-nums", children: fmt(it.qty * it.price) })
        ] }, i)) }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "px-4 py-3 bg-secondary border-t-2 border-dashed border-border flex items-center justify-between", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "text-[11px] font-bold text-muted-foreground uppercase tracking-wider", children: "Total Belanja" }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "text-base font-extrabold text-primary tabular-nums", children: fmt(itemTotal) })
        ] })
      ] }),
      tx.note && /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "mt-3 text-[11px] text-muted-foreground italic px-1", children: [
        '"',
        tx.note,
        '"'
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-3 grid grid-cols-2 gap-2", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("button", { className: "py-2.5 rounded-xl bg-card border border-border text-[11px] font-bold text-foreground", children: "Unduh Struk" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("button", { className: "py-2.5 rounded-xl bg-[var(--gradient-card)] text-primary-foreground text-[11px] font-bold", children: "Laporkan Masalah" })
      ] })
    ] })
  ] });
}
function Mini({
  icon: Icon,
  k,
  v
}) {
  return /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex items-start gap-2", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "w-7 h-7 rounded-lg bg-card border border-border flex items-center justify-center text-primary shrink-0 mt-0.5", children: /* @__PURE__ */ jsxRuntimeExports.jsx(Icon, { size: 12 }) }),
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "min-w-0", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[9px] font-bold text-muted-foreground uppercase tracking-wider", children: k }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] font-semibold text-foreground truncate", children: v })
    ] })
  ] });
}
export {
  RiwayatPage as component
};
