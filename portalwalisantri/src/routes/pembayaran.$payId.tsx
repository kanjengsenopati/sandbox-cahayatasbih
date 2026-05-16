import { createFileRoute, useNavigate, useParams, Link } from "@tanstack/react-router";
import { useMemo, useRef, useState } from "react";
import {
  ArrowLeft,
  Copy,
  Check,
  Upload,
  Building2,
  Clock,
  CheckCircle2,
  XCircle,
  Image as ImageIcon,
  Loader2,
} from "lucide-react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { fetchPaymentDetail, uploadPaymentProof } from "@/lib/api";
import { resolveImageUrl } from "@/lib/utils";

export const Route = createFileRoute("/pembayaran/$payId")({
  component: PembayaranPage,
  head: () => ({ meta: [{ title: "Pembayaran — SantriPay" }] }),
});

const fmtIDR = (n: number) =>
  new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(n);

function PembayaranPage() {
  const { payId } = useParams({ from: "/pembayaran/$payId" });
  const navigate = useNavigate();
  const queryClient = useQueryClient();

  const { data: paymentRes, isLoading } = useQuery({
    queryKey: ["payment", payId],
    queryFn: async () => {
      const res = await fetchPaymentDetail(payId);
      return res.data;
    },
  });

  const tx = useMemo(() => {
    if (!paymentRes) return null;
    const p = paymentRes.transaction;
    const proof = paymentRes.proof;
    const bank = paymentRes.banks?.[0] || {};
    
    return {
      id: p.id,
      payment_code: p.payment_code,
      billName: p.type === "BILL" ? "Pembayaran Tagihan" : p.type === "SALDO" ? "Topup Saldo" : "Pembayaran Tabungan",
      amount: Number(p.pay_amount),
      baseAmount: Number(p.pay_amount) - Number(p.unique_payment || 0),
      uniqueCode: p.unique_payment,
      status: p.status === "PAID" ? "approved" : p.status === "REJECTED" ? "rejected" : "pending",
      bankName: bank.name || "BCA", 
      bankAccount: bank.account_number || "1840558992", 
      bankHolder: bank.account_name || "Yayasan PPTQ Cahaya Tasbih",
      proofUrl: proof?.proof_image_url || proof?.proof_image,
      note: proof?.note,
      items: p.transaction_details?.map((d: any) => ({
        id: d.id,
        label: d.bill?.bill_type?.name || (p.type === "SALDO" ? "Topup Saldo" : "Pembayaran"),
        amount: d.bill?.amount || d.saldo_history?.amount || d.saving_history?.amount || 0,
      })) || [],
    };
  }, [paymentRes]);

  const uploadMutation = useMutation({
    mutationFn: async (file: File) => {
      const fd = new FormData();
      fd.append("proof", file);
      
      const bank = paymentRes?.banks?.[0];
      if (bank?.id) {
        fd.append("bank_id", bank.id);
      }
      
      return uploadPaymentProof(payId, fd);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["payment", payId] });
    },
  });

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-background">
        <Loader2 className="animate-spin text-primary" size={40} />
      </div>
    );
  }

  if (!tx) {
    return (
      <div className="min-h-screen flex flex-col items-center justify-center gap-3 text-sm">
        <p className="text-muted-foreground">Pembayaran tidak ditemukan.</p>
        <Link to="/tagihan" className="text-primary font-bold">
          Kembali
        </Link>
      </div>
    );
  }

  const baseStr = fmtIDR(tx.baseAmount);
  const totalStr = fmtIDR(tx.amount);
  const isPending = tx.status === "pending";
  const isApproved = tx.status === "approved";
  const isRejected = tx.status === "rejected";

  return (
    <div className="min-h-screen w-full flex justify-center bg-secondary">
      <div className="relative w-full max-w-md min-h-screen bg-background pb-32">
        {/* Header — legacy style */}
        <div className="px-6 pt-12 pb-3 flex items-center gap-3">
          <button
            onClick={() => navigate({ to: "/tagihan" })}
            className="w-10 h-10 rounded-xl bg-secondary border border-border flex items-center justify-center text-foreground active:scale-95 transition"
          >
            <ArrowLeft size={18} />
          </button>
          <div className="min-w-0">
            <p className="text-[11px] text-muted-foreground font-semibold uppercase tracking-wider">
              Pembayaran
            </p>
            <p className="text-base font-bold text-foreground truncate">
              {tx.billName}
            </p>
          </div>
        </div>

        {/* Status banner */}
        <div className="px-5 pt-3">
          <StatusBanner status={tx.status} />
        </div>

        {/* Nominal card */}
        <div className="px-5 pt-5">
          <div className="rounded-2xl border border-border bg-card shadow-[var(--shadow-soft)] p-4">
            <p className="text-[11px] text-muted-foreground font-semibold uppercase tracking-wider">
              Nominal Transfer
            </p>
            <div className="mt-1 flex items-end justify-between gap-3">
              <p className="text-2xl font-extrabold text-foreground">
                {totalStr}
              </p>
              <CopyButton value={String(tx.amount)} label="Salin" />
            </div>
            <p className="mt-2 text-[11px] text-muted-foreground">
              Dasar {baseStr} +{" "}
              <span className="font-bold text-primary">
                {tx.uniqueCode} kode unik
              </span>{" "}
              (3 digit terakhir wajib sesuai)
            </p>
          </div>
        </div>

        {/* Bank card — modern purple */}
        <div className="px-5 pt-3">
          <div
            className="relative overflow-hidden rounded-3xl p-4 shadow-[var(--shadow-glow)]"
            style={{ background: "var(--gradient-hero)" }}
          >
            <div className="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-white/10 blur-3xl pointer-events-none" />
            <div className="absolute -bottom-12 -left-8 w-36 h-36 rounded-full bg-white/5 blur-2xl pointer-events-none" />

            <div className="relative flex items-center gap-2.5">
              <div className="w-10 h-10 rounded-xl bg-white/15 border border-white/20 backdrop-blur text-white flex items-center justify-center shadow-[var(--shadow-soft)]">
                <Building2 size={18} />
              </div>
              <div className="min-w-0">
                <p className="text-[10px] uppercase tracking-widest text-white/70 font-semibold">
                  Transfer ke
                </p>
                <p className="text-sm font-extrabold text-white leading-tight">
                  Bank {tx.bankName}
                </p>
              </div>
            </div>

            <div className="relative mt-3 rounded-2xl bg-white/15 border border-white/20 backdrop-blur px-3.5 py-3 flex items-center justify-between gap-2">
              <div className="min-w-0">
                <p className="text-[10px] uppercase tracking-widest text-white/70 font-semibold">
                  Nomor Rekening
                </p>
                <p className="text-lg font-extrabold text-white tracking-wider tabular-nums truncate">
                  {tx.bankAccount}
                </p>
              </div>
              <button
                onClick={async () => {
                  try {
                    await navigator.clipboard.writeText(tx.bankAccount);
                  } catch {}
                }}
                className="shrink-0 inline-flex items-center gap-1.5 px-3 py-2 rounded-full bg-white/15 border border-white/20 text-white text-[11px] font-semibold backdrop-blur active:scale-95 transition"
              >
                <Copy size={12} /> Salin
              </button>
            </div>

            <p className="relative mt-2.5 text-[11px] text-white/70">
              a.n. <span className="font-bold text-white">{tx.bankHolder}</span>
            </p>
          </div>
        </div>

        {/* Items */}
        {tx.items && tx.items.length > 0 && (
          <div className="px-5 pt-3">
            <div className="rounded-2xl border border-border bg-card shadow-[var(--shadow-soft)] p-4">
              <p className="text-[11px] text-muted-foreground font-semibold uppercase tracking-wider mb-2">
                Rincian
              </p>
              <div className="divide-y divide-border">
                {tx.items.map((it) => (
                  <div
                    key={it.id}
                    className="py-2 flex items-center justify-between gap-3 text-sm"
                  >
                    <span className="text-foreground truncate">{it.label}</span>
                    <span className="font-bold text-foreground">
                      {fmtIDR(it.amount)}
                    </span>
                  </div>
                ))}
                <div className="py-2 flex items-center justify-between gap-3 text-sm">
                  <span className="text-muted-foreground">Kode Unik</span>
                  <span className="font-bold text-primary">
                    +{tx.uniqueCode}
                  </span>
                </div>
              </div>
            </div>
          </div>
        )}
        {/* Upload bukti */}
        <div className="px-5 pt-3">
          <ProofUploader tx={tx} onUpload={(f) => uploadMutation.mutate(f)} isUploading={uploadMutation.isPending} />
        </div>
 
        {/* Sticky action */}
        {isApproved && (
          <StickyAction>
            <button
              onClick={() => navigate({ to: "/riwayat" })}
              className="w-full py-3.5 rounded-2xl bg-success text-white font-bold text-sm flex items-center justify-center gap-2"
            >
              <CheckCircle2 size={16} /> Lihat di Riwayat
            </button>
          </StickyAction>
        )}
        {isRejected && (
          <StickyAction>
            <div className="w-full flex flex-col gap-2">
              {tx.note && (
                <div className="rounded-2xl bg-destructive/10 border border-destructive/20 p-3 flex items-start gap-2.5 text-destructive text-sm text-left">
                  <XCircle size={16} className="mt-0.5 shrink-0" />
                  <div>
                    <span className="font-bold block mb-0.5">Catatan Admin:</span>
                    <span>{tx.note}</span>
                  </div>
                </div>
              )}
              <button
                onClick={() => navigate({ to: "/tagihan" })}
                className="w-full py-3.5 rounded-2xl bg-secondary border border-border text-foreground font-bold text-sm"
              >
                Kembali ke Tagihan
              </button>
            </div>
          </StickyAction>
        )}
        {isPending && tx.proofUrl && (
          <StickyAction>
            <button
              onClick={() => navigate({ to: "/riwayat" })}
              className="w-full py-3.5 rounded-2xl text-primary-foreground font-bold text-sm shadow-[var(--shadow-glow)] active:scale-[0.98]"
              style={{ background: "var(--gradient-card)" }}
            >
              Lihat Status di Riwayat
            </button>
          </StickyAction>
        )}
      </div>
    </div>
  );
}

function StickyAction({ children }: { children: React.ReactNode }) {
  return (
    <div className="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md px-3 pb-3 pt-2 bg-gradient-to-t from-background via-background to-background/0 z-40">
      {children}
    </div>
  );
}

function StatusBanner({ status }: { status: PendingTx["status"] }) {
  if (status === "approved") {
    return (
      <div className="rounded-xl border border-success/30 bg-success/10 px-3 py-2.5 flex items-center gap-2">
        <CheckCircle2 size={16} className="text-success" />
        <div className="flex-1">
          <p className="text-xs font-bold text-success">Pembayaran Disetujui</p>
          <p className="text-[11px] text-success/80">
            Transaksi sudah diverifikasi petugas.
          </p>
        </div>
      </div>
    );
  }
  if (status === "rejected") {
    return (
      <div className="rounded-xl border border-destructive/30 bg-destructive/10 px-3 py-2.5 flex items-center gap-2">
        <XCircle size={16} className="text-destructive" />
        <div className="flex-1">
          <p className="text-xs font-bold text-destructive">Pembayaran Ditolak</p>
          <p className="text-[11px] text-destructive/80">
            Bukti tidak valid, silakan ulangi.
          </p>
        </div>
      </div>
    );
  }
  return (
    <div className="rounded-xl border border-[oklch(0.78_0.16_75)]/40 bg-[oklch(0.78_0.16_75)]/10 px-3 py-2.5 flex items-center gap-2">
      <Clock size={16} className="text-[oklch(0.62_0.18_75)]" />
      <div className="flex-1">
        <p className="text-xs font-bold text-[oklch(0.55_0.18_75)]">
          Menunggu Verifikasi
        </p>
        <p className="text-[11px] text-[oklch(0.55_0.18_75)]/80">
          Transfer sesuai nominal lalu unggah bukti.
        </p>
      </div>
    </div>
  );
}

function CopyButton({ value, label }: { value: string; label: string }) {
  const [copied, setCopied] = useState(false);
  return (
    <button
      onClick={async () => {
        try {
          await navigator.clipboard.writeText(value);
        } catch {
          // ignore
        }
        setCopied(true);
        setTimeout(() => setCopied(false), 1400);
      }}
      className="shrink-0 inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-primary/10 text-primary text-xs font-bold active:scale-95 transition"
    >
      {copied ? <Check size={14} /> : <Copy size={14} />}
      {copied ? "Tersalin" : label}
    </button>
  );
}

function ProofUploader({ tx, onUpload, isUploading }: { tx: any; onUpload: (f: File) => void; isUploading: boolean }) {
  const inputRef = useRef<HTMLInputElement>(null);
  const hasProof = !!tx.proofUrl;
  const locked = tx.status === "approved";

  return (
    <div className="rounded-2xl border border-border bg-card shadow-[var(--shadow-soft)] p-4">
      <div className="flex items-center justify-between gap-3">
        <div>
          <p className="text-[11px] text-muted-foreground font-semibold uppercase tracking-wider">
            Bukti Bayar
          </p>
          <p className="text-sm font-bold text-foreground">
            {hasProof ? "Bukti terunggah" : "Unggah foto bukti transfer"}
          </p>
        </div>
        {hasProof && !locked && (
          <button
            onClick={() => inputRef.current?.click()}
            className="text-[11px] font-bold text-primary"
          >
            Ganti
          </button>
        )}
      </div>

      <input
        ref={inputRef}
        type="file"
        accept="image/*"
        className="hidden"
        onChange={(e) => {
          const f = e.target.files?.[0];
          if (f) onUpload(f);
          e.target.value = "";
        }}
      />

      {hasProof ? (
        <div className="mt-3 rounded-xl overflow-hidden border border-border bg-secondary">
          <img
            src={resolveImageUrl(tx.proofUrl) || ''}
            alt="Bukti transfer"
            className="w-full max-h-72 object-contain bg-black/5"
          />
        </div>
      ) : (
        <button
          disabled={isUploading || locked}
          onClick={() => inputRef.current?.click()}
          className="mt-3 w-full rounded-xl border-2 border-dashed border-border bg-secondary/50 px-4 py-6 flex flex-col items-center justify-center gap-2 text-muted-foreground active:scale-[0.99] transition disabled:opacity-50"
        >
          <div className="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center">
            {isUploading ? <Loader2 className="animate-spin" size={18} /> : <Upload size={18} />}
          </div>
          <p className="text-sm font-bold text-foreground">
            {isUploading ? "Memproses…" : "Pilih Foto Bukti"}
          </p>
          <p className="text-[11px]">JPG / PNG, maks. 5 MB</p>
        </button>
      )}

      {!hasProof && (
        <div className="mt-3 flex items-start gap-2 text-[11px] text-muted-foreground">
          <ImageIcon size={12} className="mt-0.5 shrink-0" />
          <span>
            Setelah unggah, status menjadi{" "}
            <span className="font-bold text-foreground">Pending</span> dan
            menunggu verifikasi petugas.
          </span>
        </div>
      )}
    </div>
  );
}
