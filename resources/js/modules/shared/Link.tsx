/**
 * Brixel FactoryOS — Shared Link Component
 * Inertia.js link wrapper with active state detection.
 */
import { Link as InertiaLink, usePage } from '@inertiajs/react';
import { clsx } from 'clsx';

type LinkProps = {
  href: string;
  children: React.ReactNode;
  className?: string;
};

export default function Link({ href, children, className }: LinkProps) {
  const { url } = usePage() as any;
  const isActive = url === href || url.startsWith(href + '/');

  return (
    <InertiaLink
      href={href}
      class={clsx(className, isActive ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900')}
      method="get"
    >
      {children}
    </InertiaLink>
  );
}