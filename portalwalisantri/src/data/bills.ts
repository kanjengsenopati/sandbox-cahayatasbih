export type Installment = {
  id: string;
  label: string;
  amount: number;
  paid: boolean;
};

export type Bill = {
  id: string;
  name: string;
  shortName: string;
  category: string;
  total: number;
  paid: number;
  due?: string;
  installments: Installment[];
};

const months = [
  "Januari", "Februari", "Maret", "April", "Mei", "Juni",
  "Juli", "Agustus", "September", "Oktober", "November", "Desember",
];

const monthly = (prefix: string, perMonth: number, paidCount: number, year = 2026) =>
  months.map((m, i) => ({
    id: `${prefix}-${i}`,
    label: `Tagihan ${m} ${year}`,
    amount: perMonth,
    paid: i < paidCount,
  }));

export const BILLS: Bill[] = [
  {
    id: "b1",
    name: "BIAYA APLIKASI CT - SMP 2025/2026",
    shortName: "Biaya Aplikasi",
    category: "Pendaftaran",
    total: 60000,
    paid: 50000,
    due: "Jatuh tempo 20 Mei",
    installments: [
      { id: "b1-1", label: "Pendaftaran Awal", amount: 25000, paid: true },
      { id: "b1-2", label: "Aktivasi Akun", amount: 15000, paid: true },
      { id: "b1-3", label: "Kartu Santri Digital", amount: 10000, paid: true },
      { id: "b1-4", label: "Materai & Administrasi", amount: 10000, paid: false },
    ],
  },
  {
    id: "b2",
    name: "SYAHRIAH SMP 2025/2026",
    shortName: "Syahriah",
    category: "Bulanan",
    total: 6000000,
    paid: 5500000,
    due: "Jatuh tempo 25 Mei",
    installments: monthly("b2", 500000, 11),
  },
  {
    id: "b3",
    name: "UANG MAKAN MEI 2026",
    shortName: "Uang Makan",
    category: "Bulanan",
    total: 750000,
    paid: 0,
    due: "Jatuh tempo 28 Mei",
    installments: [
      { id: "b3-1", label: "Sarapan (30 hari)", amount: 250000, paid: false },
      { id: "b3-2", label: "Makan Siang (30 hari)", amount: 300000, paid: false },
      { id: "b3-3", label: "Makan Malam (30 hari)", amount: 200000, paid: false },
    ],
  },
  {
    id: "b4",
    name: "SERAGAM & PERLENGKAPAN 2025/2026",
    shortName: "Seragam",
    category: "Tahunan",
    total: 1250000,
    paid: 1250000,
    installments: [
      { id: "b4-1", label: "Seragam Harian (3 set)", amount: 600000, paid: true },
      { id: "b4-2", label: "Seragam Olahraga", amount: 250000, paid: true },
      { id: "b4-3", label: "Seragam Pondok", amount: 300000, paid: true },
      { id: "b4-4", label: "Atribut & Logo", amount: 100000, paid: true },
    ],
  },
  {
    id: "b5",
    name: "BUKU PAKET SMP 2025/2026",
    shortName: "Buku Paket",
    category: "Tahunan",
    total: 850000,
    paid: 600000,
    due: "Jatuh tempo 30 Mei",
    installments: [
      { id: "b5-1", label: "Buku Mata Pelajaran Umum", amount: 400000, paid: true },
      { id: "b5-2", label: "Buku Tahfidz & Tajwid", amount: 200000, paid: true },
      { id: "b5-3", label: "LKS Semester Genap", amount: 150000, paid: false },
      { id: "b5-4", label: "Buku Pendamping", amount: 100000, paid: false },
    ],
  },
  {
    id: "b6",
    name: "RIHLAH AKHIR TAHUN 2026",
    shortName: "Rihlah",
    category: "Kegiatan",
    total: 450000,
    paid: 200000,
    due: "Jatuh tempo 10 Juni",
    installments: [
      { id: "b6-1", label: "Transportasi Bus", amount: 150000, paid: true },
      { id: "b6-2", label: "Tiket Masuk Wisata", amount: 100000, paid: true },
      { id: "b6-3", label: "Konsumsi", amount: 100000, paid: false },
      { id: "b6-4", label: "Asuransi & Souvenir", amount: 100000, paid: false },
    ],
  },
  {
    id: "b7",
    name: "WISUDA TAHFIDZ 2026",
    shortName: "Wisuda Tahfidz",
    category: "Kegiatan",
    total: 350000,
    paid: 350000,
    installments: [
      { id: "b7-1", label: "Toga & Selempang", amount: 150000, paid: true },
      { id: "b7-2", label: "Sertifikat & Plakat", amount: 100000, paid: true },
      { id: "b7-3", label: "Konsumsi & Dokumentasi", amount: 100000, paid: true },
    ],
  },
];

export const fmtIDR = (n: number) => "Rp" + new Intl.NumberFormat("id-ID").format(n);

export const CATEGORY_ORDER = ["Pendaftaran", "Bulanan", "Tahunan", "Kegiatan"];
