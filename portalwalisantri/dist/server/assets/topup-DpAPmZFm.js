import { r as reactExports, T as jsxRuntimeExports } from "./server-B6wkPDkD.js";
import { c as createLucideIcon, u as useNavigate, a as useSantri, b as useQueryClient, d as uploadPaymentProof, p as postTopup, X } from "./router-CFPFE5wZ.js";
import { u as useMutation } from "./useMutation-BYL_0E1C.js";
import { B as Building2, U as Upload, C as Copy, I as Image } from "./upload-BfpYLQMJ.js";
import { L as LoaderCircle } from "./loader-circle-Nxd1j7ck.js";
import { A as ArrowLeft } from "./arrow-left-CU_-rGeT.js";
import { W as Wallet } from "./wallet-UASjtD00.js";
import { S as ShieldCheck } from "./shield-check-DkRIIV9N.js";
import { P as Plus } from "./plus-C609R3R4.js";
import { C as CircleCheck } from "./circle-check-C791vGyw.js";
import { C as Clock } from "./clock-Bt-K3S50.js";
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
const __iconNode$6 = [
  [
    "path",
    {
      d: "M13.997 4a2 2 0 0 1 1.76 1.05l.486.9A2 2 0 0 0 18.003 7H20a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h1.997a2 2 0 0 0 1.759-1.048l.489-.904A2 2 0 0 1 10.004 4z",
      key: "18u6gg"
    }
  ],
  ["circle", { cx: "12", cy: "13", r: "3", key: "1vg3eu" }]
];
const Camera = createLucideIcon("camera", __iconNode$6);
const __iconNode$5 = [
  ["circle", { cx: "12", cy: "12", r: "10", key: "1mglay" }],
  ["line", { x1: "12", x2: "12", y1: "8", y2: "12", key: "1pkeuh" }],
  ["line", { x1: "12", x2: "12.01", y1: "16", y2: "16", key: "4dfq90" }]
];
const CircleAlert = createLucideIcon("circle-alert", __iconNode$5);
const __iconNode$4 = [
  [
    "path",
    {
      d: "M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z",
      key: "1oefj6"
    }
  ],
  ["path", { d: "M14 2v5a1 1 0 0 0 1 1h5", key: "wfsgrz" }],
  ["circle", { cx: "10", cy: "12", r: "2", key: "737tya" }],
  ["path", { d: "m20 17-1.296-1.296a2.41 2.41 0 0 0-3.408 0L9 22", key: "wt3hpn" }]
];
const FileImage = createLucideIcon("file-image", __iconNode$4);
const __iconNode$3 = [
  ["path", { d: "M16 5h6", key: "1vod17" }],
  ["path", { d: "M19 2v6", key: "4bpg5p" }],
  ["path", { d: "M21 11.5V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7.5", key: "1ue2ih" }],
  ["path", { d: "m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21", key: "1xmnt7" }],
  ["circle", { cx: "9", cy: "9", r: "2", key: "af1f0g" }]
];
const ImagePlus = createLucideIcon("image-plus", __iconNode$3);
const __iconNode$2 = [
  ["path", { d: "M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8", key: "v9h5vc" }],
  ["path", { d: "M21 3v5h-5", key: "1q7to0" }],
  ["path", { d: "M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16", key: "3uifl3" }],
  ["path", { d: "M8 16H3v5", key: "1cv678" }]
];
const RefreshCw = createLucideIcon("refresh-cw", __iconNode$2);
const __iconNode$1 = [
  ["rect", { width: "14", height: "20", x: "5", y: "2", rx: "2", ry: "2", key: "1yt0o3" }],
  ["path", { d: "M12 18h.01", key: "mhygvu" }]
];
const Smartphone = createLucideIcon("smartphone", __iconNode$1);
const __iconNode = [
  ["circle", { cx: "11", cy: "11", r: "8", key: "4ej97u" }],
  ["line", { x1: "21", x2: "16.65", y1: "21", y2: "16.65", key: "13gj7c" }],
  ["line", { x1: "11", x2: "11", y1: "8", y2: "14", key: "1vmskp" }],
  ["line", { x1: "8", x2: "14", y1: "11", y2: "11", key: "durymu" }]
];
const ZoomIn = createLucideIcon("zoom-in", __iconNode);
const NOMINALS = [5e4, 1e5, 2e5, 5e5, 1e6, 2e6];
const METHODS = [{
  id: "bca",
  label: "Bank BCA",
  desc: "Transfer manual antar bank",
  icon: Building2,
  fee: 0,
  account: "1840558992",
  holder: "Yayasan PPTQ Cahaya Tasbih"
}, {
  id: "mandiri",
  label: "Bank Mandiri",
  desc: "Transfer manual antar bank",
  icon: Building2,
  fee: 0,
  account: "1370009988421",
  holder: "Yayasan PPTQ Cahaya Tasbih"
}, {
  id: "bsi",
  label: "Bank Syariah Indonesia",
  desc: "Transfer manual antar bank",
  icon: Building2,
  fee: 0,
  account: "7211884502",
  holder: "Yayasan PPTQ Cahaya Tasbih"
}, {
  id: "bri",
  label: "Bank BRI",
  desc: "Transfer manual antar bank",
  icon: Building2,
  fee: 0,
  account: "002901024458503",
  holder: "Yayasan PPTQ Cahaya Tasbih"
}, {
  id: "gopay",
  label: "GoPay",
  desc: "Transfer manual e-wallet",
  icon: Smartphone,
  fee: 0,
  account: "081288995521",
  holder: "Ust. Hasanuddin (Bendahara)"
}];
const fmt = (n) => new Intl.NumberFormat("id-ID", {
  style: "currency",
  currency: "IDR",
  minimumFractionDigits: 0
}).format(n);
function TopupPage() {
  const navigate = useNavigate();
  const {
    active,
    isLoading: isLoadingSantri
  } = useSantri();
  const queryClient = useQueryClient();
  const [step, setStep] = reactExports.useState("form");
  const [amount, setAmount] = reactExports.useState(1e5);
  const [custom, setCustom] = reactExports.useState("");
  const [method, setMethod] = reactExports.useState("bca");
  const [proof, setProof] = reactExports.useState(null);
  const [proofUrl, setProofUrl] = reactExports.useState("");
  const [refId, setRefId] = reactExports.useState("");
  const [paymentId, setPaymentId] = reactExports.useState(null);
  const selected = reactExports.useMemo(() => METHODS.find((m) => m.id === method), [method]);
  const uniqueCode = reactExports.useMemo(() => Math.floor(100 + Math.random() * 900), [amount, method]);
  const uniqueAmount = amount + uniqueCode;
  const total = uniqueAmount + selected.fee;
  const topupMutation = useMutation({
    mutationFn: async () => {
      const res = await postTopup({
        base_amount: amount,
        method: method.toUpperCase(),
        note: `Topup via ${selected.label}`
      });
      return res.data;
    },
    onSuccess: (data) => {
      setPaymentId(data.payment.id);
      setRefId(data.payment.payment_code);
      setStep("confirm");
    }
  });
  const uploadMutation = useMutation({
    mutationFn: async () => {
      if (!paymentId || !proof) return;
      const fd = new FormData();
      fd.append("proof", proof);
      return uploadPaymentProof(paymentId, fd);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: ["saldo-histories"]
      });
      setStep("pending");
    }
  });
  if (isLoadingSantri) {
    return /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "min-h-screen flex items-center justify-center bg-background", children: /* @__PURE__ */ jsxRuntimeExports.jsx(LoaderCircle, { className: "animate-spin text-primary", size: 40 }) });
  }
  if (step === "confirm") {
    return /* @__PURE__ */ jsxRuntimeExports.jsx(ConfirmScreen, { amount, uniqueCode, uniqueAmount, method: selected, total, refId, proof, proofUrl, isUploading: uploadMutation.isPending, onBack: () => setStep("form"), onSelectFile: (f) => {
      setProof(f);
      setProofUrl(f ? URL.createObjectURL(f) : "");
    }, onSubmit: () => uploadMutation.mutate() });
  }
  if (step === "pending") {
    return /* @__PURE__ */ jsxRuntimeExports.jsx(PendingScreen, { refId, total, method: selected, onHome: () => navigate({
      to: "/dashboard"
    }) });
  }
  return /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "min-h-screen w-full flex justify-center bg-secondary", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative w-full max-w-md min-h-screen bg-background pb-32", children: [
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
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-white/70 font-semibold uppercase tracking-wider", children: "Topup" }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-base font-bold text-white", children: "Tambah Saldo Santri" })
        ] })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative mt-5 flex items-center gap-3 text-white/90", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "w-11 h-11 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center", children: /* @__PURE__ */ jsxRuntimeExports.jsx(Wallet, { size: 20 }) }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-white/70", children: "Saldo Saat Ini" }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-xl font-bold tracking-tight", children: fmt(active?.saldo || 0) })
        ] })
      ] })
    ] }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "px-6 -mt-12 relative z-10", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "bg-card rounded-3xl border border-border shadow-[var(--shadow-card)] p-5", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-xs font-semibold text-muted-foreground uppercase tracking-wider", children: "Pilih Nominal" }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "grid grid-cols-3 gap-2 mt-3", children: NOMINALS.map((n) => {
        const active2 = amount === n && !custom;
        return /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: () => {
          setAmount(n);
          setCustom("");
        }, className: `py-3 rounded-2xl text-xs font-bold border transition active:scale-95 ${active2 ? "text-white border-transparent shadow-[var(--shadow-glow)] ring-2 ring-primary/40 scale-[1.03]" : "bg-secondary text-foreground border-transparent hover:border-primary/30"}`, style: active2 ? {
          background: "var(--gradient-card)"
        } : void 0, children: n >= 1e6 ? `${n / 1e6}jt` : `${n / 1e3}rb` }, n);
      }) }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-4", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("label", { className: "text-xs font-semibold text-muted-foreground uppercase tracking-wider", children: "Atau nominal lain" }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-2 flex items-center gap-2 bg-secondary rounded-2xl px-4 py-3.5 border border-transparent focus-within:border-primary transition", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "text-sm font-bold text-primary", children: "Rp" }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("input", { type: "number", inputMode: "numeric", value: custom, onChange: (e) => {
            setCustom(e.target.value);
            const v = parseInt(e.target.value, 10);
            if (!isNaN(v)) setAmount(v);
          }, placeholder: "0", className: "bg-transparent flex-1 outline-none text-foreground text-sm font-semibold placeholder:text-muted-foreground" })
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[10px] text-muted-foreground mt-1.5", children: "Minimal Rp 10.000" })
      ] })
    ] }) }),
    /* @__PURE__ */ jsxRuntimeExports.jsxs("section", { className: "px-6 mt-6", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2 px-1", children: "Metode Pembayaran" }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "bg-card rounded-2xl border border-border divide-y divide-border overflow-hidden shadow-[var(--shadow-soft)]", children: METHODS.map((m) => {
        const Icon = m.icon;
        const active2 = method === m.id;
        return /* @__PURE__ */ jsxRuntimeExports.jsxs("button", { onClick: () => setMethod(m.id), className: `w-full flex items-center gap-3 p-4 transition text-left ${active2 ? "bg-primary/5" : "active:bg-secondary"}`, children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: `w-11 h-11 rounded-xl flex items-center justify-center transition ${active2 ? "text-white shadow-[var(--shadow-glow)] ring-2 ring-primary/30" : "bg-secondary text-primary"}`, style: active2 ? {
            background: "var(--gradient-card)"
          } : void 0, children: /* @__PURE__ */ jsxRuntimeExports.jsx(Icon, { size: 18 }) }),
          /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex-1 min-w-0", children: [
            /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-sm font-bold text-foreground", children: m.label }),
            /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "text-[11px] text-muted-foreground", children: [
              m.desc,
              " · ",
              m.fee === 0 ? "Gratis" : `Biaya ${fmt(m.fee)}`
            ] })
          ] }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: `w-5 h-5 rounded-full border-2 flex items-center justify-center ${active2 ? "border-primary bg-primary" : "border-border"}`, children: active2 && /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "w-2 h-2 rounded-full bg-primary-foreground" }) })
        ] }, m.id);
      }) }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-3 flex items-center gap-2 text-[11px] text-muted-foreground px-1", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx(ShieldCheck, { size: 14, className: "text-success" }),
        "Transaksi dijamin aman & terenkripsi."
      ] })
    ] }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md px-4 pb-4 pt-3 bg-gradient-to-t from-background via-background to-background/0 z-40", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "rounded-2xl border border-border bg-card shadow-[var(--shadow-card)] p-4", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex items-center justify-between mb-3", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[10px] text-muted-foreground font-semibold uppercase tracking-wider", children: "Estimasi Bayar" }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-lg font-bold text-foreground", children: fmt(amount + selected.fee) })
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "text-[11px] text-muted-foreground", children: [
          "via ",
          /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "font-semibold text-foreground", children: selected.label })
        ] })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: () => amount >= 1e4 && topupMutation.mutate(), disabled: amount < 1e4 || topupMutation.isPending, className: "w-full py-3.5 rounded-2xl text-primary-foreground font-semibold text-sm shadow-[var(--shadow-glow)] flex items-center justify-center gap-2 disabled:opacity-50 transition active:scale-[0.98]", style: {
        background: "var(--gradient-card)"
      }, children: topupMutation.isPending ? /* @__PURE__ */ jsxRuntimeExports.jsx(LoaderCircle, { className: "animate-spin", size: 18 }) : /* @__PURE__ */ jsxRuntimeExports.jsxs(jsxRuntimeExports.Fragment, { children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx(Plus, { size: 18 }),
        " Lanjutkan Pembayaran"
      ] }) })
    ] }) })
  ] }) });
}
function ConfirmScreen({
  amount,
  uniqueCode,
  uniqueAmount,
  method,
  total,
  refId,
  proof,
  proofUrl,
  onBack,
  onSelectFile,
  onSubmit
}) {
  return /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "min-h-screen w-full flex justify-center bg-secondary", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative w-full max-w-md min-h-screen bg-background pb-36", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative px-6 pt-12 pb-20 rounded-b-[2rem] overflow-hidden", style: {
      background: "var(--gradient-hero)"
    }, children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "absolute -top-16 -right-12 w-56 h-56 rounded-full bg-primary-glow/30 blur-3xl" }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative flex items-center gap-3", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: onBack, className: "w-10 h-10 rounded-xl bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center text-white", children: /* @__PURE__ */ jsxRuntimeExports.jsx(ArrowLeft, { size: 18 }) }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-white/70 font-semibold uppercase tracking-wider", children: "Konfirmasi" }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-base font-bold text-white", children: "Detail Pembayaran" })
        ] })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "relative mt-6 text-[11px] text-white/70 uppercase tracking-widest font-semibold", children: "Total Transfer" }),
      /* @__PURE__ */ jsxRuntimeExports.jsx(CopyAmount, { value: uniqueAmount }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "relative mt-2 text-[11px] text-white/70", children: [
        "Termasuk kode unik",
        " ",
        /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "font-bold text-white", children: uniqueCode }),
        " agar transaksi mudah diverifikasi."
      ] })
    ] }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "px-6 -mt-12 relative z-10", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "bg-card rounded-3xl border border-border shadow-[var(--shadow-card)] p-5 space-y-4", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex items-center gap-3", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "w-11 h-11 rounded-xl bg-[var(--gradient-card)] flex items-center justify-center text-primary-foreground", children: /* @__PURE__ */ jsxRuntimeExports.jsx(method.icon, { size: 20 }) }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex-1 min-w-0", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-sm font-bold text-foreground", children: method.label }),
          /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "text-[11px] text-muted-foreground", children: [
            "a.n. ",
            method.holder
          ] })
        ] })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsx(CopyRow, { label: "No. Rekening / VA", value: method.account.replace(/\s/g, ""), display: method.account }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "grid grid-cols-2 gap-3 pt-2 border-t border-border", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx(Mini, { k: "Nominal", v: fmt(amount) }),
        /* @__PURE__ */ jsxRuntimeExports.jsx(Mini, { k: "Kode Unik", v: `+${uniqueCode}` }),
        /* @__PURE__ */ jsxRuntimeExports.jsx(Mini, { k: "Biaya Admin", v: fmt(method.fee) }),
        /* @__PURE__ */ jsxRuntimeExports.jsx(Mini, { k: "ID Transaksi", v: refId })
      ] })
    ] }) }),
    /* @__PURE__ */ jsxRuntimeExports.jsxs("section", { className: "px-6 mt-6", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex items-end justify-between mb-2 px-1", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-xs font-semibold text-muted-foreground uppercase tracking-wider", children: "Upload Bukti Bayar" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "text-[10px] font-bold text-destructive", children: "*Wajib" })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsx(ProofUploader, { proof, proofUrl, onSelectFile }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-3 rounded-2xl bg-card border border-border p-3.5", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "text-[11px] font-bold text-foreground mb-2 flex items-center gap-1.5", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx(ShieldCheck, { size: 13, className: "text-primary" }),
          "Pastikan bukti memenuhi:"
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("ul", { className: "space-y-1.5", children: [/* @__PURE__ */ jsxRuntimeExports.jsxs(jsxRuntimeExports.Fragment, { children: [
          "Nominal transfer ",
          /* @__PURE__ */ jsxRuntimeExports.jsxs("b", { children: [
            "persis ",
            fmt(uniqueAmount)
          ] })
        ] }), /* @__PURE__ */ jsxRuntimeExports.jsxs(jsxRuntimeExports.Fragment, { children: [
          "Tujuan: ",
          /* @__PURE__ */ jsxRuntimeExports.jsx("b", { children: method.account }),
          " (",
          method.label,
          ")"
        ] }), /* @__PURE__ */ jsxRuntimeExports.jsx(jsxRuntimeExports.Fragment, { children: "Tanggal & jam transfer terlihat jelas" }), /* @__PURE__ */ jsxRuntimeExports.jsx(jsxRuntimeExports.Fragment, { children: "Foto tidak buram dan tidak terpotong" })].map((t, i) => /* @__PURE__ */ jsxRuntimeExports.jsxs("li", { className: "flex items-start gap-2 text-[11px] text-foreground leading-relaxed", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx(CircleCheck, { size: 12, className: "text-success mt-0.5 shrink-0" }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("span", { children: t })
        ] }, i)) })
      ] })
    ] }),
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md px-4 pb-4 pt-3 bg-gradient-to-t from-background via-background to-background/0 z-40", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: onSubmit, disabled: !proof || isUploading, className: "w-full py-4 rounded-2xl text-primary-foreground font-semibold text-sm shadow-[var(--shadow-glow)] flex items-center justify-center gap-2 disabled:opacity-50 transition active:scale-[0.98]", style: {
        background: "var(--gradient-card)"
      }, children: isUploading ? /* @__PURE__ */ jsxRuntimeExports.jsx(LoaderCircle, { className: "animate-spin", size: 18 }) : /* @__PURE__ */ jsxRuntimeExports.jsxs(jsxRuntimeExports.Fragment, { children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx(Upload, { size: 18 }),
        " Kirim Bukti & Konfirmasi"
      ] }) }),
      !proof && /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-muted-foreground text-center mt-2", children: "Upload bukti bayar terlebih dahulu untuk melanjutkan." })
    ] })
  ] }) });
}
function PendingScreen({
  refId,
  total,
  method,
  onHome
}) {
  return /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "min-h-screen w-full flex justify-center bg-secondary", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "w-full max-w-md min-h-screen bg-background flex flex-col px-6 pt-16 pb-10", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex flex-col items-center text-center", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "absolute inset-0 rounded-full bg-warning/20 animate-ping" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "relative w-20 h-20 rounded-full bg-warning/15 flex items-center justify-center", children: /* @__PURE__ */ jsxRuntimeExports.jsx(Clock, { className: "text-warning", size: 40 }) })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("span", { className: "mt-5 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-warning/15 text-warning text-[11px] font-bold uppercase tracking-wider", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "w-1.5 h-1.5 rounded-full bg-warning animate-pulse" }),
        "Pending"
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("h2", { className: "text-2xl font-bold text-foreground mt-4", children: "Menunggu Konfirmasi" }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "text-sm text-muted-foreground mt-2 max-w-xs", children: [
        "Bukti bayar Anda sudah terkirim. Petugas akan memverifikasi pembayaran dalam ",
        /* @__PURE__ */ jsxRuntimeExports.jsx("b", { children: "1×24 jam" }),
        "."
      ] })
    ] }),
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-8 rounded-3xl bg-card border border-border shadow-[var(--shadow-soft)] p-5 space-y-3", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx(Row, { k: "ID Transaksi", v: refId }),
      /* @__PURE__ */ jsxRuntimeExports.jsx(Row, { k: "Total Bayar", v: fmt(total), bold: true }),
      /* @__PURE__ */ jsxRuntimeExports.jsx(Row, { k: "Metode", v: method.label }),
      /* @__PURE__ */ jsxRuntimeExports.jsx(Row, { k: "Status", v: "Menunggu approval petugas" })
    ] }),
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-6 rounded-3xl bg-card border border-border p-5", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-3", children: "Status Transaksi" }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("ol", { className: "relative border-l-2 border-border ml-2 space-y-5", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx(Step, { done: true, label: "Bukti bayar dikirim", sub: "Baru saja" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx(Step, { active: true, label: "Menunggu approval petugas", sub: "Sedang diverifikasi" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx(Step, { label: "Saldo masuk ke akun santri", sub: "Setelah disetujui" })
      ] })
    ] }),
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-auto pt-6 space-y-2", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: onHome, className: "w-full py-4 rounded-2xl text-primary-foreground font-semibold text-sm shadow-[var(--shadow-glow)]", style: {
        background: "var(--gradient-card)"
      }, children: "Kembali ke Beranda" }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("button", { className: "w-full py-3 rounded-2xl text-primary font-semibold text-sm bg-secondary", children: "Hubungi Petugas" })
    ] })
  ] }) });
}
function CopyAmount({
  value
}) {
  const [copied, setCopied] = reactExports.useState(false);
  const copy = async () => {
    try {
      await navigator.clipboard.writeText(String(value));
      setCopied(true);
      setTimeout(() => setCopied(false), 1500);
    } catch {
    }
  };
  return /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative mt-2 flex items-end gap-3 text-white", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsx("h2", { className: "text-3xl font-bold tracking-tight", children: fmt(value) }),
    /* @__PURE__ */ jsxRuntimeExports.jsxs("button", { onClick: copy, className: "mb-1.5 flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-white/15 backdrop-blur border border-white/20 text-[11px] font-semibold", children: [
      copied ? /* @__PURE__ */ jsxRuntimeExports.jsx(Check, { size: 13 }) : /* @__PURE__ */ jsxRuntimeExports.jsx(Copy, { size: 13 }),
      copied ? "Tersalin" : "Salin"
    ] })
  ] });
}
function CopyRow({
  label,
  value,
  display
}) {
  const [copied, setCopied] = reactExports.useState(false);
  const copy = async () => {
    try {
      await navigator.clipboard.writeText(value);
      setCopied(true);
      setTimeout(() => setCopied(false), 1500);
    } catch {
    }
  };
  return /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "rounded-2xl bg-secondary p-3 flex items-center gap-3", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex-1 min-w-0", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[10px] font-semibold text-muted-foreground uppercase tracking-wider", children: label }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-base font-bold text-foreground tracking-wider truncate", children: display })
    ] }),
    /* @__PURE__ */ jsxRuntimeExports.jsxs("button", { onClick: copy, className: "flex items-center gap-1 px-3 py-2 rounded-xl bg-card border border-border text-[11px] font-semibold text-primary active:scale-95 transition", children: [
      copied ? /* @__PURE__ */ jsxRuntimeExports.jsx(Check, { size: 14 }) : /* @__PURE__ */ jsxRuntimeExports.jsx(Copy, { size: 14 }),
      copied ? "OK" : "Salin"
    ] })
  ] });
}
function Mini({
  k,
  v
}) {
  return /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { children: [
    /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[10px] text-muted-foreground font-semibold uppercase tracking-wider", children: k }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-sm font-bold text-foreground truncate", children: v })
  ] });
}
function Row({
  k,
  v,
  bold
}) {
  return /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex justify-between items-center gap-3", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "text-sm text-muted-foreground", children: k }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: `text-sm text-right ${bold ? "font-bold text-foreground" : "text-foreground font-semibold"}`, children: v })
  ] });
}
function Step({
  label,
  sub,
  done,
  active
}) {
  return /* @__PURE__ */ jsxRuntimeExports.jsxs("li", { className: "ml-4", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: `absolute -left-[9px] w-4 h-4 rounded-full border-2 ${done ? "bg-success border-success" : active ? "bg-warning border-warning animate-pulse" : "bg-background border-border"}` }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: `text-sm font-bold ${active ? "text-warning" : done ? "text-foreground" : "text-muted-foreground"}`, children: label }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-muted-foreground", children: sub })
  ] });
}
const MAX_PROOF_BYTES = 5 * 1024 * 1024;
const ACCEPTED_TYPES = ["image/jpeg", "image/jpg", "image/png", "image/webp"];
function ProofUploader({
  proof,
  proofUrl,
  onSelectFile
}) {
  const galleryRef = reactExports.useRef(null);
  const cameraRef = reactExports.useRef(null);
  const [error, setError] = reactExports.useState("");
  const [progress, setProgress] = reactExports.useState(0);
  const [uploading, setUploading] = reactExports.useState(false);
  const [drag, setDrag] = reactExports.useState(false);
  const [zoom, setZoom] = reactExports.useState(false);
  const validate = (file) => {
    if (!ACCEPTED_TYPES.includes(file.type)) return "Format harus JPG, PNG, atau WEBP.";
    if (file.size > MAX_PROOF_BYTES) return `Ukuran maksimal 5 MB. File Anda ${(file.size / 1024 / 1024).toFixed(1)} MB.`;
    return "";
  };
  const handleFile = (file) => {
    if (!file) return;
    const err = validate(file);
    if (err) {
      setError(err);
      return;
    }
    setError("");
    setUploading(true);
    setProgress(0);
    let p = 0;
    const interval = setInterval(() => {
      p += 18 + Math.random() * 14;
      if (p >= 100) {
        p = 100;
        clearInterval(interval);
        setProgress(100);
        setTimeout(() => {
          setUploading(false);
          onSelectFile(file);
        }, 180);
      } else {
        setProgress(p);
      }
    }, 90);
  };
  const reset = () => {
    setError("");
    setProgress(0);
    setUploading(false);
    onSelectFile(null);
  };
  return /* @__PURE__ */ jsxRuntimeExports.jsxs(jsxRuntimeExports.Fragment, { children: [
    /* @__PURE__ */ jsxRuntimeExports.jsx("input", { ref: galleryRef, type: "file", accept: "image/jpeg,image/png,image/webp", className: "hidden", onChange: (e) => {
      handleFile(e.target.files?.[0]);
      e.target.value = "";
    } }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("input", { ref: cameraRef, type: "file", accept: "image/*", capture: "environment", className: "hidden", onChange: (e) => {
      handleFile(e.target.files?.[0]);
      e.target.value = "";
    } }),
    !proof && !uploading && /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { onDragOver: (e) => {
      e.preventDefault();
      setDrag(true);
    }, onDragLeave: () => setDrag(false), onDrop: (e) => {
      e.preventDefault();
      setDrag(false);
      handleFile(e.dataTransfer.files?.[0]);
    }, className: `rounded-3xl border-2 border-dashed p-5 transition ${drag ? "border-primary bg-primary/5 scale-[1.01]" : error ? "border-destructive/50 bg-destructive/5" : "border-border bg-card"}`, children: [
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex flex-col items-center text-center", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "w-14 h-14 rounded-2xl flex items-center justify-center text-primary-foreground shadow-[var(--shadow-glow)]", style: {
          background: "var(--gradient-card)"
        }, children: /* @__PURE__ */ jsxRuntimeExports.jsx(FileImage, { size: 24 }) }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "mt-3 text-sm font-bold text-foreground", children: "Tarik & lepas bukti di sini" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-muted-foreground mt-0.5", children: "atau pilih sumber di bawah · JPG / PNG / WEBP · maks 5 MB" })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-4 grid grid-cols-2 gap-2.5", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsxs("button", { onClick: () => cameraRef.current?.click(), className: "flex flex-col items-center gap-1 py-3 rounded-2xl bg-secondary border border-border active:scale-95 transition", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center", children: /* @__PURE__ */ jsxRuntimeExports.jsx(Camera, { size: 16 }) }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "text-[12px] font-bold text-foreground", children: "Kamera" }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "text-[10px] text-muted-foreground", children: "Foto langsung" })
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("button", { onClick: () => galleryRef.current?.click(), className: "flex flex-col items-center gap-1 py-3 rounded-2xl bg-secondary border border-border active:scale-95 transition", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center", children: /* @__PURE__ */ jsxRuntimeExports.jsx(ImagePlus, { size: 16 }) }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "text-[12px] font-bold text-foreground", children: "Galeri" }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "text-[10px] text-muted-foreground", children: "Dari perangkat" })
        ] })
      ] }),
      error && /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-3 rounded-xl bg-destructive/10 border border-destructive/30 px-3 py-2 flex items-start gap-2", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx(CircleAlert, { size: 13, className: "text-destructive mt-0.5 shrink-0" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] font-semibold text-destructive leading-relaxed", children: error })
      ] })
    ] }),
    uploading && /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "rounded-3xl border border-border bg-card p-5", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex items-center gap-3", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "w-11 h-11 rounded-2xl bg-primary/10 text-primary flex items-center justify-center", children: /* @__PURE__ */ jsxRuntimeExports.jsx(Upload, { size: 18, className: "animate-pulse" }) }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex-1 min-w-0", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-sm font-bold text-foreground", children: "Mengunggah bukti…" }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-muted-foreground", children: "Mohon tunggu sebentar" })
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("span", { className: "text-sm font-bold text-primary tabular-nums", children: [
          Math.round(progress),
          "%"
        ] })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "mt-3 h-2 rounded-full bg-secondary overflow-hidden", children: /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "h-full rounded-full transition-all", style: {
        width: `${progress}%`,
        background: "var(--gradient-card)"
      } }) })
    ] }),
    proof && !uploading && /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "rounded-3xl border border-border bg-card overflow-hidden shadow-[var(--shadow-soft)]", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative bg-secondary", children: [
        proofUrl ? /* @__PURE__ */ jsxRuntimeExports.jsx("img", { src: proofUrl, alt: "Bukti transfer", className: "w-full h-56 object-cover" }) : /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "h-56 flex items-center justify-center text-muted-foreground", children: /* @__PURE__ */ jsxRuntimeExports.jsx(Image, { size: 32 }) }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("span", { className: "absolute top-2.5 left-2.5 inline-flex items-center gap-1 px-2 py-1 rounded-full bg-success text-white text-[10px] font-bold shadow", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx(CircleCheck, { size: 11 }),
          " Siap dikirim"
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "absolute top-2.5 right-2.5 flex items-center gap-1.5", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: () => setZoom(true), className: "w-8 h-8 rounded-full bg-black/60 text-white flex items-center justify-center backdrop-blur active:scale-95", "aria-label": "Perbesar", children: /* @__PURE__ */ jsxRuntimeExports.jsx(ZoomIn, { size: 14 }) }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: reset, className: "w-8 h-8 rounded-full bg-black/60 text-white flex items-center justify-center backdrop-blur active:scale-95", "aria-label": "Hapus", children: /* @__PURE__ */ jsxRuntimeExports.jsx(X, { size: 14 }) })
        ] })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "p-3 flex items-center gap-3", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "w-9 h-9 rounded-xl bg-success/15 text-success flex items-center justify-center shrink-0", children: /* @__PURE__ */ jsxRuntimeExports.jsx(FileImage, { size: 16 }) }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex-1 min-w-0", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[12px] font-bold text-foreground truncate", children: proof.name }),
          /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "text-[10px] text-muted-foreground", children: [
            (proof.size / 1024).toFixed(0),
            " KB ·",
            " ",
            proof.type.replace("image/", "").toUpperCase()
          ] })
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("button", { onClick: () => galleryRef.current?.click(), className: "inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-secondary border border-border text-[11px] font-bold text-primary active:scale-95", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx(RefreshCw, { size: 12 }),
          " Ganti"
        ] })
      ] })
    ] }),
    zoom && proofUrl && /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { onClick: () => setZoom(false), className: "fixed inset-0 z-[60] bg-black/90 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: () => setZoom(false), className: "absolute top-5 right-5 w-10 h-10 rounded-full bg-white/15 text-white flex items-center justify-center backdrop-blur", children: /* @__PURE__ */ jsxRuntimeExports.jsx(X, { size: 18 }) }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("img", { src: proofUrl, alt: "Preview bukti", className: "max-h-full max-w-full rounded-2xl shadow-2xl", onClick: (e) => e.stopPropagation() })
    ] })
  ] });
}
export {
  TopupPage as component
};
