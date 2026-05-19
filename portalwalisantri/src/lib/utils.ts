import { clsx, type ClassValue } from "clsx";
import { twMerge } from "tailwind-merge";

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

/**
 * Resolves an image URL to bypass Nginx static file 404s on VPS environments
 * without a proper public/storage symlink.
 */
export function resolveImageUrl(url: string | null | undefined): string | null {
  if (!url) return null;
  
  // If it's static public assets starting with assets/
  if (url.startsWith('assets/') || url.startsWith('/assets/')) {
    return url.startsWith('/') ? url : `/${url}`;
  }
  
  // If the URL contains /storage/, extract everything after it to use the file-asset fallback
  const storageMatch = url.match(/\/storage\/(.*)$/);
  if (storageMatch && storageMatch[1]) {
    return `/file-asset?p=${encodeURIComponent(storageMatch[1])}`;
  }
  
  // If the URL starts with storage/ directly (from DB raw value)
  if (url.startsWith('storage/')) {
    return `/file-asset?p=${encodeURIComponent(url.replace('storage/', ''))}`;
  }
  
  // If it's a full external HTTP URL that doesn't contain /storage/
  if (url.startsWith('http://') || url.startsWith('https://')) {
    return url;
  }
  
  // If it's just a raw path without storage/ (e.g., 'proofs/xxx.png' or 'images/information/xxx.jpg')
  // We assume it belongs in storage
  let cleanUrl = url.startsWith('/') ? url.substring(1) : url;
  return `/file-asset?p=${encodeURIComponent(cleanUrl)}`;
}
