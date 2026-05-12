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

export const Route = createFileRoute("/admin/approval")({
  component: AdminApprovalPage,
  head: () => ({ meta: [{ title: "Approval Pembayaran — Admin" }] }),
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
        <div className="px-6 pt-12 pb-3 flex items-center gap-3">
          <button
            onClick={() => navigate({ to: "/dashboard" })}
            className="w-10 h-10 rounded-xl bg-secondary border border-border flex items-center justify-center"
          >
            <ArrowLeft size={18} />
          </button>
          <div className="min-w-0 flex-1">
            <p className="text-[11px] text-muted-foreground font-semibold uppercase tracking-wider">
              Panel Petugas
            </p>
            <p className="text-base font-bold text-foreground">
              Verifikasi Pembayaran
            </p>
          </div>
          <ShieldCheck size={20} className="text-primary" />
        </div>

        <div className="px-5 pt-2">
          <div className="flex bg-secondary rounded-2xl p-1">
            {(["pending", "approved", "rejected"] as const).map((t) => {
              const active = tab === t;
              const count = list.filter((x) => x.status === t).length;
              return (
                <button
                  key={t}
                  onClick={() => setTab(t)}
                  className={`flex-1 py-2 rounded-xl text-xs font-bold capitalize transition ${
                    active
                      ? "bg-card text-primary shadow-[var(--shadow-soft)]"
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
            <div className="text-center py-16 text-sm text-muted-foreground">
              Tidak ada transaksi {tab}.
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
    <div className="rounded-2xl border border-border bg-card shadow-[var(--shadow-soft)] p-4">
      <div className="flex items-start justify-between gap-3">
        <div className="min-w-0">
          <p className="text-[11px] text-muted-foreground font-mono">{tx.id}</p>
          <p className="text-sm font-bold text-foreground truncate">{tx.billName}</p>
        </div>
        <StatusPill status={tx.status} />
      </div>

      <div className="mt-3 grid grid-cols-2 gap-3 text-xs">
        <div>
          <p className="text-muted-foreground">Nominal</p>
          <p className="font-bold text-foreground">{fmtIDR(tx.amount)}</p>
        </div>
        <div>
          <p className="text-muted-foreground">Kode Unik</p>
          <p className="font-bold text-primary">+{tx.uniqueCode}</p>
        </div>
        <div>
          <p className="text-muted-foreground">Bank</p>
          <p className="font-bold text-foreground">
            {tx.bankName} · {tx.bankAccount}
          </p>
        </div>
        <div>
          <p className="text-muted-foreground">Diajukan</p>
          <p className="font-bold text-foreground">
            {new Date(tx.createdAt).toLocaleString("id-ID")}
          </p>
        </div>
      </div>

      {tx.proofDataUrl ? (
        <div className="mt-3 rounded-xl overflow-hidden border border-border bg-secondary">
          <img
            src={tx.proofDataUrl}
            alt="Bukti"
            className="w-full max-h-60 object-contain bg-black/5"
          />
        </div>
      ) : (
        <p className="mt-3 text-[11px] text-muted-foreground italic">
          Belum ada bukti unggahan dari santri.
        </p>
      )}

      {tx.status === "pending" && (
        <div className="mt-3 grid grid-cols-2 gap-2">
          <button
            onClick={() => setStatus(tx.id, "rejected")}
            className="py-2.5 rounded-xl bg-destructive/10 text-destructive font-bold text-xs flex items-center justify-center gap-1.5 active:scale-95"
          >
            <XCircle size={14} /> Tolak
          </button>
          <button
            onClick={() => setStatus(tx.id, "approved")}
            disabled={!tx.proofDataUrl}
            className="py-2.5 rounded-xl bg-success text-white font-bold text-xs flex items-center justify-center gap-1.5 active:scale-95 disabled:opacity-50"
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
      <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-success text-white text-[10px] font-bold">
        <CheckCircle2 size={11} /> Approved
      </span>
    );
  if (status === "rejected")
    return (
      <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-destructive text-white text-[10px] font-bold">
        <XCircle size={11} /> Rejected
      </span>
    );
  return (
    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[oklch(0.78_0.16_75)] text-white text-[10px] font-bold">
      <Clock size={11} /> Pending
    </span>
  );
}
