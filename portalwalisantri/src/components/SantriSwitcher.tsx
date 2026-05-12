import { useEffect, useState, type ReactNode } from "react";
import { Check, ChevronDown, Plus, X } from "lucide-react";
import { useSantri, type Santri } from "@/contexts/SantriContext";

const fmt = (n: number) =>
  "Rp" + new Intl.NumberFormat("id-ID").format(n);

/** Compact pill that opens the switcher sheet on click. */
export function SantriSwitcherTrigger({
  variant = "ghost",
  children,
}: {
  variant?: "ghost" | "subtle";
  children?: ReactNode;
}) {
  const [open, setOpen] = useState(false);
  return (
    <>
      <button
        onClick={() => setOpen(true)}
        className={
          variant === "subtle"
            ? "text-[11px] text-white/90 font-semibold underline-offset-2 hover:underline shrink-0"
            : "inline-flex items-center gap-1 text-white/90"
        }
      >
        {children ?? (
          <>
            <span>Ganti</span>
            <ChevronDown size={14} />
          </>
        )}
      </button>
      <SantriSwitcherSheet open={open} onClose={() => setOpen(false)} />
    </>
  );
}

export function SantriSwitcherSheet({
  open,
  onClose,
}: {
  open: boolean;
  onClose: () => void;
}) {
  const { santri, active, setActiveId } = useSantri();

  // Lock background scroll while sheet is open
  useEffect(() => {
    if (!open) return;
    const prev = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    return () => {
      document.body.style.overflow = prev;
    };
  }, [open]);

  if (!open) return null;

  return (
    <div className="fixed inset-0 z-[60] flex items-end justify-center">
      {/* Backdrop */}
      <button
        onClick={onClose}
        aria-label="Tutup"
        className="absolute inset-0 bg-black/50 backdrop-blur-sm animate-in fade-in"
      />

      {/* Sheet */}
      <div className="relative w-[calc(100%-1rem)] max-w-md mx-2 mb-2 bg-background rounded-[1.75rem] pb-4 pt-2.5 shadow-[var(--shadow-card)] animate-in slide-in-from-bottom duration-200">
        <div className="mx-auto w-10 h-1 rounded-full bg-border" />

        <div className="px-4 mt-2.5 flex items-center justify-between">
          <div className="min-w-0">
            <h3 className="text-[15px] font-bold text-foreground leading-tight">Pilih Santri</h3>
            <p className="text-[11px] text-muted-foreground leading-tight">
              Akun wali Anda terhubung ke {santri.length} santri.
            </p>
          </div>
          <button
            onClick={onClose}
            className="w-8 h-8 rounded-lg bg-secondary flex items-center justify-center text-muted-foreground shrink-0"
          >
            <X size={14} />
          </button>
        </div>

        <div className="px-3 mt-3 space-y-1.5 max-h-[55vh] overflow-y-auto">
          {santri.map((s) => (
            <SantriRow
              key={s.id}
              santri={s}
              active={s.id === active.id}
              onPick={() => {
                setActiveId(s.id);
                onClose();
              }}
            />
          ))}

          <button className="w-full mt-1.5 flex items-center justify-center gap-1.5 py-2.5 rounded-xl border-2 border-dashed border-border text-primary text-[13px] font-bold active:scale-[0.99] transition">
            <Plus size={14} /> Tambah Santri
          </button>
        </div>
      </div>
    </div>
  );
}

function SantriRow({
  santri,
  active,
  onPick,
}: {
  santri: Santri;
  active: boolean;
  onPick: () => void;
}) {
  return (
    <button
      onClick={onPick}
      className={`w-full flex items-center gap-2.5 p-2.5 rounded-xl border transition text-left ${
        active
          ? "border-primary bg-accent/60"
          : "border-border bg-card active:bg-secondary"
      }`}
    >
      <div
        className={`w-10 h-10 rounded-xl bg-gradient-to-br ${santri.color} text-primary-foreground text-[13px] font-bold flex items-center justify-center shadow-[var(--shadow-soft)] shrink-0`}
      >
        {santri.initials}
      </div>

      <div className="flex-1 min-w-0">
        <div className="flex items-center gap-1.5">
          <p className="text-[13px] font-bold text-foreground truncate">
            {santri.name}
          </p>
          {active && (
            <span className="inline-flex items-center text-[8px] font-bold uppercase tracking-wider text-primary bg-primary/10 px-1 py-0.5 rounded">
              Aktif
            </span>
          )}
        </div>
        <p className="text-[10px] text-muted-foreground leading-tight">
          {santri.jenjang} · Kelas {santri.className} · ••{santri.cardSuffix}
        </p>
        <div className="mt-0.5 flex items-center gap-2 text-[10px] font-semibold">
          <span className="text-success tabular-nums">Saldo {fmt(santri.saldo)}</span>
          <span className="text-muted-foreground tabular-nums">
            Tagihan {fmt(santri.totalDue)}
          </span>
        </div>
      </div>

      <span
        className={`w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 ${
          active ? "bg-primary border-primary" : "border-border"
        }`}
      >
        {active && <Check size={12} className="text-primary-foreground" strokeWidth={3} />}
      </span>
    </button>
  );
}
