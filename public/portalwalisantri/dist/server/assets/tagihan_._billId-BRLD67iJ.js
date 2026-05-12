import { r as reactExports, T as jsxRuntimeExports } from "./server-B6wkPDkD.js";
import { n as useParams, u as useNavigate, a as useSantri, e as useQuery, o as fetchBillDetail, q as postCheckout } from "./router-CFPFE5wZ.js";
import { S as SantriSwitcherTrigger } from "./SantriSwitcher-8zeyOsw8.js";
import { u as useMutation } from "./useMutation-BYL_0E1C.js";
import { L as LoaderCircle } from "./loader-circle-Nxd1j7ck.js";
import { A as ArrowLeft } from "./arrow-left-CU_-rGeT.js";
import { C as CircleCheck } from "./circle-check-C791vGyw.js";
import { C as Check } from "./check-D3O2M5Eb.js";
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
const fmt = (n) => new Intl.NumberFormat("id-ID", {
  style: "currency",
  currency: "IDR",
  minimumFractionDigits: 0
}).format(n);
function BillDetail() {
  const {
    billId
  } = useParams({
    from: "/tagihan_/$billId"
  });
  const navigate = useNavigate();
  const {
    active,
    isLoading: isLoadingSantri
  } = useSantri();
  const [picked, setPicked] = reactExports.useState(/* @__PURE__ */ new Set());
  const {
    data: detailData,
    isLoading: isLoadingDetail
  } = useQuery({
    queryKey: ["bill-detail", billId],
    queryFn: async () => {
      const res = await fetchBillDetail(billId);
      return res.data;
    }
  });
  const bill = reactExports.useMemo(() => {
    if (!detailData) return null;
    const b = detailData.bill;
    const details = detailData.details || [];
    return {
      id: b.id,
      name: b.name,
      shortName: b.name,
      total: b.total_amount,
      paid: b.paid_amount,
      installments: details.map((d) => ({
        id: d.id,
        label: d.month_name ? `${d.month_name} ${d.year}` : d.name,
        amount: d.amount,
        paid: d.status === "PAID" || d.paid_amount >= d.amount
      }))
    };
  }, [detailData]);
  const checkoutMutation = useMutation({
    mutationFn: async (installmentIds) => {
      const res = await postCheckout({
        installments: installmentIds,
        method: "BCA"
        // default method
      });
      return res.data;
    },
    onSuccess: (data) => {
      navigate({
        to: "/pembayaran/$payId",
        params: {
          payId: String(data.payment.id)
        }
      });
    }
  });
  const unpaid = reactExports.useMemo(() => bill?.installments.filter((i) => !i.paid) || [], [bill]);
  const allUnpaidPicked = unpaid.length > 0 && unpaid.every((i) => picked.has(i.id));
  const togglePick = (id) => setPicked((s) => {
    const n = new Set(s);
    n.has(id) ? n.delete(id) : n.add(id);
    return n;
  });
  const togglePickAll = () => setPicked(allUnpaidPicked ? /* @__PURE__ */ new Set() : new Set(unpaid.map((i) => i.id)));
  const pickedTotal = reactExports.useMemo(() => bill?.installments.filter((i) => picked.has(i.id)).reduce((a, b) => a + b.amount, 0) || 0, [picked, bill?.installments]);
  if (isLoadingSantri || isLoadingDetail) {
    return /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "min-h-screen flex items-center justify-center bg-background", children: /* @__PURE__ */ jsxRuntimeExports.jsx(LoaderCircle, { className: "animate-spin text-primary", size: 40 }) });
  }
  if (!bill) return null;
  const remaining = Math.max(0, bill.total - bill.paid);
  const isFullyPaid = remaining === 0;
  const isPartial = bill.paid > 0 && !isFullyPaid;
  return /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "min-h-screen w-full flex justify-center bg-secondary", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative w-full max-w-md min-h-screen bg-background pb-32", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "px-6 pt-12 pb-3 flex items-center gap-3", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: () => navigate({
        to: "/tagihan"
      }), className: "w-10 h-10 rounded-xl bg-secondary border border-border flex items-center justify-center text-foreground active:scale-95 transition", children: /* @__PURE__ */ jsxRuntimeExports.jsx(ArrowLeft, { size: 18 }) }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "min-w-0", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-muted-foreground font-semibold uppercase tracking-wider", children: "Tagihan" }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "text-base font-bold text-foreground truncate", children: [
          "Detail ",
          bill.shortName
        ] })
      ] })
    ] }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "px-4 mt-1", children: /* @__PURE__ */ jsxRuntimeExports.jsx(ActiveSantriPill, {}) }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "px-4 pt-4", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "rounded-3xl border border-border bg-card p-4 shadow-[var(--shadow-card)]", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex items-start justify-between gap-3", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex items-center gap-2 min-w-0", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "w-1 h-5 rounded-full bg-primary shrink-0" }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("h2", { className: "text-sm font-semibold text-foreground tracking-tight uppercase truncate", children: bill.name })
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("span", { className: `shrink-0 inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-semibold text-white ${isFullyPaid ? "bg-success" : isPartial ? "bg-[oklch(0.78_0.16_75)]" : "bg-[oklch(0.62_0.22_25)]"}`, children: [
          isFullyPaid && /* @__PURE__ */ jsxRuntimeExports.jsx(CircleCheck, { size: 12 }),
          isFullyPaid ? "Lunas" : isPartial ? "Proses Bayar" : "Belum Bayar"
        ] })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-3", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "text-[11px] text-muted-foreground", children: [
          "Total ",
          bill.shortName
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-2xl font-bold text-foreground tabular-nums", children: fmt(bill.total) })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-3 pt-3 border-t border-border grid grid-cols-2 gap-4", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-muted-foreground", children: "Sudah Bayar" }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-sm font-semibold text-foreground tabular-nums", children: fmt(bill.paid) })
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-muted-foreground", children: "Belum Bayar" }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: `text-sm font-semibold tabular-nums ${isFullyPaid ? "text-success" : "text-[oklch(0.62_0.22_25)]"}`, children: fmt(remaining) })
        ] })
      ] })
    ] }) }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "px-5 pt-2", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-5", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsxs("h3", { className: "text-base font-bold text-foreground", children: [
        "Detail ",
        bill.shortName
      ] }),
      unpaid.length > 0 && /* @__PURE__ */ jsxRuntimeExports.jsxs("button", { onClick: togglePickAll, className: "mt-4 flex items-center gap-3 px-1", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx(CheckBox, { checked: allUnpaidPicked }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "text-sm font-semibold text-foreground", children: "Bayar Semua" })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "mt-4 space-y-3", children: bill.installments.map((it) => {
        const checked = picked.has(it.id);
        return /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { onClick: () => !it.paid && togglePick(it.id), role: it.paid ? void 0 : "button", className: `relative flex items-center gap-3 pl-4 pr-3 py-3 rounded-2xl bg-secondary/70 border transition ${!it.paid && checked ? "border-primary ring-1 ring-primary/40" : "border-border"} ${it.paid ? "" : "cursor-pointer active:scale-[0.99]"}`, children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: `absolute left-0 top-3 bottom-3 w-1 rounded-r-full ${it.paid ? "bg-success" : "bg-primary"}` }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "shrink-0", children: /* @__PURE__ */ jsxRuntimeExports.jsx(CheckBox, { checked: it.paid || checked, disabled: it.paid }) }),
          /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex-1 min-w-0", children: [
            /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-xs text-muted-foreground", children: fmt(it.amount) }),
            /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-sm font-bold text-primary leading-tight mt-0.5", children: it.label })
          ] }),
          it.paid ? /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "shrink-0 px-5 py-2.5 rounded-xl bg-success text-white text-xs font-bold", children: "Lunas" }) : /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: (e) => {
            e.stopPropagation();
            checkoutMutation.mutate([it.id]);
          }, disabled: checkoutMutation.isPending, className: "shrink-0 px-4 py-2.5 rounded-xl text-xs font-bold text-primary-foreground shadow-[var(--shadow-soft)] active:scale-95 transition flex items-center justify-center min-w-[100px]", style: {
            background: "var(--gradient-card)"
          }, children: checkoutMutation.isPending ? /* @__PURE__ */ jsxRuntimeExports.jsx(LoaderCircle, { className: "animate-spin", size: 14 }) : "Bayar Sekarang" })
        ] }, it.id);
      }) })
    ] }) }),
    unpaid.length > 0 && /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md px-3 pb-3 pt-2 bg-gradient-to-t from-background via-background to-background/0 z-40", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "rounded-xl border border-border bg-card shadow-[var(--shadow-card)] px-3 py-2 flex items-center gap-3", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "min-w-0 flex-1", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "text-[9px] text-muted-foreground font-semibold uppercase tracking-wider leading-none", children: [
          "Total · ",
          picked.size,
          "/",
          unpaid.length
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-base font-extrabold text-foreground leading-tight mt-0.5", children: fmt(pickedTotal) })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: () => {
        const items = Array.from(picked);
        if (items.length === 0) return;
        checkoutMutation.mutate(items);
      }, disabled: picked.size === 0 || checkoutMutation.isPending, className: "shrink-0 px-5 py-2.5 rounded-xl text-primary-foreground font-bold text-sm shadow-[var(--shadow-glow)] disabled:opacity-50 transition active:scale-[0.98] flex items-center justify-center min-w-[120px]", style: {
        background: "var(--gradient-card)"
      }, children: checkoutMutation.isPending ? /* @__PURE__ */ jsxRuntimeExports.jsx(LoaderCircle, { className: "animate-spin", size: 18 }) : "Lanjutkan" })
    ] }) })
  ] }) });
}
function CheckBox({
  checked,
  disabled
}) {
  return /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: `w-6 h-6 rounded-md border-2 flex items-center justify-center transition ${checked ? disabled ? "bg-success border-success" : "bg-primary border-primary" : "bg-transparent border-muted-foreground/40"}`, children: checked && /* @__PURE__ */ jsxRuntimeExports.jsx(Check, { size: 14, className: "text-white", strokeWidth: 3 }) });
}
function ActiveSantriPill() {
  const {
    active
  } = useSantri();
  return /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative overflow-hidden rounded-3xl p-4 text-primary-foreground shadow-[var(--shadow-glow)]", style: {
    background: "var(--gradient-hero)"
  }, children: [
    /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "absolute -top-12 -right-8 w-40 h-40 rounded-full bg-white/10 blur-3xl" }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "absolute -bottom-10 -left-6 w-32 h-32 rounded-full bg-white/5 blur-2xl" }),
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative flex items-center gap-3", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "w-12 h-12 rounded-2xl bg-white/15 border border-white/20 backdrop-blur text-primary-foreground flex items-center justify-center shrink-0 font-bold shadow-[var(--shadow-soft)]", children: active?.initials }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "min-w-0 flex-1", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[10px] uppercase tracking-widest text-white/70 font-semibold", children: "Nama Siswa / Santri" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-sm font-extrabold text-white truncate leading-tight mt-0.5", children: active?.name?.toUpperCase() }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "text-[11px] text-white/70 mt-0.5", children: [
          active?.jenjang,
          " · Kelas ",
          active?.className,
          " · ••",
          active?.cardSuffix
        ] })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsx(SantriSwitcherTrigger, { children: /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "px-2.5 py-1 rounded-full bg-white/15 border border-white/20 text-[10px] font-semibold backdrop-blur flex items-center gap-1 shrink-0", children: "Ganti" }) })
    ] })
  ] });
}
export {
  BillDetail as component
};
