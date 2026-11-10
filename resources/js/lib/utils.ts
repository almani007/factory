/**
 * Brixel FactoryOS — shadcn/ui utils
 * clsx + tailwind-merge for robust conditional class composition.
 */

import { type ClassValue, clsx } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}
