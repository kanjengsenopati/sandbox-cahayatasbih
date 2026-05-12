import { r as reactExports, T as jsxRuntimeExports } from "./server-B6wkPDkD.js";
import { u as useNavigate, a as useSantri, e as useQuery, f as fetchBills, L as Link } from "./router-CFPFE5wZ.js";
import { M as MobileShell } from "./MobileShell-Dd7LjuQz.js";
import { S as SantriSwitcherTrigger } from "./SantriSwitcher-8zeyOsw8.js";
import { L as LoaderCircle } from "./loader-circle-Nxd1j7ck.js";
import { A as ArrowLeft } from "./arrow-left-CU_-rGeT.js";
import { S as Search } from "./search-Dxuaf2Zh.js";
import { R as Receipt } from "./receipt-CFDuUZ2Y.js";
import { C as CircleCheck } from "./circle-check-C791vGyw.js";
import { C as ChevronRight } from "./chevron-right-CyqJh9PM.js";
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
import "./chevron-down-CmeavGEV.js";
import "./plus-C609R3R4.js";
import "./check-D3O2M5Eb.js";
const fmtIDR = (n) => new Intl.NumberFormat("id-ID", {
  style: "currency",
  currency: "IDR",
  minimumFractionDigits: 0
}).format(n);
function Tagihan() {
  const navigate = useNavigate();
  const {
    active,
    isLoading: isLoadingSantri
  } = useSantri();
  const [tab, setTab] = reactExports.useState("due");
  const [q, setQ] = reactExports.useState("");
  const {
    data: billsData,
    isLoading: isLoadingBills
  } = useQuery({
    queryKey: ["bills", active?.id],
    queryFn: async () => {
      const res = await fetchBills();
      return res.data;
    },
    enabled: !!active
  });
  const bills = reactExports.useMemo(() => {
    if (!billsData) return [];
    return billsData.map((b) => ({
      id: b.id,
      name: b.name,
      category: b.category_name || "Lainnya",
      total: b.total_amount,
      paid: b.paid_amount,
      due: b.due_date ? `Jatuh tempo: ${new Date(b.due_date).toLocaleDateString("id-ID", {
        day: "numeric",
        month: "short",
        year: "numeric"
      })}` : ""
    }));
  }, [billsData]);
  const filtered = reactExports.useMemo(() => {
    return bills.filter((b) => {
      const isPaid = b.paid >= b.total;
      if (tab === "due" && isPaid) return false;
      if (tab === "paid" && !isPaid) return false;
      if (q && !b.name.toLowerCase().includes(q.toLowerCase())) return false;
      return true;
    });
  }, [bills, tab, q]);
  const grouped = reactExports.useMemo(() => {
    const map = /* @__PURE__ */ new Map();
    for (const b of filtered) {
      map.set(b.category, [...map.get(b.category) ?? [], b]);
    }
    return Array.from(map.entries());
  }, [filtered]);
  if (isLoadingSantri || isLoadingBills) {
    return /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "min-h-screen flex items-center justify-center bg-background", children: /* @__PURE__ */ jsxRuntimeExports.jsx(LoaderCircle, { className: "animate-spin text-primary", size: 40 }) });
  }
  const totalDue = bills.filter((b) => b.paid < b.total).reduce((acc, b) => acc + (b.total - b.paid), 0);
  return /* @__PURE__ */ jsxRuntimeExports.jsxs(MobileShell, { children: [
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "px-6 pt-12 pb-4 flex items-center gap-3", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: () => navigate({
        to: "/dashboard"
      }), className: "w-10 h-10 rounded-xl bg-secondary border border-border flex items-center justify-center text-foreground active:scale-95 transition", children: /* @__PURE__ */ jsxRuntimeExports.jsx(ArrowLeft, { size: 18 }) }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-muted-foreground font-semibold uppercase tracking-wider", children: "Tagihan" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-base font-bold text-foreground", children: "Pembayaran Santri" })
      ] })
    ] }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "px-4 mt-2 relative z-10", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "rounded-2xl p-5 text-primary-foreground shadow-[var(--shadow-glow)]", style: {
      background: "var(--gradient-card)"
    }, children: [
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex items-start justify-between gap-3", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "min-w-0", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-white/70", children: "Nama Siswa / Santri" }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-base font-bold tracking-tight mt-0.5 truncate", children: active.name.toUpperCase() }),
          /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "text-[11px] text-white/70 mt-0.5", children: [
            active.jenjang,
            " · Kelas ",
            active.className
          ] })
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsx(SantriSwitcherTrigger, { variant: "subtle", children: "Ganti Santri" })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-5", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-white/70", children: "Tagihan Saat ini" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-2xl font-extrabold tracking-tight mt-0.5", children: fmtIDR(totalDue) })
      ] })
    ] }) }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "px-4 mt-5", children: /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "flex border-b border-border", children: [{
      id: "due",
      label: "Tagihan"
    }, {
      id: "paid",
      label: "Lunas"
    }].map((t) => {
      const active2 = tab === t.id;
      return /* @__PURE__ */ jsxRuntimeExports.jsxs("button", { onClick: () => setTab(t.id), className: `flex-1 pb-3 pt-2 text-sm font-bold relative transition ${active2 ? "text-primary" : "text-muted-foreground"}`, children: [
        t.label,
        active2 && /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "absolute bottom-0 left-1/2 -translate-x-1/2 w-12 h-1 rounded-full bg-primary" })
      ] }, t.id);
    }) }) }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "px-4 mt-4", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex items-center gap-2 bg-secondary rounded-full px-4 py-3 border border-transparent focus-within:border-primary transition", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx(Search, { size: 16, className: "text-muted-foreground" }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("input", { value: q, onChange: (e) => setQ(e.target.value), placeholder: "Cari Data", className: "bg-transparent flex-1 outline-none text-sm font-medium text-foreground placeholder:text-muted-foreground" })
    ] }) }),
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "px-4 mt-5 space-y-6", children: [
      grouped.length === 0 && /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "text-center py-12", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "w-14 h-14 rounded-2xl bg-secondary mx-auto flex items-center justify-center text-muted-foreground", children: /* @__PURE__ */ jsxRuntimeExports.jsx(Receipt, { size: 26 }) }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "mt-3 text-sm font-bold text-foreground", children: tab === "paid" ? "Belum ada tagihan lunas" : "Tidak ada tagihan" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-xs text-muted-foreground", children: "Coba ubah kata kunci pencarian." })
      ] }),
      grouped.map(([cat, items]) => /* @__PURE__ */ jsxRuntimeExports.jsxs("section", { children: [
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex items-center justify-between mb-2 px-1", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("h3", { className: "text-[11px] font-bold text-muted-foreground uppercase tracking-widest", children: cat }),
          /* @__PURE__ */ jsxRuntimeExports.jsxs("span", { className: "text-[10px] font-semibold text-muted-foreground", children: [
            items.length,
            " item"
          ] })
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "space-y-3", children: items.map((b) => /* @__PURE__ */ jsxRuntimeExports.jsx(BillCard, { bill: b }, b.id)) })
      ] }, cat))
    ] })
  ] });
}
function BillCard({
  bill
}) {
  const remaining = Math.max(0, bill.total - bill.paid);
  const isPaid = remaining === 0;
  const pct = Math.min(100, Math.round(bill.paid / bill.total * 100));
  return /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative rounded-2xl bg-card border border-border shadow-[var(--shadow-soft)] overflow-hidden", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: `absolute left-0 top-3 bottom-3 w-1 rounded-r-full ${isPaid ? "bg-emerald-500" : "bg-primary"}` }),
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "p-4 pl-5", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex items-start justify-between gap-3", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "min-w-0", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[13px] font-bold text-foreground leading-snug", children: bill.name }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-base font-extrabold text-foreground tracking-tight mt-1", children: fmtIDR(bill.total) }),
          !isPaid && bill.due && /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[10px] text-muted-foreground mt-0.5", children: bill.due })
        ] }),
        isPaid ? /* @__PURE__ */ jsxRuntimeExports.jsxs("span", { className: "shrink-0 inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-600 text-[11px] font-bold", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx(CircleCheck, { size: 13 }),
          " Lunas"
        ] }) : /* @__PURE__ */ jsxRuntimeExports.jsxs(Link, { to: "/tagihan/$billId", params: {
          billId: bill.id
        }, className: "shrink-0 px-4 py-2.5 rounded-xl text-primary-foreground text-xs font-bold shadow-[var(--shadow-soft)] active:scale-95 transition flex items-center gap-1", style: {
          background: "var(--gradient-card)"
        }, children: [
          "Bayar ",
          /* @__PURE__ */ jsxRuntimeExports.jsx(ChevronRight, { size: 14 })
        ] })
      ] }),
      !isPaid && /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "mt-3 h-1.5 rounded-full bg-secondary overflow-hidden", children: /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "h-full rounded-full bg-[var(--gradient-card)]", style: {
        width: `${pct}%`
      } }) }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-3 grid grid-cols-2 gap-2", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[10px] text-muted-foreground mb-1", children: "Sudah Dibayarkan :" }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "rounded-full bg-emerald-500 text-white text-xs font-bold px-3 py-2 text-center truncate", children: fmtIDR(bill.paid) })
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[10px] text-muted-foreground mb-1 text-right", children: isPaid ? "Status :" : "Kekurangan :" }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: `rounded-full text-white text-xs font-bold px-3 py-2 text-center truncate ${isPaid ? "bg-emerald-500" : "bg-slate-400"}`, children: isPaid ? "Lunas" : fmtIDR(remaining) })
        ] })
      ] })
    ] })
  ] });
}
export {
  Tagihan as component
};
