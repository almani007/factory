/**
 * Brixel FactoryOS — shadcn/ui Button Component
 */

import * as React from "react";
import { Slot } from "@radix-ui/react-slot";
import { clsx } from "clsx";
import { cn } from "@/lib/utils";

interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: "default" | "destructive" | "outline" | "secondary" | "ghost" | "link";
  size?: "default" | "sm" | "lg" | "icon";
  asChild?: boolean;
}

const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  ({ className, variant, size, asChild = false, ...props }, ref) => {
    const Comp = asChild ? Slot : "button";
    return (
      <Comp
        ref={ref}
        className={cn(
          "inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-factory-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50",
          {
            default: "bg-factory-600 text-white hover:bg-factory-500",
            destructive:
              "bg-red-600 text-white hover:bg-red-700",
            outline:
              "border border-input bg-background hover:bg-accent hover:text-accent-foreground",
            secondary:
              "bg-secondary text-secondary-foreground hover:bg-secondary/80",
            ghost:
              "hover:bg-accent hover:text-accent-foreground",
            link:
              "text-primary underline-offset-4 hover:underline",
          },
          {
            sm: "h-8 px-3 text-xs",
            lg: "h-11 px-8 text-base",
            icon: "h-10 w-10",
          },
          className
        )}
        {...props}
      />
    );
  }
);
Button.displayName = "Button";

export { Button };