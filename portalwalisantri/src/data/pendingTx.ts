// Lightweight localStorage-backed store for pending payments.
// Replaces backend until Lovable Cloud is enabled.

export type PaymentStatus = "pending" | "approved" | "rejected";

export type PendingTx = {
  id: string;
  billId?: string;
  billName: string;
  amount: number; // includes 3-digit unique code
  baseAmount: number;
  uniqueCode: number; // last 3 digits
  bankName: string;
  bankAccount: string;
  bankHolder: string;
  proofDataUrl?: string; // base64 image
  status: PaymentStatus;
  createdAt: string; // ISO
  updatedAt: string; // ISO
  items?: { id: string; label: string; amount: number }[];
};

const KEY = "santripay.pendingTx.v1";
const EVT = "santripay:pendingTx:changed";

function read(): PendingTx[] {
  if (typeof window === "undefined") return [];
  try {
    const raw = localStorage.getItem(KEY);
    return raw ? (JSON.parse(raw) as PendingTx[]) : [];
  } catch {
    return [];
  }
}

function write(list: PendingTx[]) {
  if (typeof window === "undefined") return;
  localStorage.setItem(KEY, JSON.stringify(list));
  window.dispatchEvent(new Event(EVT));
}

export function listPendingTx(): PendingTx[] {
  return read().sort(
    (a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime(),
  );
}

export function getPendingTx(id: string): PendingTx | undefined {
  return read().find((t) => t.id === id);
}

export function createPendingTx(input: Omit<PendingTx, "id" | "status" | "createdAt" | "updatedAt" | "uniqueCode" | "amount"> & { baseAmount: number }) {
  const list = read();
  const uniqueCode = Math.floor(100 + Math.random() * 900); // 3 digits
  const now = new Date().toISOString();
  const tx: PendingTx = {
    ...input,
    id: `PAY-${Date.now().toString(36).toUpperCase()}`,
    uniqueCode,
    amount: input.baseAmount + uniqueCode,
    status: "pending",
    createdAt: now,
    updatedAt: now,
  };
  write([tx, ...list]);
  return tx;
}

export function attachProof(id: string, proofDataUrl: string) {
  const list = read();
  const idx = list.findIndex((t) => t.id === id);
  if (idx === -1) return;
  list[idx] = { ...list[idx], proofDataUrl, updatedAt: new Date().toISOString() };
  write(list);
}

export function setStatus(id: string, status: PaymentStatus) {
  const list = read();
  const idx = list.findIndex((t) => t.id === id);
  if (idx === -1) return;
  list[idx] = { ...list[idx], status, updatedAt: new Date().toISOString() };
  write(list);
}

export function subscribePendingTx(cb: () => void): () => void {
  if (typeof window === "undefined") return () => {};
  const handler = () => cb();
  window.addEventListener(EVT, handler);
  window.addEventListener("storage", handler);
  return () => {
    window.removeEventListener(EVT, handler);
    window.removeEventListener("storage", handler);
  };
}
