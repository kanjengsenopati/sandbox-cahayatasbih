import { QueryClient } from "@tanstack/react-query";
import { createRouter, createHashHistory } from "@tanstack/react-router";
import { routeTree } from "./routeTree.gen";

export const getRouter = () => {
  const queryClient = new QueryClient({
    defaultOptions: {
      queries: {
        // Data dianggap fresh selama 5 menit — mencegah refetch berulang saat navigasi antar halaman
        staleTime: 5 * 60 * 1000,
        // Data di-cache di memory selama 10 menit setelah komponen unmount
        gcTime: 10 * 60 * 1000,
        // Jangan refetch saat window focus (mengurangi API calls saat user alt-tab)
        refetchOnWindowFocus: false,
        // Tetap refetch saat reconnect internet (penting untuk PWA offline)
        refetchOnReconnect: true,
        // Retry 1x jika gagal (default 3 terlalu banyak untuk mobile network)
        retry: 1,
      },
    },
  });

  const router = createRouter({
    routeTree,
    context: { queryClient },
    history: createHashHistory(),
    scrollRestoration: true,
    defaultPreloadStaleTime: 0,
  });

  return router;
};
