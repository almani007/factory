/**
 * Brixel FactoryOS — shadcn/ui Badge Component
 * Semantic status badges with consistent sizing.
 */

import * as React from "react";
import { cva, type VariantProps } from "class-variance-authority";
import { cn } from "@/lib/utils";

const badgeVariants = cva(
  "inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors",
  {
    variants: {
      variant: {
        default:
          "border-transparent bg-primary text-primary-foreground",
        secondary:
          "border-transparent bg-secondary text-secondary-foreground",
        success:
          "border-transparent bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-100",
        warning:
          "border-transparent bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-100",
        error:
          "border-transparent bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-100",
        info:
          "border-transparent bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-100",
        neutral:
          "border-transparent bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200",
        outline:
          "border-slate-300 bg-transparent text-slate-600",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  }
);

interface BadgeProps
  extends React.HTMLAttributes<HTMLSpanElement>,
    VariantProps<typeof badgeVariants> {}

const Badge = React.forwardRef<HTMLSpanElement, BadgeProps>(
  ({ className, variant, ...props }, ref) => {
    return (
      <span
        ref={ref}
        className={cn(badgeVariants({ variant }), className)}
        {...props}
      />
    );
  }
);
Badge.displayName = "Badge";

export { Badge, badgeVariants };
