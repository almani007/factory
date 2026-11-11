/**
 * Brixel FactoryOS — shadcn/ui Button Component
 * Professional enterprise styling with glass effects and gradients.
 */

import * as React from "react";
import { Slot } from "@radix-ui/react-slot";
import { cva, type VariantProps } from "class-variance-authority";
import { cn } from "@/lib/utils";

const buttonVariants = cva(
  "inline-flex items-center justify-center gap-1.5 rounded-xl text-sm font-medium transition-all duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed",
  {
    variants: {
      variant: {
        default:
          "bg-gradient-to-br from-factory-600 to-factory-900 text-white shadow-sm shadow-factory-500/25 hover:shadow-factory-500/40 hover:-translate-y-1 transition-all focus-visible:ring-factory-500/30 active:scale-95",
        destructive:
          "bg-gradient-to-br from-rose-600 to-rose-900 text-white shadow-sm shadow-rose-500/25 hover:shadow-rose-500/40 hover:-translate-y-1 transition-all focus-visible:ring-rose-500/30 active:scale-95",
        outline:
          "border border-slate-200 bg-white text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300 hover:-translate-y-1 transition-all focus-visible:ring-factory-500/30 active:scale-95",
        secondary:
          "bg-slate-100 text-slate-800 shadow-sm hover:bg-slate-200 hover:-translate-y-1 transition-all focus-visible:ring-slate-400/30 active:scale-95",
        ghost:
          "text-slate-600 hover:bg-slate-100 hover:text-slate-900 focus-visible:ring-slate-400/30 active:scale-95",
        link:
          "text-factory-600 underline-offset-4 hover:underline focus-visible:ring-factory-500/30 active:scale-95",
        glass:
          "glass text-slate-800 hover:bg-slate-200/60 focus-visible:ring-factory-500/30 active:scale-95",
      },
      size: {
        default: "h-10 px-4",
        sm: "h-9 px-3 text-xs",
        lg: "h-11 px-6",
        icon: "h-10 w-10",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  }
);

interface ButtonProps
  extends React.ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof buttonVariants> {
  asChild?: boolean;
}

const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  ({ className, variant, size, asChild = false, ...props }, ref) => {
    const Comp = asChild ? Slot : "button";
    return (
      <Comp
        ref={ref}
        className={cn(buttonVariants({ variant, size, className }))}
        {...props}
      />
    );
  }
);
Button.displayName = "Button";

export { Button, buttonVariants };
