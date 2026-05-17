import { T as jsxRuntimeExports } from "./server-B6wkPDkD.js";
import { c as createLucideIcon, u as useNavigate, e as useQuery, i as fetchProfile } from "./router-CFPFE5wZ.js";
import { M as MobileShell } from "./MobileShell-Dd7LjuQz.js";
import { L as LoaderCircle } from "./loader-circle-Nxd1j7ck.js";
import { B as Bell } from "./bell-B8JX_l6V.js";
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
import "./receipt-CFDuUZ2Y.js";
const __iconNode$4 = [
  ["circle", { cx: "12", cy: "12", r: "10", key: "1mglay" }],
  ["path", { d: "M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3", key: "1u773s" }],
  ["path", { d: "M12 17h.01", key: "p32p05" }]
];
const CircleQuestionMark = createLucideIcon("circle-question-mark", __iconNode$4);
const __iconNode$3 = [
  ["rect", { width: "20", height: "14", x: "2", y: "5", rx: "2", key: "ynyp8z" }],
  ["line", { x1: "2", x2: "22", y1: "10", y2: "10", key: "1b3vmo" }]
];
const CreditCard = createLucideIcon("credit-card", __iconNode$3);
const __iconNode$2 = [
  ["path", { d: "m16 17 5-5-5-5", key: "1bji2h" }],
  ["path", { d: "M21 12H9", key: "dn1m92" }],
  ["path", { d: "M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4", key: "1uf3rs" }]
];
const LogOut = createLucideIcon("log-out", __iconNode$2);
const __iconNode$1 = [
  [
    "path",
    {
      d: "M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915",
      key: "1i5ecw"
    }
  ],
  ["circle", { cx: "12", cy: "12", r: "3", key: "1v7zrd" }]
];
const Settings = createLucideIcon("settings", __iconNode$1);
const __iconNode = [
  [
    "path",
    {
      d: "M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z",
      key: "oel41y"
    }
  ]
];
const Shield = createLucideIcon("shield", __iconNode);
const groups = [{
  title: "Akun",
  items: [{
    label: "Keamanan & PIN",
    icon: Shield
  }, {
    label: "Notifikasi",
    icon: Bell
  }, {
    label: "Metode Pembayaran",
    icon: CreditCard
  }]
}, {
  title: "Lainnya",
  items: [{
    label: "Pengaturan",
    icon: Settings
  }, {
    label: "Bantuan",
    icon: CircleQuestionMark
  }]
}];
function Profil() {
  useNavigate();
  const {
    data: profileData,
    isLoading
  } = useQuery({
    queryKey: ["profile"],
    queryFn: async () => {
      const res = await fetchProfile();
      return res.data;
    }
  });
  if (isLoading) {
    return /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "min-h-screen flex items-center justify-center bg-background", children: /* @__PURE__ */ jsxRuntimeExports.jsx(LoaderCircle, { className: "animate-spin text-primary", size: 40 }) });
  }
  const user = profileData?.user;
  const students = profileData?.students || [];
  const initials = user?.name ? user.name.split(" ").map((n) => n[0]).join("").substring(0, 2).toUpperCase() : "W";
  const handleLogout = () => {
    window.location.href = "/wali/logout";
  };
  return /* @__PURE__ */ jsxRuntimeExports.jsxs(MobileShell, { children: [
    /* @__PURE__ */ jsxRuntimeExports.jsx("header", { className: "px-6 pt-12 pb-6", children: /* @__PURE__ */ jsxRuntimeExports.jsx("h1", { className: "text-2xl font-bold text-foreground", children: "Profil" }) }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("section", { className: "px-6", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "rounded-3xl p-5 text-primary-foreground shadow-[var(--shadow-glow)] flex items-center gap-4", style: {
      background: "var(--gradient-hero)"
    }, children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "w-16 h-16 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center text-2xl font-bold border border-white/20", children: user?.avatar ? /* @__PURE__ */ jsxRuntimeExports.jsx("img", { src: `/${user.avatar}`, alt: user.name, className: "w-full h-full object-cover rounded-2xl" }) : initials }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex-1", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "font-bold text-lg", children: user?.name }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-xs text-white/70", children: user?.phone }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "text-[11px] text-white/60 mt-1", children: [
          "Wali dari ",
          students.map((s) => s.name).join(", ")
        ] })
      ] })
    ] }) }),
    groups.map((g) => /* @__PURE__ */ jsxRuntimeExports.jsxs("section", { className: "px-6 mt-6", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2 px-1", children: g.title }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "bg-card rounded-2xl border border-border divide-y divide-border overflow-hidden shadow-[var(--shadow-soft)]", children: g.items.map((it) => {
        const Icon = it.icon;
        return /* @__PURE__ */ jsxRuntimeExports.jsxs("button", { className: "w-full flex items-center gap-3 p-4 active:bg-secondary transition", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "w-10 h-10 rounded-xl bg-secondary flex items-center justify-center text-primary", children: /* @__PURE__ */ jsxRuntimeExports.jsx(Icon, { size: 18 }) }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "flex-1 text-left text-sm font-semibold text-foreground", children: it.label }),
          /* @__PURE__ */ jsxRuntimeExports.jsx(ChevronRight, { size: 18, className: "text-muted-foreground" })
        ] }, it.label);
      }) })
    ] }, g.title)),
    /* @__PURE__ */ jsxRuntimeExports.jsx("section", { className: "px-6 mt-6", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("button", { onClick: handleLogout, className: "w-full flex items-center justify-center gap-2 p-4 rounded-2xl bg-destructive/10 text-destructive font-semibold text-sm border border-destructive/20", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx(LogOut, { size: 18 }),
      "Keluar"
    ] }) })
  ] });
}
export {
  Profil as component
};
