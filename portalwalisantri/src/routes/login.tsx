import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useState } from "react";
import { Eye, EyeOff, Loader2 } from "lucide-react";
import { postLogin } from "@/lib/api";
import { useMutation, useQueryClient } from "@tanstack/react-query";

export const Route = createFileRoute("/login")({
  component: LoginPage,
  head: () => ({
    meta: [
      { title: "Masuk — CT-Mobile" },
      { name: "description", content: "Aplikasi mobile wali santri, kamar, dan perizinan Cahaya Tasbih." },
    ],
  }),
});

function LoginPage() {
  const [show, setShow] = useState(false);
  const [rememberMe, setRememberMe] = useState(() => {
    if (typeof window !== "undefined") {
      return localStorage.getItem("remember_me") === "true";
    }
    return false;
  });
  const [phone, setPhone] = useState(() => {
    if (typeof window !== "undefined" && localStorage.getItem("remember_me") === "true") {
      return localStorage.getItem("remembered_phone") || "";
    }
    return "";
  });
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [showRoleSelector, setShowRoleSelector] = useState(false);
  const navigate = useNavigate();
  const queryClient = useQueryClient();

  const loginMutation = useMutation({
    mutationFn: async (data: any) => {
      const res = await postLogin(data);
      return res.data;
    },
    onSuccess: (data: any) => {
      if (rememberMe) {
        localStorage.setItem("remember_me", "true");
        localStorage.setItem("remembered_phone", phone);
      } else {
        localStorage.removeItem("remember_me");
        localStorage.removeItem("remembered_phone");
      }

      if (data.status === "requires_role_selection") {
        setShowRoleSelector(true);
        return;
      }

      // Invalidate queries to ensure fresh data after login
      queryClient.invalidateQueries({ queryKey: ["students"] });
      queryClient.invalidateQueries({ queryKey: ["active-student"] });
      queryClient.invalidateQueries({ queryKey: ["dashboard"] });

      if (data.role === "penanggung_jawab") {
        navigate({ to: "/penanggung-jawab/dashboard" });
      } else {
        navigate({ to: "/dashboard" });
      }
    },
    onError: (err: any) => {
      setError(err.response?.data?.message || "Login gagal. Cek nomor WhatsApp dan password Anda.");
    },
  });

  const handleLogin = (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    loginMutation.mutate({ phone, password });
  };

  const selectRoleAndLogin = (selectedRole: string) => {
    setShowRoleSelector(false);
    loginMutation.mutate({ phone, password, role: selectedRole });
  };

  return (
    <div className="min-h-screen w-full bg-slate-50 relative flex justify-center overflow-x-hidden">
      {/* Hero Banner 60vh */}
      <div className="absolute top-0 left-0 w-full h-[60vh] bg-[#9b1de8] overflow-hidden rounded-b-[32px] shadow-[0_10px_30px_rgba(155,29,232,0.15)]">
        {/* Background Diamond Pattern */}
        <div
          className="absolute inset-0 opacity-[0.05]"
          style={{
            backgroundImage: `
              linear-gradient(45deg, #000 25%, transparent 25%, transparent 75%, #000 75%, #000), 
              linear-gradient(45deg, #000 25%, transparent 25%, transparent 75%, #000 75%, #000)
            `,
            backgroundSize: "60px 60px",
            backgroundPosition: "0 0, 30px 30px"
          }}
        />
        {/* Subtle Glows to enhance the vibe */}
        <div className="absolute top-[-10%] right-[-10%] w-[50vh] h-[50vh] bg-[#b445ff]/40 blur-[80px] rounded-full mix-blend-screen" />
        <div className="absolute bottom-[-10%] left-[-20%] w-[60vh] h-[60vh] bg-[#610a9c]/50 blur-[100px] rounded-full mix-blend-multiply" />
      </div>

      <div className="relative w-full max-w-md min-h-screen flex flex-col z-10 pb-10">

        {/* Top Logo Area */}
        <div className="flex flex-col items-center justify-center pt-[10vh] pb-6">
          <div className="w-[110px] h-[110px] rounded-full bg-white p-1 shadow-2xl relative flex justify-center items-center">
            {/* Using the default logo from assets */}
            <img src="/assets/media/logos/logo.png" alt="Logo Cahaya Tasbih" className="w-[95%] h-[95%] object-contain rounded-full" />
            {/* Subtle ring around it as per design */}
            <div className="absolute inset-0 rounded-full ring-2 ring-white/50 shadow-[0_0_20px_rgba(255,255,255,0.2)]" />
          </div>
          <h2 className="mt-5 text-[16px] font-bold text-white tracking-wide drop-shadow-md">
            PPTQ CAHAYA TASBIH Mobile
          </h2>
        </div>

        {/* Floating White Card */}
        <div className="bg-white rounded-[24px] mx-5 px-6 pt-8 pb-8 flex flex-col shadow-[0_8px_30px_rgb(0,0,0,0.08)] mt-[6vh] mb-auto">
          <div className="mb-7">
            <h1 className="text-[22px] font-bold text-slate-900 tracking-tight">Masuk</h1>
            <p className="text-[14px] font-medium text-slate-500 mt-1.5 leading-relaxed">
              Masuk ke aplikasi untuk memantau data santri / siswa
            </p>
          </div>

          <form onSubmit={handleLogin} className="flex flex-col">
            {error && (
              <div className="p-3 mb-5 rounded-xl bg-red-50 text-red-600 text-[12px] font-bold text-center border border-red-100">
                {error}
              </div>
            )}

            <div className="space-y-4">
              <div>
                <label className="text-[12px] font-bold text-slate-500 mb-1.5 block">
                  Nomor WhatsApp
                </label>
                <div className="flex items-center gap-3 bg-secondary rounded-2xl px-4 py-3.5 focus-within:ring-2 focus-within:ring-[#9b1de8]/20 focus-within:bg-white transition-all border border-slate-100 focus-within:border-[#9b1de8]/30">
                  <input
                    type="tel"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value)}
                    placeholder="08xxxxxx"
                    className="bg-transparent flex-1 outline-none text-slate-800 text-[14px] font-medium placeholder:text-slate-400"
                  />
                </div>
              </div>

              <div>
                <label className="text-[12px] font-bold text-slate-500 mb-1.5 block">
                  Kata Sandi
                </label>
                <div className="flex items-center gap-3 bg-secondary rounded-2xl px-4 py-3.5 focus-within:ring-2 focus-within:ring-[#9b1de8]/20 focus-within:bg-white transition-all border border-slate-100 focus-within:border-[#9b1de8]/30">
                  <input
                    type={show ? "text" : "password"}
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    placeholder="password"
                    className="bg-transparent flex-1 outline-none text-slate-800 text-[14px] font-medium placeholder:text-slate-400"
                  />
                  <button
                    type="button"
                    onClick={() => setShow(!show)}
                    className="text-slate-400 hover:text-slate-600 transition"
                  >
                    {show ? <EyeOff size={18} strokeWidth={2.5} /> : <Eye size={18} strokeWidth={2.5} />}
                  </button>
                </div>
              </div>
            </div>

            <div className="flex items-center mt-4">
              <label className="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  checked={rememberMe}
                  onChange={(e) => setRememberMe(e.target.checked)}
                  className="rounded border-slate-300 text-[#9b1de8] focus:ring-[#9b1de8]/20 w-4 h-4"
                />
                <span className="text-[12px] font-bold text-slate-500">Ingat Saya</span>
              </label>
            </div>

            {/* Submit Button */}
            <div className="mt-8">
              <button
                type="submit"
                disabled={loginMutation.isPending}
                className="w-full py-4 rounded-[20px] bg-[#9b1de8] text-white font-bold text-[15px] shadow-[0_8px_25px_rgba(155,29,232,0.35)] transition active:scale-[0.98] disabled:opacity-70 flex items-center justify-center"
              >
                {loginMutation.isPending ? (
                  <Loader2 className="animate-spin" size={20} />
                ) : (
                  "Masuk"
                )}
              </button>
            </div>
          </form>
        </div>
      </div>

      {/* Glassmorphic Role Selector Dialog */}
      {showRoleSelector && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-5 bg-slate-900/60 backdrop-blur-md transition-opacity duration-300">
          <div className="bg-white rounded-[24px] w-full max-w-sm p-6 shadow-[0_8px_30px_rgb(0,0,0,0.08)] border border-slate-100 flex flex-col">
            <h3 className="text-[18px] font-bold text-slate-900 text-center mb-1">
              Pilih Peran Masuk
            </h3>
            <p className="text-[13px] font-medium text-slate-500 text-center mb-6 leading-relaxed">
              Nomor WhatsApp Anda terdaftar sebagai Wali Santri dan juga Penanggung Jawab. Silakan pilih identitas untuk masuk:
            </p>

            <div className="space-y-3">
              <button
                type="button"
                onClick={() => selectRoleAndLogin("wali")}
                className="w-full p-4 rounded-[20px] bg-slate-50 hover:bg-slate-100 border border-slate-200 transition-all active:scale-[0.98] flex items-center gap-4 text-left group"
              >
                <div className="w-10 h-10 rounded-full bg-[#9b1de8]/10 flex items-center justify-center text-[#9b1de8] group-hover:bg-[#9b1de8] group-hover:text-white transition-all shrink-0">
                  <span className="font-bold text-[14px]">WS</span>
                </div>
                <div>
                  <div className="font-bold text-slate-800 text-[14px]">Wali Santri</div>
                  <div className="text-slate-400 text-[11px] font-medium leading-tight mt-0.5">Akses Tabungan, Saldo, & Perizinan Anak</div>
                </div>
              </button>

              <button
                type="button"
                onClick={() => selectRoleAndLogin("penanggung_jawab")}
                className="w-full p-4 rounded-[20px] bg-slate-50 hover:bg-slate-100 border border-slate-200 transition-all active:scale-[0.98] flex items-center gap-4 text-left group"
              >
                <div className="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-all shrink-0">
                  <span className="font-bold text-[14px]">PJ</span>
                </div>
                <div>
                  <div className="font-bold text-slate-800 text-[14px]">Penanggung Jawab</div>
                  <div className="text-slate-400 text-[11px] font-medium leading-tight mt-0.5">Akses Persetujuan Izin & Supervisi Santri</div>
                </div>
              </button>
            </div>

            <button
              type="button"
              onClick={() => setShowRoleSelector(false)}
              className="mt-6 text-center text-[12px] font-bold text-slate-400 hover:text-slate-600 transition"
            >
              Batal
            </button>
          </div>
        </div>
      )}
    </div>
  );
}
