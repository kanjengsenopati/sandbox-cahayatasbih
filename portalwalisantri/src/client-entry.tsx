import { createRoot } from "react-dom/client";
import { RouterProvider } from "@tanstack/react-router";
import { getRouter } from "./router";
import "./styles.css";

// === FORCE HARD RESET: Unregister old SW + purge all caches ===
if ("serviceWorker" in navigator) {
  navigator.serviceWorker.getRegistrations().then((registrations) => {
    registrations.forEach((r) => r.unregister());
  });
}
if ("caches" in window) {
  caches.keys().then((names) => {
    names.forEach((name) => caches.delete(name));
  });
}
// === END FORCE RESET ===

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
