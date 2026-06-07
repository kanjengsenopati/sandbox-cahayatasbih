import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { ArrowLeft, CheckCircle2, XCircle, Clock, ShieldCheck } from "lucide-react";
import {
  listPendingTx,
  setStatus,
  subscribePendingTx,
  type PendingTx,
} from "@/data/pendingTx";
import { fmtIDR } from "@/data/bills";
import { Text } from "@/components/Text";

export const Route = createFileRoute("/admin/approval")({
  component: AdminApprovalPage,
  head: () => ({ meta: [{ title: "Persetujuan Pembayaran — Admin" }] }),
});

function AdminApprovalPage() {
  const navigate = useNavigate();
  const [list, setList] = useState<PendingTx[]>([]);
  const [tab, setTab] = useState<"pending" | "approved" | "rejected">("pending");

  useEffect(() => {
    const refresh = () => setList(listPendingTx());
    refresh();
    return subscribePendingTx(refresh);
  }, []);

  const filtered = list.filter((t) => t.status === tab);

  return (
    <div className="min-h-screen w-full flex justify-center bg-secondary">
      <div className="relative w-full max-w-md min-h-screen bg-background pb-12">
        <div className="px-5 pt-12 pb-3 flex items-center gap-3">
          <button
            onClick={() => navigate({ to: "/dashboard" })}
            className="w-10 h-10 rounded-[24px] bg-secondary border border-border flex items-center justify-center"
          >
            <ArrowLeft size={18} />
          </button>
          <div className="min-w-0 flex-1">
            <Text.Label className="block mb-0.5">Panel Petugas</Text.Label>
            <Text.H1>Verifikasi Pembayaran</Text.H1>
          </div>
          <ShieldCheck size={20} className="text-blue-600" />
        </div>

        <div className="px-5 pt-2">
          <div className="flex bg-secondary rounded-[24px] p-1">
            {(["pending", "approved", "rejected"] as const).map((t) => {
              const active = tab === t;
              const count = list.filter((x) => x.status === t).length;
              return (
                <button
                  key={t}
                  onClick={() => setTab(t)}
                  className={`flex-1 py-2 rounded-[24px] text-xs font-bold capitalize transition ${
                    active
                      ? "bg-card text-blue-600 shadow-[0_8px_30px_rgb(0,0,0,0.04)]"
                      : "text-muted-foreground"
                  }`}
                >
                  {t === "pending" ? "Pending" : t === "approved" ? "Approved" : "Rejected"}{" "}
                  <span className="opacity-70">({count})</span>
                </button>
              );
            })}
          </div>
        </div>

        <div className="px-5 pt-4 space-y-3">
          {filtered.length === 0 && (
            <div className="text-center py-16">
              <Text.Body className="text-slate-400">Tidak ada transaksi {tab}.</Text.Body>
            </div>
          )}
          {filtered.map((t) => (
            <ApprovalCard key={t.id} tx={t} />
          ))}
        </div>
      </div>
    </div>
  );
}

function ApprovalCard({ tx }: { tx: PendingTx }) {
  return (
    <div className="rounded-[24px] bg-card shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-5">
      <div className="flex items-start justify-between gap-3">
        <div className="min-w-0">
          <Text.Caption className="font-mono not-italic block mb-0.5">{tx.id}</Text.Caption>
          <Text.H2 className="truncate">{tx.billName}</Text.H2>
        </div>
        <StatusPill status={tx.status} />
      </div>

      <div className="mt-4 grid grid-cols-2 gap-4">
        <div>
          <Text.Label className="block mb-1">Nominal</Text.Label>
          <Text.Amount className="block">{fmtIDR(tx.amount)}</Text.Amount>
        </div>
        <div>
          <Text.Label className="block mb-1">Kode Unik</Text.Label>
          <Text.Body className="font-bold text-blue-600">+{tx.uniqueCode}</Text.Body>
        </div>
        <div className="col-span-2">
          <Text.Label className="block mb-1">Bank Penerima</Text.Label>
          <Text.Body className="font-bold text-slate-800 leading-snug">
            {tx.bankName}
          </Text.Body>
          <div className="mt-1 space-y-0.5">
            <Text.Caption className="block text-slate-500 not-italic leading-none">
              No. Rek: {tx.bankAccount}
            </Text.Caption>
            <Text.Caption className="block text-slate-500 not-italic leading-none">
              Nama Pemilik: {tx.bankHolder || "-"}
            </Text.Caption>
          </div>
        </div>
        <div className="col-span-2">
          <Text.Label className="block mb-0.5">Tanggal Diajukan</Text.Label>
          <Text.Body className="text-slate-600">
            {new Date(tx.createdAt).toLocaleString("id-ID")}
          </Text.Body>
        </div>
      </div>

      {tx.proofDataUrl ? (
        <div className="mt-4 rounded-[24px] overflow-hidden border border-border bg-secondary">
          <img
            src={tx.proofDataUrl}
            alt="Bukti"
            className="w-full max-h-60 object-contain bg-black/5"
          />
        </div>
      ) : (
        <Text.Caption className="mt-4 block">
          Belum ada bukti unggahan dari santri.
        </Text.Caption>
      )}

      {tx.status === "pending" && (
        <div className="mt-4 grid grid-cols-2 gap-2">
          <button
            onClick={() => setStatus(tx.id, "rejected")}
            className="py-2.5 rounded-[24px] bg-red-600/10 text-red-600 font-bold text-xs flex items-center justify-center gap-1.5 active:scale-95"
          >
            <XCircle size={14} /> Tolak
          </button>
          <button
            onClick={() => setStatus(tx.id, "approved")}
            disabled={!tx.proofDataUrl}
            className="py-2.5 rounded-[24px] bg-emerald-600 text-white font-bold text-xs flex items-center justify-center gap-1.5 active:scale-95 disabled:opacity-50"
          >
            <CheckCircle2 size={14} /> Setujui
          </button>
        </div>
      )}
    </div>
  );
}

function StatusPill({ status }: { status: PendingTx["status"] }) {
  if (status === "approved")
    return (
      <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-600/10 text-emerald-600 text-[10px] font-bold">
        <CheckCircle2 size={11} /> Approved
      </span>
    );
  if (status === "rejected")
    return (
      <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-red-600/10 text-red-600 text-[10px] font-bold">
        <XCircle size={11} /> Rejected
      </span>
    );
  return (
    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-600/10 text-amber-600 text-[10px] font-bold">
      <Clock size={11} /> Pending
    </span>
  );
}
