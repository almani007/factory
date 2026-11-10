/**
 * Brixel FactoryOS — shadcn/ui utils
 */

export function cn(...classes: (string | undefined | null | false)[]): string {
  return classes.filter(Boolean).join(" ");
}