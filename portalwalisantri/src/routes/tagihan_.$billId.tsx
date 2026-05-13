import { createFileRoute, useNavigate, useParams } from "@tanstack/react-router";
import { useEffect, useMemo, useState } from "react";
import { ArrowLeft, Check, CheckCircle2, Loader2 } from "lucide-react";
import { useSantri } from "@/contexts/SantriContext";
import { SantriSwitcherTrigger } from "@/components/SantriSwitcher";
import { useQuery, useMutation } from "@tanstack/react-query";
import { fetchBillDetail, postCheckout, fetchPaymentMethods } from "@/lib/api";
import { Building2, CreditCard, Smartphone, ShieldCheck } from "lucide-react";

export const Route = createFileRoute("/tagihan_/$billId")({
  component: BillDetail,
  head: () => ({
    meta: [{ title: `Detail Tagihan — SantriPay` }],
  }),
});

const fmt = (n: number) =>
  new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(n);

function BillDetail() {
  const { billId } = useParams({ from: "/tagihan_/$billId" });
  const navigate = useNavigate();
  const { active, isLoading: isLoadingSantri } = useSantri();
  const [picked, setPicked] = useState<Set<string>>(new Set());

  const { data: detailData, isLoading: isLoadingDetail } = useQuery({
    queryKey: ["bill-detail", billId],
    queryFn: async () => {
      const res = await fetchBillDetail(billId);
      return res.data;
    },
  });

  const bill = useMemo(() => {
    if (!detailData) return null;
    const b = detailData.bill;
    const details = detailData.details || [];
    
    return {
      id: billId,
      name: detailData.billType.name,
      shortName: detailData.billType.name,
      total: detailData.summary.total,
      paid: detailData.summary.paid,
      installments: detailData.bills.map((d: any) => ({
        id: String(d.id),
        label: d.month_name ? `${d.month_name} ${d.year}` : d.name,
        amount: Number(d.amount),
        paid: d.status === "PAID",
      })),
    };
  }, [detailData]);

  const [method, setMethod] = useState<string>("");

  const { data: methodsRes, isLoading: isLoadingMethods } = useQuery({
    queryKey: ["payment-methods", "BILL", Array.from(picked)],
    queryFn: async () => {
      const res = await fetchPaymentMethods({ type: "BILL", bill_ids: Array.from(picked) });
      return res.data;
    },
    enabled: picked.size > 0,
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

  const selectedMethod = useMemo(() => methods.find((m: any) => m.id === method), [method, methods]);

  useEffect(() => {
    if (methods.length > 0 && !method) {
      setMethod(methods[0].id);
    }
  }, [methods, method]);

  const checkoutMutation = useMutation({
    mutationFn: async ({ installmentIds, methodId }: { installmentIds: string[], methodId: string }) => {
      const res = await postCheckout({
        bill_ids: installmentIds,
        payment_method_id: methodId,
      });
      return res.data;
    },
    onSuccess: (data) => {
      navigate({ to: "/pembayaran/$payId", params: { payId: String(data.transaction.id) } });
    },
  });

  const unpaid = useMemo(() => bill?.installments.filter((i: any) => !i.paid) || [], [bill]);
  const allUnpaidPicked = unpaid.length > 0 && unpaid.every((i: any) => picked.has(i.id));

  const togglePick = (id: string) =>
    setPicked((s) => {
      const n = new Set(s);
      n.has(id) ? n.delete(id) : n.add(id);
      return n;
    });

  const togglePickAll = () =>
    setPicked(allUnpaidPicked ? new Set() : new Set(unpaid.map((i) => i.id)));

  const pickedTotal = useMemo(
    () => bill?.installments.filter((i: any) => picked.has(i.id)).reduce((a: number, b: any) => a + b.amount, 0) || 0,
    [picked, bill?.installments],
  );

  if (isLoadingSantri || isLoadingDetail) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-background">
        <Loader2 className="animate-spin text-primary" size={40} />
      </div>
    );
  }

  if (!bill) return null;

  const remaining = Math.max(0, bill.total - bill.paid);
  const isFullyPaid = remaining === 0;
  const isPartial = bill.paid > 0 && !isFullyPaid;

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
              Tagihan
            </p>
            <p className="text-base font-bold text-foreground truncate">
              Detail {bill.shortName}
            </p>
          </div>
        </div>

        {/* Active santri pill */}
        <div className="px-4 mt-1">
          <ActiveSantriPill />
        </div>

        {/* Bill summary card */}
        <div className="px-4 pt-4">
          <div className="rounded-3xl border border-border bg-card p-4 shadow-[var(--shadow-card)]">
            <div className="flex items-start justify-between gap-3">
              <div className="flex items-center gap-2 min-w-0">
                <span className="w-1 h-5 rounded-full bg-primary shrink-0" />
                <h2 className="text-sm font-semibold text-foreground tracking-tight uppercase truncate">
                  {bill.name}
                </h2>
              </div>
              <span
                className={`shrink-0 inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-semibold text-white ${
                  isFullyPaid
                    ? "bg-success"
                    : isPartial
                    ? "bg-[oklch(0.78_0.16_75)]"
                    : "bg-[oklch(0.62_0.22_25)]"
                }`}
              >
                {isFullyPaid && <CheckCircle2 size={12} />}
                {isFullyPaid ? "Lunas" : isPartial ? "Proses Bayar" : "Belum Bayar"}
              </span>
            </div>

            <div className="mt-3">
              <p className="text-[11px] text-muted-foreground">Total {bill.shortName}</p>
              <p className="text-2xl font-bold text-foreground tabular-nums">{fmt(bill.total)}</p>
            </div>

            <div className="mt-3 pt-3 border-t border-border grid grid-cols-2 gap-4">
              <div>
                <p className="text-[11px] text-muted-foreground">Sudah Bayar</p>
                <p className="text-sm font-semibold text-foreground tabular-nums">{fmt(bill.paid)}</p>
              </div>
              <div>
                <p className="text-[11px] text-muted-foreground">Belum Bayar</p>
                <p
                  className={`text-sm font-semibold tabular-nums ${
                    isFullyPaid ? "text-success" : "text-[oklch(0.62_0.22_25)]"
                  }`}
                >
                  {fmt(remaining)}
                </p>
              </div>
            </div>
          </div>
        </div>

        <div className="px-5 pt-2">

          {/* Installments */}
          <div className="mt-5">
            <h3 className="text-base font-bold text-foreground">
              Detail {bill.shortName}
            </h3>

            {unpaid.length > 0 && (
              <button
                onClick={togglePickAll}
                className="mt-4 flex items-center gap-3 px-1"
              >
                <CheckBox checked={allUnpaidPicked} />
                <span className="text-sm font-semibold text-foreground">Bayar Semua</span>
              </button>
            )}

            <div className="mt-4 space-y-3">
              {bill.installments.map((it) => {
                const checked = picked.has(it.id);
                return (
                  <div
                    key={it.id}
                    onClick={() => !it.paid && togglePick(it.id)}
                    role={it.paid ? undefined : "button"}
                    className={`relative flex items-center gap-3 pl-4 pr-3 py-3 rounded-2xl bg-secondary/70 border transition ${
                      !it.paid && checked
                        ? "border-primary ring-1 ring-primary/40"
                        : "border-border"
                    } ${it.paid ? "" : "cursor-pointer active:scale-[0.99]"}`}
                  >
                    <span
                      className={`absolute left-0 top-3 bottom-3 w-1 rounded-r-full ${
                        it.paid ? "bg-success" : "bg-primary"
                      }`}
                    />

                    <span className="shrink-0">
                      <CheckBox checked={it.paid || checked} disabled={it.paid} />
                    </span>

                    <div className="flex-1 min-w-0">
                      <p className="text-xs text-muted-foreground">{fmt(it.amount)}</p>
                      <p className="text-sm font-bold text-primary leading-tight mt-0.5">
                        {it.label}
                      </p>
                    </div>

                    {it.paid ? (
                      <span className="shrink-0 px-5 py-2.5 rounded-xl bg-success text-white text-xs font-bold">
                        Lunas
                      </span>
                    ) : (
                      <button
                        onClick={(e) => {
                          e.stopPropagation();
                          if (!selectedMethod) {
                            togglePick(it.id);
                            return;
                          }
                          checkoutMutation.mutate({ 
                            installmentIds: [it.id], 
                            methodId: selectedMethod.payment_method_id 
                          });
                        }}
                        disabled={checkoutMutation.isPending}
                        className="shrink-0 px-4 py-2.5 rounded-xl text-xs font-bold text-primary-foreground shadow-[var(--shadow-soft)] active:scale-95 transition flex items-center justify-center min-w-[100px]"
                        style={{ background: "var(--gradient-card)" }}
                      >
                        {checkoutMutation.isPending ? <Loader2 className="animate-spin" size={14} /> : "Bayar Sekarang"}
                      </button>
                    )}
                  </div>
                );
              })}
            </div>
          </div>

          {/* Payment Methods Section */}
          {picked.size > 0 && (
            <div className="mt-8">
              <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2 px-1">
                Metode Pembayaran
              </p>
              <div className="bg-card rounded-2xl border border-border divide-y divide-border overflow-hidden shadow-[var(--shadow-soft)]">
                {isLoadingMethods ? (
                  <div className="p-8 flex flex-col items-center justify-center gap-3">
                    <Loader2 className="animate-spin text-primary" size={24} />
                    <p className="text-xs text-muted-foreground">Memuat metode pembayaran...</p>
                  </div>
                ) : methods.length === 0 ? (
                  <div className="p-8 text-center">
                    <p className="text-xs text-muted-foreground">Tidak ada metode pembayaran tersedia untuk tagihan ini.</p>
                  </div>
                ) : (
                  methods.map((m: any) => {
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
                  })
                )}
              </div>
              <div className="mt-3 flex items-center gap-2 text-[11px] text-muted-foreground px-1">
                <ShieldCheck size={14} className="text-success" />
                Transaksi dijamin aman & terenkripsi.
              </div>
            </div>
          )}
        </div>

        {/* Sticky pay bar — compact */}
        {unpaid.length > 0 && (
          <div className="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md px-3 pb-3 pt-2 bg-gradient-to-t from-background via-background to-background/0 z-40">
            <div className="rounded-xl border border-border bg-card shadow-[var(--shadow-card)] px-3 py-2 flex items-center gap-3">
              <div className="min-w-0 flex-1">
                <p className="text-[9px] text-muted-foreground font-semibold uppercase tracking-wider leading-none">
                  Total · {picked.size}/{unpaid.length}
                </p>
                <p className="text-base font-extrabold text-foreground leading-tight mt-0.5">
                  {fmt(pickedTotal)}
                </p>
              </div>
              <button
                onClick={() => {
                  const items = Array.from(picked);
                  if (items.length === 0 || !selectedMethod) return;
                  checkoutMutation.mutate({ 
                    installmentIds: items, 
                    methodId: selectedMethod.payment_method_id 
                  });
                }}
                disabled={picked.size === 0 || !method || checkoutMutation.isPending}
                className="shrink-0 px-5 py-2.5 rounded-xl text-primary-foreground font-bold text-sm shadow-[var(--shadow-glow)] disabled:opacity-50 transition active:scale-[0.98] flex items-center justify-center min-w-[120px]"
                style={{ background: "var(--gradient-card)" }}
              >
                {checkoutMutation.isPending ? <Loader2 className="animate-spin" size={18} /> : "Lanjutkan"}
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

function CheckBox({ checked, disabled }: { checked: boolean; disabled?: boolean }) {
  return (
    <span
      className={`w-6 h-6 rounded-md border-2 flex items-center justify-center transition ${
        checked
          ? disabled
            ? "bg-success border-success"
            : "bg-primary border-primary"
          : "bg-transparent border-muted-foreground/40"
      }`}
    >
      {checked && <Check size={14} className="text-white" strokeWidth={3} />}
    </span>
  );
}

function ActiveSantriPill() {
  const { active } = useSantri();
  return (
    <div
      className="relative overflow-hidden rounded-3xl p-4 text-primary-foreground shadow-[var(--shadow-glow)]"
      style={{ background: "var(--gradient-hero)" }}
    >
      <div className="absolute -top-12 -right-8 w-40 h-40 rounded-full bg-white/10 blur-3xl" />
      <div className="absolute -bottom-10 -left-6 w-32 h-32 rounded-full bg-white/5 blur-2xl" />

      <div className="relative flex items-center gap-3">
        <div className="w-12 h-12 rounded-2xl bg-white/15 border border-white/20 backdrop-blur text-primary-foreground flex items-center justify-center shrink-0 font-bold shadow-[var(--shadow-soft)]">
          {active?.initials}
        </div>
        <div className="min-w-0 flex-1">
          <p className="text-[10px] uppercase tracking-widest text-white/70 font-semibold">
            Nama Siswa / Santri
          </p>
          <p className="text-sm font-extrabold text-white truncate leading-tight mt-0.5">
            {active?.name?.toUpperCase()}
          </p>
          <p className="text-[11px] text-white/70 mt-0.5">
            {active?.jenjang} · Kelas {active?.className} · ••{active?.cardSuffix}
          </p>
        </div>
        <SantriSwitcherTrigger>
          <span className="px-2.5 py-1 rounded-full bg-white/15 border border-white/20 text-[10px] font-semibold backdrop-blur flex items-center gap-1 shrink-0">
            Ganti
          </span>
        </SantriSwitcherTrigger>
      </div>
    </div>
  );
}
