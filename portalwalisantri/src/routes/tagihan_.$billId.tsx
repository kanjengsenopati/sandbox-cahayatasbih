import { createFileRoute, useNavigate, useParams } from "@tanstack/react-router";
import { useEffect, useMemo, useState } from "react";
import { ArrowLeft, Check, CheckCircle2, Loader2 } from "lucide-react";
import { useSantri } from "@/contexts/SantriContext";
import { SantriSwitcherTrigger } from "@/components/SantriSwitcher";
import { useQuery, useMutation } from "@tanstack/react-query";
import { fetchBillDetail, postCheckout, fetchPaymentMethods } from "@/lib/api";
import { Building2, CreditCard, Smartphone, ShieldCheck, Download } from "lucide-react";
import { Text } from "@/components/Text";

export const Route = createFileRoute("/tagihan_/$billId")({
  component: BillDetail,
  head: () => ({
    meta: [{ title: `Detail Tagihan — SantriPay` }],
  }),
});

const fmt = (n: number) =>
  new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(n);

const toTitleCase = (str: string) => {
  if (!str) return "";
  return str.split(" ").map(w => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase()).join(" ");
};

const formatThousand = (n: number | undefined | null) => {
  if (n === undefined || n === null) return "";
  if (n === 0) return "";
  return new Intl.NumberFormat("id-ID").format(n);
};

function BillDetail() {
  const { billId } = useParams({ from: "/tagihan_/$billId" });
  const navigate = useNavigate();
  const { active, isLoading: isLoadingSantri } = useSantri();
  const [picked, setPicked] = useState<Set<string>>(new Set());
  const [customAmounts, setCustomAmounts] = useState<Record<string, number>>({});

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
    
    const mappedInstallments = detailData.bills.map((d: any) => ({
      id: String(d.id),
      label: d.translated_month ? `${toTitleCase(d.translated_month)} ${d.year}` : toTitleCase(d.name || ''),
      month: d.translated_month || '',
      monthNum: Number(d.month),
      year: Number(d.year),
      amount: Number(d.remaining_amount ?? d.amount),
      originalAmount: Number(d.amount),
      paidAmount: Number(d.paid_amount ?? 0),
      paid: d.status === "PAID",
      isPendingConfirmation: !!d.is_pending_confirmation,
    }));

    // Sort installments in academic year order: July (7) to June (6)
    mappedInstallments.sort((a: any, b: any) => {
      const aNum = Number(a.monthNum);
      const bNum = Number(b.monthNum);
      
      // Fallback if month values are invalid or non-numeric
      if (isNaN(aNum) || isNaN(bNum) || aNum < 1 || aNum > 12 || bNum < 1 || bNum > 12) {
        return a.id.localeCompare(b.id);
      }

      // Academic index: July (7) is 0, December (12) is 5, January (1) is 6, June (6) is 11
      const aIndex = aNum >= 7 ? aNum - 7 : aNum + 5;
      const bIndex = bNum >= 7 ? bNum - 7 : bNum + 5;

      if (a.year !== b.year) {
        return a.year - b.year;
      }
      return aIndex - bIndex;
    });

    return {
      id: billId,
      name: detailData.billType.name,
      shortName: detailData.billType.name,
      academicYear: detailData.academic_year_name || detailData.billType.academic_year?.name || '',
      total: detailData.summary.total,
      paid: detailData.summary.paid,
      installments: mappedInstallments,
    };
  }, [detailData]);

  const [paymentOptions, setPaymentOptions] = useState<Record<string, "LUNAS" | "ANGSUR">>({});

  useEffect(() => {
    if (bill?.installments) {
      const initialAmounts: Record<string, number> = {};
      const initialOptions: Record<string, "LUNAS" | "ANGSUR"> = {};
      bill.installments.forEach((it) => {
        if (!it.paid && !it.isPendingConfirmation) {
          initialAmounts[it.id] = it.amount;
          initialOptions[it.id] = "LUNAS";
        }
      });
      setCustomAmounts(initialAmounts);
      setPaymentOptions(initialOptions);
    }
  }, [bill]);

  const pickedTotal = useMemo(
    () => bill?.installments
      .filter((i: any) => picked.has(i.id))
      .reduce((a: number, b: any) => {
        const amt = detailData?.billType?.payment_input_type === 'FREE'
          ? (customAmounts[b.id] ?? b.amount)
          : b.amount;
        return a + amt;
      }, 0) || 0,
    [picked, bill?.installments, customAmounts, detailData?.billType?.payment_input_type],
  );

  const [method, setMethod] = useState<string>("");

  const { data: methodsRes, isLoading: isLoadingMethods } = useQuery({
    queryKey: ["payment-methods", "BILL", Array.from(picked), bill?.installments?.[0]?.id],
    queryFn: async () => {
      const billIds =
        Array.from(picked).length > 0
          ? Array.from(picked)
          : bill?.installments?.[0]?.id
          ? [bill.installments[0].id]
          : [];
      const res = await fetchPaymentMethods({ type: "BILL", bill_ids: billIds });
      return res.data;
    },
    enabled: !!bill,
  });

  const methods = useMemo(() => {
    if (!methodsRes) return [];
    return methodsRes.flatMap((m: any) => {
      if (m.type === "BALANCE") {
        const isSaldoVisible = (active as any)?.show_pwa_saldo !== false;
        const allowSaldoPayment = (active as any)?.allow_pwa_saldo_payment !== false;
        if (!isSaldoVisible || !allowSaldoPayment || m.is_disabled) {
          return [];
        }
        const studentBalance = active?.saldo ?? 0;
        if (studentBalance < pickedTotal) {
          return [];
        }
        return [{
          id: m.id,
          payment_method_id: m.id,
          label: m.name,
          desc: `Bayar instan menggunakan Saldo Santri (Saldo: ${fmt(studentBalance)})`,
          icon: CreditCard,
          fee: 0,
          account: "-",
          holder: "-",
        }];
      }
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
  }, [methodsRes, active?.saldo, pickedTotal]);

  const selectedMethod = useMemo(() => methods.find((m: any) => m.id === method), [method, methods]);

  useEffect(() => {
    if (methods.length > 0) {
      if (!method || !methods.some((m: any) => m.id === method)) {
        setMethod(methods[0].id);
      }
    } else {
      setMethod("");
    }
  }, [methods, method]);

  const checkoutMutation = useMutation({
    mutationFn: async ({ 
      installmentIds, 
      methodId, 
      customAmounts 
    }: { 
      installmentIds: string[], 
      methodId: string, 
      customAmounts?: Record<string, number> 
    }) => {
      const res = await postCheckout({
        bill_ids: installmentIds,
        payment_method_id: methodId,
        custom_amounts: customAmounts,
      });
      return res.data;
    },
    onSuccess: (data) => {
      navigate({ to: "/pembayaran/$payId", params: { payId: String(data.transaction.id) } });
    },
  });

  const unpaid = useMemo(() => bill?.installments.filter((i: any) => !i.paid && !i.isPendingConfirmation) || [], [bill]);
  const allUnpaidPicked = unpaid.length > 0 && unpaid.every((i: any) => picked.has(i.id));

  const togglePick = (id: string) =>
    setPicked((s) => {
      const n = new Set(s);
      if (n.has(id)) {
        const idx = unpaid.findIndex((item: any) => item.id === id);
        if (idx !== -1) {
          for (let i = idx; i < unpaid.length; i++) {
            n.delete(unpaid[i].id);
          }
        }
      } else {
        n.add(id);
      }
      return n;
    });

  const togglePickAll = () =>
    setPicked(allUnpaidPicked ? new Set() : new Set(unpaid.map((i) => i.id)));



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

        {/* Payment Methods Section (2 Columns, directly under Tagihan main card) */}
        <div className="px-4 pt-4">
          <Text.Label className="block mb-2 px-1 text-slate-400">
            METODE PEMBAYARAN
          </Text.Label>

          {isLoadingMethods ? (
            <div className="bg-white rounded-[24px] border border-slate-100 p-6 flex flex-col items-center justify-center gap-2 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
              <Loader2 className="animate-spin text-blue-600" size={24} />
              <Text.Caption className="text-slate-500 font-semibold not-italic">Memuat metode pembayaran...</Text.Caption>
            </div>
          ) : methods.length === 0 ? (
            <div className="bg-white rounded-[24px] border border-slate-100 p-6 text-center shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
              <Text.Caption className="text-slate-400 not-italic">Tidak ada metode pembayaran tersedia untuk tagihan ini.</Text.Caption>
            </div>
          ) : (
            <div className="flex flex-col gap-2.5">
              {methods.map((m: any) => {
                const Icon = m.icon;
                const isActive = method === m.id;
                return (
                  <button
                    key={m.id}
                    type="button"
                    onClick={() => setMethod(m.id)}
                    className={`relative flex items-center justify-between p-3.5 rounded-[22px] transition-all text-left border-2 ${
                      isActive
                        ? "border-blue-600 bg-blue-50/50 shadow-[0_8px_30px_rgb(37,99,235,0.10)] ring-1 ring-blue-600/30"
                        : "border-slate-100 bg-white hover:bg-slate-50/80 shadow-[0_8px_30px_rgb(0,0,0,0.04)]"
                    }`}
                  >
                    {/* Left: Icon + (Title & Subtitle aligned vertically) */}
                    <div className="flex items-center gap-3 min-w-0 flex-1 pr-2">
                      <div
                        className={`w-10 h-10 rounded-[14px] flex items-center justify-center shrink-0 transition-all ${
                          isActive
                            ? "bg-blue-600 text-white shadow-xs"
                            : "bg-slate-100 text-blue-600"
                        }`}
                      >
                        <Icon size={18} strokeWidth={2} />
                      </div>

                      <div className="flex-1 min-w-0">
                        <Text.Body className="font-bold text-slate-900 leading-tight text-sm truncate">
                          {m.label}
                        </Text.Body>
                        <Text.Caption className="text-[11px] text-slate-500 mt-0.5 line-clamp-1 leading-snug not-italic block truncate">
                          {m.desc} · {m.fee === 0 ? "Gratis" : `Biaya ${fmt(m.fee)}`}
                        </Text.Caption>
                      </div>
                    </div>

                    {/* Right: Radio Selection Indicator */}
                    <div
                      className={`w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all shrink-0 ${
                        isActive ? "border-blue-600 bg-blue-600" : "border-slate-300 bg-white"
                      }`}
                    >
                      {isActive && <div className="w-2 h-2 rounded-full bg-white" />}
                    </div>
                  </button>
                );
              })}
            </div>
          )}

          <div className="mt-2.5 flex items-center gap-2 text-[11px] text-slate-400 px-1">
            <ShieldCheck size={14} className="text-emerald-600 shrink-0" />
            <Text.Caption className="not-italic text-slate-500">Transaksi dijamin aman & terenkripsi.</Text.Caption>
          </div>
        </div>

        {/* Installments Detail List */}
        <div className="px-5 pt-2">
          <div className="mt-5">
            <div className="flex items-center gap-3">
              <h3 className="text-base font-bold text-foreground">
                Detail {bill.shortName}
              </h3>
              {bill.academicYear && (
                <span className="px-3 py-1 rounded-full bg-primary text-white text-[11px] font-bold tracking-tight shadow-sm">
                  {bill.academicYear}
                </span>
              )}
            </div>

            {unpaid.length > 0 && (
              <button
                onClick={togglePickAll}
                className="mt-4 flex items-center gap-3 px-1"
              >
                <CheckBox checked={allUnpaidPicked} />
                <span className="text-sm font-bold text-foreground">Bayar Semua</span>
              </button>
            )}

            <div className="mt-4 space-y-3">
              {bill.installments.map((it) => {
                const checked = picked.has(it.id);
                const isInstallmentPaid = it.paid;
                const isInstallmentPending = it.isPendingConfirmation;
                
                const idx = unpaid.findIndex((item: any) => item.id === it.id);
                const isOrderDisabled = idx !== -1 && idx > 0 && !picked.has(unpaid[idx - 1].id);
                const isRowDisabled = isInstallmentPaid || isInstallmentPending || isOrderDisabled;

                return (
                  <div
                    key={it.id}
                    onClick={() => !isRowDisabled && togglePick(it.id)}
                    role={isRowDisabled ? undefined : "button"}
                    className={`relative flex flex-col pl-4 pr-3 py-3.5 rounded-2xl bg-secondary/70 border transition ${
                      !it.paid && !it.isPendingConfirmation && checked
                        ? "border-primary ring-1 ring-primary/40"
                        : "border-border"
                    } ${isRowDisabled ? "opacity-50 cursor-not-allowed" : "cursor-pointer active:scale-[0.99]"}`}
                  >
                    <span
                      className={`absolute left-0 top-3 bottom-3 w-1 rounded-r-full ${
                        it.paid ? "bg-success" : it.isPendingConfirmation ? "bg-[oklch(0.78_0.16_75)]" : "bg-primary"
                      }`}
                    />

                    <div className="flex items-center gap-3 w-full">
                      <span className="shrink-0">
                        <CheckBox checked={it.paid || checked} disabled={isRowDisabled} />
                      </span>

                      <div className="flex-1 min-w-0">
                        {it.label && (
                          <p className="text-[11px] font-bold text-primary tracking-tight mb-1">{toTitleCase(it.label)}</p>
                        )}
                        <p className="text-base font-bold text-foreground tabular-nums leading-tight">{fmt(it.amount)}</p>
                        {it.paidAmount > 0 && !it.paid && (
                          <p className="text-[10px] text-muted-foreground mt-0.5">Sisa dari {fmt(it.originalAmount)}</p>
                        )}
                      </div>

                      {it.paid ? (
                        <div className="shrink-0 flex items-center gap-2">
                          <a
                            href={`/ct-mobile/bill-receipt/${it.id}`}
                            target="_blank"
                            className="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-emerald-700 bg-emerald-50 hover:bg-emerald-100 hover:text-emerald-800 transition-all border border-emerald-200 shadow-[0_2px_8px_rgb(16,185,129,0.15)] font-bold text-xs active:scale-95"
                            title="Download Kuitansi"
                            onClick={(e) => e.stopPropagation()}
                          >
                            <Download size={14} strokeWidth={3} />
                            <span>Unduh Kuitansi</span>
                          </a>
                          <span className="px-5 py-2.5 rounded-xl bg-success text-white text-xs font-bold shadow-sm">
                            Lunas
                          </span>
                        </div>
                      ) : it.isPendingConfirmation ? (
                        <span className="shrink-0 px-3 py-2.5 rounded-xl bg-[oklch(0.78_0.16_75)] text-white text-xs font-bold">
                          Menunggu Verifikasi
                        </span>
                      ) : (
                        <button
                          onClick={(e) => {
                            e.stopPropagation();
                            if (isRowDisabled) return;
                            if (!selectedMethod) {
                              togglePick(it.id);
                              return;
                            }
                            const amt = customAmounts[it.id] ?? it.amount;
                            if (amt <= 0) return;
                            checkoutMutation.mutate({ 
                              installmentIds: [it.id], 
                              methodId: selectedMethod.payment_method_id,
                              customAmounts: detailData?.billType?.payment_input_type === 'FREE' ? { [it.id]: amt } : undefined
                            });
                          }}
                          disabled={isRowDisabled || checkoutMutation.isPending || (checked && (customAmounts[it.id] ?? 0) <= 0)}
                          className="shrink-0 px-4 py-2.5 rounded-xl text-xs font-bold text-primary-foreground shadow-[var(--shadow-soft)] active:scale-95 transition flex items-center justify-center min-w-[100px]"
                          style={{ background: "var(--gradient-card)" }}
                        >
                          {checkoutMutation.isPending ? <Loader2 className="animate-spin" size={14} /> : "Bayar Sekarang"}
                        </button>
                      )}
                    </div>

                    {/* Custom Amount input field for FREE input type */}
                    {detailData?.billType?.payment_input_type === 'FREE' && checked && (
                      <div className="mt-3 pt-3 border-t border-border w-full" onClick={(e) => e.stopPropagation()}>
                        {/* Segment selector Lunas / Angsur */}
                        <div className="flex gap-2 mb-3">
                          <button
                            type="button"
                            onClick={() => {
                              setPaymentOptions(prev => ({ ...prev, [it.id]: 'LUNAS' }));
                              setCustomAmounts(prev => ({ ...prev, [it.id]: it.amount }));
                            }}
                            className={`flex-1 py-2 text-xs font-bold rounded-xl border transition ${
                              paymentOptions[it.id] !== 'ANGSUR'
                                ? "bg-primary text-white border-primary shadow-sm"
                                : "bg-secondary text-slate-600 border-border hover:bg-slate-100"
                            }`}
                          >
                            Lunas
                          </button>
                          <button
                            type="button"
                            onClick={() => {
                              setPaymentOptions(prev => ({ ...prev, [it.id]: 'ANGSUR' }));
                            }}
                            className={`flex-1 py-2 text-xs font-bold rounded-xl border transition ${
                              paymentOptions[it.id] === 'ANGSUR'
                                ? "bg-primary text-white border-primary shadow-sm"
                                : "bg-secondary text-slate-600 border-border hover:bg-slate-100"
                            }`}
                          >
                            Angsur
                          </button>
                        </div>

                        {paymentOptions[it.id] === 'ANGSUR' && (
                          <>
                            <Text.Label className="block mb-1">
                              Nominal Cicilan / Angsuran
                            </Text.Label>
                            <div className="relative flex items-center mt-1.5">
                              <span className="absolute left-3.5 text-slate-500 font-semibold text-sm">Rp</span>
                              <input
                                type="text"
                                value={formatThousand(customAmounts[it.id])}
                                onChange={(e) => {
                                  const clean = e.target.value.replace(/\D/g, "");
                                  const numVal = clean === "" ? 0 : parseInt(clean);
                                  const val = Math.min(it.amount, Math.max(0, numVal));
                                  setCustomAmounts(prev => ({
                                    ...prev,
                                    [it.id]: val
                                  }));
                                }}
                                className="w-full pl-9 pr-3 py-2 bg-background border border-border rounded-xl font-bold text-foreground text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/40"
                                placeholder="Masukkan nominal"
                              />
                            </div>
                            {((customAmounts[it.id] ?? 0) <= 0) && (
                              <Text.Caption className="text-red-600 mt-1 block font-semibold not-italic">
                                Nominal harus lebih dari Rp 0
                              </Text.Caption>
                            )}
                            {(customAmounts[it.id] > 0 && customAmounts[it.id] < it.amount) && (
                              <Text.Caption className="text-emerald-600 mt-1 block font-semibold not-italic">
                                Sisa Angsuran akan menjadi {fmt(it.amount - customAmounts[it.id])}
                              </Text.Caption>
                            )}
                          </>
                        )}
                      </div>
                    )}
                  </div>
                );
              })}
            </div>
          </div>
        </div>
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

                  // Construct custom amounts payload if payment_input_type is FREE
                  const reqCustomAmounts: Record<string, number> = {};
                  if (detailData?.billType?.payment_input_type === 'FREE') {
                    items.forEach((id) => {
                      reqCustomAmounts[id] = customAmounts[id] ?? 0;
                    });
                  }

                  checkoutMutation.mutate({ 
                    installmentIds: items, 
                    methodId: selectedMethod.payment_method_id,
                    customAmounts: detailData?.billType?.payment_input_type === 'FREE' ? reqCustomAmounts : undefined
                  });
                }}
                disabled={
                  picked.size === 0 || 
                  !method || 
                  checkoutMutation.isPending ||
                  (detailData?.billType?.payment_input_type === 'FREE' && 
                    Array.from(picked).some((id) => (customAmounts[id] ?? 0) <= 0))
                }
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
