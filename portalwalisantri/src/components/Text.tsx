import React from "react";

export const Text = {
  H1: ({ children, className = "", style }: { children: React.ReactNode; className?: string; style?: React.CSSProperties }) => (
    <h1 
      className={`font-bold text-slate-900 leading-tight ${className}`}
      style={{ fontSize: "calc(22px * var(--font-scale, 1))", ...style }}
    >
      {children}
    </h1>
  ),
  H2: ({ children, className = "", style }: { children: React.ReactNode; className?: string; style?: React.CSSProperties }) => (
    <h2 
      className={`font-semibold text-slate-800 leading-snug ${className}`}
      style={{ fontSize: "calc(16px * var(--font-scale, 1))", ...style }}
    >
      {children}
    </h2>
  ),
  Amount: ({ children, className = "", style }: { children: React.ReactNode; className?: string; style?: React.CSSProperties }) => (
    <span 
      className={`font-bold text-emerald-600 leading-none ${className}`}
      style={{ fontSize: "calc(18px * var(--font-scale, 1))", ...style }}
    >
      {children}
    </span>
  ),
  Label: ({ children, className = "", style }: { children: React.ReactNode; className?: string; style?: React.CSSProperties }) => (
    <span 
      className={`font-semibold tracking-tight text-slate-400 leading-none ${className}`}
      style={{ fontSize: "calc(11px * var(--font-scale, 1))", ...style }}
    >
      {children}
    </span>
  ),
  Body: ({ children, className = "", style }: { children: React.ReactNode; className?: string; style?: React.CSSProperties }) => (
    <p 
      className={`font-medium text-slate-600 leading-relaxed ${className}`}
      style={{ fontSize: "calc(14px * var(--font-scale, 1))", ...style }}
    >
      {children}
    </p>
  ),
  Caption: ({ children, className = "", style }: { children: React.ReactNode; className?: string; style?: React.CSSProperties }) => (
    <span 
      className={`font-regular italic text-slate-400 leading-normal ${className}`}
      style={{ fontSize: "calc(12px * var(--font-scale, 1))", ...style }}
    >
      {children}
    </span>
  ),
};
