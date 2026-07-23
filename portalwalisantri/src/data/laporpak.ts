// Data Store & Helper for Lapor Pak (Public Form & Admin UI)

export type KendalaType =
  | "Kendala Login"
  | "Kendala Download dan Install"
  | "Kendala Tagihan Belum Sesuai"
  | "Kendala Nominal Saldo Belum Sesuai"
  | "Kendala Lainnya";

export const KENDALA_OPTIONS: KendalaType[] = [
  "Kendala Login",
  "Kendala Download dan Install",
  "Kendala Tagihan Belum Sesuai",
  "Kendala Nominal Saldo Belum Sesuai",
  "Kendala Lainnya",
];

export type ReportStatus = "Kendala" | "Teratasi";

export interface StudentMaster {
  id: string;
  name: string;
  school: string;
  class: string;
  displayLabel: string; // "Nama - Sekolah - Kelas"
  parentName: string;
  parentPhone: string;
}

export interface LaporPakReport {
  id: string;
  studentId: string;
  studentName: string;
  school: string;
  className: string;
  parentName: string;
  parentPhone: string;
  isParentUpdated: boolean;
  kendala: KendalaType;
  keterangan?: string;
  timestamp: string; // ISO String or formatted
  status: ReportStatus;
}

// Master Data Siswa untuk Pencarian (Nama - Sekolah - Kelas)
export const MOCK_STUDENTS: StudentMaster[] = [
  {
    id: "S001",
    name: "Ahmad Fauzi",
    school: "SMA Islam Cahaya",
    class: "XII IPA 1",
    displayLabel: "Ahmad Fauzi - SMA Islam Cahaya - XII IPA 1",
    parentName: "Budi Santoso",
    parentPhone: "081234567890",
  },
  {
    id: "S002",
    name: "Siti Nurhaliza",
    school: "SMP Islam Cahaya",
    class: "IX B",
    displayLabel: "Siti Nurhaliza - SMP Islam Cahaya - IX B",
    parentName: "Haji Sulaiman",
    parentPhone: "085711223344",
  },
  {
    id: "S003",
    name: "Muhammad Rizky",
    school: "SDIT Cahaya Tasbih",
    class: "VI Tahfidz",
    displayLabel: "Muhammad Rizky - SDIT Cahaya Tasbih - VI Tahfidz",
    parentName: "Dewi Kusumawati",
    parentPhone: "081987654321",
  },
  {
    id: "S004",
    name: "Zahra Amalia",
    school: "SMA Islam Cahaya",
    class: "XI IPS 2",
    displayLabel: "Zahra Amalia - SMA Islam Cahaya - XI IPS 2",
    parentName: "Agus Pratama",
    parentPhone: "081399887766",
  },
  {
    id: "S005",
    name: "Faris Al-Faruq",
    school: "SMP Islam Cahaya",
    class: "VIII A",
    displayLabel: "Faris Al-Faruq - SMP Islam Cahaya - VIII A",
    parentName: "Rudi Hermawan",
    parentPhone: "082155443322",
  },
];

const INITIAL_REPORTS: LaporPakReport[] = [
  {
    id: "LP-1001",
    studentId: "S001",
    studentName: "Ahmad Fauzi",
    school: "SMA Islam Cahaya",
    className: "XII IPA 1",
    parentName: "Budi Santoso",
    parentPhone: "081234567890",
    isParentUpdated: false,
    kendala: "Kendala Login",
    keterangan: "Lupa kata sandi dan nomor WA lama sudah tidak aktif untuk reset OTP.",
    timestamp: "2026-07-23 07:15",
    status: "Kendala",
  },
  {
    id: "LP-1002",
    studentId: "S002",
    studentName: "Siti Nurhaliza",
    school: "SMP Islam Cahaya",
    className: "IX B",
    parentName: "Haji Sulaiman",
    parentPhone: "085711223344",
    isParentUpdated: false,
    kendala: "Kendala Tagihan Belum Sesuai",
    keterangan: "Tagihan bulan Juli tercatat ganda di portal.",
    timestamp: "2026-07-22 14:30",
    status: "Teratasi",
  },
  {
    id: "LP-1003",
    studentId: "S003",
    studentName: "Muhammad Rizky",
    school: "SDIT Cahaya Tasbih",
    className: "VI Tahfidz",
    parentName: "Dewi Kusumawati",
    parentPhone: "081987654321",
    isParentUpdated: true,
    kendala: "Kendala Nominal Saldo Belum Sesuai",
    keterangan: "Topup saldo via transfer bank Rp 200.000 belum masuk.",
    timestamp: "2026-07-23 08:05",
    status: "Kendala",
  },
  {
    id: "LP-1004",
    studentId: "S004",
    studentName: "Zahra Amalia",
    school: "SMA Islam Cahaya",
    className: "XI IPS 2",
    parentName: "Agus Pratama",
    parentPhone: "081399887766",
    isParentUpdated: false,
    kendala: "Kendala Download dan Install",
    keterangan: "Gagal install APK di HP Android versi 14.",
    timestamp: "2026-07-21 10:12",
    status: "Teratasi",
  },
];

const STORAGE_KEY = "cahayatasbih_laporpak_reports";

type Listener = () => void;
const listeners = new Set<Listener>();

export function getLaporPakReports(): LaporPakReport[] {
  if (typeof window === "undefined") return INITIAL_REPORTS;
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(INITIAL_REPORTS));
      return INITIAL_REPORTS;
    }
    return JSON.parse(raw);
  } catch (e) {
    console.error("Failed to parse LaporPak reports", e);
    return INITIAL_REPORTS;
  }
}

export function subscribeLaporPak(listener: Listener): () => void {
  listeners.add(listener);
  return () => {
    listeners.delete(listener);
  };
}

function notify() {
  listeners.forEach((fn) => fn());
}

export function addLaporPakReport(report: Omit<LaporPakReport, "id" | "timestamp" | "status">): LaporPakReport {
  const current = getLaporPakReports();
  const now = new Date();
  const dateStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}-${String(
    now.getDate()
  ).padStart(2, "0")} ${String(now.getHours()).padStart(2, "0")}:${String(now.getMinutes()).padStart(2, "0")}`;

  const newReport: LaporPakReport = {
    ...report,
    id: `LP-${Math.floor(1000 + Math.random() * 9000)}`,
    timestamp: dateStr,
    status: "Kendala",
  };

  const updated = [newReport, ...current];
  if (typeof window !== "undefined") {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(updated));
  }
  notify();
  return newReport;
}

export function toggleLaporPakStatus(id: string): LaporPakReport[] {
  const current = getLaporPakReports();
  const updated = current.map((r) => {
    if (r.id === id) {
      const nextStatus: ReportStatus = r.status === "Kendala" ? "Teratasi" : "Kendala";
      return { ...r, status: nextStatus };
    }
    return r;
  });

  if (typeof window !== "undefined") {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(updated));
  }
  notify();
  return updated;
}

export function deleteLaporPakReport(id: string): LaporPakReport[] {
  const current = getLaporPakReports();
  const updated = current.filter((r) => r.id !== id);
  if (typeof window !== "undefined") {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(updated));
  }
  notify();
  return updated;
}
