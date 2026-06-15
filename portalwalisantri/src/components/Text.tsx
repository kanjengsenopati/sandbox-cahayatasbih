import React from "react";

export const Text = {
  H1: ({ children, className = "", style }: { children: React.ReactNode; className?: string; style?: React.CSSProperties }) => (
    <h1 
      className={`text-[1.375rem] font-bold text-slate-900 leading-tight ${className}`}
      style={style}
    >
      {children}
    </h1>
  ),
  H2: ({ children, className = "", style }: { children: React.ReactNode; className?: string; style?: React.CSSProperties }) => (
    <h2 
      className={`text-[1rem] font-semibold text-slate-800 leading-snug ${className}`}
      style={style}
    >
      {children}
    </h2>
  ),
  Amount: ({ children, className = "", style }: { children: React.ReactNode; className?: string; style?: React.CSSProperties }) => (
    <span 
      className={`text-[1.125rem] font-bold text-emerald-600 leading-none ${className}`}
      style={style}
    >
      {children}
    </span>
  ),
  Label: ({ children, className = "", style }: { children: React.ReactNode; className?: string; style?: React.CSSProperties }) => (
    <span 
      className={`text-[0.6875rem] font-semibold tracking-tight text-slate-400 leading-none ${className}`}
      style={style}
    >
      {children}
    </span>
  ),
  Body: ({ children, className = "", style }: { children: React.ReactNode; className?: string; style?: React.CSSProperties }) => (
    <p 
      className={`text-[0.875rem] font-medium text-slate-600 leading-relaxed ${className}`}
      style={style}
    >
      {children}
    </p>
  ),
  Caption: ({ children, className = "", style }: { children: React.ReactNode; className?: string; style?: React.CSSProperties }) => (
    <span 
      className={`text-[0.75rem] font-regular italic text-slate-400 leading-normal ${className}`}
      style={style}
    >
      {children}
    </span>
  ),
};
