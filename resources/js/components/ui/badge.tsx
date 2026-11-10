/**
 * Brixel FactoryOS — shadcn/ui Badge Component
 */

import * as React from "react";
import { cn } from "@/lib/utils";

interface BadgeProps extends React.HTMLAttributes<HTMLSpanElement> {
  variant?: "default" | "secondary" | "success" | "warning" | "error" | "info";
}

const Badge = React.forwardRef<HTMLSpanElement, BadgeProps>(
  ({ className, variant, ...props }, ref) => {
    return (
      <span
        ref={ref}
        className={cn(
          "inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold",
          {
            default: "border-transparent bg-primary text-primary-foreground",
            secondary:
              "border-transparent bg-secondary text-secondary-foreground",
            success:
              "border-transparent bg-green-100 text-green-800",
            warning:
              "border-transparent bg-yellow-100 text-yellow-800",
            error:
              "border-transparent bg-red-100 text-red-800",
            info:
              "border-transparent bg-blue-100 text-blue-800",
          },
          className
        )}
        {...props}
      />
    );
  }
);
Badge.displayName = "Badge";

export { Badge };