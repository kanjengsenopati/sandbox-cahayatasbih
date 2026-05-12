import { r as reactExports, T as jsxRuntimeExports } from "./server-B6wkPDkD.js";
import { u as useNavigate, C as CircleX } from "./router-CFPFE5wZ.js";
import { A as ArrowLeft } from "./arrow-left-CU_-rGeT.js";
import { S as ShieldCheck } from "./shield-check-DkRIIV9N.js";
import { C as CircleCheck } from "./circle-check-C791vGyw.js";
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
const KEY = "santripay.pendingTx.v1";
const EVT = "santripay:pendingTx:changed";
function read() {
  if (typeof window === "undefined") return [];
  try {
    const raw = localStorage.getItem(KEY);
    return raw ? JSON.parse(raw) : [];
  } catch {
    return [];
  }
}
function write(list) {
  if (typeof window === "undefined") return;
  localStorage.setItem(KEY, JSON.stringify(list));
  window.dispatchEvent(new Event(EVT));
}
function listPendingTx() {
  return read().sort(
    (a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime()
  );
}
function setStatus(id, status) {
  const list = read();
  const idx = list.findIndex((t) => t.id === id);
  if (idx === -1) return;
  list[idx] = { ...list[idx], status, updatedAt: (/* @__PURE__ */ new Date()).toISOString() };
  write(list);
}
function subscribePendingTx(cb) {
  if (typeof window === "undefined") return () => {
  };
  const handler = () => cb();
  window.addEventListener(EVT, handler);
  window.addEventListener("storage", handler);
  return () => {
    window.removeEventListener(EVT, handler);
    window.removeEventListener("storage", handler);
  };
}
const fmtIDR = (n) => "Rp" + new Intl.NumberFormat("id-ID").format(n);
function AdminApprovalPage() {
  const navigate = useNavigate();
  const [list, setList] = reactExports.useState([]);
  const [tab, setTab] = reactExports.useState("pending");
  reactExports.useEffect(() => {
    const refresh = () => setList(listPendingTx());
    refresh();
    return subscribePendingTx(refresh);
  }, []);
  const filtered = list.filter((t) => t.status === tab);
  return /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "min-h-screen w-full flex justify-center bg-secondary", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative w-full max-w-md min-h-screen bg-background pb-12", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "px-6 pt-12 pb-3 flex items-center gap-3", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: () => navigate({
        to: "/dashboard"
      }), className: "w-10 h-10 rounded-xl bg-secondary border border-border flex items-center justify-center", children: /* @__PURE__ */ jsxRuntimeExports.jsx(ArrowLeft, { size: 18 }) }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "min-w-0 flex-1", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-muted-foreground font-semibold uppercase tracking-wider", children: "Panel Petugas" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-base font-bold text-foreground", children: "Verifikasi Pembayaran" })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsx(ShieldCheck, { size: 20, className: "text-primary" })
    ] }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "px-5 pt-2", children: /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "flex bg-secondary rounded-2xl p-1", children: ["pending", "approved", "rejected"].map((t) => {
      const active = tab === t;
      const count = list.filter((x) => x.status === t).length;
      return /* @__PURE__ */ jsxRuntimeExports.jsxs("button", { onClick: () => setTab(t), className: `flex-1 py-2 rounded-xl text-xs font-bold capitalize transition ${active ? "bg-card text-primary shadow-[var(--shadow-soft)]" : "text-muted-foreground"}`, children: [
        t === "pending" ? "Pending" : t === "approved" ? "Approved" : "Rejected",
        " ",
        /* @__PURE__ */ jsxRuntimeExports.jsxs("span", { className: "opacity-70", children: [
          "(",
          count,
          ")"
        ] })
      ] }, t);
    }) }) }),
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "px-5 pt-4 space-y-3", children: [
      filtered.length === 0 && /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "text-center py-16 text-sm text-muted-foreground", children: [
        "Tidak ada transaksi ",
        tab,
        "."
      ] }),
      filtered.map((t) => /* @__PURE__ */ jsxRuntimeExports.jsx(ApprovalCard, { tx: t }, t.id))
    ] })
  ] }) });
}
function ApprovalCard({
  tx
}) {
  return /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "rounded-2xl border border-border bg-card shadow-[var(--shadow-soft)] p-4", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex items-start justify-between gap-3", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "min-w-0", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-muted-foreground font-mono", children: tx.id }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-sm font-bold text-foreground truncate", children: tx.billName })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsx(StatusPill, { status: tx.status })
    ] }),
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-3 grid grid-cols-2 gap-3 text-xs", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-muted-foreground", children: "Nominal" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "font-bold text-foreground", children: fmtIDR(tx.amount) })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-muted-foreground", children: "Kode Unik" }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "font-bold text-primary", children: [
          "+",
          tx.uniqueCode
        ] })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-muted-foreground", children: "Bank" }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "font-bold text-foreground", children: [
          tx.bankName,
          " · ",
          tx.bankAccount
        ] })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-muted-foreground", children: "Diajukan" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "font-bold text-foreground", children: new Date(tx.createdAt).toLocaleString("id-ID") })
      ] })
    ] }),
    tx.proofDataUrl ? /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "mt-3 rounded-xl overflow-hidden border border-border bg-secondary", children: /* @__PURE__ */ jsxRuntimeExports.jsx("img", { src: tx.proofDataUrl, alt: "Bukti", className: "w-full max-h-60 object-contain bg-black/5" }) }) : /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "mt-3 text-[11px] text-muted-foreground italic", children: "Belum ada bukti unggahan dari santri." }),
    tx.status === "pending" && /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-3 grid grid-cols-2 gap-2", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsxs("button", { onClick: () => setStatus(tx.id, "rejected"), className: "py-2.5 rounded-xl bg-destructive/10 text-destructive font-bold text-xs flex items-center justify-center gap-1.5 active:scale-95", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx(CircleX, { size: 14 }),
        " Tolak"
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("button", { onClick: () => setStatus(tx.id, "approved"), disabled: !tx.proofDataUrl, className: "py-2.5 rounded-xl bg-success text-white font-bold text-xs flex items-center justify-center gap-1.5 active:scale-95 disabled:opacity-50", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx(CircleCheck, { size: 14 }),
        " Setujui"
      ] })
    ] })
  ] });
}
function StatusPill({
  status
}) {
  if (status === "approved") return /* @__PURE__ */ jsxRuntimeExports.jsxs("span", { className: "inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-success text-white text-[10px] font-bold", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsx(CircleCheck, { size: 11 }),
    " Approved"
  ] });
  if (status === "rejected") return /* @__PURE__ */ jsxRuntimeExports.jsxs("span", { className: "inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-destructive text-white text-[10px] font-bold", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsx(CircleX, { size: 11 }),
    " Rejected"
  ] });
  return /* @__PURE__ */ jsxRuntimeExports.jsxs("span", { className: "inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[oklch(0.78_0.16_75)] text-white text-[10px] font-bold", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsx(Clock, { size: 11 }),
    " Pending"
  ] });
}
export {
  AdminApprovalPage as component
};
