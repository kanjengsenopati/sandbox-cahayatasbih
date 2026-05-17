import React from "react";

export const Text = {
  H1: ({ children, className = "" }: { children: React.ReactNode; className?: string }) => (
    <h1 className={`text-[22px] font-bold text-slate-900 leading-tight ${className}`}>
      {children}
    </h1>
  ),
  H2: ({ children, className = "" }: { children: React.ReactNode; className?: string }) => (
    <h2 className={`text-[16px] font-semibold text-slate-800 leading-snug ${className}`}>
      {children}
    </h2>
  ),
  Amount: ({ children, className = "" }: { children: React.ReactNode; className?: string }) => (
    <span className={`text-[18px] font-bold text-emerald-600 leading-none ${className}`}>
      {children}
    </span>
  ),
  Label: ({ children, className = "" }: { children: React.ReactNode; className?: string }) => (
    <span className={`text-[11px] font-bold uppercase tracking-widest text-slate-400 leading-none ${className}`}>
      {children}
    </span>
  ),
  Body: ({ children, className = "" }: { children: React.ReactNode; className?: string }) => (
    <p className={`text-[14px] font-medium text-slate-600 leading-relaxed ${className}`}>
      {children}
    </p>
  ),
  Caption: ({ children, className = "" }: { children: React.ReactNode; className?: string }) => (
    <span className={`text-[12px] font-regular italic text-slate-400 leading-normal ${className}`}>
      {children}
    </span>
  ),
};
