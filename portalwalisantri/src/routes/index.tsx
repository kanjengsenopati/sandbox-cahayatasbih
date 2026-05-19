import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useEffect } from "react";
import { fetchProfile } from "@/lib/api";
import { useQuery } from "@tanstack/react-query";
import { Loader2 } from "lucide-react";

export const Route = createFileRoute("/")({
  component: RootDispatcher,
  head: () => ({
    meta: [
      { title: "CT-Mobile — Loading" },
      { name: "description", content: "Mempersiapkan halaman aplikasi CT-Mobile." },
    ],
  }),
});

function RootDispatcher() {
  const navigate = useNavigate();

  const { data: profileRes, isLoading, isError } = useQuery({
    queryKey: ["profile"],
    queryFn: async () => {
      const res = await fetchProfile();
      return res.data;
    },
    retry: false,
  });

  useEffect(() => {
    if (!isLoading) {
      if (isError || !profileRes) {
        navigate({ to: "/login" });
      } else if (profileRes.role === "asatidz") {
        navigate({ to: "/asatidz/dashboard" });
      } else {
        navigate({ to: "/dashboard" });
      }
    }
  }, [profileRes, isLoading, isError, navigate]);

  return (
    <div className="min-h-screen w-full flex items-center justify-center bg-slate-50">
      <div className="flex flex-col items-center gap-3">
        <Loader2 className="animate-spin text-[#9b1de8]" size={36} />
        <p className="text-sm font-medium text-slate-500">Mempersiapkan aplikasi...</p>
      </div>
    </div>
  );
}
