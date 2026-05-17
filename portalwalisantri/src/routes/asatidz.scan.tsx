import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { ArrowLeft, Loader2, Scan, Camera, MapPin, CheckCircle2, UserCheck, ShieldAlert } from "lucide-react";
import { useMutation } from "@tanstack/react-query";
import { postScanBarcode } from "@/lib/api";

export const Route = createFileRoute("/asatidz/scan")({
  component: AsatidzScanPage,
  head: () => ({ meta: [{ title: "Verifikasi Gerbang — CT-Mobile" }] }),
});

function AsatidzScanPage() {
  const navigate = useNavigate();
  const [barcodeToken, setBarcodeToken] = useState("");
  
  // Location & Photos State
  const [lat, setLat] = useState("");
  const [lng, setLng] = useState("");
  const [gpsLoading, setGpsLoading] = useState(false);
  const [gpsError, setGpsError] = useState("");

  const [photoSantri, setPhotoSantri] = useState<string | null>(null);
  const [photoEscort, setPhotoEscort] = useState<string | null>(null);
  
  // Checkout Info
  const [escortName, setEscortName] = useState("");
  const [escortRelation, setEscortRelation] = useState("Orang Tua");
  
  const [errorMsg, setErrorMsg] = useState("");
  const [successMsg, setSuccessMsg] = useState("");

  // Get Geolocation automatically on load
  useEffect(() => {
    getGPSLocation();
  }, []);

  const getGPSLocation = () => {
    if (!navigator.geolocation) {
      setGpsError("Browser tidak mendukung GPS.");
      return;
    }

    setGpsLoading(true);
    setGpsError("");

    navigator.geolocation.getCurrentPosition(
      (position) => {
        setLat(position.coords.latitude.toString());
        setLng(position.coords.longitude.toString());
        setGpsLoading(false);
      },
      (error) => {
        setGpsError("Gagal mengambil GPS: Aktifkan GPS pada gawai Anda.");
        setGpsLoading(false);
      },
      { enableHighAccuracy: true, timeout: 10000 }
    );
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>, type: "santri" | "escort") => {
    const file = e.target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onloadend = () => {
      const base64String = reader.result as string;
      if (type === "santri") {
        setPhotoSantri(base64String);
      } else {
        setPhotoEscort(base64String);
      }
    };
    reader.readAsDataURL(file);
  };

  const scanMutation = useMutation({
    mutationFn: async (data: any) => {
      const res = await postScanBarcode(data);
      return res.data;
    },
    onSuccess: (data: any) => {
      setSuccessMsg(data.message || "Verifikasi gerbang berhasil!");
      setBarcodeToken("");
      setPhotoSantri(null);
      setPhotoEscort(null);
      setEscortName("");
      setTimeout(() => {
        setSuccessMsg("");
        navigate({ to: "/asatidz/dashboard" });
      }, 3000);
    },
    onError: (err: any) => {
      setErrorMsg(err.response?.data?.message || "Gagal memproses verifikasi barcode gerbang.");
    },
  });

  const handleVerifySubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg("");
    setSuccessMsg("");

    if (!barcodeToken.trim()) {
      setErrorMsg("Harap masukkan atau pindai kode barcode.");
      return;
    }

    if (!photoSantri || !photoEscort) {
      setErrorMsg("Verifikasi Keamanan Wajib: Ambil foto santri & foto penjemput/pengantar.");
      return;
    }

    scanMutation.mutate({
      barcode_token: barcodeToken,
      latitude: lat,
      longitude: lng,
      photo_santri: photoSantri,
      photo_escort: photoEscort,
      escort_name: escortName,
      escort_relation: escortRelation,
    });
  };

  return (
    <div className="min-h-screen w-full flex justify-center bg-secondary">
      <div className="relative w-full max-w-md min-h-screen bg-background pb-20">
        
        {/* Top Navbar */}
        <div className="px-6 pt-12 pb-4 flex items-center gap-3 border-b border-slate-100 bg-card">
          <button
            onClick={() => navigate({ to: "/asatidz/dashboard" })}
            className="w-10 h-10 rounded-xl bg-secondary border border-border flex items-center justify-center text-slate-600 active:scale-95"
          >
            <ArrowLeft size={18} />
          </button>
          <div>
            <p className="text-[10px] text-muted-foreground font-bold uppercase tracking-wider">Keamanan Gerbang</p>
            <h1 className="text-base font-bold text-foreground">Scan Perizinan Keluar/Masuk</h1>
          </div>
        </div>

        {/* Scan Body Form */}
        <form onSubmit={handleVerifySubmit} className="p-6 space-y-5">
          {errorMsg && (
            <div className="p-4 bg-rose-50 border border-rose-100 text-rose-700 text-xs font-bold rounded-2xl flex items-start gap-2">
              <ShieldAlert size={16} className="shrink-0 mt-0.5" />
              <p>{errorMsg}</p>
            </div>
          )}

          {successMsg && (
            <div className="p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-bold rounded-2xl flex items-start gap-2">
              <CheckCircle2 size={16} className="shrink-0 mt-0.5 animate-bounce" />
              <p>{successMsg}</p>
            </div>
          )}

          {/* Barcode input field */}
          <div className="bg-card border border-border rounded-3xl p-5 shadow-[var(--shadow-soft)] space-y-4">
            <div>
              <label className="text-[11px] font-bold text-slate-400 mb-2 uppercase tracking-widest block flex items-center gap-1.5">
                <Scan size={14} className="text-[#9b1de8]" /> Input / Tempel Kode Barcode
              </label>
              <input
                type="text"
                required
                value={barcodeToken}
                onChange={(e) => setBarcodeToken(e.target.value)}
                placeholder="Contoh: CT-XXXXXXXX"
                className="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3.5 outline-none text-slate-800 font-mono font-bold text-base focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition uppercase"
              />
            </div>
          </div>

          {/* GPS Coordinates locked */}
          <div className="bg-card border border-border rounded-3xl p-5 shadow-[var(--shadow-soft)] space-y-3">
            <div className="flex justify-between items-center">
              <span className="text-[11px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
                <MapPin size={14} className="text-rose-500" /> Koordinat GPS Gerbang
              </span>
              <button
                type="button"
                onClick={getGPSLocation}
                className="text-[10px] font-bold text-indigo-600 hover:text-indigo-800"
              >
                Segarkan GPS
              </button>
            </div>

            {gpsLoading ? (
              <div className="flex items-center gap-2 text-xs text-slate-500">
                <Loader2 className="animate-spin" size={14} />
                <span>Mengunci koordinat satelit...</span>
              </div>
            ) : gpsError ? (
              <p className="text-[11px] text-rose-500 font-semibold">{gpsError}</p>
            ) : lat && lng ? (
              <div className="grid grid-cols-2 gap-2 text-xs bg-slate-50 rounded-xl p-3 border border-slate-100 font-mono">
                <div>
                  <p className="text-[10px] text-slate-400 uppercase font-sans">Latitude</p>
                  <p className="font-semibold text-slate-700 mt-0.5">{lat}</p>
                </div>
                <div>
                  <p className="text-[10px] text-slate-400 uppercase font-sans">Longitude</p>
                  <p className="font-semibold text-slate-700 mt-0.5">{lng}</p>
                </div>
              </div>
            ) : (
              <p className="text-xs text-amber-600 font-semibold">Menunggu akses lokasi...</p>
            )}
          </div>

          {/* Checkout optional penjemput info */}
          <div className="bg-card border border-border rounded-3xl p-5 shadow-[var(--shadow-soft)] space-y-4">
            <h3 className="text-xs font-bold text-slate-800 uppercase tracking-wide flex items-center gap-1.5">
              👤 Identitas Penjemput (Khusus Scan Keluar)
            </h3>
            
            <div className="grid grid-cols-2 gap-3">
              <div>
                <label className="text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider block">Nama Penjemput</label>
                <input
                  type="text"
                  value={escortName}
                  onChange={(e) => setEscortName(e.target.value)}
                  placeholder="Nama Penjemput"
                  className="w-full bg-slate-50 border border-slate-100 rounded-xl px-3 py-2 text-xs font-semibold focus:bg-white outline-none focus:ring-1 focus:ring-indigo-500/20"
                />
              </div>

              <div>
                <label className="text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider block">Hubungan</label>
                <select
                  value={escortRelation}
                  onChange={(e) => setEscortRelation(e.target.value)}
                  className="w-full bg-slate-50 border border-slate-100 rounded-xl px-3 py-2 text-xs font-semibold focus:bg-white outline-none focus:ring-1 focus:ring-indigo-500/20"
                >
                  <option value="Orang Tua">Orang Tua (Ayah/Ibu)</option>
                  <option value="Keluarga">Keluarga (Paman/Kakak)</option>
                  <option value="Utusan">Utusan Wali</option>
                  <option value="Lainnya">Lainnya</option>
                </select>
              </div>
            </div>
          </div>

          {/* HTML5 Native Camera Snapshot Snapshots */}
          <div className="bg-card border border-border rounded-3xl p-5 shadow-[var(--shadow-soft)] space-y-4">
            <h3 className="text-xs font-bold text-slate-800 uppercase tracking-wide flex items-center gap-1.5">
              📸 Bukti Foto Fisik (Wajib)
            </h3>

            <div className="grid grid-cols-2 gap-4">
              {/* Photo 1: Santri */}
              <div className="flex flex-col items-center">
                <label className="text-[10px] font-bold text-slate-400 mb-2 uppercase tracking-wider text-center">Foto Santri</label>
                <div className="relative w-full aspect-square bg-slate-50 border border-dashed border-slate-200 rounded-2xl overflow-hidden flex flex-col items-center justify-center">
                  {photoSantri ? (
                    <img src={photoSantri} alt="Santri" className="w-full h-full object-cover" />
                  ) : (
                    <Camera size={20} className="text-slate-400" />
                  )}
                  
                  <input
                    type="file"
                    accept="image/*"
                    capture="environment"
                    onChange={(e) => handleFileChange(e, "santri")}
                    className="absolute inset-0 opacity-0 cursor-pointer"
                  />
                </div>
                {photoSantri && (
                  <button
                    type="button"
                    onClick={() => setPhotoSantri(null)}
                    className="text-[10px] text-rose-500 font-bold mt-1.5"
                  >
                    Hapus
                  </button>
                )}
              </div>

              {/* Photo 2: Escort */}
              <div className="flex flex-col items-center">
                <label className="text-[10px] font-bold text-slate-400 mb-2 uppercase tracking-wider text-center">Foto Penjemput</label>
                <div className="relative w-full aspect-square bg-slate-50 border border-dashed border-slate-200 rounded-2xl overflow-hidden flex flex-col items-center justify-center">
                  {photoEscort ? (
                    <img src={photoEscort} alt="Escort" className="w-full h-full object-cover" />
                  ) : (
                    <Camera size={20} className="text-slate-400" />
                  )}
                  
                  <input
                    type="file"
                    accept="image/*"
                    capture="environment"
                    onChange={(e) => handleFileChange(e, "escort")}
                    className="absolute inset-0 opacity-0 cursor-pointer"
                  />
                </div>
                {photoEscort && (
                  <button
                    type="button"
                    onClick={() => setPhotoEscort(null)}
                    className="text-[10px] text-rose-500 font-bold mt-1.5"
                  >
                    Hapus
                  </button>
                )}
              </div>
            </div>
          </div>

          {/* Submit Button */}
          <button
            type="submit"
            disabled={scanMutation.isPending}
            className="w-full py-4 rounded-[20px] bg-gradient-to-r from-indigo-600 to-indigo-800 text-white font-bold text-[14px] shadow-lg shadow-indigo-950/20 active:scale-[0.98] disabled:opacity-70 flex items-center justify-center gap-2"
          >
            {scanMutation.isPending ? (
              <Loader2 className="animate-spin" size={18} />
            ) : (
              <>
                <UserCheck size={18} /> Verifikasi & Buka Gerbang
              </>
            )}
          </button>
        </form>
      </div>
    </div>
  );
}
