import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { ArrowLeft, MonitorSmartphone, ChevronRight, Type, Palette } from "lucide-react";
import { useState } from "react";
import { Text } from "@/components/Text";

export const Route = createFileRoute("/profil_/pengaturan")({
  component: PengaturanPage,
  head: () => ({ meta: [{ title: "Pengaturan — SantriPay" }] }),
});

function PengaturanPage() {
  const navigate = useNavigate();

  // State for theme and font scale
  const [theme, setTheme] = useState<"asli" | "modern">(() => {
    if (typeof window !== "undefined") {
      return (localStorage.getItem("ct-ui-theme") as "asli" | "modern") || "asli";
    }
    return "asli";
  });

  const [scale, setScale] = useState<number>(() => {
    if (typeof window !== "undefined") {
      return parseFloat(localStorage.getItem("ct-font-scale") || "1.0");
    }
    return 1.0;
  });

  // Scales options
  const scales = [
    { value: 0.85, label: "85%", desc: "Ukuran Teks Kecil" },
    { value: 1.0, label: "100%", desc: "Ukuran Teks Normal" },
    { value: 1.15, label: "115%", desc: "Ukuran Teks Besar" },
    { value: 1.3, label: "130%", desc: "Ukuran Teks Sangat Besar" },
  ];

  const currentScaleIndex = scales.findIndex((s) => s.value === scale) === -1 
    ? 1 
    : scales.findIndex((s) => s.value === scale);

  const changeTheme = (newTheme: "asli" | "modern") => {
    setTheme(newTheme);
    localStorage.setItem("ct-ui-theme", newTheme);
    document.documentElement.classList.toggle("theme-modern", newTheme === "modern");
  };

  const changeScaleIndex = (newIndex: number) => {
    if (newIndex >= 0 && newIndex < scales.length) {
      const newScale = scales[newIndex].value;
      setScale(newScale);
      localStorage.setItem("ct-font-scale", newScale.toString());
      document.documentElement.style.setProperty("--font-scale", newScale.toString());
    }
  };

  return (
    <div className="min-h-screen w-full flex justify-center bg-secondary transition-colors duration-300">
      <div className="relative w-full max-w-md min-h-screen bg-background pb-24 transition-colors duration-300">
        {/* Hero */}
        <div
          className="relative px-5 pt-12 pb-20 rounded-b-[2rem] overflow-hidden"
          style={{ background: "var(--gradient-hero)" }}
        >
          <div className="absolute -top-16 -right-12 w-56 h-56 rounded-full bg-primary-glow/30 blur-3xl" />
          <div className="relative flex items-center gap-3">
            <button
              onClick={() => navigate({ to: "/profil" })}
              className="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center text-white active:scale-95 transition-all"
            >
              <ArrowLeft size={18} />
            </button>
            <div>
              <Text.Label className="text-white/70 block leading-none">Profil</Text.Label>
              <Text.H1 className="text-white mt-1">Pengaturan Aplikasi</Text.H1>
            </div>
          </div>
        </div>

        {/* Content */}
        <div className="px-5 -mt-12 relative z-10 space-y-4">
          <div 
            className={`bg-card rounded-[24px] p-5 divide-y divide-border/60 transition-all duration-300 ${
              theme === "modern" 
                ? "shadow-[var(--shadow-card)]" 
                : "shadow-[0_8px_30px_rgba(0,0,0,0.04)]"
            }`}
          >
            {/* Opsi 1: Font Scale */}
            <div className="w-full flex items-center justify-between py-3.5 gap-3">
              <div className="flex items-center gap-3">
                <div 
                  className={`w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-300 ${
                    theme === "modern"
                      ? "bg-secondary shadow-[inset_2px_2px_5px_rgba(0,0,0,0.05),inset_-2px_-2px_5px_rgba(255,255,255,0.7)] text-slate-400"
                      : "bg-slate-100 dark:bg-slate-800 text-slate-400"
                  }`}
                >
                  <Type size={18} strokeWidth={2} />
                </div>
                <div>
                  <Text.H2>Ukuran Huruf</Text.H2>
                  <Text.Caption className="block mt-0.5">{scales[currentScaleIndex].desc}</Text.Caption>
                </div>
              </div>

              {/* Controller Font Scale */}
              <div 
                className={`flex items-center p-1 rounded-xl transition-all duration-300 ${
                  theme === "modern"
                    ? "bg-secondary shadow-[inset_2px_2px_5px_rgba(0,0,0,0.05),inset_-2px_-2px_5px_rgba(255,255,255,0.7)]"
                    : "bg-slate-100 dark:bg-slate-800"
                }`}
              >
                <button 
                  onClick={() => changeScaleIndex(currentScaleIndex - 1)}
                  disabled={currentScaleIndex === 0}
                  className={`w-7 h-7 rounded-lg flex items-center justify-center font-bold transition-all ${
                    currentScaleIndex === 0 
                      ? "opacity-30 cursor-not-allowed text-slate-400" 
                      : theme === "modern"
                        ? "bg-card shadow-[2px_2px_5px_rgba(0,0,0,0.05),-2px_-2px_5px_rgba(255,255,255,0.9)] text-slate-700 dark:text-slate-200 active:shadow-[inset_2px_2px_5px_rgba(0,0,0,0.05)]"
                        : "hover:bg-white dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 shadow-sm active:scale-95"
                  }`}
                >
                  <span className="text-[11px] font-extrabold select-none">A-</span>
                </button>
                <span className="text-xs font-bold text-slate-600 dark:text-slate-300 w-12 text-center select-none">
                  {scales[currentScaleIndex].label}
                </span>
                <button 
                  onClick={() => changeScaleIndex(currentScaleIndex + 1)}
                  disabled={currentScaleIndex === scales.length - 1}
                  className={`w-7 h-7 rounded-lg flex items-center justify-center font-bold transition-all ${
                    currentScaleIndex === scales.length - 1 
                      ? "opacity-30 cursor-not-allowed text-slate-400" 
                      : theme === "modern"
                        ? "bg-card shadow-[2px_2px_5px_rgba(0,0,0,0.05),-2px_-2px_5px_rgba(255,255,255,0.9)] text-slate-700 dark:text-slate-200 active:shadow-[inset_2px_2px_5px_rgba(0,0,0,0.05)]"
                        : "hover:bg-white dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 shadow-sm active:scale-95"
                  }`}
                >
                  <span className="text-[14px] font-extrabold select-none">A+</span>
                </button>
              </div>
            </div>

            {/* Opsi 2: Ubah Tampilan */}
            <div className="w-full flex items-center justify-between py-3.5 gap-3">
              <div className="flex items-center gap-3">
                <div 
                  className={`w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-300 ${
                    theme === "modern"
                      ? "bg-secondary shadow-[inset_2px_2px_5px_rgba(0,0,0,0.05),inset_-2px_-2px_5px_rgba(255,255,255,0.7)] text-slate-400"
                      : "bg-slate-100 dark:bg-slate-800 text-slate-400"
                  }`}
                >
                  <Palette size={18} strokeWidth={2} />
                </div>
                <div>
                  <Text.H2>Tampilan UI</Text.H2>
                  <Text.Caption className="block mt-0.5">
                    {theme === "asli" ? "Tampilan Asli" : "Tampilan Modern"}
                  </Text.Caption>
                </div>
              </div>

              {/* Segmented Control */}
              <div 
                className={`flex items-center p-0.5 rounded-xl transition-all duration-300 ${
                  theme === "modern"
                    ? "bg-secondary shadow-[inset_2px_2px_5px_rgba(0,0,0,0.05),inset_-2px_-2px_5px_rgba(255,255,255,0.7)]"
                    : "bg-slate-100 dark:bg-slate-800 border border-slate-200/50 dark:border-slate-700/50"
                }`}
              >
                <button
                  onClick={() => changeTheme("asli")}
                  className={`px-3 py-1.5 rounded-lg text-xs font-bold transition-all duration-300 select-none ${
                    theme === "asli"
                      ? "bg-white dark:bg-slate-700 text-primary dark:text-white shadow-sm"
                      : "text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300"
                  }`}
                >
                  Asli
                </button>
                <button
                  onClick={() => changeTheme("modern")}
                  className={`px-3 py-1.5 rounded-lg text-xs font-bold transition-all duration-300 select-none ${
                    theme === "modern"
                      ? "bg-card text-primary dark:text-white shadow-[2px_2px_5px_rgba(0,0,0,0.05),-2px_-2px_5px_rgba(255,255,255,0.9)]"
                      : "text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300"
                  }`}
                >
                  Modern
                </button>
              </div>
            </div>

            {/* Opsi 3: Sesi Perangkat Aktif */}
            <button className="w-full flex items-center justify-between py-3.5 text-left active:opacity-75 transition-all">
              <div className="flex items-center gap-3">
                <div 
                  className={`w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-300 ${
                    theme === "modern"
                      ? "bg-secondary shadow-[inset_2px_2px_5px_rgba(0,0,0,0.05),inset_-2px_-2px_5px_rgba(255,255,255,0.7)] text-slate-400"
                      : "bg-slate-100 dark:bg-slate-800 text-slate-400"
                  }`}
                >
                  <MonitorSmartphone size={18} strokeWidth={2} />
                </div>
                <div>
                  <Text.H2>Sesi Perangkat Aktif</Text.H2>
                  <Text.Caption className="block mt-0.5">Kelola perangkat yang terhubung</Text.Caption>
                </div>
              </div>
              <ChevronRight size={18} className="text-slate-400" strokeWidth={2} />
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
