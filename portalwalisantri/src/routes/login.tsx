import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useState } from "react";
import { Phone, Lock, Eye, EyeOff, GraduationCap, ArrowRight, Zap, Loader2 } from "lucide-react";
import { postLogin } from "@/lib/api";
import { useMutation } from "@tanstack/react-query";

export const Route = createFileRoute("/login")({
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

  const handleDemoLogin = () => {
    // For demo, we might use specific credentials or just skip
    navigate({ to: "/dashboard" });
  };

  return (
    <div className="min-h-screen w-full flex justify-center bg-background">
      <div className="relative w-full max-w-md min-h-screen flex flex-col">
        {/* Hero header — expanded */}
        <div
          className="relative pt-7 pb-24 px-6 rounded-b-[2.5rem] overflow-hidden"
          style={{ background: "var(--gradient-hero)" }}
        >
          <div className="absolute -top-24 -right-16 w-72 h-72 rounded-full bg-primary-glow/30 blur-3xl" />
          <div className="absolute -bottom-16 -left-16 w-60 h-60 rounded-full bg-white/15 blur-3xl" />
          <div className="absolute top-10 right-8 w-24 h-24 rounded-full border border-white/15" />
          <div className="absolute top-20 right-20 w-12 h-12 rounded-full border border-white/10" />
          <div
            className="absolute inset-0 opacity-[0.07]"
            style={{
              backgroundImage:
                "linear-gradient(rgba(255,255,255,0.6) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.6) 1px, transparent 1px)",
              backgroundSize: "26px 26px",
              maskImage: "radial-gradient(ellipse at top right, black 30%, transparent 70%)",
            }}
          />

          <div className="relative flex items-center justify-between gap-3">
            <div className="flex items-center gap-2.5 min-w-0">
              <div className="w-11 h-11 rounded-xl bg-white/15 backdrop-blur-md flex items-center justify-center border border-white/25 shrink-0 shadow-lg">
                <GraduationCap className="text-white" size={22} />
              </div>
              <div className="flex flex-col min-w-0 leading-tight">
                <span className="text-[10px] font-semibold text-white/70 uppercase tracking-[0.18em]">
                  PPTQ
                </span>
                <span className="text-[15px] font-extrabold text-white tracking-[0.04em] whitespace-nowrap">
                  CAHAYA&nbsp;TASBIH
                </span>
              </div>
            </div>
            <div className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/15 border border-white/20 backdrop-blur shrink-0">
              <span className="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse" />
              <span className="text-[9px] font-semibold text-white tracking-wider uppercase">
                Portal Wali
              </span>
            </div>
          </div>

          {/* Title block */}
          <div className="relative mt-8 space-y-3">
            <div className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/10 border border-white/15 backdrop-blur">
              <span className="text-[10px] font-bold text-white tracking-wider uppercase">
                SantriPay
              </span>
            </div>
            <h1 className="text-[22px] leading-[1.2] font-extrabold text-white tracking-tight">
              Selamat Datang, <span className="text-white/90">Wali Santri</span>
            </h1>
            <p className="text-[13px] text-white/75 leading-relaxed max-w-[20rem]">
              Pantau saldo, bayar tagihan, dan kelola keuangan ananda di pondok
              dalam satu genggaman.
            </p>
          </div>
        </div>

        {/* Form card */}
        <div className="px-6 -mt-8 relative z-10">
          <form
            onSubmit={handleLogin}
            className="bg-card rounded-3xl p-6 shadow-[var(--shadow-card)] border border-border space-y-4"
          >
            {error && (
              <div className="p-3 rounded-xl bg-destructive/10 border border-destructive/20 text-destructive text-xs font-semibold text-center">
                {error}
              </div>
            )}
            <div>
              <label className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                No. Handphone
              </label>
              <div className="mt-2 flex items-center gap-3 bg-secondary rounded-2xl px-4 py-3.5 border border-transparent focus-within:border-primary transition">
                <Phone size={18} className="text-primary" />
                <input
                  type="tel"
                  value={phone}
                  onChange={(e) => setPhone(e.target.value)}
                  placeholder="08xxxxxxxxxx"
                  className="bg-transparent flex-1 outline-none text-foreground text-sm font-medium placeholder:text-muted-foreground"
                />
              </div>
            </div>

            <div>
              <label className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                Password
              </label>
              <div className="mt-2 flex items-center gap-3 bg-secondary rounded-2xl px-4 py-3.5 border border-transparent focus-within:border-primary transition">
                <Lock size={18} className="text-primary" />
                <input
                  type={show ? "text" : "password"}
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="••••••••"
                  className="bg-transparent flex-1 outline-none text-foreground text-sm font-medium placeholder:text-muted-foreground"
                />
                <button
                  type="button"
                  onClick={() => setShow((s) => !s)}
                  className="text-muted-foreground"
                >
                  {show ? <EyeOff size={18} /> : <Eye size={18} />}
                </button>
              </div>
            </div>

            <div className="flex justify-end">
              <button type="button" className="text-xs font-semibold text-primary">
                Lupa Password?
              </button>
            </div>

            <button
              type="submit"
              disabled={loginMutation.isPending}
              className="w-full py-4 rounded-2xl text-primary-foreground font-semibold text-sm shadow-[var(--shadow-glow)] flex items-center justify-center gap-2 transition active:scale-[0.98] disabled:opacity-70"
              style={{ background: "var(--gradient-card)" }}
            >
              {loginMutation.isPending ? (
                <Loader2 className="animate-spin" size={18} />
              ) : (
                <>
                  Masuk Sekarang
                  <ArrowRight size={18} />
                </>
              )}
            </button>
          </form>

        </div>
      </div>
    </div>
  );
}
