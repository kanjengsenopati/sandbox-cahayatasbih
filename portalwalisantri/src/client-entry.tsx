import { createRoot } from "react-dom/client";
import { RouterProvider } from "@tanstack/react-router";
import { getRouter } from "./router";
import "./styles.css";

// === FORCE PURGE CACHE ON VERSION UPDATE ===
const CURRENT_VERSION = "2026-06-19_v3";
if (typeof window !== "undefined") {
    const savedVersion = localStorage.getItem("pwa_version");
    if (savedVersion !== CURRENT_VERSION) {
        console.log(
            `Wali Santri PWA: Version mismatch (${savedVersion} vs ${CURRENT_VERSION}). Purging caches...`,
        );
        if ("caches" in window) {
            caches
                .keys()
                .then((keys) => {
                    return Promise.all(keys.map((key) => caches.delete(key)));
                })
                .then(() => {
                    localStorage.setItem("pwa_version", CURRENT_VERSION);
                    console.log("Wali Santri PWA: Cache purged, restarting...");
                    window.location.reload();
                })
                .catch((err) => {
                    console.error("Wali Santri PWA: Purge error", err);
                    localStorage.setItem("pwa_version", CURRENT_VERSION);
                });
        } else {
            localStorage.setItem("pwa_version", CURRENT_VERSION);
        }
    }
}
// === END FORCE PURGE CACHE ===

// === HASH REDIRECT FOR LOGIN ===
if (typeof window !== "undefined") {
    const path = window.location.pathname;
    if (
        (path.endsWith("/login") || path.endsWith("/login/")) &&
        !window.location.hash.startsWith("#/login")
    ) {
        window.location.hash = "#/login";
    }
}
// === END HASH REDIRECT ===

console.log("Wali Santri PWA: Initializing...");

const router = getRouter();

const rootElement = document.getElementById("root");
if (rootElement) {
    try {
        console.log("Wali Santri PWA: Root element found, rendering...");
        const root = createRoot(rootElement);
        root.render(<RouterProvider router={router} />);
    } catch (err) {
        console.error("Wali Santri PWA: Render Error:", err);
    }
} else {
    console.error("Wali Santri PWA: Root element NOT found!");
}
