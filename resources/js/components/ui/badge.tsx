/**
 * Brixel FactoryOS — shadcn/ui Badge Component
 * Refined status badges.
 */

import * as React from "react";
import { cva, type VariantProps } from "class-variance-authority";
import { cn } from "@/lib/utils";

const badgeVariants = cva(
  "inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold",
  {
    variants: {
      variant: {
        default:
          "border-transparent bg-slate-100 text-slate-600",
        secondary:
          "border-transparent bg-slate-200 text-slate-700",
        destructive:
          "border-transparent bg-rose-50 text-rose-600",
        outline:
          "border-slate-200 bg-transparent text-slate-600",
        success:
          "border-emerald-200 bg-emerald-50 text-emerald-700",
        warning:
          "border-amber-200 bg-amber-50 text-amber-700",
        error:
          "border-rose-200 bg-rose-50 text-rose-700",
        info:
          "border-blue-200 bg-blue-50 text-blue-700",
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
