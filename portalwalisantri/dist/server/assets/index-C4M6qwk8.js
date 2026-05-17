import { r as reactExports, T as jsxRuntimeExports } from "./server-B6wkPDkD.js";
import { c as createLucideIcon, u as useNavigate, m as postLogin } from "./router-CFPFE5wZ.js";
import { u as useMutation } from "./useMutation-BYL_0E1C.js";
import { E as EyeOff, a as Eye } from "./eye-DoTsyshc.js";
import { L as LoaderCircle } from "./loader-circle-Nxd1j7ck.js";
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
const __iconNode$4 = [
  ["path", { d: "M5 12h14", key: "1ays0h" }],
  ["path", { d: "m12 5 7 7-7 7", key: "xquz4c" }]
];
const ArrowRight = createLucideIcon("arrow-right", __iconNode$4);
const __iconNode$3 = [
  [
    "path",
    {
      d: "M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z",
      key: "j76jl0"
    }
  ],
  ["path", { d: "M22 10v6", key: "1lu8f3" }],
  ["path", { d: "M6 12.5V16a6 3 0 0 0 12 0v-3.5", key: "1r8lef" }]
];
const GraduationCap = createLucideIcon("graduation-cap", __iconNode$3);
const __iconNode$2 = [
  ["rect", { width: "18", height: "11", x: "3", y: "11", rx: "2", ry: "2", key: "1w4ew1" }],
  ["path", { d: "M7 11V7a5 5 0 0 1 10 0v4", key: "fwvmzm" }]
];
const Lock = createLucideIcon("lock", __iconNode$2);
const __iconNode$1 = [
  [
    "path",
    {
      d: "M13.832 16.568a1 1 0 0 0 1.213-.303l.355-.465A2 2 0 0 1 17 15h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2A18 18 0 0 1 2 4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-.8 1.6l-.468.351a1 1 0 0 0-.292 1.233 14 14 0 0 0 6.392 6.384",
      key: "9njp5v"
    }
  ]
];
const Phone = createLucideIcon("phone", __iconNode$1);
const __iconNode = [
  [
    "path",
    {
      d: "M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z",
      key: "1xq2db"
    }
  ]
];
const Zap = createLucideIcon("zap", __iconNode);
function LoginPage() {
  const [show, setShow] = reactExports.useState(false);
  const [phone, setPhone] = reactExports.useState("");
  const [password, setPassword] = reactExports.useState("");
  const [error, setError] = reactExports.useState("");
  const navigate = useNavigate();
  const loginMutation = useMutation({
    mutationFn: async (data) => {
      const res = await postLogin(data);
      return res.data;
    },
    onSuccess: () => {
      navigate({
        to: "/dashboard"
      });
    },
    onError: (err) => {
      setError(err.response?.data?.message || "Login gagal. Cek nomor HP dan password Anda.");
    }
  });
  const handleLogin = (e) => {
    e.preventDefault();
    setError("");
    loginMutation.mutate({
      phone,
      password
    });
  };
  const handleDemoLogin = () => {
    navigate({
      to: "/dashboard"
    });
  };
  return /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "min-h-screen w-full flex justify-center bg-background", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative w-full max-w-md min-h-screen flex flex-col", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative pt-7 pb-24 px-6 rounded-b-[2.5rem] overflow-hidden", style: {
      background: "var(--gradient-hero)"
    }, children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "absolute -top-24 -right-16 w-72 h-72 rounded-full bg-primary-glow/30 blur-3xl" }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "absolute -bottom-16 -left-16 w-60 h-60 rounded-full bg-white/15 blur-3xl" }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "absolute top-10 right-8 w-24 h-24 rounded-full border border-white/15" }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "absolute top-20 right-20 w-12 h-12 rounded-full border border-white/10" }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "absolute inset-0 opacity-[0.07]", style: {
        backgroundImage: "linear-gradient(rgba(255,255,255,0.6) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.6) 1px, transparent 1px)",
        backgroundSize: "26px 26px",
        maskImage: "radial-gradient(ellipse at top right, black 30%, transparent 70%)"
      } }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative flex items-center justify-between gap-3", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex items-center gap-2.5 min-w-0", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "w-11 h-11 rounded-xl bg-white/15 backdrop-blur-md flex items-center justify-center border border-white/25 shrink-0 shadow-lg", children: /* @__PURE__ */ jsxRuntimeExports.jsx(GraduationCap, { className: "text-white", size: 22 }) }),
          /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex flex-col min-w-0 leading-tight", children: [
            /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "text-[10px] font-semibold text-white/70 uppercase tracking-[0.18em]", children: "PPTQ" }),
            /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "text-[15px] font-extrabold text-white tracking-[0.04em] whitespace-nowrap", children: "CAHAYA TASBIH" })
          ] })
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/15 border border-white/20 backdrop-blur shrink-0", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse" }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "text-[9px] font-semibold text-white tracking-wider uppercase", children: "Portal Wali" })
        ] })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative mt-8 space-y-3", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/10 border border-white/15 backdrop-blur", children: /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "text-[10px] font-bold text-white tracking-wider uppercase", children: "SantriPay" }) }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("h1", { className: "text-[26px] leading-[1.15] font-extrabold text-white tracking-tight", children: [
          "Selamat Datang,",
          /* @__PURE__ */ jsxRuntimeExports.jsx("br", {}),
          /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "text-white/90", children: "Wali Santri" })
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[13px] text-white/75 leading-relaxed max-w-[20rem]", children: "Pantau saldo, bayar tagihan, dan kelola keuangan ananda di pondok dalam satu genggaman." })
      ] })
    ] }),
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "px-6 -mt-8 relative z-10", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsxs("form", { onSubmit: handleLogin, className: "bg-card rounded-3xl p-6 shadow-[var(--shadow-card)] border border-border space-y-4", children: [
        error && /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "p-3 rounded-xl bg-destructive/10 border border-destructive/20 text-destructive text-xs font-semibold text-center", children: error }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("label", { className: "text-xs font-semibold text-muted-foreground uppercase tracking-wider", children: "No. Handphone" }),
          /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-2 flex items-center gap-3 bg-secondary rounded-2xl px-4 py-3.5 border border-transparent focus-within:border-primary transition", children: [
            /* @__PURE__ */ jsxRuntimeExports.jsx(Phone, { size: 18, className: "text-primary" }),
            /* @__PURE__ */ jsxRuntimeExports.jsx("input", { type: "tel", value: phone, onChange: (e) => setPhone(e.target.value), placeholder: "08xxxxxxxxxx", className: "bg-transparent flex-1 outline-none text-foreground text-sm font-medium placeholder:text-muted-foreground" })
          ] })
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("label", { className: "text-xs font-semibold text-muted-foreground uppercase tracking-wider", children: "Password" }),
          /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-2 flex items-center gap-3 bg-secondary rounded-2xl px-4 py-3.5 border border-transparent focus-within:border-primary transition", children: [
            /* @__PURE__ */ jsxRuntimeExports.jsx(Lock, { size: 18, className: "text-primary" }),
            /* @__PURE__ */ jsxRuntimeExports.jsx("input", { type: show ? "text" : "password", value: password, onChange: (e) => setPassword(e.target.value), placeholder: "••••••••", className: "bg-transparent flex-1 outline-none text-foreground text-sm font-medium placeholder:text-muted-foreground" }),
            /* @__PURE__ */ jsxRuntimeExports.jsx("button", { type: "button", onClick: () => setShow((s) => !s), className: "text-muted-foreground", children: show ? /* @__PURE__ */ jsxRuntimeExports.jsx(EyeOff, { size: 18 }) : /* @__PURE__ */ jsxRuntimeExports.jsx(Eye, { size: 18 }) })
          ] })
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "flex justify-end", children: /* @__PURE__ */ jsxRuntimeExports.jsx("button", { type: "button", className: "text-xs font-semibold text-primary", children: "Lupa Password?" }) }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("button", { type: "submit", disabled: loginMutation.isPending, className: "w-full py-4 rounded-2xl text-primary-foreground font-semibold text-sm shadow-[var(--shadow-glow)] flex items-center justify-center gap-2 transition active:scale-[0.98] disabled:opacity-70", style: {
          background: "var(--gradient-card)"
        }, children: loginMutation.isPending ? /* @__PURE__ */ jsxRuntimeExports.jsx(LoaderCircle, { className: "animate-spin", size: 18 }) : /* @__PURE__ */ jsxRuntimeExports.jsxs(jsxRuntimeExports.Fragment, { children: [
          "Masuk Sekarang",
          /* @__PURE__ */ jsxRuntimeExports.jsx(ArrowRight, { size: 18 })
        ] }) })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-5 flex items-center gap-3", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "flex-1 h-px bg-border" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "text-[10px] font-bold uppercase tracking-widest text-muted-foreground", children: "atau" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "flex-1 h-px bg-border" })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("button", { type: "button", onClick: handleDemoLogin, className: "mt-4 w-full py-3.5 rounded-2xl border border-dashed border-primary/40 bg-primary/5 hover:bg-primary/10 transition active:scale-[0.98] flex items-center justify-center gap-2 text-primary font-semibold text-sm", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx(Zap, { size: 16, className: "fill-primary" }),
        "Quick Login — Demo Wali Siswa"
      ] })
    ] })
  ] }) });
}
export {
  LoginPage as component
};
