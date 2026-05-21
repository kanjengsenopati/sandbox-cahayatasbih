import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { ArrowLeft, ShieldCheck, Key, Smartphone, ChevronRight, X, Loader2 } from "lucide-react";
import { useState } from "react";
import { useMutation } from "@tanstack/react-query";
import { updatePassword } from "@/lib/api";

export const Route = createFileRoute("/profil_/keamanan")({
  component: KeamananPage,
  head: () => ({ meta: [{ title: "Keamanan & PIN — SantriPay" }] }),
});

function KeamananPage() {
  const navigate = useNavigate();
  const [showPwdForm, setShowPwdForm] = useState(false);
  const [pwdData, setPwdData] = useState({ current_password: "", new_password: "", new_password_confirmation: "" });
  const [pwdMsg, setPwdMsg] = useState({ type: "", text: "" });

  const pwdMutation = useMutation({
    mutationFn: async (data: any) => {
      const res = await updatePassword(data);
      return res.data;
    },
    onSuccess: () => {
      setPwdMsg({ type: "success", text: "Password berhasil diperbarui!" });
      setPwdData({ current_password: "", new_password: "", new_password_confirmation: "" });
      setTimeout(() => {
        setShowPwdForm(false);
        setPwdMsg({ type: "", text: "" });
      }, 2000);
    },
    onError: (err: any) => {
      setPwdMsg({ type: "error", text: err.response?.data?.message || "Gagal memperbarui password." });
    }
  });

  const handleUpdatePwd = (e: React.FormEvent) => {
    e.preventDefault();
    if (pwdData.new_password !== pwdData.new_password_confirmation) {
      setPwdMsg({ type: "error", text: "Konfirmasi password tidak cocok." });
      return;
    }
    pwdMutation.mutate(pwdData);
  };

  return (
    <div className="min-h-screen w-full flex justify-center bg-secondary">
      <div className="relative w-full max-w-md min-h-screen bg-background pb-24">
        {/* Hero */}
        <div
          className="relative px-6 pt-12 pb-20 rounded-b-[2rem] overflow-hidden"
          style={{ background: "var(--gradient-hero)" }}
        >
          <div className="absolute -top-16 -right-12 w-56 h-56 rounded-full bg-primary-glow/30 blur-3xl" />
          <div className="relative flex items-center gap-3">
            <button
              onClick={() => navigate({ to: "/profil" })}
              className="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center text-white"
            >
              <ArrowLeft size={18} />
            </button>
            <div>
              <p className="text-[11px] text-white/70 font-semibold uppercase tracking-wider">Profil</p>
              <p className="text-base font-bold text-white">Keamanan & PIN</p>
            </div>
          </div>
        </div>

        {/* Content */}
        <div className="px-6 -mt-12 relative z-10 space-y-4">
          <div className="bg-card rounded-3xl border border-border shadow-[var(--shadow-card)] overflow-hidden">
            <div className="divide-y divide-border p-4">
              <button className="w-full flex items-center gap-3 py-3 active:opacity-70 transition text-left">
                <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                  <Key size={18} />
                </div>
                <div className="flex-1">
                  <p className="text-sm font-bold text-foreground">Ubah PIN Transaksi</p>
                  <p className="text-[11px] text-muted-foreground mt-0.5">Perbarui PIN 6-digit secara berkala</p>
                </div>
                <ChevronRight size={18} className="text-muted-foreground" />
              </button>
              
              <button 
                onClick={() => setShowPwdForm(!showPwdForm)}
                className="w-full flex items-center gap-3 py-3 active:opacity-70 transition text-left"
              >
                <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                  <ShieldCheck size={18} />
                </div>
                <div className="flex-1">
                  <p className="text-sm font-bold text-foreground">Ubah Password Akun</p>
                  <p className="text-[11px] text-muted-foreground mt-0.5">Password login ke portal web/aplikasi</p>
                </div>
                {showPwdForm ? <X size={18} className="text-muted-foreground" /> : <ChevronRight size={18} className="text-muted-foreground" />}
              </button>
              
              {showPwdForm && (
                <div className="pt-4 pb-2">
                  <form onSubmit={handleUpdatePwd} className="space-y-3">
                    <input 
                      type="password" 
                      placeholder="Password Lama" 
                      className="w-full bg-secondary text-sm px-4 py-2.5 rounded-xl border-none outline-none focus:ring-1 focus:ring-primary"
                      value={pwdData.current_password}
                      onChange={e => setPwdData({...pwdData, current_password: e.target.value})}
                      required
                    />
                    <input 
                      type="password" 
                      placeholder="Password Baru" 
                      className="w-full bg-secondary text-sm px-4 py-2.5 rounded-xl border-none outline-none focus:ring-1 focus:ring-primary"
                      value={pwdData.new_password}
                      onChange={e => setPwdData({...pwdData, new_password: e.target.value})}
                      required
                    />
                    <input 
                      type="password" 
                      placeholder="Konfirmasi Password Baru" 
                      className="w-full bg-secondary text-sm px-4 py-2.5 rounded-xl border-none outline-none focus:ring-1 focus:ring-primary"
                      value={pwdData.new_password_confirmation}
                      onChange={e => setPwdData({...pwdData, new_password_confirmation: e.target.value})}
                      required
                    />
                    {pwdMsg.text && (
                      <p className={`text-xs font-semibold ${pwdMsg.type === 'error' ? 'text-destructive' : 'text-success'}`}>
                        {pwdMsg.text}
                      </p>
                    )}
                    <button 
                      type="submit" 
                      disabled={pwdMutation.isPending}
                      className="w-full py-2.5 rounded-xl bg-primary text-white font-bold text-sm flex items-center justify-center gap-2 mt-2 disabled:opacity-50"
                    >
                      {pwdMutation.isPending ? <Loader2 size={16} className="animate-spin" /> : "Simpan Password"}
                    </button>
                  </form>
                </div>
              )}

              <button className="w-full flex items-center gap-3 py-3 active:opacity-70 transition text-left">
                <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                  <Smartphone size={18} />
                </div>
                <div className="flex-1">
                  <p className="text-sm font-bold text-foreground">Autentikasi Biometrik</p>
                  <p className="text-[11px] text-muted-foreground mt-0.5">Login cepat dengan sidik jari/FaceID</p>
                </div>
                <div className="w-10 h-6 bg-secondary rounded-full flex items-center px-1">
                  <div className="w-4 h-4 bg-muted-foreground rounded-full" />
                </div>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
