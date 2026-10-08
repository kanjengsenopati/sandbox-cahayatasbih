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
  UploadCloud,
} from "lucide-react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { fetchPaymentDetail, uploadPaymentProof, cancelPaymentProof, cancelPaymentTransaction } from "@/lib/api";
import { resolveImageUrl } from "@/lib/utils";
import { compressImage } from "@/lib/image-compress";
import { toast } from "sonner";
import { Text } from "@/components/Text";

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

  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [selectedFileUrl, setSelectedFileUrl] = useState<string>("");
  const [showConfirmUpload, setShowConfirmUpload] = useState(false);
  const [showConfirmCancel, setShowConfirmCancel] = useState(false);
  const [showConfirmCancelTransaction, setShowConfirmCancelTransaction] = useState(false);

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
      status: p.status === "PAID" 
        ? "approved" 
        : (p.status === "REJECTED" || proof?.status === "REJECTED") 
          ? "rejected" 
          : (p.status === "CANCELLED" || p.status === "cancelled")
            ? "cancelled"
            : "pending",
      bankName: bank.name || "BCA", 
      bankAccount: bank.account_number || "1840558992", 
      bankHolder: bank.account_name || "Yayasan PPTQ Cahaya Tasbih",
      proofUrl: proof?.proof_image_url || proof?.proof_image,
      note: proof?.note,
      items: (p.type === "SALDO" || p.type === "SAVING")
        ? [
            {
              id: p.id,
              label: p.type === "SALDO" ? "Topup Saldo" : "Pembayaran Tabungan",
              amount: Number(p.pay_amount) - Number(p.unique_payment || 0),
              year: 0,
              month: 0,
            }
          ]
        : (p.transaction_details?.map((d: any) => ({
            id: d.id,
            label: d.bill?.bill_type?.name 
              ? (d.bill.translated_month 
                  ? `${d.bill.bill_type.name} - ${d.bill.translated_month} ${d.bill.year}` 
                  : `${d.bill.bill_type.name} - ${d.bill.year}`)
              : "Pembayaran",
            amount: d.amount || d.bill?.amount || d.saldo_history?.amount || d.saving_history?.amount || 0,
            year: d.bill ? Number(d.bill.year) : 0,
            month: d.bill ? Number(d.bill.month) : 0,
          })) || []).sort((a: any, b: any) => {
            if (a.year !== b.year) return a.year - b.year;
            return a.month - b.month;
          }),
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
      setSelectedFile(null);
      setSelectedFileUrl("");
      toast.success("Bukti transfer berhasil diunggah.");
    },
    onError: (err: any) => {
      toast.error(err.response?.data?.message || "Gagal mengunggah bukti transfer.");
    }
  });

  const cancelMutation = useMutation({
    mutationFn: async () => {
      return cancelPaymentProof(payId);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["payment", payId] });
      setSelectedFile(null);
      setSelectedFileUrl("");
      toast.success("Bukti transfer berhasil ditarik.");
    },
    onError: (err: any) => {
      toast.error(err.response?.data?.message || "Gagal menarik bukti transfer.");
    }
  });

  const cancelTransactionMutation = useMutation({
    mutationFn: async () => {
      return cancelPaymentTransaction(payId);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["payment", payId] });
      queryClient.invalidateQueries({ queryKey: ["bills"] });
      queryClient.invalidateQueries({ queryKey: ["dashboard"] });
      toast.success("Transaksi pembayaran berhasil dibatalkan. Tagihan dapat dipilih kembali.");
      navigate({ to: "/tagihan" });
    },
    onError: (err: any) => {
      toast.error(err.response?.data?.message || "Gagal membatalkan transaksi.");
    }
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
  const isCancelled = tx.status === "cancelled";

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
          <StatusBanner status={tx.status} hasProof={!!tx.proofUrl} note={tx.note} />
        </div>

        {/* Info batas waktu 2 jam saat menunggu unggah bukti */}
        {isPending && !tx.proofUrl && (
          <div className="px-5 pt-3">
            <div className="rounded-[24px] bg-amber-50/90 border border-amber-200/90 p-3.5 flex items-start gap-3 shadow-[0_8px_30px_rgb(245,158,11,0.04)]">
              <Clock size={18} className="text-amber-600 shrink-0 mt-0.5" />
              <div className="text-xs text-amber-950 leading-snug">
                <span className="font-bold block text-amber-900 mb-0.5">Batas Waktu Pembayaran: 2 Jam</span>
                Harap segera lakukan transfer dan unggah bukti transfer. Jika dalam 2 jam belum ada unggahan bukti, transaksi akan otomatis dibatalkan dan tagihan dapat dipilih kembali.
              </div>
            </div>
          </div>
        )}

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
          <ProofUploader
            tx={tx}
            selectedFile={selectedFile}
            selectedFileUrl={selectedFileUrl}
            onSelectFile={(f) => {
              setSelectedFile(f);
              setSelectedFileUrl(URL.createObjectURL(f));
            }}
            onRemoveFile={() => {
              setSelectedFile(null);
              setSelectedFileUrl("");
            }}
            isUploading={uploadMutation.isPending}
          />
        </div>
 
        {/* Sticky action */}
        {isApproved && (
          <StickyAction>
            <button
              onClick={() => navigate({ to: "/dashboard", hash: "transaksi-terkini" })}
              className="w-full py-3.5 rounded-[24px] bg-success text-white font-bold text-sm flex items-center justify-center gap-2 shadow-[0_8px_30px_rgb(0,0,0,0.04)]"
            >
              <CheckCircle2 size={16} /> Lihat di Transaksi Terkini
            </button>
          </StickyAction>
        )}
        {isCancelled && (
          <StickyAction>
            <button
              onClick={() => navigate({ to: "/tagihan" })}
              className="w-full py-3.5 rounded-[24px] bg-secondary border border-border text-foreground font-bold text-sm shadow-[0_8px_30px_rgb(0,0,0,0.04)]"
            >
              Kembali ke Tagihan
            </button>
          </StickyAction>
        )}
        {isRejected && (
          <StickyAction>
            <div className="w-full flex flex-col gap-2">
              {tx.note && (
                <div className="rounded-[24px] bg-destructive/10 border border-destructive/20 p-3 flex items-start gap-2.5 text-destructive text-sm text-left">
                  <XCircle size={16} className="mt-0.5 shrink-0" />
                  <div>
                    <span className="font-bold block mb-0.5">Alasan Penolakan Bendahara:</span>
                    <span>{tx.note}</span>
                  </div>
                </div>
              )}
              {selectedFile ? (
                <button
                  onClick={() => setShowConfirmUpload(true)}
                  disabled={uploadMutation.isPending}
                  className="w-full py-4 rounded-[24px] text-white font-bold text-sm shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex items-center justify-center gap-2 active:scale-95 transition bg-primary"
                  style={{ background: "var(--gradient-card)" }}
                >
                  {uploadMutation.isPending ? <Loader2 className="animate-spin" size={18} /> : <><Upload size={18} /> Kirim Bukti & Konfirmasi</>}
                </button>
              ) : (
                <div className="grid grid-cols-2 gap-2 w-full">
                  <button
                    onClick={() => setShowConfirmCancelTransaction(true)}
                    disabled={cancelTransactionMutation.isPending}
                    className="py-3.5 rounded-[24px] bg-red-50 text-red-600 border border-red-200 font-bold text-xs active:scale-95 transition flex items-center justify-center gap-1.5 shadow-[0_8px_30px_rgb(239,68,68,0.06)]"
                  >
                    {cancelTransactionMutation.isPending ? <Loader2 className="animate-spin" size={14} /> : <><XCircle size={14} /> Batalkan Transaksi</>}
                  </button>
                  <button
                    onClick={() => navigate({ to: "/tagihan" })}
                    className="py-3.5 rounded-[24px] bg-secondary border border-border text-foreground font-bold text-xs active:scale-95 transition flex items-center justify-center"
                  >
                    Kembali ke Tagihan
                  </button>
                </div>
              )}
            </div>
          </StickyAction>
        )}
        {isPending && (
          <>
            {selectedFile ? (
              <StickyAction>
                <button
                  onClick={() => setShowConfirmUpload(true)}
                  disabled={uploadMutation.isPending}
                  className="w-full py-4 rounded-[24px] text-white font-bold text-sm shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex items-center justify-center gap-2 active:scale-95 transition bg-primary"
                  style={{ background: "var(--gradient-card)" }}
                >
                  {uploadMutation.isPending ? <Loader2 className="animate-spin" size={18} /> : <><Upload size={18} /> Kirim Bukti & Konfirmasi</>}
                </button>
              </StickyAction>
            ) : tx.proofUrl ? (
              <StickyAction>
                <div className="flex flex-col gap-2 w-full">
                  <button
                    onClick={() => navigate({ to: "/dashboard", hash: "transaksi-terkini" })}
                    className="w-full py-3.5 rounded-[24px] text-white font-bold text-sm shadow-[var(--shadow-glow)] active:scale-[0.98] bg-primary"
                    style={{ background: "var(--gradient-card)" }}
                  >
                    Lihat Status di Transaksi Terkini
                  </button>
                  <button
                    onClick={() => setShowConfirmCancel(true)}
                    disabled={cancelMutation.isPending}
                    className="w-full py-3 rounded-[24px] bg-red-600/10 text-red-600 border border-red-600/20 font-bold text-sm active:scale-95 transition flex items-center justify-center gap-1.5"
                  >
                    {cancelMutation.isPending ? <Loader2 className="animate-spin" size={16} /> : "Tarik & Upload Ulang"}
                  </button>
                </div>
              </StickyAction>
            ) : (
              <StickyAction>
                <div className="flex flex-col gap-2 w-full">
                  <button
                    onClick={() => setShowConfirmCancelTransaction(true)}
                    disabled={cancelTransactionMutation.isPending}
                    className="w-full py-3.5 rounded-[24px] bg-red-50 text-red-600 border border-red-200 font-bold text-sm active:scale-95 transition flex items-center justify-center gap-1.5 shadow-[0_8px_30px_rgb(239,68,68,0.06)]"
                  >
                    {cancelTransactionMutation.isPending ? <Loader2 className="animate-spin" size={16} /> : <><XCircle size={16} /> Batalkan Transaksi</>}
                  </button>
                </div>
              </StickyAction>
            )}
          </>
        )}

        {/* Confirm Upload Modal */}
        {showConfirmUpload && selectedFile && (
          <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-5 animate-in fade-in">
            <div className="bg-background rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] w-full max-w-sm p-6 flex flex-col items-center text-center animate-in zoom-in-95 duration-200">
              <div className="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center mb-4">
                <Upload size={24} />
              </div>
              <h3 className="text-base font-bold text-foreground mb-2">Konfirmasi Kirim Bukti</h3>
              <p className="text-sm text-muted-foreground mb-6">
                Pastikan gambar bukti transfer Anda sudah benar dan nominalnya sesuai dengan tagihan.
              </p>
              <div className="grid grid-cols-2 gap-3 w-full">
                <button
                  onClick={() => setShowConfirmUpload(false)}
                  className="py-3 rounded-[24px] border border-border text-foreground font-bold text-sm active:scale-95 transition"
                >
                  Batal
                </button>
                <button
                  onClick={() => {
                    setShowConfirmUpload(false);
                    uploadMutation.mutate(selectedFile);
                  }}
                  className="py-3 rounded-[24px] bg-primary text-white font-bold text-sm shadow-[0_8px_30px_rgb(0,0,0,0.04)] active:scale-95 transition"
                  style={{ background: "var(--gradient-card)" }}
                >
                  Ya, Kirim
                </button>
              </div>
            </div>
          </div>
        )}

        {/* Confirm Cancel Proof Modal */}
        {showConfirmCancel && (
          <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-5 animate-in fade-in">
            <div className="bg-background rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] w-full max-w-sm p-6 flex flex-col items-center text-center animate-in zoom-in-95 duration-200">
              <div className="w-12 h-12 rounded-full bg-red-600/10 text-red-600 flex items-center justify-center mb-4">
                <XCircle size={24} />
              </div>
              <h3 className="text-base font-bold text-foreground mb-2">Tarik Bukti Pembayaran?</h3>
              <p className="text-sm text-muted-foreground mb-6">
                Tindakan ini akan membatalkan bukti transfer saat ini dan mengembalikan status transaksi ke menunggu pembayaran.
              </p>
              <div className="grid grid-cols-2 gap-3 w-full">
                <button
                  onClick={() => setShowConfirmCancel(false)}
                  className="py-3 rounded-[24px] border border-border text-foreground font-bold text-sm active:scale-95 transition"
                >
                  Batal
                </button>
                <button
                  onClick={() => {
                    setShowConfirmCancel(false);
                    cancelMutation.mutate();
                  }}
                  className="py-3 rounded-[24px] bg-red-600 text-white font-bold text-sm shadow-[0_8px_30px_rgb(0,0,0,0.04)] active:scale-95 transition"
                >
                  Ya, Tarik
                </button>
              </div>
            </div>
          </div>
        )}

        {/* Confirm Cancel Transaction Modal */}
        {showConfirmCancelTransaction && (
          <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-5 animate-in fade-in">
            <div className="bg-background rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] w-full max-w-sm p-6 flex flex-col items-center text-center animate-in zoom-in-95 duration-200">
              <div className="w-12 h-12 rounded-full bg-red-600/10 text-red-600 flex items-center justify-center mb-4">
                <XCircle size={24} />
              </div>
              <h3 className="text-base font-bold text-foreground mb-2">Batalkan Transaksi?</h3>
              <p className="text-sm text-muted-foreground mb-6">
                Transaksi ini akan dibatalkan dan item tagihan akan di-rollback sehingga Anda dapat memilih dan membayarnya kembali.
              </p>
              <div className="grid grid-cols-2 gap-3 w-full">
                <button
                  onClick={() => setShowConfirmCancelTransaction(false)}
                  className="py-3 rounded-[24px] border border-border text-foreground font-bold text-sm active:scale-95 transition"
                >
                  Kembali
                </button>
                <button
                  onClick={() => {
                    setShowConfirmCancelTransaction(false);
                    cancelTransactionMutation.mutate();
                  }}
                  className="py-3 rounded-[24px] bg-red-600 text-white font-bold text-sm shadow-[0_8px_30px_rgb(0,0,0,0.04)] active:scale-95 transition"
                >
                  Ya, Batalkan
                </button>
              </div>
            </div>
          </div>
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

function StatusBanner({
  status,
  hasProof,
  note,
}: {
  status: "approved" | "rejected" | "pending" | "cancelled";
  hasProof?: boolean;
  note?: string;
}) {
  if (status === "approved") {
    return (
      <div className="rounded-[24px] border border-emerald-200/80 bg-gradient-to-r from-emerald-50 to-teal-50/60 p-4 shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex items-start gap-3.5">
        <div className="w-11 h-11 rounded-[16px] bg-emerald-600/15 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-600/20 shadow-xs">
          <CheckCircle2 size={22} strokeWidth={2.5} />
        </div>
        <div className="flex-1 min-w-0">
          <div className="flex items-center gap-2 mb-1">
            <span className="px-2.5 py-0.5 rounded-full bg-emerald-600/15 border border-emerald-600/20 text-emerald-700 text-[10px] font-black uppercase tracking-wider">
              Lunas
            </span>
          </div>
          <Text.H2 className="text-[15px] font-extrabold text-emerald-950 leading-tight">
            Pembayaran Disetujui
          </Text.H2>
          <Text.Body className="text-[12px] text-emerald-900/80 mt-1 leading-snug">
            Transaksi sudah diverifikasi dan disetujui oleh bendahara.
          </Text.Body>
        </div>
      </div>
    );
  }

  if (status === "cancelled") {
    return (
      <div className="rounded-[24px] border border-slate-300/80 bg-slate-100/70 p-4 shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex items-start gap-3.5">
        <div className="w-11 h-11 rounded-[16px] bg-slate-300 text-slate-700 flex items-center justify-center shrink-0 border border-slate-300 shadow-xs">
          <XCircle size={22} strokeWidth={2.5} />
        </div>
        <div className="flex-1 min-w-0">
          <div className="flex items-center gap-2 mb-1">
            <span className="px-2.5 py-0.5 rounded-full bg-slate-200 text-slate-700 text-[10px] font-black uppercase tracking-wider">
              Dibatalkan
            </span>
          </div>
          <Text.H2 className="text-[15px] font-extrabold text-slate-900 leading-tight">
            Transaksi Dibatalkan
          </Text.H2>
          <Text.Body className="text-[12px] text-slate-600 mt-1 leading-snug">
            Transaksi ini telah dibatalkan. Tagihan telah di-rollback dan dapat dipilih kembali di halaman Tagihan.
          </Text.Body>
        </div>
      </div>
    );
  }

  if (status === "rejected") {
    return (
      <div className="rounded-[24px] border border-red-200/80 bg-gradient-to-r from-red-50 to-rose-50/60 p-4 shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex items-start gap-3.5">
        <div className="w-11 h-11 rounded-[16px] bg-red-600/15 text-red-600 flex items-center justify-center shrink-0 border border-red-600/20 shadow-xs">
          <XCircle size={22} strokeWidth={2.5} />
        </div>
        <div className="flex-1 min-w-0">
          <div className="flex items-center gap-2 mb-1">
            <span className="px-2.5 py-0.5 rounded-full bg-red-600/15 border border-red-600/20 text-red-700 text-[10px] font-black uppercase tracking-wider">
              Ditolak
            </span>
          </div>
          <Text.H2 className="text-[15px] font-extrabold text-red-950 leading-tight">
            Pembayaran Ditolak Bendahara
          </Text.H2>
          <Text.Body className="text-[12px] text-red-900/80 mt-1 leading-snug">
            Bukti transfer ditolak oleh bendahara sekolah.
          </Text.Body>
          {note && (
            <div className="mt-2.5 p-3 rounded-[16px] bg-red-100/90 border border-red-200 text-xs text-red-950">
              <span className="font-bold block mb-0.5 text-red-900">Alasan Penolakan:</span>
              <p className="font-semibold">{note}</p>
            </div>
          )}
        </div>
      </div>
    );
  }

  // Pending status
  if (!hasProof) {
    return (
      <div className="rounded-[24px] border border-amber-300/80 bg-gradient-to-r from-amber-50 via-amber-50/80 to-orange-50/70 p-4 shadow-[0_8px_30px_rgb(245,158,11,0.08)] flex items-start gap-3.5">
        <div className="w-11 h-11 rounded-[16px] bg-amber-500/20 text-amber-600 flex items-center justify-center shrink-0 border border-amber-500/30 shadow-xs">
          <UploadCloud size={22} strokeWidth={2.5} />
        </div>
        <div className="flex-1 min-w-0">
          <div className="flex items-center gap-2 mb-1">
            <span className="px-2.5 py-0.5 rounded-full bg-amber-500/20 border border-amber-500/30 text-amber-800 text-[10px] font-black uppercase tracking-wider">
              Perlu Bukti Bayar
            </span>
          </div>
          <Text.H2 className="text-[15px] font-extrabold text-amber-950 leading-tight">
            Menunggu Unggah Bukti Bayar
          </Text.H2>
          <Text.Body className="text-[12px] text-amber-900/85 mt-1 leading-snug">
            Transfer sesuai nominal lalu segera unggah foto bukti transfer di bawah.
          </Text.Body>
        </div>
      </div>
    );
  }

  return (
    <div className="rounded-[24px] border border-blue-200/80 bg-gradient-to-r from-blue-50 via-indigo-50/60 to-blue-50/40 p-4 shadow-[0_8px_30px_rgb(37,99,235,0.06)] flex items-start gap-3.5">
      <div className="w-11 h-11 rounded-[16px] bg-blue-600/15 text-blue-600 flex items-center justify-center shrink-0 border border-blue-600/20 shadow-xs">
        <Clock size={22} strokeWidth={2.5} />
      </div>
      <div className="flex-1 min-w-0">
        <div className="flex items-center gap-2 mb-1">
          <span className="px-2.5 py-0.5 rounded-full bg-blue-600/15 border border-blue-600/20 text-blue-700 text-[10px] font-black uppercase tracking-wider">
            Menunggu Verifikasi
          </span>
        </div>
        <Text.H2 className="text-[15px] font-extrabold text-blue-950 leading-tight">
          Menunggu Verifikasi Bendahara
        </Text.H2>
        <Text.Body className="text-[12px] text-blue-900/80 mt-1 leading-snug">
          Bukti transfer telah diterima. Mohon menunggu konfirmasi dan verifikasi oleh bendahara sekolah.
        </Text.Body>
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

function ProofUploader({
  tx,
  selectedFile,
  selectedFileUrl,
  onSelectFile,
  onRemoveFile,
  isUploading,
}: {
  tx: any;
  selectedFile: File | null;
  selectedFileUrl: string;
  onSelectFile: (f: File) => void;
  onRemoveFile: () => void;
  isUploading: boolean;
}) {
  const inputRef = useRef<HTMLInputElement>(null);
  const [isCompressing, setIsCompressing] = useState(false);
  const hasProof = !!tx.proofUrl;
  const locked = tx.status === "approved";

  return (
    <div className="rounded-[24px] border border-border bg-card shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-4">
      <div className="flex items-center justify-between gap-3">
        <div>
          <p className="text-[11px] text-muted-foreground font-semibold uppercase tracking-wider">
            Bukti Bayar
          </p>
          <p className="text-sm font-bold text-foreground">
            {selectedFile 
              ? "Pratinjau Bukti" 
              : hasProof 
                ? "Bukti terunggah" 
                : "Unggah foto bukti transfer"}
          </p>
        </div>
        {(selectedFile || (hasProof && !locked)) && (
          <button
            onClick={() => {
              if (selectedFile) {
                onRemoveFile();
              } else {
                inputRef.current?.click();
              }
            }}
            className="text-[11px] font-bold text-primary"
          >
            {selectedFile ? "Batal" : "Ganti"}
          </button>
        )}
      </div>

      <input
        ref={inputRef}
        type="file"
        accept="image/*"
        className="hidden"
        onChange={async (e) => {
          const f = e.target.files?.[0];
          if (f) {
            try {
              setIsCompressing(true);
              const compressed = await compressImage(f);
              onSelectFile(compressed);
            } catch (err) {
              console.error("Compression error:", err);
              onSelectFile(f);
            } finally {
              setIsCompressing(false);
            }
          }
          e.target.value = "";
        }}
      />

      {selectedFile ? (
        <div className="mt-3 rounded-[24px] overflow-hidden border border-border bg-secondary relative">
          <img
            src={selectedFileUrl}
            alt="Pratinjau bukti"
            className="w-full max-h-72 object-contain bg-black/5"
          />
          <span className="absolute top-2.5 left-2.5 inline-flex items-center gap-1 px-2 py-1 rounded-full bg-primary text-white text-[10px] font-bold shadow">
            Siap dikirim
          </span>
        </div>
      ) : hasProof ? (
        <div className="mt-3 rounded-[24px] overflow-hidden border border-border bg-secondary">
          <img
            src={resolveImageUrl(tx.proofUrl) || ''}
            alt="Bukti transfer"
            className="w-full max-h-72 object-contain bg-black/5"
          />
        </div>
      ) : (
        <button
          disabled={isUploading || locked || isCompressing}
          onClick={() => inputRef.current?.click()}
          className="mt-3 w-full rounded-[24px] border-2 border-dashed border-border bg-secondary/50 px-4 py-6 flex flex-col items-center justify-center gap-2 text-muted-foreground active:scale-[0.99] transition disabled:opacity-50"
        >
          <div className="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center">
            {isUploading || isCompressing ? <Loader2 className="animate-spin" size={18} /> : <Upload size={18} />}
          </div>
          <p className="text-sm font-bold text-foreground">
            {isCompressing ? "Mengompres Gambar…" : isUploading ? "Memproses…" : "Pilih Foto Bukti"}
          </p>
          <p className="text-[11px]">JPG / PNG, maks. 20 MB (Auto-compress s.d 300KB)</p>
        </button>
      )}

      {!hasProof && !selectedFile && (
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
