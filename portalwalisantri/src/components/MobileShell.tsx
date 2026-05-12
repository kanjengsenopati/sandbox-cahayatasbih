import { ReactNode } from "react";
import { BottomNav } from "./BottomNav";

export function MobileShell({ children }: { children: ReactNode }) {
  return (
    <div className="min-h-screen w-full flex justify-center bg-gradient-to-b from-secondary to-background">
      <div className="relative w-full max-w-md min-h-screen bg-background pb-32">
        {children}
        <BottomNav />
      </div>
    </div>
  );
}
