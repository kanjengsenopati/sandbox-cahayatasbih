import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useQuery } from "@tanstack/react-query";
import { fetchInformationDetail } from "@/lib/api";
import { ArrowLeft, Calendar, Tag, Loader2, ImageOff } from "lucide-react";
import { MobileShell } from "@/components/MobileShell";
import { useState } from "react";
import { resolveImageUrl } from "@/lib/utils";

export const Route = createFileRoute("/berita/$newsId")({
  component: BeritaDetail,
  head: () => ({
    meta: [{ title: "Berita — SantriPay" }],
  }),
});

function BeritaDetail() {
  const { newsId } = Route.useParams();
  const navigate = useNavigate();
  const [imgError, setImgError] = useState(false);

  const { data, isLoading, error } = useQuery({
    queryKey: ["berita-detail", newsId],
    queryFn: async () => {
      const res = await fetchInformationDetail(newsId);
      return res.data.data;
    },
  });

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-background">
        <Loader2 className="animate-spin text-primary" size={40} />
      </div>
    );
  }

  if (error || !data) {
    return (
      <div className="min-h-screen flex flex-col items-center justify-center p-6 text-center space-y-4 bg-background">
        <p className="text-muted-foreground">Berita tidak ditemukan.</p>
        <button
          onClick={() => navigate({ to: "/dashboard" })}
          className="text-primary font-bold"
        >
          Kembali ke Beranda
        </button>
      </div>
    );
  }

  const imageUrl = resolveImageUrl(data.image);

  const formattedDate = new Date(data.created_at).toLocaleDateString("id-ID", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
  });

  return (
    <MobileShell>
      {/* Sticky Header */}
      <header className="sticky top-0 z-30 flex items-center gap-3 px-5 py-4 bg-background/80 backdrop-blur-md border-b border-border/50">
        <button
          onClick={() => navigate({ to: "/dashboard" })}
          className="w-10 h-10 rounded-2xl bg-secondary flex items-center justify-center active:scale-90 transition-transform"
        >
          <ArrowLeft size={18} className="text-foreground" />
        </button>
        <h1 className="text-base font-bold text-foreground truncate">
          Detail Berita
        </h1>
      </header>

      <div className="px-5 pb-10">
        {/* Feature Image */}
        {imageUrl && !imgError ? (
          <div className="relative w-full aspect-video rounded-3xl overflow-hidden shadow-[var(--shadow-card)] mt-5 mb-6">
            <img
              src={imageUrl}
              alt={data.title}
              className="w-full h-full object-cover"
              onError={() => setImgError(true)}
            />
            <div className="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent" />
          </div>
        ) : (
          <div className="relative w-full aspect-video rounded-3xl overflow-hidden shadow-[var(--shadow-card)] mt-5 mb-6 bg-secondary flex items-center justify-center">
            <ImageOff size={48} className="text-muted-foreground/30" />
          </div>
        )}

        {/* Category Badge */}
        {data.information_category && (
          <div className="mb-4">
            <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-wider">
              <Tag size={10} />
              {data.information_category.name}
            </span>
          </div>
        )}

        {/* Title */}
        <h2 className="text-xl font-bold text-foreground leading-tight mb-4">
          {data.title}
        </h2>

        {/* Meta */}
        <div className="flex items-center gap-2 mb-6">
          <Calendar size={12} className="text-muted-foreground" />
          <span className="text-xs text-muted-foreground font-medium">
            {formattedDate}
          </span>
        </div>

        {/* Separator */}
        <div className="h-px bg-border mb-6" />

        {/* Content */}
        <div
          className="prose prose-sm max-w-none text-foreground/85 leading-relaxed
            [&_p]:mb-4 [&_p]:text-sm [&_p]:leading-relaxed
            [&_img]:rounded-2xl [&_img]:shadow-[var(--shadow-soft)]
            [&_h1]:text-lg [&_h1]:font-bold [&_h1]:mb-3
            [&_h2]:text-base [&_h2]:font-bold [&_h2]:mb-3
            [&_h3]:text-sm [&_h3]:font-bold [&_h3]:mb-2
            [&_ul]:pl-5 [&_ul]:mb-4 [&_ul]:list-disc
            [&_ol]:pl-5 [&_ol]:mb-4 [&_ol]:list-decimal
            [&_li]:text-sm [&_li]:mb-1
            [&_a]:text-primary [&_a]:underline
            [&_blockquote]:border-l-4 [&_blockquote]:border-primary/30 [&_blockquote]:pl-4 [&_blockquote]:italic
            [&_table]:w-full [&_table]:text-sm [&_th]:bg-secondary [&_th]:p-2 [&_td]:p-2 [&_td]:border-b [&_td]:border-border"
          dangerouslySetInnerHTML={{ __html: data.content }}
        />
      </div>
    </MobileShell>
  );
}
