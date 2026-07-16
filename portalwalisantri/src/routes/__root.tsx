import { useEffect } from "react";
import { toast } from "sonner";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import {
  Outlet,
  Link,
  createRootRouteWithContext,
  useRouter,
  useNavigate,
  HeadContent,
  Scripts,
} from "@tanstack/react-router";
import { BellRing } from "lucide-react";

import appCss from "../styles.css?url";
import { SantriProvider } from "@/contexts/SantriContext";
import { InstallPromptModal } from "@/components/InstallPromptModal";
import { IOSInstallBanner } from "@/components/IOSInstallBanner";
import { Toaster } from "@/components/ui/sonner";

function NotFoundComponent() {
  return (
    <div className="flex min-h-screen items-center justify-center bg-background px-4">
      <div className="max-w-md text-center">
        <h1 className="text-7xl font-bold text-foreground">404</h1>
        <h2 className="mt-4 text-xl font-semibold text-foreground">Page not found</h2>
        <p className="mt-2 text-sm text-muted-foreground">
          The page you're looking for doesn't exist or has been moved.
        </p>
        <div className="mt-6">
          <Link
            to="/"
            className="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
          >
            Go home
          </Link>
        </div>
      </div>
    </div>
  );
}

function ErrorComponent({ error, reset }: { error: Error; reset: () => void }) {
  console.error(error);
  const router = useRouter();

  return (
    <div className="flex min-h-screen items-center justify-center bg-background px-4">
      <div className="max-w-md text-center">
        <h1 className="text-xl font-semibold tracking-tight text-foreground">
          This page didn't load
        </h1>
        <p className="mt-2 text-sm text-muted-foreground">
          Something went wrong on our end. You can try refreshing or head back home.
        </p>
        <div className="mt-6 flex flex-wrap justify-center gap-2">
          <button
            onClick={() => {
              router.invalidate();
              reset();
            }}
            className="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
          >
            Try again
          </button>
          <a
            href="/"
            className="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent"
          >
            Go home
          </a>
        </div>
      </div>
    </div>
  );
}

export const Route = createRootRouteWithContext<{ queryClient: QueryClient }>()({
  head: () => ({
    meta: [
      { charSet: "utf-8" },
      { name: "viewport", content: "width=device-width, initial-scale=1" },
      { title: "Lovable App" },
      { name: "description", content: "A modern fintech mobile app for parents and students to manage school finances and transactions." },
      { name: "author", content: "Lovable" },
      { property: "og:title", content: "Lovable App" },
      { property: "og:description", content: "A modern fintech mobile app for parents and students to manage school finances and transactions." },
      { property: "og:type", content: "website" },
      { name: "twitter:card", content: "summary" },
      { name: "twitter:site", content: "@Lovable" },
      { name: "twitter:title", content: "Lovable App" },
      { name: "twitter:description", content: "A modern fintech mobile app for parents and students to manage school finances and transactions." },
      { property: "og:image", content: "https://pub-bb2e103a32db4e198524a2e9ed8f35b4.r2.dev/36d114ed-6400-4cbb-8be2-b7481563b603/id-preview-29c3cd1a--480d78d1-3b0b-4b54-b47c-1928e52dec53.lovable.app-1778423056931.png" },
      { name: "twitter:image", content: "https://pub-bb2e103a32db4e198524a2e9ed8f35b4.r2.dev/36d114ed-6400-4cbb-8be2-b7481563b603/id-preview-29c3cd1a--480d78d1-3b0b-4b54-b47c-1928e52dec53.lovable.app-1778423056931.png" },
    ],
    links: [
      {
        rel: "stylesheet",
        href: appCss,
      },
    ],
  }),
  shellComponent: RootShell,
  component: RootComponent,
  notFoundComponent: NotFoundComponent,
  errorComponent: ErrorComponent,
});

function RootShell({ children }: { children: React.ReactNode }) {
  return (
    <>
      {children}
    </>
  );
}

const APP_VERSION = "1.1.0"; // Increment this to force update and purge caches

function RootComponent() {
  const { queryClient } = Route.useRouteContext();
  const navigate = useNavigate();

  useEffect(() => {
    // 1. Initialize Theme (Original vs Modern/Neumorphism)
    const savedTheme = localStorage.getItem("ct-ui-theme") || "asli";
    document.documentElement.classList.toggle("theme-modern", savedTheme === "modern");

    // 2. Initialize Font Scale
    const savedScale = localStorage.getItem("ct-font-scale") || "1.0";
    document.documentElement.style.setProperty("--font-scale", savedScale);

    // 3. Auto Remove Old Cache & Force Update on version change
    const savedVersion = localStorage.getItem("ct-app-version");
    if (savedVersion !== APP_VERSION) {
      console.log(`New version detected (${APP_VERSION}). Purging caches and force-refreshing...`);
      if (typeof window !== "undefined" && "caches" in window) {
        caches.keys().then((keys) => {
          return Promise.all(keys.map((key) => caches.delete(key)));
        }).then(() => {
          localStorage.setItem("ct-app-version", APP_VERSION);
          window.location.reload();
        }).catch((err) => {
          console.error("Failed to clear old caches:", err);
          localStorage.setItem("ct-app-version", APP_VERSION);
          window.location.reload();
        });
      } else {
        localStorage.setItem("ct-app-version", APP_VERSION);
        window.location.reload();
      }
    }
  }, []);

  useEffect(() => {
    if (typeof window !== "undefined" && "Notification" in window) {
      try {
        // Dynamic import to avoid breaking if Firebase variables are not set yet or unsupported
        import("@/lib/firebase").then(({ messaging }) => {
          if (!messaging) return;
          import("firebase/messaging").then(({ onMessage }) => {
            onMessage(messaging, (payload) => {
              const toasterEnabled = localStorage.getItem("ct_toaster_enabled") !== "false";
              if (toasterEnabled) {
                const title = payload.notification?.title || "Pemberitahuan Baru";
                const body = payload.notification?.body || "";
                const type = payload.data?.type || "";

                toast.custom((t) => (
                  <div className="w-full max-w-sm bg-white/95 backdrop-blur-md rounded-[24px] border border-slate-100 shadow-[0_10px_30px_rgba(0,0,0,0.08)] p-5 flex gap-4 items-start animate-in fade-in slide-in-from-top-4 duration-300">
                    <div className={`w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 border ${
                      type === 'Transaction' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-blue-50 text-blue-600 border-blue-100'
                    }`}>
                      <BellRing size={22} className={type === 'Transaction' ? 'animate-bounce' : 'animate-pulse'} />
                    </div>
                    <div className="flex-1 min-w-0">
                      <div className="flex justify-between items-baseline">
                        <span className={`text-[10px] font-extrabold uppercase tracking-widest ${
                          type === 'Transaction' ? 'text-emerald-600' : 'text-blue-600'
                        }`}>
                          {type === 'Transaction' ? 'Transaksi Baru' : 'Info Santri'}
                        </span>
                        <span className="text-[10px] text-slate-400">Baru saja</span>
                      </div>
                      <p className="text-[14px] font-bold text-slate-900 mt-1">{title}</p>
                      <p className="text-[12px] text-slate-500 mt-1.5 leading-relaxed">{body}</p>
                      <div className="mt-4 flex gap-2">
                        <button
                          onClick={() => {
                            toast.dismiss(t);
                            if (type === 'Transaction') navigate({ to: "/tagihan" });
                            else if (type === 'StudentPermit') navigate({ to: "/perizinan" });
                            else navigate({ to: "/" });
                          }}
                          className="px-4 py-2 rounded-xl bg-primary text-white text-[11px] font-bold hover:opacity-90 active:scale-95 transition-all shadow-sm"
                        >
                          Lihat Detail
                        </button>
                        <button
                          onClick={() => toast.dismiss(t)}
                          className="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-[11px] font-bold hover:bg-slate-200"
                        >
                          Tutup
                        </button>
                      </div>
                    </div>
                  </div>
                ), { duration: 8000 });
              }

              // Real-time PWA cache invalidation
              console.log("Real-time notification received, invalidating queries...");
              queryClient.invalidateQueries({ queryKey: ["dashboard"] });
              queryClient.invalidateQueries({ queryKey: ["profile"] });
              queryClient.invalidateQueries({ queryKey: ["permits"] });
              queryClient.invalidateQueries({ queryKey: ["bills"] });
              queryClient.invalidateQueries({ queryKey: ["payment"] });
              queryClient.invalidateQueries({ queryKey: ["students"] });
              queryClient.invalidateQueries({ queryKey: ["active"] });
            });
          }).catch(console.error);
        }).catch(console.error);
      } catch (err) {
        console.error("Gagal mendaftarkan penerima notifikasi foreground:", err);
      }
    }
  }, []);

  return (
    <QueryClientProvider client={queryClient}>
      <SantriProvider>
        <Outlet />
        <InstallPromptModal />
        <IOSInstallBanner />
        <Toaster />
      </SantriProvider>
    </QueryClientProvider>
  );
}
