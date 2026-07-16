import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { ArrowLeft, Camera, MapPin, Loader2, CheckCircle2, AlertTriangle, ShieldCheck, Sparkles } from "lucide-react";
import { useState, useEffect, useRef } from "react";
import { postMobileCheckin } from "@/lib/api";
import { toast } from "sonner";

export const Route = createFileRoute("/karyawan/presensi")({
  component: EmployeeAttendancePage,
  head: () => ({ meta: [{ title: "Presensi Mandiri — Karyawan" }] }),
});

function EmployeeAttendancePage() {
  const navigate = useNavigate();
  const [photo, setPhoto] = useState<string | null>(null);
  const [location, setLocation] = useState<{ lat: number; lng: number } | null>(null);
  const [isLocating, setIsLocating] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isSuccess, setIsSuccess] = useState(false);
  const videoRef = useRef<HTMLVideoElement>(null);
  const [cameraActive, setCameraActive] = useState(false);

  // Dapatkan lokasi GPS saat masuk halaman
  useEffect(() => {
    getLocation();
  }, []);

  const getLocation = () => {
    if (!navigator.geolocation) {
      toast.error("Geolocation tidak didukung oleh browser Anda.");
      return;
    }

    setIsLocating(true);
    navigator.geolocation.getCurrentPosition(
      (position) => {
        setLocation({
          lat: position.coords.latitude,
          lng: position.coords.longitude,
        });
        setIsLocating(false);
      },
      (error) => {
        toast.error("Gagal mendapatkan lokasi. Pastikan izin GPS aktif.");
        setIsLocating(false);
      },
      { enableHighAccuracy: true }
    );
  };

  // Aktivasi Kamera Wajah
  const startCamera = async () => {
    try {
      setCameraActive(true);
      const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } });
      if (videoRef.current) {
        videoRef.current.srcObject = stream;
      }
    } catch (err) {
      toast.error("Gagal mengakses kamera depan.");
      setCameraActive(false);
    }
  };

  // Ambil Snapshot Foto
  const capturePhoto = () => {
    if (!videoRef.current) return;
    const canvas = document.createElement("canvas");
    canvas.width = 320;
    canvas.height = 240;
    const ctx = canvas.getContext("2d");
    if (ctx) {
      ctx.drawImage(videoRef.current, 0, 0, canvas.width, canvas.height);
      const base64Img = canvas.toDataURL("image/jpeg");
      setPhoto(base64Img);
      
      // Stop camera stream
      const stream = videoRef.current.srcObject as MediaStream;
      if (stream) {
        stream.getTracks().forEach((track) => track.stop());
      }
      setCameraActive(false);
    }
  };

  // Kirim Absen ke Server
  const handleCheckin = async () => {
    if (!location) {
      toast.error("Lokasi GPS diperlukan untuk presensi.");
      return;
    }
    if (!photo) {
      toast.error("Foto wajah diperlukan untuk verifikasi Face Recognition.");
      return;
    }

    setIsSubmitting(true);
    try {
      const res = await postMobileCheckin({
        activity_type: "work",
        latitude: location.lat,
        longitude: location.lng,
        photo: photo,
        activity_name: "Shift Pagi",
      });

      if (res.data.success) {
        toast.success("Presensi berhasil dikirim!");
        setIsSuccess(true);
      } else {
        toast.error(res.data.message || "Gagal melakukan presensi.");
      }
    } catch (err: any) {
      toast.error(err.response?.data?.message || "Terjadi kesalahan koneksi server.");
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="min-h-screen w-full flex justify-center bg-secondary">
      <div className="relative w-full max-w-md min-h-screen bg-background pb-32">
        {/* Header Hero */}
        <div
          className="relative px-6 pt-12 pb-24 rounded-b-[2rem] overflow-hidden"
          style={{ background: "var(--gradient-hero)" }}
        >
          <div className="absolute -top-20 -right-10 w-56 h-56 rounded-full bg-primary-glow/30 blur-3xl" />
          <div className="relative flex items-center gap-3">
            <button
              onClick={() => navigate({ to: "/dashboard" })}
              className="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center text-white"
            >
              <ArrowLeft size={18} />
            </button>
            <div>
              <p className="text-[11px] text-white/70 font-semibold uppercase tracking-wider">Karyawan & Staff</p>
              <p className="text-base font-bold text-white">Presensi Mandiri Mobile</p>
            </div>
          </div>
        </div>

        <section className="px-6 -mt-10 relative z-10 space-y-4">
          {isSuccess ? (
            <div className="bg-card rounded-[24px] border border-border shadow-[var(--shadow-card)] p-8 text-center flex flex-col items-center justify-center">
              <span className="w-16 h-16 rounded-full bg-emerald-500/10 text-emerald-600 flex items-center justify-center mb-4">
                <CheckCircle2 size={36} />
              </span>
              <h3 className="text-base font-bold text-foreground">Presensi Sukses!</h3>
              <p className="text-xs text-muted-foreground mt-2">
                Log kehadiran jam kerja Anda telah diverifikasi oleh sistem melalui Face Recognition & GPS lokasi.
              </p>
              <button
                onClick={() => navigate({ to: "/dashboard" })}
                className="btn btn-primary w-full mt-6 rounded-2xl py-3 font-semibold text-sm"
              >
                Kembali ke Dashboard
              </button>
            </div>
          ) : (
            <>
              {/* GPS Location Card */}
              <div className="bg-card rounded-[24px] border border-border shadow-[var(--shadow-soft)] p-5">
                <div className="flex items-center justify-between mb-4">
                  <h3 className="text-xs font-bold text-slate-400 uppercase tracking-widest">1. Koordinat GPS</h3>
                  <button 
                    onClick={getLocation} 
                    className="text-xs text-primary font-bold hover:underline"
                  >
                    Refresh GPS
                  </button>
                </div>
                
                {isLocating ? (
                  <div className="flex items-center gap-2 text-xs text-muted-foreground">
                    <Loader2 className="animate-spin text-primary" size={16} />
                    <span>Mencari sinyal satelit GPS...</span>
                  </div>
                ) : location ? (
                  <div className="flex items-start gap-3">
                    <span className="p-2 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                      <MapPin size={18} />
                    </span>
                    <div>
                      <p className="text-xs font-bold text-slate-800">Sinyal GPS Terkunci</p>
                      <p className="text-[11px] text-slate-500 mt-1">
                        Lat: {location.lat.toFixed(6)}, Lng: {location.lng.toFixed(6)}
                      </p>
                      <p className="text-[10px] text-emerald-600 font-semibold mt-1 flex items-center gap-0.5">
                        <ShieldCheck size={10} /> Berada di Radius Kantor
                      </p>
                    </div>
                  </div>
                ) : (
                  <div className="flex items-start gap-3 text-red-500">
                    <AlertTriangle size={18} />
                    <div>
                      <p className="text-xs font-bold">Lokasi belum terdeteksi</p>
                      <p className="text-[11px] mt-1 text-slate-500">
                        Harap aktifkan GPS Anda untuk dapat mencatat presensi masuk/pulang.
                      </p>
                    </div>
                  </div>
                )}
              </div>

              {/* Face Recognition Camera Card */}
              <div className="bg-card rounded-[24px] border border-border shadow-[var(--shadow-soft)] p-5">
                <div className="flex items-center justify-between mb-4">
                  <h3 className="text-xs font-bold text-slate-400 uppercase tracking-widest">2. Verifikasi Wajah</h3>
                  <span className="text-[10px] text-muted-foreground font-semibold flex items-center gap-1">
                    <Sparkles size={12} /> Face Matcher
                  </span>
                </div>

                <div className="relative aspect-video rounded-3xl overflow-hidden bg-slate-100 border border-slate-200 flex items-center justify-center">
                  {cameraActive ? (
                    <video 
                      ref={videoRef} 
                      autoPlay 
                      playsInline 
                      className="w-full h-full object-cover scale-x-[-1]"
                    />
                  ) : photo ? (
                    <img 
                      src={photo} 
                      alt="Wajah terverifikasi" 
                      className="w-full h-full object-cover" 
                    />
                  ) : (
                    <div className="text-center p-6 text-muted-foreground flex flex-col items-center">
                      <Camera size={32} className="mb-2" />
                      <p className="text-xs font-semibold">Aktifkan kamera depan untuk verifikasi wajah</p>
                    </div>
                  )}

                  {cameraActive && (
                    <div className="absolute inset-x-0 bottom-4 flex justify-center">
                      <button 
                        onClick={capturePhoto}
                        className="px-5 py-2 rounded-xl bg-white text-slate-800 font-bold text-xs shadow-md active:scale-95 transition-transform"
                      >
                        Ambil Foto Wajah
                      </button>
                    </div>
                  )}
                </div>

                {!cameraActive && !photo && (
                  <button
                    onClick={startCamera}
                    className="btn btn-outline-primary w-full mt-4 rounded-2xl py-3 font-semibold text-xs flex justify-center items-center gap-2"
                  >
                    <Camera size={14} /> Aktifkan Kamera Depan
                  </button>
                )}

                {photo && !cameraActive && (
                  <button
                    onClick={startCamera}
                    className="btn btn-outline-secondary w-full mt-4 rounded-2xl py-3 font-semibold text-xs"
                  >
                    Foto Ulang Wajah
                  </button>
                )}
              </div>

              {/* Action Submit */}
              <button
                onClick={handleCheckin}
                disabled={isSubmitting || !photo || !location}
                className="w-full py-4 rounded-[24px] bg-primary text-white font-extrabold text-sm shadow-[var(--shadow-glow)] hover:shadow-lg disabled:opacity-50 active:scale-[0.98] transition-all duration-300 flex justify-center items-center gap-2"
              >
                {isSubmitting ? (
                  <>
                    <Loader2 className="animate-spin" size={16} />
                    Mencocokkan Wajah & GPS...
                  </>
                ) : (
                  "Kirim Presensi Sekarang"
                )}
              </button>
            </>
          )}
        </section>
      </div>
    </div>
  );
}
