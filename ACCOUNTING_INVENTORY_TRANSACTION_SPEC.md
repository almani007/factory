# Zoventra FactoryOS — Accounting & Inventory Transaction Specification

**Version 1.0** · 10 November 2026

This document specifies exact posting rules, costing behaviour and reversal logic for the inventory ledger and double-entry accounting engine.

---

## 1. Inventory Ledger — Posting Rules

### 1.1 Authoritative Source

`stock_movements` / `stock_movement_lines` are the authoritative stock history.
`stock_balances` is a controlled read projection, updated atomically with movements.

### 1.2 Movement Types

| Type | Direction | Description |
|---|---|---|
| goods_receipt | IN | Purchase GRN received into warehouse |
| goods_issue | OUT | Material issued to production |
| material_return | IN | Unused material returned from production |
| production_output | OUT | Finished goods produced |
| finished_goods_receipt | IN | Quality-approved finished goods received |
| stock_transfer_out | OUT | Transfer out of warehouse |
| stock_transfer_in | IN | Transfer into warehouse |
| stock_adjustment | IN/OUT | Cycle count adjustment |
| stock_damage | OUT | Damaged stock written off |
| stock_scrap | OUT | Scrapped material |
| customer_return | IN | Customer return received |
| supplier_return | OUT | Return to supplier |

### 1.3 Atomic Stock Posting

Every stock posting must:
1. Begin a database transaction.
2. Acquire row-level locks on affected stock_balance rows (`FOR UPDATE`).
3. Validate `Available >= quantity` for OUT movements (unless override policy allows negative).
4. Insert `stock_movements` header + `stock_movement_lines`.
5. Update `stock_balances` projection.
6. Commit transaction.

**Pseudo-code:**
```php
DB::transaction(function () use ($dto) {
    $movement = StockMovement::create($dto->movementData());
    foreach ($dto->lines as $line) {
        StockMovementLine::create([...$line, 'movement_id' => $movement->id]);
        $this->updateBalance($line, $dto->postingDate);
    }
    $movement->posted_at = now();
    $movement->save();
}, 3); // 3 retries on deadlock/serialization failure
```

### 1.4 Posted Movement Immutability

- Posted movements cannot be silently edited or deleted.
- Corrections use a reversal movement (opposite sign, references original movement).
- Every reversal carries a reason and authorised user ID.

### 1.5 Stock Balances Projection

```
Available = OnHand - Reserved
```

Updated in same transaction as movement posting. Indexed by `(tenant_id, factory_id, warehouse_id, item_id)`.

---

## 2. Double-Entry Accounting — Posting Rules

### 2.1 Authoritative Source

`journal_entries` / `journal_lines` are the authoritative financial record.

### 2.2 Journal Entry Structure

**journal_entries:**
- accounting_book_id, fiscal_period_id
- document_date, posting_date
- currency, exchange_rate_to_base
- reference, description
- status (draft/posted/reversed)
- reversal_reference_id (nullable)
- source_transaction_type, source_transaction_id
- created_by, approved_by, posted_at

**journal_lines:**
- journal_entry_id, account_id
- debit_amount, credit_amount (numeric, 2 decimal places)
- debit_amount_base, credit_amount_base
- currency, exchange_rate
- dimension values (cost centre, department, factory)

### 2.3 Balancing Rule

```
SUM(debit_amount) = SUM(credit_amount)
```

Validated before posting. Cannot post an unbalanced journal.

### 2.4 Posting Lifecycle

```
Draft → Submitted → Posted → (Reversed if corrected)
```

- Draft journals may be edited freely.
- Submitted journals await approval.
- Posted journals cannot be silently changed.
- Closed periods cannot accept backdated postings (beyond configurable tolerance).

### 2.5 Duplicate Posting Prevention

Each journal entry carries a unique `reference` within an accounting book + period. Duplicate references are rejected.

### 2.6 Source Document Linkage

Every journal line must link to its source transaction:
- Purchase order → supplier bill → journal (Dr Inventory, Cr AP)
- Goods receipt → journal (Dr Inventory, Cr GRN clearing)
- Production completion → journal (Dr Finished Goods, Cr WIP)
- Sales invoice → journal (Dr AR, Cr Revenue)
- Customer payment → journal (Dr Cash, Cr AR)

---

## 3. Standard Accounting Posting Examples

### 3.1 Purchase Raw Material on Credit

| Account | Debit | Credit |
|---|---|---|
| Raw Material Inventory | 100,000 | — |
| Supplier Payable (AP) | — | 100,000 |

### 3.2 Material Issued into Production

| Account | Debit | Credit |
|---|---|---|
| Work in Progress (WIP) | 100,000 | — |
| Raw Material Inventory | — | 100,000 |

### 3.3 Finished Goods Completed

| Account | Debit | Credit |
|---|---|---|
| Finished Goods Inventory | 100,000 | — |
| Work in Progress | — | 100,000 |

### 3.4 Finished Goods Sold

| Account | Debit | Credit |
|---|---|---|
| Cost of Goods Sold | 100,000 | — |
| Finished Goods Inventory | — | 100,000 |

### 3.5 Sales Revenue (posted separately)

| Account | Debit | Credit |
|---|---|---|
| Accounts Receivable | 150,000 | — |
| Sales Revenue | — | 150,000 |

### 3.6 Production Labour Cost

| Account | Debit | Credit |
|---|---|---|
| Work in Progress (Direct Labour) | 25,000 | — |
| Labour Payable / Cash | — | 25,000 |

### 3.7 Machine Overhead Allocation

| Account | Debit | Credit |
|---|---|---|
| Work in Progress (Machine Overhead) | 15,000 | — |
| Manufacturing Overhead Control | — | 15,000 |

---

## 4. Costing Architecture

### 4.1 Supported Costing Methods (MVP)

| Method | Usage |
|---|---|
| Standard costing | Planned/expected product cost |
| Actual costing | Actual manufacturing expenses |
| Weighted average | Inventory valuation |
| FIFO | Inventory valuation |
| Job costing | Customer-specific manufacturing |

### 4.2 Cost Components

1. Raw material cost
2. Direct labour
3. Machine operating cost
4. Electricity and utilities
5. Packaging
6. Subcontracting
7. Manufacturing overhead
8. Scrap and wastage
9. Rework cost

### 4.3 Work-Order Costing Flow

```
Material Issue → Dr WIP, Cr Raw Material
Labour Entry   → Dr WIP, Cr Labour Payable
Machine Time   → Dr WIP, Cr Machine Overhead
Overhead       → Dr WIP, Cr Overhead Control
Completion     → Dr Finished Goods, Cr WIP
Variance       → Dr/Cr Variance Account (Actual - Standard)
```

### 4.4 Variance Analysis

Variance = Actual Cost − Standard Cost

Broken down by:
- Material variance (price + usage)
- Labour variance (rate + efficiency)
- Machine variance (rate + efficiency)
- Overhead variance (spending + volume)

System must explain *why* variance occurred, not just show the difference.

### 4.5 Historical Cost Protection

Historical costs must not change when current product prices change. Cost snapshots are taken at production order completion and stored immutably.

---

## 5. Reversal & Correction Rules

### 5.1 Inventory Reversal

A stock movement reversal:
1. Creates a new movement with opposite sign.
2. References original movement ID in `reversal_reference`.
3. Carries reason code and authorised user ID.
4. Updates stock balances atomically.

### 5.2 Journal Reversal

A posted journal correction:
1. Creates a reversing journal entry (dr/cr swapped).
2. References original journal entry ID.
3. Posts in same period (or next period if original period is closed).
4. Creates replacement journal if needed.

### 5.3 Period Closing

- Closed periods cannot accept ordinary backdated postings.
- Period close requires all journals posted and balanced.
- Close creates a period-close journal carrying forward balances.
- Re-open requires authorised user and audit record.

---

## 6. Multi-Currency & Tax

### 6.1 Currency

- All amounts stored in transaction currency and base currency.
- Exchange rate preserved at transaction time (not looked up later).
- Currency revaluation supported at period end.

### 6.2 Tax

- Tax codes and rates configurable by jurisdiction (PK, UK VAT, EU VAT, etc.).
- Tax rules are jurisdiction-specific configuration adapters — not hardcoded.
- Tax transactions tracked separately for reporting.

---

## 7. Integration Consistency

Inventory and accounting transactions cannot silently diverge:

1. Every stock movement that affects value triggers a corresponding journal line.
2. Every journal affecting inventory references a stock movement.
3. A reconciliation report identifies any drift between stock ledger and GL inventory accounts.
4. The drift report runs automatically and alerts on mismatch.