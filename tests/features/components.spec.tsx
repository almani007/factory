/**
 * Brixel FactoryOS — Frontend Tests
 * React component tests using Vitest and React Testing Library
 */

import { render, screen } from '@testing-library/react';
import { AppLayout } from '@/components/layout';
import { PageHeader } from '@/components/layout';
import { DataTable } from '@/components/DataTable';
import { StatStrip } from '@/components/StatStrip';
import { KPICard } from '@/components/KPICard';
import { Button } from '@/components/ui/button';

describe('Components', () => {
  describe('AppLayout', () => {
    it('renders the app layout with navigation', () => {
      const { container } = render(
        <AppLayout title="Test">
          <h1>Test Content</h1>
        </AppLayout>
      );

      expect(container).toBeInTheDocument();
      expect(container.querySelector('h1')).toHaveTextContent('Test Content');
    });

    it('displays breadcrumbs when provided', () => {
      const { getByText } = render(
        <AppLayout title="Test Page">
          <h1>Test Content</h1>
        </AppLayout>
      );
    });
  });

  describe('PageHeader', () => {
    it('renders page title', () => {
      const { getByText } = render(
        <PageHeader title="Test Page" subtitle="Test subtitle" />
      );

      expect(getByText('Test Page')).toBeInTheDocument();
      expect(getByText('Test subtitle')).toBeInTheDocument();
    });
  });

  describe('DataTable', () => {
    it('renders table with data', () => {
      const columns = [
        { key: 'name', label: 'Name' },
        { key: 'status', label: 'Status' },
      ];
      const data = [
        { id: 1, name: 'Item 1', status: 'active' },
        { id: 2, name: 'Item 2', status: 'pending' },
      ];

      const { getByText } = render(
        <DataTable
          title="Test Table"
          columns={columns}
          data={data}
        />
      );

      expect(getByText('Test Table')).toBeInTheDocument();
      expect(getByText('Item 1')).toBeInTheDocument();
      expect(getByText('Item 2')).toBeInTheDocument();
    });
  });

  describe('StatStrip', () => {
    it('renders stat items', () => {
      const items = [
        { label: 'Total', value: '100', hint: 'All items' },
        { label: 'Active', value: '50', hint: 'Active items', tone: 'success' },
      ];

      const { getByText } = render(<StatStrip items={items} />);

      expect(getByText('Total')).toBeInTheDocument();
      expect(getByText('100')).toBeInTheString();
      expect(getByText('Active')).toBeInTheDocument();
      expect(getByText('50')).toBeInTheDocument();
    });
  });

  describe('KPICard', () => {
    it('renders KPI with value', () => {
      const { getByText } = render(
        <KPICard
          title="Total Revenue"
          value="Rs. 1,000,000"
          change="+10%"
          trend="up"
        />
      );

      expect(getByText('Total Revenue')).toBeInTheDocument();
      expect(getByText('Rs. 1,000,000')).toBeInTheDocument();
    });
  });

  describe('Button', () => {
    it('renders button with text', () => {
      const { getByText } = render(
        <Button>Click Me</Button>
      );

      expect(getByText('Click Me')).toBeInTheDocument();
    });
  });
});
