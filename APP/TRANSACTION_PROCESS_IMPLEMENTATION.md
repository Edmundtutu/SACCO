# SACCO Transaction Management System - Implementation Guide

## Overview

This document provides a comprehensive guide to the SACCO transaction management system implementation. The system is built with Laravel and follows a robust, event-driven architecture with double-entry bookkeeping principles.

## Architecture Overview

The transaction management system is designed with the following key components:

### 1. Core Components

- **DTOs (Data Transfer Objects)**: Handle data validation and transformation
- **Services**: Business logic and transaction processing
- **Handlers**: Transaction-type specific processing
- **Events & Listeners**: Event-driven notifications and logging
- **Models**: Database entities with relationships
- **Controllers**: API endpoints for transaction operations
- **Form Requests**: Input validation and authorization

### 2. Transaction Flow

```
Request → Validation → DTO → Handler → Service → Database → Events → Response
```

## Database Schema

### Key Tables

#### Transactions Table
- **Primary Key**: `id`
- **Unique Identifier**: `transaction_number`
- **Member Reference**: `member_id` (FK to users table)
- **Account Reference**: `account_id` (FK to accounts table)
- **Transaction Details**: `type`, `amount`, `fee_amount`, `net_amount`
- **Status Tracking**: `status`, `balance_before`, `balance_after`
- **Audit Trail**: `processed_by`, `transaction_date`, `value_date`
- **Related Records**: `related_loan_id`, `related_account_id`
- **Metadata**: JSON field for additional transaction data

#### Accounts Table
- **Primary Key**: `id`
- **Unique Identifier**: `account_number`
- **Member Reference**: `member_id` (FK to users table)
- **Product Reference**: `savings_product_id` (FK to savings_products table)
- **Balance Information**: `balance`, `available_balance`, `minimum_balance`
- **Interest Tracking**: `interest_earned`, `last_interest_calculation`
- **Status**: `status` (active, inactive, dormant, closed)

#### General Ledger Table
- **Primary Key**: `id`
- **Transaction Reference**: `transaction_id` (unique identifier)
- **Account Details**: `account_code`, `account_name`, `account_type`
- **Amounts**: `debit_amount`, `credit_amount`
- **Batch Tracking**: `batch_id` for grouping related entries
- **Audit Trail**: `posted_by`, `posted_at`, `status`

## Transaction Types

### 1. Deposit Transactions
- **Type**: `deposit`
- **Category**: `savings`
- **Handler**: `DepositHandler`
- **Validation**: Minimum amount, daily limits, account status
- **Accounting**: Debit Cash, Credit Member Savings Payable

### 2. Withdrawal Transactions
- **Type**: `withdrawal`
- **Category**: `savings`
- **Handler**: `WithdrawalHandler`
- **Validation**: Sufficient balance, minimum balance requirement, daily limits
- **Accounting**: Debit Member Savings Payable, Credit Cash (+ Fee Income if applicable)

### 3. Share Purchase Transactions
- **Type**: `share_purchase`
- **Category**: `share`
- **Handler**: `SharePurchaseHandler`
- **Validation**: Multiple of share value, maximum shares per transaction
- **Accounting**: Debit Cash, Credit Member Share Capital

### 4. Loan Disbursement Transactions
- **Type**: `loan_disbursement`
- **Category**: `loan`
- **Handler**: `LoanDisbursementHandler`
- **Validation**: Loan approval status, guarantor confirmation
- **Accounting**: Debit Loans Receivable, Credit Cash

### 5. Loan Repayment Transactions
- **Type**: `loan_repayment`
- **Category**: `loan`
- **Handler**: `LoanRepaymentHandler`
- **Validation**: Active loan status, minimum repayment amount
- **Accounting**: Debit Cash, Credit Loans Receivable, Credit Interest Income

## Some of the API Endpoints

### Transaction Processing Endpoints

#### Deposit
```http
POST /api/transactions/deposit
Content-Type: application/json

{
    "member_id": 1,
    "account_id": 1,
    "amount": 50000,
    "description": "Monthly savings deposit",
    "payment_reference": "REF123456"
}
```

#### Withdrawal
```http
POST /api/transactions/withdrawal
Content-Type: application/json

{
    "member_id": 1,
    "account_id": 1,
    "amount": 25000,
    "description": "Emergency withdrawal"
}
```

#### Share Purchase
```http
POST /api/transactions/share-purchase
Content-Type: application/json

{
    "member_id": 1,
    "amount": 100000,
    "description": "Share capital investment"
}
```

#### Loan Disbursement
```http
POST /api/transactions/loan-disbursement
Content-Type: application/json

{
    "loan_id": 1,
    "disbursement_method": "cash",
    "notes": "Initial disbursement"
}
```

#### Loan Repayment
```http
POST /api/transactions/loan-repayment
Content-Type: application/json

{
    "loan_id": 1,
    "amount": 15000,
    "payment_method": "cash",
    "notes": "Monthly installment"
}
```

### Query Endpoints

#### Transaction History
```http
GET /api/transactions/history?member_id=1&start_date=2024-01-01&end_date=2024-12-31&type=deposit
```

#### Transaction Summary
```http
GET /api/transactions/summary?member_id=1&start_date=2024-01-01&end_date=2024-12-31
```

#### Transaction Reversal
```http
POST /api/transactions/reverse
Content-Type: application/json

{
    "transaction_id": 123,
    "reason": "Processing error"
}
```

## Event System

### Events

#### TransactionProcessed
- **Triggered**: After successful transaction processing
- **Data**: Transaction object
- **Purpose**: Logging, notifications, real-time updates

#### TransactionFailed
- **Triggered**: When transaction processing fails
- **Data**: Transaction data, error message
- **Purpose**: Error logging, alerting

#### BalanceUpdated
- **Triggered**: After account balance changes
- **Data**: Account ID, old balance, new balance
- **Purpose**: Real-time balance updates, notifications

### Listeners

#### LogTransactionActivity
- **Event**: TransactionProcessed
- **Purpose**: Log transaction details to application logs

#### SendTransactionNotification
- **Event**: TransactionProcessed
- **Purpose**: Send notifications to members and staff

#### UpdateMemberStatistics
- **Event**: TransactionProcessed
- **Purpose**: Update member statistics and analytics

## Error Handling

### Custom Exceptions

#### InvalidTransactionException
- **Triggered**: Invalid transaction data or business rule violations
- **Examples**: Invalid amount, inactive account, insufficient balance

#### InsufficientBalanceException
- **Triggered**: Withdrawal exceeds available balance
- **Handling**: Prevent transaction, return error to user

#### TransactionProcessingException
- **Triggered**: System errors during transaction processing
- **Handling**: Rollback transaction, log error, notify administrators

## Security Features

### 1. Authorization
- User authentication required for all endpoints
- Role-based access control for transaction processing
- Member can only access their own transactions

### 2. Validation
- Input validation using Form Request classes
- Business rule validation in handlers
- Database constraints and foreign key relationships

### 3. Audit Trail
- All transactions logged with processing user
- Balance changes tracked with before/after values
- Transaction reversal tracking with reasons

### 4. Rate Limiting
- Daily transaction limits per member
- Maximum transaction amounts
- Transaction frequency limits

## Double-Entry Bookkeeping

### Accounting Principles
- Every transaction has equal debits and credits
- Automatic balance verification
- Chart of accounts integration

### Account Types
- **Assets**: Cash, Loans Receivable
- **Liabilities**: Member Savings Payable
- **Equity**: Member Share Capital
- **Income**: Interest Income, Fee Income
- **Expenses**: Operating expenses

### Example Entries

#### Deposit Transaction
```
Debit:  Cash in Hand                   50,000
Credit: Member Savings Payable         50,000
```

#### Withdrawal Transaction
```
Debit:  Member Savings Payable         25,000
Credit: Cash in Hand                   24,000
Credit: Fee Income                      1,000
```

## Frontend Integration Guide


### 1. API Integration
- Use consistent error handling for all endpoints
- Implement proper loading states
- Handle network errors gracefully

### 3. Real-time Updates
- Subscribe to transaction events via WebSocket
- Update UI when balance changes occur
- Show transaction confirmations

### 4. Form Handling
- Validate inputs before submission
- Show appropriate error messages
- Implement confirmation dialogs for large amounts

### 5. Transaction History
- Implement pagination for large datasets
- Add filtering and search capabilities
- Export functionality for reports
