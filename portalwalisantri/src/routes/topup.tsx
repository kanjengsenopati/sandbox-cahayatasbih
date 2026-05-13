import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useEffect, useMemo, useRef, useState } from "react";
import {
  ArrowLeft,
  Wallet,
  Plus,
  CheckCircle2,
  Building2,
  Smartphone,
  CreditCard,
  ShieldCheck,
  Copy,
  Check,
  Upload,
  Clock,
  ImageIcon,
  X,
  Camera,
  ImagePlus,
  AlertCircle,
  ZoomIn,
  RefreshCw,
  FileImage,
  Loader2,
} from "lucide-react";
import { useSantri } from "@/contexts/SantriContext";
import { postTopup, uploadPaymentProof, fetchPaymentMethods } from "@/lib/api";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";

export const Route = createFileRoute("/topup")({
  component: TopupPage,
  head: () => ({ meta: [{ title: "Topup Saldo — SantriPay" }] }),
});

const NOMINALS = [50_000, 100_000, 200_000, 500_000, 1_000_000, 2_000_000];

const fmt = (n: number) =>
  new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(n);

type Step = "form" | "confirm" | "pending";

function TopupPage() {
  const navigate = useNavigate();
  const { active, isLoading: isLoadingSantri } = useSantri();
  const queryClient = useQueryClient();
  const [step, setStep] = useState<Step>("form");
  const [amount, setAmount] = useState<number>(100_000);
  const [custom, setCustom] = useState<string>("");
  const [proof, setProof] = useState<File | null>(null);
  const [proofUrl, setProofUrl] = useState<string>("");
  const [refId, setRefId] = useState<string>("");
  const [paymentId, setPaymentId] = useState<number | null>(null);

  const { data: methodsRes, isLoading: isLoadingMethods } = useQuery({
    queryKey: ["payment-methods"],
    queryFn: async () => {
      const res = await fetchPaymentMethods();
      return res.data;
    },
  });

  const methods = useMemo(() => {
    if (!methodsRes) return [];
    return methodsRes.flatMap((m: any) => {
      if (m.type === "TRANSFER") {
        return (m.banks || []).map((b: any) => ({
          id: b.id,
          payment_method_id: m.id,
          label: b.name,
          desc: "Transfer manual antar bank",
          icon: Building2,
          fee: 0,
          account: b.account_number,
          holder: b.account_name,
        }));
      }
      if (m.type === "XENDIT") {
        return [{
          id: m.id,
          payment_method_id: m.id,
          label: m.name,
          desc: "Pembayaran otomatis via Xendit",
          icon: CreditCard,
          fee: 0,
          account: "-",
          holder: "-",
        }];
      }
      return [];
    });
  }, [methodsRes]);

  const [method, setMethod] = useState<string>("");

  useEffect(() => {
    if (methods.length > 0 && !method) {
      setMethod(methods[0].id);
    }
  }, [methods, method]);

  const selected = useMemo(() => methods.find((m: any) => m.id === method), [method, methods]);

  const uniqueCode = useMemo(() => Math.floor(100 + Math.random() * 900), [amount, method]);
  const uniqueAmount = amount + uniqueCode;
  const total = uniqueAmount + (selected?.fee || 0);

  const topupMutation = useMutation({
    mutationFn: async () => {
      if (!selected) return;
      const res = await postTopup({
        amount: amount,
        payment_method_id: selected.payment_method_id,
        student_id: active?.id,
        // Optional fields for backward compatibility if needed
        method: selected.label.toUpperCase(),
        note: `Topup via ${selected.label}`,
      });
      return res.data;
    },
    onSuccess: (data) => {
      setPaymentId(data.transaction.id);
      setRefId(data.transaction.payment_code);
      setStep("confirm");
    },
  });

  const uploadMutation = useMutation({
    mutationFn: async () => {
      if (!paymentId || !proof) return;
      const fd = new FormData();
      fd.append("proof", proof);
      return uploadPaymentProof(paymentId, fd);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["saldo-histories"] });
      setStep("pending");
    },
  });

  if (isLoadingSantri || isLoadingMethods) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-background">
        <Loader2 className="animate-spin text-primary" size={40} />
      </div>
    );
  }

  if (step === "confirm") {
    return (
      <ConfirmScreen
        amount={amount}
        uniqueCode={uniqueCode}
        uniqueAmount={uniqueAmount}
        method={selected}
        total={total}
        refId={refId}
        proof={proof}
        proofUrl={proofUrl}
        isUploading={uploadMutation.isPending}
        onBack={() => setStep("form")}
        onSelectFile={(f) => {
          setProof(f);
          setProofUrl(f ? URL.createObjectURL(f) : "");
        }}
        onSubmit={() => uploadMutation.mutate()}
      />
    );
  }

  if (step === "pending") {
    return (
      <PendingScreen
        refId={refId}
        total={total}
        method={selected}
        onHome={() => navigate({ to: "/dashboard" })}
      />
    );
  }

  // STEP: form
  return (
    <div className="min-h-screen w-full flex justify-center bg-secondary">
      <div className="relative w-full max-w-md min-h-screen bg-background pb-32">

        {/* Hero */}
        <div
          className="relative px-6 pt-12 pb-20 rounded-b-[2rem] overflow-hidden"
          style={{ background: "var(--gradient-hero)" }}
        >
          <div className="absolute -top-16 -right-12 w-56 h-56 rounded-full bg-primary-glow/30 blur-3xl" />
          <div
            className="absolute inset-0 opacity-[0.07]"
            style={{
              backgroundImage:
                "linear-gradient(rgba(255,255,255,0.6) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.6) 1px, transparent 1px)",
              backgroundSize: "26px 26px",
              maskImage: "radial-gradient(ellipse at top right, black 30%, transparent 70%)",
            }}
          />
          <div className="relative flex items-center gap-3">
            <button
              onClick={() => navigate({ to: "/dashboard" })}
              className="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center text-white"
            >
              <ArrowLeft size={18} />
            </button>
            <div>
              <p className="text-[11px] text-white/70 font-semibold uppercase tracking-wider">Topup</p>
              <p className="text-base font-bold text-white">Tambah Saldo Santri</p>
            </div>
          </div>

          <div className="relative mt-5 flex items-center gap-3 text-white/90">
            <div className="w-11 h-11 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center">
              <Wallet size={20} />
            </div>
            <div>
              <p className="text-[11px] text-white/70">Saldo Saat Ini</p>
              <p className="text-xl font-bold tracking-tight">{fmt(active?.saldo || 0)}</p>
            </div>
          </div>
        </div>

        {/* Amount card */}
        <div className="px-6 -mt-12 relative z-10">
          <div className="bg-card rounded-3xl border border-border shadow-[var(--shadow-card)] p-5">
            <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
              Pilih Nominal
            </p>
            <div className="grid grid-cols-3 gap-2 mt-3">
              {NOMINALS.map((n) => {
                const active = amount === n && !custom;
                return (
                  <button
                    key={n}
                    onClick={() => {
                      setAmount(n);
                      setCustom("");
                    }}
                    className={`py-3 rounded-2xl text-xs font-bold border transition active:scale-95 ${
                      active
                        ? "text-white border-transparent shadow-[var(--shadow-glow)] ring-2 ring-primary/40 scale-[1.03]"
                        : "bg-secondary text-foreground border-transparent hover:border-primary/30"
                    }`}
                    style={
                      active ? { background: "var(--gradient-card)" } : undefined
                    }
                  >
                    {n >= 1_000_000 ? `${n / 1_000_000}jt` : `${n / 1000}rb`}
                  </button>
                );
              })}
            </div>

            <div className="mt-4">
              <label className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                Atau nominal lain
              </label>
              <div className="mt-2 flex items-center gap-2 bg-secondary rounded-2xl px-4 py-3.5 border border-transparent focus-within:border-primary transition">
                <span className="text-sm font-bold text-primary">Rp</span>
                <input
                  type="number"
                  inputMode="numeric"
                  value={custom}
                  onChange={(e) => {
                    setCustom(e.target.value);
                    const v = parseInt(e.target.value, 10);
                    if (!isNaN(v)) setAmount(v);
                  }}
                  placeholder="0"
                  className="bg-transparent flex-1 outline-none text-foreground text-sm font-semibold placeholder:text-muted-foreground"
                />
              </div>
              <p className="text-[10px] text-muted-foreground mt-1.5">Minimal Rp 10.000</p>
            </div>
          </div>
        </div>

        {/* Methods */}
        <section className="px-6 mt-6">
          <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2 px-1">
            Metode Pembayaran
          </p>
          <div className="bg-card rounded-2xl border border-border divide-y divide-border overflow-hidden shadow-[var(--shadow-soft)]">
            {methods.map((m: any) => {
              const Icon = m.icon;
              const active = method === m.id;
              return (
                <button
                  key={m.id}
                  onClick={() => setMethod(m.id)}
                  className={`w-full flex items-center gap-3 p-4 transition text-left ${
                    active ? "bg-primary/5" : "active:bg-secondary"
                  }`}
                >
                  <div
                    className={`w-11 h-11 rounded-xl flex items-center justify-center transition ${
                      active
                        ? "text-white shadow-[var(--shadow-glow)] ring-2 ring-primary/30"
                        : "bg-secondary text-primary"
                    }`}
                    style={active ? { background: "var(--gradient-card)" } : undefined}
                  >
                    <Icon size={18} />
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-bold text-foreground">{m.label}</p>
                    <p className="text-[11px] text-muted-foreground">
                      {m.desc} · {m.fee === 0 ? "Gratis" : `Biaya ${fmt(m.fee)}`}
                    </p>
                  </div>
                  <div
                    className={`w-5 h-5 rounded-full border-2 flex items-center justify-center ${
                      active ? "border-primary bg-primary" : "border-border"
                    }`}
                  >
                    {active && <div className="w-2 h-2 rounded-full bg-primary-foreground" />}
                  </div>
                </button>
              );
            })}
          </div>

          <div className="mt-3 flex items-center gap-2 text-[11px] text-muted-foreground px-1">
            <ShieldCheck size={14} className="text-success" />
            Transaksi dijamin aman & terenkripsi.
          </div>
        </section>

        {/* Footer summary */}
        <div className="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md px-4 pb-4 pt-3 bg-gradient-to-t from-background via-background to-background/0 z-40">
          <div className="rounded-2xl border border-border bg-card shadow-[var(--shadow-card)] p-4">
            <div className="flex items-center justify-between mb-3">
              <div>
                <p className="text-[10px] text-muted-foreground font-semibold uppercase tracking-wider">
                  Estimasi Bayar
                </p>
                <p className="text-lg font-bold text-foreground">{fmt(amount + (selected?.fee || 0))}</p>
              </div>
              <p className="text-[11px] text-muted-foreground">
                via <span className="font-semibold text-foreground">{selected?.label || "-"}</span>
              </p>
            </div>
            <button
              onClick={() => amount >= 10_000 && topupMutation.mutate()}
              disabled={amount < 10_000 || topupMutation.isPending}
              className="w-full py-3.5 rounded-2xl text-primary-foreground font-semibold text-sm shadow-[var(--shadow-glow)] flex items-center justify-center gap-2 disabled:opacity-50 transition active:scale-[0.98]"
              style={{ background: "var(--gradient-card)" }}
            >
              {topupMutation.isPending ? (
                <Loader2 className="animate-spin" size={18} />
              ) : (
                <>
                  <Plus size={18} /> Lanjutkan Pembayaran
                </>
              )}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

/* ───────────── Confirmation Screen ───────────── */

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
  onSubmit,
}: {
  amount: number;
  uniqueCode: number;
  uniqueAmount: number;
  method: Method;
  total: number;
  refId: string;
  proof: File | null;
  proofUrl: string;
  onBack: () => void;
  onSelectFile: (f: File | null) => void;
  onSubmit: () => void;
}) {
  return (
    <div className="min-h-screen w-full flex justify-center bg-secondary">
      <div className="relative w-full max-w-md min-h-screen bg-background pb-36">
        {/* Hero */}
        <div
          className="relative px-6 pt-12 pb-20 rounded-b-[2rem] overflow-hidden"
          style={{ background: "var(--gradient-hero)" }}
        >
          <div className="absolute -top-16 -right-12 w-56 h-56 rounded-full bg-primary-glow/30 blur-3xl" />
          <div className="relative flex items-center gap-3">
            <button
              onClick={onBack}
              className="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center text-white"
            >
              <ArrowLeft size={18} />
            </button>
            <div>
              <p className="text-[11px] text-white/70 font-semibold uppercase tracking-wider">
                Konfirmasi
              </p>
              <p className="text-base font-bold text-white">Detail Pembayaran</p>
            </div>
          </div>

          <p className="relative mt-6 text-[11px] text-white/70 uppercase tracking-widest font-semibold">
            Total Transfer
          </p>
          <CopyAmount value={uniqueAmount} />
          <p className="relative mt-2 text-[11px] text-white/70">
            Termasuk kode unik{" "}
            <span className="font-bold text-white">{uniqueCode}</span> agar
            transaksi mudah diverifikasi.
          </p>
        </div>

        {/* Account info */}
        <div className="px-6 -mt-12 relative z-10">
          <div className="bg-card rounded-3xl border border-border shadow-[var(--shadow-card)] p-5 space-y-4">
            <div className="flex items-center gap-3">
              <div className="w-11 h-11 rounded-xl bg-[var(--gradient-card)] flex items-center justify-center text-primary-foreground">
                <method.icon size={20} />
              </div>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-bold text-foreground">{method.label}</p>
                <p className="text-[11px] text-muted-foreground">a.n. {method.holder}</p>
              </div>
            </div>

            <CopyRow label="No. Rekening / VA" value={method.account.replace(/\s/g, "")} display={method.account} />

            <div className="grid grid-cols-2 gap-3 pt-2 border-t border-border">
              <Mini k="Nominal" v={fmt(amount)} />
              <Mini k="Kode Unik" v={`+${uniqueCode}`} />
              <Mini k="Biaya Admin" v={fmt(method.fee)} />
              <Mini k="ID Transaksi" v={refId} />
            </div>
          </div>
        </div>

        {/* Upload proof */}
        <section className="px-6 mt-6">
          <div className="flex items-end justify-between mb-2 px-1">
            <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
              Upload Bukti Bayar
            </p>
            <span className="text-[10px] font-bold text-destructive">*Wajib</span>
          </div>

          <ProofUploader
            proof={proof}
            proofUrl={proofUrl}
            onSelectFile={onSelectFile}
          />

          {/* Tips checklist */}
          <div className="mt-3 rounded-2xl bg-card border border-border p-3.5">
            <p className="text-[11px] font-bold text-foreground mb-2 flex items-center gap-1.5">
              <ShieldCheck size={13} className="text-primary" />
              Pastikan bukti memenuhi:
            </p>
            <ul className="space-y-1.5">
              {[
                <>Nominal transfer <b>persis {fmt(uniqueAmount)}</b></>,
                <>Tujuan: <b>{method.account}</b> ({method.label})</>,
                <>Tanggal & jam transfer terlihat jelas</>,
                <>Foto tidak buram dan tidak terpotong</>,
              ].map((t, i) => (
                <li key={i} className="flex items-start gap-2 text-[11px] text-foreground leading-relaxed">
                  <CheckCircle2 size={12} className="text-success mt-0.5 shrink-0" />
                  <span>{t}</span>
                </li>
              ))}
            </ul>
          </div>
        </section>

        {/* Footer */}
        <div className="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md px-4 pb-4 pt-3 bg-gradient-to-t from-background via-background to-background/0 z-40">
          <button
            onClick={onSubmit}
            disabled={!proof || isUploading}
            className="w-full py-4 rounded-2xl text-primary-foreground font-semibold text-sm shadow-[var(--shadow-glow)] flex items-center justify-center gap-2 disabled:opacity-50 transition active:scale-[0.98]"
            style={{ background: "var(--gradient-card)" }}
          >
            {isUploading ? (
              <Loader2 className="animate-spin" size={18} />
            ) : (
              <>
                <Upload size={18} /> Kirim Bukti & Konfirmasi
              </>
            )}
          </button>
          {!proof && (
            <p className="text-[11px] text-muted-foreground text-center mt-2">
              Upload bukti bayar terlebih dahulu untuk melanjutkan.
            </p>
          )}
        </div>
      </div>
    </div>
  );
}

/* ───────────── Pending Screen ───────────── */

function PendingScreen({
  refId,
  total,
  method,
  onHome,
}: {
  refId: string;
  total: number;
  method: Method;
  onHome: () => void;
}) {
  return (
    <div className="min-h-screen w-full flex justify-center bg-secondary">
      <div className="w-full max-w-md min-h-screen bg-background flex flex-col px-6 pt-16 pb-10">
        <div className="flex flex-col items-center text-center">
          <div className="relative">
            <span className="absolute inset-0 rounded-full bg-warning/20 animate-ping" />
            <div className="relative w-20 h-20 rounded-full bg-warning/15 flex items-center justify-center">
              <Clock className="text-warning" size={40} />
            </div>
          </div>
          <span className="mt-5 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-warning/15 text-warning text-[11px] font-bold uppercase tracking-wider">
            <span className="w-1.5 h-1.5 rounded-full bg-warning animate-pulse" />
            Pending
          </span>
          <h2 className="text-2xl font-bold text-foreground mt-4">
            Menunggu Konfirmasi
          </h2>
          <p className="text-sm text-muted-foreground mt-2 max-w-xs">
            Bukti bayar Anda sudah terkirim. Petugas akan memverifikasi
            pembayaran dalam <b>1×24 jam</b>.
          </p>
        </div>

        <div className="mt-8 rounded-3xl bg-card border border-border shadow-[var(--shadow-soft)] p-5 space-y-3">
          <Row k="ID Transaksi" v={refId} />
          <Row k="Total Bayar" v={fmt(total)} bold />
          <Row k="Metode" v={method.label} />
          <Row k="Status" v="Menunggu approval petugas" />
        </div>

        {/* Timeline */}
        <div className="mt-6 rounded-3xl bg-card border border-border p-5">
          <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-3">
            Status Transaksi
          </p>
          <ol className="relative border-l-2 border-border ml-2 space-y-5">
            <Step done label="Bukti bayar dikirim" sub="Baru saja" />
            <Step active label="Menunggu approval petugas" sub="Sedang diverifikasi" />
            <Step label="Saldo masuk ke akun santri" sub="Setelah disetujui" />
          </ol>
        </div>

        <div className="mt-auto pt-6 space-y-2">
          <button
            onClick={onHome}
            className="w-full py-4 rounded-2xl text-primary-foreground font-semibold text-sm shadow-[var(--shadow-glow)]"
            style={{ background: "var(--gradient-card)" }}
          >
            Kembali ke Beranda
          </button>
          <button className="w-full py-3 rounded-2xl text-primary font-semibold text-sm bg-secondary">
            Hubungi Petugas
          </button>
        </div>
      </div>
    </div>
  );
}

/* ───────────── Helpers ───────────── */

function CopyAmount({ value }: { value: number }) {
  const [copied, setCopied] = useState(false);
  const copy = async () => {
    try {
      await navigator.clipboard.writeText(String(value));
      setCopied(true);
      setTimeout(() => setCopied(false), 1500);
    } catch {}
  };
  return (
    <div className="relative mt-2 flex items-end gap-3 text-white">
      <h2 className="text-3xl font-bold tracking-tight">{fmt(value)}</h2>
      <button
        onClick={copy}
        className="mb-1.5 flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-white/15 backdrop-blur border border-white/20 text-[11px] font-semibold"
      >
        {copied ? <Check size={13} /> : <Copy size={13} />}
        {copied ? "Tersalin" : "Salin"}
      </button>
    </div>
  );
}

function CopyRow({ label, value, display }: { label: string; value: string; display: string }) {
  const [copied, setCopied] = useState(false);
  const copy = async () => {
    try {
      await navigator.clipboard.writeText(value);
      setCopied(true);
      setTimeout(() => setCopied(false), 1500);
    } catch {}
  };
  return (
    <div className="rounded-2xl bg-secondary p-3 flex items-center gap-3">
      <div className="flex-1 min-w-0">
        <p className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wider">
          {label}
        </p>
        <p className="text-base font-bold text-foreground tracking-wider truncate">
          {display}
        </p>
      </div>
      <button
        onClick={copy}
        className="flex items-center gap-1 px-3 py-2 rounded-xl bg-card border border-border text-[11px] font-semibold text-primary active:scale-95 transition"
      >
        {copied ? <Check size={14} /> : <Copy size={14} />}
        {copied ? "OK" : "Salin"}
      </button>
    </div>
  );
}

function Mini({ k, v }: { k: string; v: string }) {
  return (
    <div>
      <p className="text-[10px] text-muted-foreground font-semibold uppercase tracking-wider">{k}</p>
      <p className="text-sm font-bold text-foreground truncate">{v}</p>
    </div>
  );
}

function Row({ k, v, bold }: { k: string; v: string; bold?: boolean }) {
  return (
    <div className="flex justify-between items-center gap-3">
      <span className="text-sm text-muted-foreground">{k}</span>
      <span className={`text-sm text-right ${bold ? "font-bold text-foreground" : "text-foreground font-semibold"}`}>
        {v}
      </span>
    </div>
  );
}

function Step({ label, sub, done, active }: { label: string; sub: string; done?: boolean; active?: boolean }) {
  return (
    <li className="ml-4">
      <span
        className={`absolute -left-[9px] w-4 h-4 rounded-full border-2 ${
          done
            ? "bg-success border-success"
            : active
            ? "bg-warning border-warning animate-pulse"
            : "bg-background border-border"
        }`}
      />
      <p className={`text-sm font-bold ${active ? "text-warning" : done ? "text-foreground" : "text-muted-foreground"}`}>
        {label}
      </p>
      <p className="text-[11px] text-muted-foreground">{sub}</p>
    </li>
  );
}

/* ───────────── Proof Uploader ───────────── */

const MAX_PROOF_BYTES = 5 * 1024 * 1024; // 5 MB
const ACCEPTED_TYPES = ["image/jpeg", "image/jpg", "image/png", "image/webp"];

function ProofUploader({
  proof,
  proofUrl,
  onSelectFile,
}: {
  proof: File | null;
  proofUrl: string;
  onSelectFile: (f: File | null) => void;
}) {
  const galleryRef = useRef<HTMLInputElement>(null);
  const cameraRef = useRef<HTMLInputElement>(null);
  const [error, setError] = useState<string>("");
  const [progress, setProgress] = useState<number>(0);
  const [uploading, setUploading] = useState(false);
  const [drag, setDrag] = useState(false);
  const [zoom, setZoom] = useState(false);

  const validate = (file: File): string => {
    if (!ACCEPTED_TYPES.includes(file.type)) return "Format harus JPG, PNG, atau WEBP.";
    if (file.size > MAX_PROOF_BYTES)
      return `Ukuran maksimal 5 MB. File Anda ${(file.size / 1024 / 1024).toFixed(1)} MB.`;
    return "";
  };

  const handleFile = (file: File | undefined | null) => {
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

  return (
    <>
      <input
        ref={galleryRef}
        type="file"
        accept="image/jpeg,image/png,image/webp"
        className="hidden"
        onChange={(e) => {
          handleFile(e.target.files?.[0]);
          e.target.value = "";
        }}
      />
      <input
        ref={cameraRef}
        type="file"
        accept="image/*"
        capture="environment"
        className="hidden"
        onChange={(e) => {
          handleFile(e.target.files?.[0]);
          e.target.value = "";
        }}
      />

      {!proof && !uploading && (
        <div
          onDragOver={(e) => {
            e.preventDefault();
            setDrag(true);
          }}
          onDragLeave={() => setDrag(false)}
          onDrop={(e) => {
            e.preventDefault();
            setDrag(false);
            handleFile(e.dataTransfer.files?.[0]);
          }}
          className={`rounded-3xl border-2 border-dashed p-5 transition ${
            drag
              ? "border-primary bg-primary/5 scale-[1.01]"
              : error
              ? "border-destructive/50 bg-destructive/5"
              : "border-border bg-card"
          }`}
        >
          <div className="flex flex-col items-center text-center">
            <div
              className="w-14 h-14 rounded-2xl flex items-center justify-center text-primary-foreground shadow-[var(--shadow-glow)]"
              style={{ background: "var(--gradient-card)" }}
            >
              <FileImage size={24} />
            </div>
            <p className="mt-3 text-sm font-bold text-foreground">
              Tarik & lepas bukti di sini
            </p>
            <p className="text-[11px] text-muted-foreground mt-0.5">
              atau pilih sumber di bawah · JPG / PNG / WEBP · maks 5 MB
            </p>
          </div>

          <div className="mt-4 grid grid-cols-2 gap-2.5">
            <button
              onClick={() => cameraRef.current?.click()}
              className="flex flex-col items-center gap-1 py-3 rounded-2xl bg-secondary border border-border active:scale-95 transition"
            >
              <div className="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                <Camera size={16} />
              </div>
              <span className="text-[12px] font-bold text-foreground">Kamera</span>
              <span className="text-[10px] text-muted-foreground">Foto langsung</span>
            </button>
            <button
              onClick={() => galleryRef.current?.click()}
              className="flex flex-col items-center gap-1 py-3 rounded-2xl bg-secondary border border-border active:scale-95 transition"
            >
              <div className="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                <ImagePlus size={16} />
              </div>
              <span className="text-[12px] font-bold text-foreground">Galeri</span>
              <span className="text-[10px] text-muted-foreground">Dari perangkat</span>
            </button>
          </div>

          {error && (
            <div className="mt-3 rounded-xl bg-destructive/10 border border-destructive/30 px-3 py-2 flex items-start gap-2">
              <AlertCircle size={13} className="text-destructive mt-0.5 shrink-0" />
              <p className="text-[11px] font-semibold text-destructive leading-relaxed">
                {error}
              </p>
            </div>
          )}
        </div>
      )}

      {uploading && (
        <div className="rounded-3xl border border-border bg-card p-5">
          <div className="flex items-center gap-3">
            <div className="w-11 h-11 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
              <Upload size={18} className="animate-pulse" />
            </div>
            <div className="flex-1 min-w-0">
              <p className="text-sm font-bold text-foreground">Mengunggah bukti…</p>
              <p className="text-[11px] text-muted-foreground">Mohon tunggu sebentar</p>
            </div>
            <span className="text-sm font-bold text-primary tabular-nums">
              {Math.round(progress)}%
            </span>
          </div>
          <div className="mt-3 h-2 rounded-full bg-secondary overflow-hidden">
            <div
              className="h-full rounded-full transition-all"
              style={{ width: `${progress}%`, background: "var(--gradient-card)" }}
            />
          </div>
        </div>
      )}

      {proof && !uploading && (
        <div className="rounded-3xl border border-border bg-card overflow-hidden shadow-[var(--shadow-soft)]">
          <div className="relative bg-secondary">
            {proofUrl ? (
              <img src={proofUrl} alt="Bukti transfer" className="w-full h-56 object-cover" />
            ) : (
              <div className="h-56 flex items-center justify-center text-muted-foreground">
                <ImageIcon size={32} />
              </div>
            )}

            <span className="absolute top-2.5 left-2.5 inline-flex items-center gap-1 px-2 py-1 rounded-full bg-success text-white text-[10px] font-bold shadow">
              <CheckCircle2 size={11} /> Siap dikirim
            </span>

            <div className="absolute top-2.5 right-2.5 flex items-center gap-1.5">
              <button
                onClick={() => setZoom(true)}
                className="w-8 h-8 rounded-full bg-black/60 text-white flex items-center justify-center backdrop-blur active:scale-95"
                aria-label="Perbesar"
              >
                <ZoomIn size={14} />
              </button>
              <button
                onClick={reset}
                className="w-8 h-8 rounded-full bg-black/60 text-white flex items-center justify-center backdrop-blur active:scale-95"
                aria-label="Hapus"
              >
                <X size={14} />
              </button>
            </div>
          </div>

          <div className="p-3 flex items-center gap-3">
            <div className="w-9 h-9 rounded-xl bg-success/15 text-success flex items-center justify-center shrink-0">
              <FileImage size={16} />
            </div>
            <div className="flex-1 min-w-0">
              <p className="text-[12px] font-bold text-foreground truncate">{proof.name}</p>
              <p className="text-[10px] text-muted-foreground">
                {(proof.size / 1024).toFixed(0)} KB ·{" "}
                {proof.type.replace("image/", "").toUpperCase()}
              </p>
            </div>
            <button
              onClick={() => galleryRef.current?.click()}
              className="inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-secondary border border-border text-[11px] font-bold text-primary active:scale-95"
            >
              <RefreshCw size={12} /> Ganti
            </button>
          </div>
        </div>
      )}

      {zoom && proofUrl && (
        <div
          onClick={() => setZoom(false)}
          className="fixed inset-0 z-[60] bg-black/90 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in"
        >
          <button
            onClick={() => setZoom(false)}
            className="absolute top-5 right-5 w-10 h-10 rounded-full bg-white/15 text-white flex items-center justify-center backdrop-blur"
          >
            <X size={18} />
          </button>
          <img
            src={proofUrl}
            alt="Preview bukti"
            className="max-h-full max-w-full rounded-2xl shadow-2xl"
            onClick={(e) => e.stopPropagation()}
          />
        </div>
      )}
    </>
  );
}
