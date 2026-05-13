import { createContext, useContext, ReactNode } from "react";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { useLocation } from "@tanstack/react-router";
import { fetchStudents, fetchActiveStudent } from "@/lib/api";
import axios from "axios";

export type Santri = {
  id: string;
  name: string;
  avatar?: string;
  classroom?: {
    name: string;
  };
  school?: {
    name: string;
  };
  barcode: string;
  saldo: number;
  saving: number;
  daily_limit: number;
  total_shopping_today?: number;
  // Computed
  initials: string;
  className: string;
  jenjang: string;
  cardSuffix: string;
};

type Ctx = {
  santri: Santri[];
  active: Santri | null;
  isLoading: boolean;
  switchStudent: (id: string) => Promise<void>;
};

const SantriContext = createContext<Ctx | null>(null);

const mapSantri = (s: any): Santri => ({
  ...s,
  id: s.id?.toString() || "",
  name: s.name || "Santri",
  initials: s.name ? s.name.split(' ').filter(Boolean).map((n: string) => n[0]).join('').substring(0, 2).toUpperCase() : "S",
  className: s.classroom?.name || "-",
  jenjang: s.school?.name || "-",
  cardSuffix: s.barcode ? s.barcode.slice(-4) : "****",
  color: "from-primary to-primary-glow",
  totalDue: 0, // Fallback
});

export function SantriProvider({ children }: { children: ReactNode }) {
  const queryClient = useQueryClient();
  const location = useLocation();
  const isLoginPage = location.pathname === "/login";

  const { data: students = [], isLoading: isLoadingStudents } = useQuery({
    queryKey: ["students"],
    queryFn: async () => {
      const res = await fetchStudents();
      return (res.data || []).map(mapSantri);
    },
    enabled: !isLoginPage,
    retry: false,
  });

  const { data: active = null, isLoading: isLoadingActive } = useQuery({
    queryKey: ["active-student"],
    queryFn: async () => {
      const res = await fetchActiveStudent();
      return res.data ? mapSantri(res.data) : null;
    },
    enabled: !isLoginPage,
    retry: false,
  });

  const switchStudent = async (id: string) => {
    try {
      // Use standard axios for the web route that handles session switching
      await axios.post(`/wali/switch-student/${id}`, {}, {
        withCredentials: true,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content
        }
      });
      // Invalidate queries to refetch active student and other data
      queryClient.invalidateQueries({ queryKey: ["active-student"] });
      queryClient.invalidateQueries({ queryKey: ["dashboard"] });
      queryClient.invalidateQueries({ queryKey: ["bills"] });
      queryClient.invalidateQueries({ queryKey: ["saldo-histories"] });
    } catch (error) {
      console.error("Failed to switch student", error);
    }
  };

  return (
    <SantriContext.Provider 
      value={{ 
        santri: students, 
        active, 
        isLoading: isLoadingStudents || isLoadingActive,
        switchStudent 
      }}
    >
      {children}
    </SantriContext.Provider>
  );
}

export function useSantri() {
  const ctx = useContext(SantriContext);
  if (!ctx) throw new Error("useSantri must be used within SantriProvider");
  return ctx;
}
