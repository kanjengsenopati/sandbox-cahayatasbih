import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useState } from "react";
import { Eye, EyeOff, Loader2 } from "lucide-react";
import { postLogin } from "@/lib/api";
import { useMutation } from "@tanstack/react-query";

export const Route = createFileRoute("/")({
  component: LoginPage,
  head: () => ({
    meta: [
      { title: "Masuk — SantriPay" },
      { name: "description", content: "Aplikasi keuangan digital untuk wali santri dan siswa." },
    ],
  }),
});

function LoginPage() {
  const [show, setShow] = useState(false);
  const [phone, setPhone] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const navigate = useNavigate();

  const loginMutation = useMutation({
    mutationFn: async (data: any) => {
      const res = await postLogin(data);
      return res.data;
    },
    onSuccess: () => {
      navigate({ to: "/dashboard" });
    },
    onError: (err: any) => {
      setError(err.response?.data?.message || "Login gagal. Cek nomor HP dan password Anda.");
    },
  });

  const handleLogin = (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    loginMutation.mutate({ phone, password });
  };

  return (
    <div className="min-h-screen w-full flex justify-center bg-[#9b1de8] relative overflow-hidden">
      {/* Background Diamond Pattern matching Gambar 2 */}
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
      <div className="absolute bottom-1/2 left-[-20%] w-[60vh] h-[60vh] bg-[#610a9c]/50 blur-[100px] rounded-full mix-blend-multiply" />

      <div className="relative w-full max-w-md min-h-screen flex flex-col z-10">
        
        {/* Top Logo Area (Centered) */}
        <div className="flex-none pt-[12vh] pb-[8vh] flex justify-center items-center">
          <div className="w-32 h-32 rounded-full bg-white p-1 shadow-2xl relative flex justify-center items-center">
            {/* Using the default logo from assets */}
            <img src="/assets/media/logos/logo.png" alt="Logo Cahaya Tasbih" className="w-[95%] h-[95%] object-contain rounded-full" />
            {/* Subtle ring around it as per design */}
            <div className="absolute inset-0 rounded-full ring-2 ring-white/50 shadow-[0_0_20px_rgba(255,255,255,0.2)]" />
          </div>
        </div>

        {/* Bottom White Card (Full stretch to bottom) */}
        <div className="flex-1 bg-white rounded-t-[2.5rem] px-8 pt-10 pb-8 flex flex-col shadow-[0_-10px_40px_rgba(0,0,0,0.1)]">
          <div className="mb-8">
            <h1 className="text-3xl font-extrabold text-slate-900 tracking-tight">Masuk</h1>
            <p className="text-[14px] text-slate-500 mt-2 leading-relaxed">
              Masuk ke aplikasi untuk memantau data santri / siswa
            </p>
          </div>

          <form onSubmit={handleLogin} className="flex-1 flex flex-col">
            {error && (
              <div className="p-3 mb-4 rounded-xl bg-red-50 text-red-600 text-xs font-bold text-center border border-red-100">
                {error}
              </div>
            )}
            
            <div className="space-y-5">
              <div>
                <label className="text-xs font-bold text-slate-500 mb-2 block">
                  Nomor Telepon
                </label>
                <div className="flex items-center gap-3 bg-slate-100/80 rounded-full px-5 py-4 focus-within:ring-2 focus-within:ring-[#9b1de8]/20 focus-within:bg-white transition-all border border-transparent focus-within:border-[#9b1de8]/30">
                  <input
                    type="tel"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value)}
                    placeholder="08xxxxxx"
                    className="bg-transparent flex-1 outline-none text-slate-800 text-[15px] font-semibold placeholder:text-slate-400 placeholder:font-medium"
                  />
                </div>
              </div>

              <div>
                <label className="text-xs font-bold text-slate-500 mb-2 block">
                  Kata Sandi
                </label>
                <div className="flex items-center gap-3 bg-slate-100/80 rounded-full px-5 py-4 focus-within:ring-2 focus-within:ring-[#9b1de8]/20 focus-within:bg-white transition-all border border-transparent focus-within:border-[#9b1de8]/30">
                  <input
                    type={show ? "text" : "password"}
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    placeholder="password"
                    className="bg-transparent flex-1 outline-none text-slate-800 text-[15px] font-semibold placeholder:text-slate-400 placeholder:font-medium"
                  />
                  <button
                    type="button"
                    onClick={() => setShow(!show)}
                    className="text-slate-500 hover:text-slate-700 transition"
                  >
                    {show ? <EyeOff size={22} strokeWidth={2.5} /> : <Eye size={22} strokeWidth={2.5} />}
                  </button>
                </div>
              </div>
            </div>

            <div className="flex justify-end mt-4 mb-auto">
              <button type="button" className="text-[13px] font-bold text-[#9b1de8]">
                Lupa Kata Sandi
              </button>
            </div>

            {/* Submit Button pushes to bottom */}
            <div className="mt-8 pt-4">
              <button
                type="submit"
                disabled={loginMutation.isPending}
                className="w-full py-4 rounded-full bg-[#9b1de8] text-white font-bold text-base shadow-[0_8px_25px_rgba(155,29,232,0.35)] transition active:scale-[0.98] disabled:opacity-70 flex items-center justify-center"
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
    </div>
  );
}

