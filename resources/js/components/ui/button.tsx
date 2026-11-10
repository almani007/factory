/**
 * Brixel FactoryOS — shadcn/ui Button Component
 * CVA-based variants: default, destructive, outline, secondary, ghost, link, glass.
 */

import * as React from "react";
import { Slot } from "@radix-ui/react-slot";
import { cva, type VariantProps } from "class-variance-authority";
import { cn } from "@/lib/utils";

const buttonVariants = cva(
  "inline-flex items-center justify-center gap-1.5 rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed",
  {
    variants: {
      variant: {
        default:
          "bg-factory-600 text-white shadow-sm hover:bg-factory-700 focus-visible:ring-factory-500/30",
        destructive:
          "bg-rose-600 text-white shadow-sm hover:bg-rose-700 focus-visible:ring-rose-500/30",
        outline:
          "border border-slate-300 bg-white text-slate-700 shadow-sm hover:bg-slate-50 focus-visible:ring-factory-500/30",
        secondary:
          "bg-slate-100 text-slate-800 shadow-sm hover:bg-slate-200 focus-visible:ring-slate-400/30",
        ghost:
          "text-slate-600 hover:bg-slate-100 hover:text-slate-900 focus-visible:ring-slate-400/30",
        link:
          "text-factory-600 underline-offset-4 hover:underline focus-visible:ring-factory-500/30",
        glass:
          "glass text-slate-800 hover:bg-slate-200/60 focus-visible:ring-factory-500/30",
      },
      size: {
        default: "h-9 px-4",
        sm: "h-8 px-3 text-xs",
        lg: "h-10 px-6",
        icon: "h-9 w-9",
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
