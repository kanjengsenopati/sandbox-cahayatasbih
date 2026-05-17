import { r as reactExports, T as jsxRuntimeExports } from "./server-B6wkPDkD.js";
import { a as useSantri, X } from "./router-CFPFE5wZ.js";
import { C as ChevronDown } from "./chevron-down-CmeavGEV.js";
import { P as Plus } from "./plus-C609R3R4.js";
import { C as Check } from "./check-D3O2M5Eb.js";
const fmt = (n) => "Rp" + new Intl.NumberFormat("id-ID").format(n);
function SantriSwitcherTrigger({
  variant = "ghost",
  children
}) {
  const [open, setOpen] = reactExports.useState(false);
  return /* @__PURE__ */ jsxRuntimeExports.jsxs(jsxRuntimeExports.Fragment, { children: [
    /* @__PURE__ */ jsxRuntimeExports.jsx(
      "button",
      {
        onClick: () => setOpen(true),
        className: variant === "subtle" ? "text-[11px] text-white/90 font-semibold underline-offset-2 hover:underline shrink-0" : "inline-flex items-center gap-1 text-white/90",
        children: children ?? /* @__PURE__ */ jsxRuntimeExports.jsxs(jsxRuntimeExports.Fragment, { children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("span", { children: "Ganti" }),
          /* @__PURE__ */ jsxRuntimeExports.jsx(ChevronDown, { size: 14 })
        ] })
      }
    ),
    /* @__PURE__ */ jsxRuntimeExports.jsx(SantriSwitcherSheet, { open, onClose: () => setOpen(false) })
  ] });
}
function SantriSwitcherSheet({
  open,
  onClose
}) {
  const { santri, active, setActiveId } = useSantri();
  reactExports.useEffect(() => {
    if (!open) return;
    const prev = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    return () => {
      document.body.style.overflow = prev;
    };
  }, [open]);
  if (!open) return null;
  return /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "fixed inset-0 z-[60] flex items-end justify-center", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsx(
      "button",
      {
        onClick: onClose,
        "aria-label": "Tutup",
        className: "absolute inset-0 bg-black/50 backdrop-blur-sm animate-in fade-in"
      }
    ),
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative w-[calc(100%-1rem)] max-w-md mx-2 mb-2 bg-background rounded-[1.75rem] pb-4 pt-2.5 shadow-[var(--shadow-card)] animate-in slide-in-from-bottom duration-200", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "mx-auto w-10 h-1 rounded-full bg-border" }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "px-4 mt-2.5 flex items-center justify-between", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "min-w-0", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("h3", { className: "text-[15px] font-bold text-foreground leading-tight", children: "Pilih Santri" }),
          /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "text-[11px] text-muted-foreground leading-tight", children: [
            "Akun wali Anda terhubung ke ",
            santri.length,
            " santri."
          ] })
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsx(
          "button",
          {
            onClick: onClose,
            className: "w-8 h-8 rounded-lg bg-secondary flex items-center justify-center text-muted-foreground shrink-0",
            children: /* @__PURE__ */ jsxRuntimeExports.jsx(X, { size: 14 })
          }
        )
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "px-3 mt-3 space-y-1.5 max-h-[55vh] overflow-y-auto", children: [
        santri.map((s) => /* @__PURE__ */ jsxRuntimeExports.jsx(
          SantriRow,
          {
            santri: s,
            active: s.id === active.id,
            onPick: () => {
              setActiveId(s.id);
              onClose();
            }
          },
          s.id
        )),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("button", { className: "w-full mt-1.5 flex items-center justify-center gap-1.5 py-2.5 rounded-xl border-2 border-dashed border-border text-primary text-[13px] font-bold active:scale-[0.99] transition", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx(Plus, { size: 14 }),
          " Tambah Santri"
        ] })
      ] })
    ] })
  ] });
}
function SantriRow({
  santri,
  active,
  onPick
}) {
  return /* @__PURE__ */ jsxRuntimeExports.jsxs(
    "button",
    {
      onClick: onPick,
      className: `w-full flex items-center gap-2.5 p-2.5 rounded-xl border transition text-left ${active ? "border-primary bg-accent/60" : "border-border bg-card active:bg-secondary"}`,
      children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx(
          "div",
          {
            className: `w-10 h-10 rounded-xl bg-gradient-to-br ${santri.color} text-primary-foreground text-[13px] font-bold flex items-center justify-center shadow-[var(--shadow-soft)] shrink-0`,
            children: santri.initials
          }
        ),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex-1 min-w-0", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex items-center gap-1.5", children: [
            /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[13px] font-bold text-foreground truncate", children: santri.name }),
            active && /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "inline-flex items-center text-[8px] font-bold uppercase tracking-wider text-primary bg-primary/10 px-1 py-0.5 rounded", children: "Aktif" })
          ] }),
          /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "text-[10px] text-muted-foreground leading-tight", children: [
            santri.jenjang,
            " · Kelas ",
            santri.className,
            " · ••",
            santri.cardSuffix
          ] }),
          /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-0.5 flex items-center gap-2 text-[10px] font-semibold", children: [
            /* @__PURE__ */ jsxRuntimeExports.jsxs("span", { className: "text-success tabular-nums", children: [
              "Saldo ",
              fmt(santri.saldo)
            ] }),
            /* @__PURE__ */ jsxRuntimeExports.jsxs("span", { className: "text-muted-foreground tabular-nums", children: [
              "Tagihan ",
              fmt(santri.totalDue)
            ] })
          ] })
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsx(
          "span",
          {
            className: `w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 ${active ? "bg-primary border-primary" : "border-border"}`,
            children: active && /* @__PURE__ */ jsxRuntimeExports.jsx(Check, { size: 12, className: "text-primary-foreground", strokeWidth: 3 })
          }
        )
      ]
    }
  );
}
export {
  SantriSwitcherTrigger as S
};
