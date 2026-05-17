import { M as useRouter, T as jsxRuntimeExports } from "./server-B6wkPDkD.js";
import { c as createLucideIcon, L as Link } from "./router-CFPFE5wZ.js";
import { R as Receipt } from "./receipt-CFDuUZ2Y.js";
function useLocation(opts) {
  const router = useRouter();
  {
    const location = router.stores.location.get();
    return location;
  }
}
const __iconNode$1 = [
  ["path", { d: "M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8", key: "5wwlr5" }],
  [
    "path",
    {
      d: "M3 10a2 2 0 0 1 .709-1.528l7-6a2 2 0 0 1 2.582 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z",
      key: "r6nss1"
    }
  ]
];
const House = createLucideIcon("house", __iconNode$1);
const __iconNode = [
  ["path", { d: "M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2", key: "975kel" }],
  ["circle", { cx: "12", cy: "7", r: "4", key: "17ys0d" }]
];
const User = createLucideIcon("user", __iconNode);
const items = [
  { to: "/dashboard", label: "Beranda", icon: House },
  { to: "/tagihan", label: "Tagihan", icon: Receipt },
  { to: "/profil", label: "Profil", icon: User }
];
function BottomNav() {
  const loc = useLocation();
  return /* @__PURE__ */ jsxRuntimeExports.jsx("nav", { className: "fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md z-50 px-4 pb-4 pt-2", children: /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "flex items-center justify-around rounded-3xl bg-card/95 backdrop-blur-xl border border-border shadow-[var(--shadow-card)] px-2 py-2", children: items.map(({ to, label, icon: Icon }) => {
    const active = loc.pathname === to;
    return /* @__PURE__ */ jsxRuntimeExports.jsxs(
      Link,
      {
        to,
        className: `flex flex-col items-center gap-1 px-4 py-1.5 rounded-2xl transition-all ${active ? "bg-primary/10 -translate-y-0.5" : ""}`,
        children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx(
            "div",
            {
              className: `flex items-center justify-center w-10 h-10 rounded-2xl transition-all ${active ? "text-white shadow-[var(--shadow-glow)] ring-2 ring-primary/30 scale-110" : "text-muted-foreground"}`,
              style: active ? { background: "var(--gradient-card)" } : void 0,
              children: /* @__PURE__ */ jsxRuntimeExports.jsx(Icon, { size: 20, strokeWidth: 2.4 })
            }
          ),
          /* @__PURE__ */ jsxRuntimeExports.jsx(
            "span",
            {
              className: `text-[10px] font-bold tracking-wide ${active ? "text-primary" : "text-muted-foreground"}`,
              children: label
            }
          )
        ]
      },
      to
    );
  }) }) });
}
function MobileShell({ children }) {
  return /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "min-h-screen w-full flex justify-center bg-gradient-to-b from-secondary to-background", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative w-full max-w-md min-h-screen bg-background pb-32", children: [
    children,
    /* @__PURE__ */ jsxRuntimeExports.jsx(BottomNav, {})
  ] }) });
}
export {
  MobileShell as M
};
