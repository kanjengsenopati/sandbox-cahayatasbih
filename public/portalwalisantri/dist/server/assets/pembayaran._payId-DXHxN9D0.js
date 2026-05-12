import { r as reactExports, T as jsxRuntimeExports } from "./server-B6wkPDkD.js";
import { n as useParams, u as useNavigate, b as useQueryClient, e as useQuery, r as fetchPaymentDetail, d as uploadPaymentProof, L as Link, C as CircleX } from "./router-CFPFE5wZ.js";
import { u as useMutation } from "./useMutation-BYL_0E1C.js";
import { L as LoaderCircle } from "./loader-circle-Nxd1j7ck.js";
import { A as ArrowLeft } from "./arrow-left-CU_-rGeT.js";
import { B as Building2, C as Copy, U as Upload, I as Image } from "./upload-BfpYLQMJ.js";
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
const fmtIDR = (n) => new Intl.NumberFormat("id-ID", {
  style: "currency",
  currency: "IDR",
  minimumFractionDigits: 0
}).format(n);
function PembayaranPage() {
  const {
    payId
  } = useParams({
    from: "/pembayaran/$payId"
  });
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const {
    data: paymentRes,
    isLoading
  } = useQuery({
    queryKey: ["payment", payId],
    queryFn: async () => {
      const res = await fetchPaymentDetail(payId);
      return res.data;
    }
  });
  const tx = reactExports.useMemo(() => {
    if (!paymentRes) return null;
    const p = paymentRes.payment;
    const details = paymentRes.details || [];
    return {
      id: p.id,
      payment_code: p.payment_code,
      billName: p.description || "Pembayaran Tagihan",
      amount: p.total_amount,
      baseAmount: p.base_amount,
      uniqueCode: p.unique_code,
      status: p.status === "PAID" ? "approved" : p.status === "REJECTED" ? "rejected" : "pending",
      bankName: p.method || "BCA",
      bankAccount: "1840558992",
      // Mocked for now or from setting
      bankHolder: "Yayasan PPTQ Cahaya Tasbih",
      proofUrl: p.proof_path,
      items: details.map((d) => ({
        id: d.id,
        label: d.name || `Cicilan ${d.month_name || ""} ${d.year || ""}`,
        amount: d.amount
      }))
    };
  }, [paymentRes]);
  const uploadMutation = useMutation({
    mutationFn: async (file) => {
      const fd = new FormData();
      fd.append("proof", file);
      return uploadPaymentProof(payId, fd);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: ["payment", payId]
      });
    }
  });
  if (isLoading) {
    return /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "min-h-screen flex items-center justify-center bg-background", children: /* @__PURE__ */ jsxRuntimeExports.jsx(LoaderCircle, { className: "animate-spin text-primary", size: 40 }) });
  }
  if (!tx) {
    return /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "min-h-screen flex flex-col items-center justify-center gap-3 text-sm", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-muted-foreground", children: "Pembayaran tidak ditemukan." }),
      /* @__PURE__ */ jsxRuntimeExports.jsx(Link, { to: "/tagihan", className: "text-primary font-bold", children: "Kembali" })
    ] });
  }
  const baseStr = fmtIDR(tx.baseAmount);
  const totalStr = fmtIDR(tx.amount);
  const isPending = tx.status === "pending";
  const isApproved = tx.status === "approved";
  const isRejected = tx.status === "rejected";
  return /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "min-h-screen w-full flex justify-center bg-secondary", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative w-full max-w-md min-h-screen bg-background pb-32", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "px-6 pt-12 pb-3 flex items-center gap-3", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: () => navigate({
        to: "/tagihan"
      }), className: "w-10 h-10 rounded-xl bg-secondary border border-border flex items-center justify-center text-foreground active:scale-95 transition", children: /* @__PURE__ */ jsxRuntimeExports.jsx(ArrowLeft, { size: 18 }) }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "min-w-0", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-muted-foreground font-semibold uppercase tracking-wider", children: "Pembayaran" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-base font-bold text-foreground truncate", children: tx.billName })
      ] })
    ] }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "px-5 pt-3", children: /* @__PURE__ */ jsxRuntimeExports.jsx(StatusBanner, { status: tx.status }) }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "px-5 pt-5", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "rounded-2xl border border-border bg-card shadow-[var(--shadow-soft)] p-4", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-muted-foreground font-semibold uppercase tracking-wider", children: "Nominal Transfer" }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-1 flex items-end justify-between gap-3", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-2xl font-extrabold text-foreground", children: totalStr }),
        /* @__PURE__ */ jsxRuntimeExports.jsx(CopyButton, { value: String(tx.amount), label: "Salin" })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "mt-2 text-[11px] text-muted-foreground", children: [
        "Dasar ",
        baseStr,
        " +",
        " ",
        /* @__PURE__ */ jsxRuntimeExports.jsxs("span", { className: "font-bold text-primary", children: [
          tx.uniqueCode,
          " kode unik"
        ] }),
        " ",
        "(3 digit terakhir wajib sesuai)"
      ] })
    ] }) }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "px-5 pt-3", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative overflow-hidden rounded-3xl p-4 shadow-[var(--shadow-glow)]", style: {
      background: "var(--gradient-hero)"
    }, children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "absolute -top-10 -right-10 w-40 h-40 rounded-full bg-white/10 blur-3xl pointer-events-none" }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "absolute -bottom-12 -left-8 w-36 h-36 rounded-full bg-white/5 blur-2xl pointer-events-none" }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative flex items-center gap-2.5", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "w-10 h-10 rounded-xl bg-white/15 border border-white/20 backdrop-blur text-white flex items-center justify-center shadow-[var(--shadow-soft)]", children: /* @__PURE__ */ jsxRuntimeExports.jsx(Building2, { size: 18 }) }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "min-w-0", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[10px] uppercase tracking-widest text-white/70 font-semibold", children: "Transfer ke" }),
          /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "text-sm font-extrabold text-white leading-tight", children: [
            "Bank ",
            tx.bankName
          ] })
        ] })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "relative mt-3 rounded-2xl bg-white/15 border border-white/20 backdrop-blur px-3.5 py-3 flex items-center justify-between gap-2", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "min-w-0", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[10px] uppercase tracking-widest text-white/70 font-semibold", children: "Nomor Rekening" }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-lg font-extrabold text-white tracking-wider tabular-nums truncate", children: tx.bankAccount })
        ] }),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("button", { onClick: async () => {
          try {
            await navigator.clipboard.writeText(tx.bankAccount);
          } catch {
          }
        }, className: "shrink-0 inline-flex items-center gap-1.5 px-3 py-2 rounded-full bg-white/15 border border-white/20 text-white text-[11px] font-semibold backdrop-blur active:scale-95 transition", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx(Copy, { size: 12 }),
          " Salin"
        ] })
      ] }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("p", { className: "relative mt-2.5 text-[11px] text-white/70", children: [
        "a.n. ",
        /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "font-bold text-white", children: tx.bankHolder })
      ] })
    ] }) }),
    tx.items && tx.items.length > 0 && /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "px-5 pt-3", children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "rounded-2xl border border-border bg-card shadow-[var(--shadow-soft)] p-4", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-muted-foreground font-semibold uppercase tracking-wider mb-2", children: "Rincian" }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "divide-y divide-border", children: [
        tx.items.map((it) => /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "py-2 flex items-center justify-between gap-3 text-sm", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "text-foreground truncate", children: it.label }),
          /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "font-bold text-foreground", children: fmtIDR(it.amount) })
        ] }, it.id)),
        /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "py-2 flex items-center justify-between gap-3 text-sm", children: [
          /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "text-muted-foreground", children: "Kode Unik" }),
          /* @__PURE__ */ jsxRuntimeExports.jsxs("span", { className: "font-bold text-primary", children: [
            "+",
            tx.uniqueCode
          ] })
        ] })
      ] })
    ] }) }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "px-5 pt-3", children: /* @__PURE__ */ jsxRuntimeExports.jsx(ProofUploader, { tx, onUpload: (f) => uploadMutation.mutate(f), isUploading: uploadMutation.isPending }) }),
    isApproved && /* @__PURE__ */ jsxRuntimeExports.jsx(StickyAction, { children: /* @__PURE__ */ jsxRuntimeExports.jsxs("button", { onClick: () => navigate({
      to: "/riwayat"
    }), className: "w-full py-3.5 rounded-2xl bg-success text-white font-bold text-sm flex items-center justify-center gap-2", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx(CircleCheck, { size: 16 }),
      " Lihat di Riwayat"
    ] }) }),
    isRejected && /* @__PURE__ */ jsxRuntimeExports.jsx(StickyAction, { children: /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: () => navigate({
      to: "/tagihan"
    }), className: "w-full py-3.5 rounded-2xl bg-secondary border border-border text-foreground font-bold text-sm", children: "Kembali ke Tagihan" }) }),
    isPending && tx.proofUrl && /* @__PURE__ */ jsxRuntimeExports.jsx(StickyAction, { children: /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: () => navigate({
      to: "/riwayat"
    }), className: "w-full py-3.5 rounded-2xl text-primary-foreground font-bold text-sm shadow-[var(--shadow-glow)] active:scale-[0.98]", style: {
      background: "var(--gradient-card)"
    }, children: "Lihat Status di Riwayat" }) })
  ] }) });
}
function StickyAction({
  children
}) {
  return /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md px-3 pb-3 pt-2 bg-gradient-to-t from-background via-background to-background/0 z-40", children });
}
function StatusBanner({
  status
}) {
  if (status === "approved") {
    return /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "rounded-xl border border-success/30 bg-success/10 px-3 py-2.5 flex items-center gap-2", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx(CircleCheck, { size: 16, className: "text-success" }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex-1", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-xs font-bold text-success", children: "Pembayaran Disetujui" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-success/80", children: "Transaksi sudah diverifikasi petugas." })
      ] })
    ] });
  }
  if (status === "rejected") {
    return /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "rounded-xl border border-destructive/30 bg-destructive/10 px-3 py-2.5 flex items-center gap-2", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx(CircleX, { size: 16, className: "text-destructive" }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex-1", children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-xs font-bold text-destructive", children: "Pembayaran Ditolak" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-destructive/80", children: "Bukti tidak valid, silakan ulangi." })
      ] })
    ] });
  }
  return /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "rounded-xl border border-[oklch(0.78_0.16_75)]/40 bg-[oklch(0.78_0.16_75)]/10 px-3 py-2.5 flex items-center gap-2", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsx(Clock, { size: 16, className: "text-[oklch(0.62_0.18_75)]" }),
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex-1", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-xs font-bold text-[oklch(0.55_0.18_75)]", children: "Menunggu Verifikasi" }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-[oklch(0.55_0.18_75)]/80", children: "Transfer sesuai nominal lalu unggah bukti." })
    ] })
  ] });
}
function CopyButton({
  value,
  label
}) {
  const [copied, setCopied] = reactExports.useState(false);
  return /* @__PURE__ */ jsxRuntimeExports.jsxs("button", { onClick: async () => {
    try {
      await navigator.clipboard.writeText(value);
    } catch {
    }
    setCopied(true);
    setTimeout(() => setCopied(false), 1400);
  }, className: "shrink-0 inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-primary/10 text-primary text-xs font-bold active:scale-95 transition", children: [
    copied ? /* @__PURE__ */ jsxRuntimeExports.jsx(Check, { size: 14 }) : /* @__PURE__ */ jsxRuntimeExports.jsx(Copy, { size: 14 }),
    copied ? "Tersalin" : label
  ] });
}
function ProofUploader({
  tx,
  onUpload,
  isUploading
}) {
  const inputRef = reactExports.useRef(null);
  const hasProof = !!tx.proofUrl;
  const locked = tx.status === "approved";
  return /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "rounded-2xl border border-border bg-card shadow-[var(--shadow-soft)] p-4", children: [
    /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "flex items-center justify-between gap-3", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { children: [
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px] text-muted-foreground font-semibold uppercase tracking-wider", children: "Bukti Bayar" }),
        /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-sm font-bold text-foreground", children: hasProof ? "Bukti terunggah" : "Unggah foto bukti transfer" })
      ] }),
      hasProof && !locked && /* @__PURE__ */ jsxRuntimeExports.jsx("button", { onClick: () => inputRef.current?.click(), className: "text-[11px] font-bold text-primary", children: "Ganti" })
    ] }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("input", { ref: inputRef, type: "file", accept: "image/*", className: "hidden", onChange: (e) => {
      const f = e.target.files?.[0];
      if (f) onUpload(f);
      e.target.value = "";
    } }),
    hasProof ? /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "mt-3 rounded-xl overflow-hidden border border-border bg-secondary", children: /* @__PURE__ */ jsxRuntimeExports.jsx("img", { src: `/${tx.proofUrl}`, alt: "Bukti transfer", className: "w-full max-h-72 object-contain bg-black/5" }) }) : /* @__PURE__ */ jsxRuntimeExports.jsxs("button", { disabled: isUploading || locked, onClick: () => inputRef.current?.click(), className: "mt-3 w-full rounded-xl border-2 border-dashed border-border bg-secondary/50 px-4 py-6 flex flex-col items-center justify-center gap-2 text-muted-foreground active:scale-[0.99] transition disabled:opacity-50", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: "w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center", children: isUploading ? /* @__PURE__ */ jsxRuntimeExports.jsx(LoaderCircle, { className: "animate-spin", size: 18 }) : /* @__PURE__ */ jsxRuntimeExports.jsx(Upload, { size: 18 }) }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-sm font-bold text-foreground", children: isUploading ? "Memproses…" : "Pilih Foto Bukti" }),
      /* @__PURE__ */ jsxRuntimeExports.jsx("p", { className: "text-[11px]", children: "JPG / PNG, maks. 5 MB" })
    ] }),
    !hasProof && /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: "mt-3 flex items-start gap-2 text-[11px] text-muted-foreground", children: [
      /* @__PURE__ */ jsxRuntimeExports.jsx(Image, { size: 12, className: "mt-0.5 shrink-0" }),
      /* @__PURE__ */ jsxRuntimeExports.jsxs("span", { children: [
        "Setelah unggah, status menjadi",
        " ",
        /* @__PURE__ */ jsxRuntimeExports.jsx("span", { className: "font-bold text-foreground", children: "Pending" }),
        " dan menunggu verifikasi petugas."
      ] })
    ] })
  ] });
}
export {
  PembayaranPage as component
};
