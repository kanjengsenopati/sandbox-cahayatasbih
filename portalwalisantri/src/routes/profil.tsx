import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { ChevronRight, Shield, Bell, CreditCard, HelpCircle, LogOut, Settings, Loader2, RefreshCw } from "lucide-react";
import { MobileShell } from "@/components/MobileShell";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { fetchProfile, postLogout, postSwitchRole } from "@/lib/api";

export const Route = createFileRoute("/profil")({
  component: Profil,
  head: () => ({ meta: [{ title: "Profil — SantriPay" }] }),
});

const groups = [
  {
    title: "Akun",
    items: [
      { label: "Keamanan & PIN", icon: Shield },
      { label: "Notifikasi", icon: Bell },
      { label: "Metode Pembayaran", icon: CreditCard },
    ],
  },
  {
    title: "Lainnya",
    items: [
      { label: "Pengaturan", icon: Settings },
      { label: "Bantuan", icon: HelpCircle },
    ],
  },
];

function Profil() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();

  const { data: profileData, isLoading } = useQuery({
    queryKey: ["profile"],
    queryFn: async () => {
      const res = await fetchProfile();
      return res.data;
    },
  });

  const switchMutation = useMutation({
    mutationFn: async () => {
      const res = await postSwitchRole();
      return res.data;
    },
    onSuccess: (data: any) => {
      queryClient.invalidateQueries();
      if (data.role === "asatidz") {
        window.location.href = '/ct-mobile/asatidz/dashboard';
      } else {
        window.location.href = '/ct-mobile/dashboard';
      }
    },
    onError: (err: any) => {
      alert(err.response?.data?.message || "Gagal beralih peran.");
    }
  });

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-background">
        <Loader2 className="animate-spin text-primary" size={40} />
      </div>
    );
  }

  const user = profileData?.user;
  const students = profileData?.students || [];
  const initials = user?.name ? user.name.split(' ').map((n: string) => n[0]).join('').substring(0, 2).toUpperCase() : "W";

  const handleLogout = async () => {
    try {
      await postLogout();
      window.location.href = '/ct-mobile/login';
    } catch (error) {
      console.error("Logout error:", error);
      window.location.href = '/ct-mobile/login';
    }
  };

  return (
    <MobileShell>
      <header className="px-6 pt-12 pb-6">
        <h1 className="text-2xl font-bold text-foreground">Profil</h1>
      </header>

      <section className="px-6">
        <div
          className="rounded-3xl p-5 text-primary-foreground shadow-[var(--shadow-glow)] flex items-center gap-4"
          style={{ background: "var(--gradient-hero)" }}
        >
          <div className="w-16 h-16 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center text-2xl font-bold border border-white/20">
            {user?.avatar ? (
              <img src={`/${user.avatar}`} alt={user.name} className="w-full h-full object-cover rounded-2xl" />
            ) : initials}
          </div>
          <div className="flex-1">
            <p className="font-bold text-lg">{user?.name}</p>
            <p className="text-xs text-white/70">{user?.phone}</p>
            {profileData?.role === 'wali' && students.length > 0 && (
              <p className="text-[11px] text-white/60 mt-1">
                Wali dari {students.map((s: any) => s.name).join(', ')}
              </p>
            )}
            {profileData?.role === 'asatidz' && (
              <p className="text-[11px] text-white/60 mt-1">
                Ustadz / Ustadzah Pembimbing
              </p>
            )}
          </div>
        </div>
      </section>

      {profileData?.is_dual_role && (
        <section className="px-6 mt-5">
          <div className="rounded-[24px] p-4 bg-emerald-50 border border-emerald-100 flex items-center justify-between shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-xl bg-emerald-600/10 flex items-center justify-center text-emerald-600 shrink-0">
                <RefreshCw size={18} className={switchMutation.isPending ? "animate-spin" : ""} />
              </div>
              <div>
                <p className="text-[13px] font-bold text-slate-800">Mode Dual-Identitas Aktif</p>
                <p className="text-[11px] font-medium text-slate-500 mt-0.5">Beralih peran tanpa masuk ulang</p>
              </div>
            </div>
            <button
              onClick={() => switchMutation.mutate()}
              disabled={switchMutation.isPending}
              className="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[12px] shadow-[0_4px_12px_rgba(16,185,129,0.2)] active:scale-[0.98] transition-all flex items-center gap-1.5 shrink-0"
            >
              {switchMutation.isPending ? (
                <Loader2 className="animate-spin" size={12} />
              ) : (
                <>
                  Pindah ke {profileData?.role === 'wali' ? 'Asatidz' : 'Wali'}
                </>
              )}
            </button>
          </div>
        </section>
      )}

      {groups.map((g) => (
        <section key={g.title} className="px-6 mt-6">
          <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2 px-1">
            {g.title}
          </p>
          <div className="bg-card rounded-2xl border border-border divide-y divide-border overflow-hidden shadow-[var(--shadow-soft)]">
            {g.items.map((it) => {
              const Icon = it.icon;
              return (
                <button
                  key={it.label}
                  className="w-full flex items-center gap-3 p-4 active:bg-secondary transition"
                >
                  <div className="w-10 h-10 rounded-xl bg-secondary flex items-center justify-center text-primary">
                    <Icon size={18} />
                  </div>
                  <span className="flex-1 text-left text-sm font-semibold text-foreground">
                    {it.label}
                  </span>
                  <ChevronRight size={18} className="text-muted-foreground" />
                </button>
              );
            })}
          </div>
        </section>
      ))}

      <section className="px-6 mt-6">
        <button
          onClick={handleLogout}
          className="w-full flex items-center justify-center gap-2 p-4 rounded-2xl bg-destructive/10 text-destructive font-semibold text-sm border border-destructive/20"
        >
          <LogOut size={18} />
          Keluar
        </button>
      </section>
    </MobileShell>
  );
}
