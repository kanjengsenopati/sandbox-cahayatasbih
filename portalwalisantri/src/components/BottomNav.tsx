import { Link, useLocation } from "@tanstack/react-router";
import { Home, Receipt, User } from "lucide-react";

const items = [
  { to: "/dashboard", label: "Beranda", icon: Home },
  { to: "/tagihan", label: "Tagihan", icon: Receipt },
  { to: "/profil", label: "Profil", icon: User },
] as const;

export function BottomNav() {
  const loc = useLocation();
  return (
    <nav className="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md z-50 px-4 pb-4 pt-2">
      <div className="flex items-center justify-around rounded-3xl bg-card/95 backdrop-blur-xl border border-border shadow-[var(--shadow-card)] px-2 py-2">
        {items.map(({ to, label, icon: Icon }) => {
          const active = loc.pathname === to;
          return (
            <Link
              key={to}
              to={to}
              className={`flex flex-col items-center gap-1 px-4 py-1.5 rounded-2xl transition-all ${
                active ? "bg-primary/10 -translate-y-0.5" : ""
              }`}
            >
              <div
                className={`flex items-center justify-center w-10 h-10 rounded-2xl transition-all ${
                  active
                    ? "text-white shadow-[var(--shadow-glow)] ring-2 ring-primary/30 scale-110"
                    : "text-muted-foreground"
                }`}
                style={
                  active
                    ? { background: "var(--gradient-card)" }
                    : undefined
                }
              >
                <Icon size={20} strokeWidth={2.4} />
              </div>
              <span
                className={`text-[10px] font-bold tracking-wide ${
                  active ? "text-primary" : "text-muted-foreground"
                }`}
              >
                {label}
              </span>
            </Link>
          );
        })}
      </div>
    </nav>
  );
}
