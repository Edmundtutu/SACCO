# SACCO REST API - Complete Documentation

## Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Error Handling](#error-handling)
4. [Rate Limiting](#rate-limiting)
5. [API Endpoints](#api-endpoints)
   - [Authentication Endpoints](#authentication-endpoints)
   - [Savings & Accounts Endpoints](#savings--accounts-endpoints)
   - [Loans Endpoints](#loans-endpoints)
   - [Shares Endpoints](#shares-endpoints)
   - [Reports Endpoints](#reports-endpoints)
6. [Data Models](#data-models)
7. [Status Codes](#status-codes)
8. [Examples](#examples)

---

## Overview

The SACCO REST API provides comprehensive functionality for managing Savings and Credit Cooperative Organizations. It follows RESTful principles and returns JSON responses.

### Base URL
```
Production: https://your-domain.com/api
Development: http://localhost:8000/api
```

### API Version
- Current Version: 1.0
- Content-Type: `application/json`
- Authentication: JWT Bearer Token

---

## Authentication

The API uses JWT (JSON Web Tokens) for authentication. Include the token in the Authorization header for protected endpoints.

### Header Format
```
Authorization: Bearer {your-jwt-token}
```

### Token Lifecycle
- **Expiration**: 60 minutes (configurable)
- **Refresh**: Available before expiration
- **Logout**: Invalidates token immediately

---

## Error Handling

### Standard Error Response
```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

### Common Error Codes
- `400` - Bad Request (validation errors)
- `401` - Unauthorized (authentication required)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found (resource doesn't exist)
- `422` - Unprocessable Entity (validation failed)
- `500` - Internal Server Error

---

## Rate Limiting

- **Limit**: 60 requests per minute per IP
- **Headers**: Rate limit info included in response headers
- **Exceeded**: Returns 429 status code

---

## API Endpoints

## Authentication Endpoints

### 1. Register Member

Register a new SACCO member account.

**Endpoint:** `POST /auth/register`

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+1234567890",
  "national_id": "ID123456789",
  "date_of_birth": "1990-01-15",
  "gender": "male",
  "address": "123 Main Street, City",
  "occupation": "Teacher",
  "monthly_income": 25000,
  "next_of_kin_name": "Jane Doe",
  "next_of_kin_relationship": "Spouse",
  "next_of_kin_phone": "+1234567891",
  "next_of_kin_address": "123 Main Street, City",
  "employer_name": "ABC School",
  "employer_address": "456 School Road",
  "employer_phone": "+1234567892"
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Registration successful. Your membership is pending approval.",
  "data": {
    "member_number": "M000001",
    "status": "pending_approval"
  }
}
```

**Validation Rules:**
- `name`: required, string, max 255 characters
- `email`: required, email, unique
- `password`: required, min 8 characters, confirmed
- `phone`: required, string, max 20 characters
- `national_id`: required, string, unique
- `date_of_birth`: required, valid date
- `gender`: required, in: male,female,other
- `monthly_income`: required, numeric, min 0

---

### 2. Login

Authenticate user and receive JWT token.

**Endpoint:** `POST /auth/login`

**Request Body:**
```json
{
  "email": "admin@sacco.com",
  "password": "password123"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "System Administrator",
      "email": "admin@sacco.com",
      "member_number": "ADMIN001",
      "role": "admin",
      "status": "active"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

**Error Response (401):**
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

---

### 3. Get Profile

Get authenticated user's profile information.

**Endpoint:** `GET /auth/profile`
**Authentication:** Required

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "member_number": "M000001",
      "role": "member",
      "status": "active",
      "phone": "+1234567890",
      "national_id": "ID123456789",
      "date_of_birth": "1990-01-15",
      "gender": "male",
      "address": "123 Main Street, City",
      "occupation": "Teacher",
      "monthly_income": "25000.00",
      "membership_date": "2024-01-15",
      "member_profile": {
        "next_of_kin_name": "Jane Doe",
        "next_of_kin_relationship": "Spouse",
        "next_of_kin_phone": "+1234567891",
        "employer_name": "ABC School"
      }
    },
    "summary": {
      "total_savings": "45000.00",
      "total_shares": 150,
      "active_loan_balance": "0.00"
    }
  }
}
```

---

### 4. Update Profile

Update user profile information.

**Endpoint:** `PUT /auth/profile`
**Authentication:** Required

**Request Body:**
```json
{
  "name": "John Updated Doe",
  "phone": "+1234567899",
  "address": "456 New Address",
  "occupation": "Senior Teacher",
  "monthly_income": 30000
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "name": "John Updated Doe",
    "phone": "+1234567899",
    "address": "456 New Address",
    "occupation": "Senior Teacher",
    "monthly_income": "30000.00"
  }
}
```

---

### 5. Change Password

Change user password.

**Endpoint:** `POST /auth/change-password`
**Authentication:** Required

**Request Body:**
```json
{
  "current_password": "oldpassword123",
  "new_password": "newpassword123",
  "new_password_confirmation": "newpassword123"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Password changed successfully"
}
```

---

### 6. Logout

Logout user and invalidate token.

**Endpoint:** `POST /auth/logout`
**Authentication:** Required

**Success Response (200):**
```json
{
  "success": true,
  "message": "Successfully logged out"
}
```

---

### 7. Refresh Token

Refresh JWT token before expiration.

**Endpoint:** `POST /auth/refresh`
**Authentication:** Required

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

---

### 8. Approve Member (Admin Only)

Approve or reject pending member registration.

**Endpoint:** `POST /auth/approve-member/{memberId}`
**Authentication:** Required (Admin/Staff only)

**Request Body:**
```json
{
  "action": "approve",
  "rejection_reason": "Optional reason if rejecting"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Member approved successfully"
}
```

---

## Savings & Accounts Endpoints

### 1. Get Member Accounts

Get all accounts for authenticated member.

**Endpoint:** `GET /savings/accounts`
**Authentication:** Required

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "account_number": "SA00000001",
      "balance": "25000.00",
      "available_balance": "25000.00",
      "minimum_balance": "1000.00",
      "interest_earned": "1250.00",
      "status": "active",
      "last_transaction_date": "2024-01-15T10:30:00.000000Z",
      "savings_product": {
        "id": 1,
        "name": "Compulsory Savings",
        "type": "compulsory",
        "interest_rate": "5.00",
        "minimum_balance": "1000.00"
      }
    }
  ]
}
```

---

### 2. Get Savings Products

Get all available savings products.

**Endpoint:** `GET /savings/products`
**Authentication:** Required

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Compulsory Savings",
      "code": "CS001",
      "description": "Mandatory savings for all members",
      "type": "compulsory",
      "minimum_balance": "1000.00",
      "maximum_balance": null,
      "interest_rate": "5.00",
      "interest_calculation": "simple",
      "interest_payment_frequency": "annually",
      "minimum_monthly_contribution": "500.00",
      "withdrawal_fee": "100.00",
      "allow_partial_withdrawals": false,
      "minimum_notice_days": 30,
      "is_active": true
    },
    {
      "id": 2,
      "name": "Voluntary Savings",
      "code": "VS001",
      "description": "Flexible savings account with easy access",
      "type": "voluntary",
      "minimum_balance": "500.00",
      "interest_rate": "3.00",
      "withdrawal_fee": "50.00",
      "allow_partial_withdrawals": true,
      "minimum_notice_days": 0,
      "is_active": true
    }
  ]
}
```

---

### 3. Make Deposit

Make a deposit to a savings account.

**Endpoint:** `POST /savings/deposit`
**Authentication:** Required

**Request Body:**
```json
{
  "account_id": 1,
  "amount": 5000,
  "payment_method": "cash",
  "payment_reference": "REF123456",
  "description": "Monthly savings deposit"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Deposit successful",
  "data": {
    "transaction": {
      "id": 100,
      "transaction_number": "TXN0000000100",
      "type": "deposit",
      "amount": "5000.00",
      "fee_amount": "0.00",
      "net_amount": "5000.00",
      "balance_before": "20000.00",
      "balance_after": "25000.00",
      "description": "Monthly savings deposit",
      "payment_method": "cash",
      "status": "completed",
      "transaction_date": "2024-01-15T10:30:00.000000Z"
    },
    "new_balance": "25000.00"
  }
}
```

**Validation Rules:**
- `account_id`: required, exists in accounts table
- `amount`: required, numeric, min 0.01
- `payment_method`: required, in: cash,bank_transfer,mobile_money,check
- `description`: optional, string

---

### 4. Make Withdrawal

Make a withdrawal from a savings account.

**Endpoint:** `POST /savings/withdraw`
**Authentication:** Required

**Request Body:**
```json
{
  "account_id": 1,
  "amount": 2000,
  "description": "Emergency withdrawal"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Withdrawal successful",
  "data": {
    "transaction": {
      "id": 101,
      "transaction_number": "TXN0000000101",
      "type": "withdrawal",
      "amount": "2000.00",
      "fee_amount": "50.00",
      "net_amount": "2000.00",
      "balance_before": "25000.00",
      "balance_after": "22950.00",
      "description": "Emergency withdrawal",
      "status": "completed",
      "transaction_date": "2024-01-15T11:30:00.000000Z"
    },
    "withdrawal_fee": "50.00",
    "new_balance": "22950.00"
  }
}
```

**Error Response (400):**
```json
{
  "success": false,
  "message": "Withdrawal not allowed. Check account status and minimum balance."
}
```

---

### 5. Get Account Transactions

Get transaction history for a specific account.

**Endpoint:** `GET /savings/accounts/{accountId}/transactions`
**Authentication:** Required

**Query Parameters:**
- `start_date`: Filter from date (YYYY-MM-DD)
- `end_date`: Filter to date (YYYY-MM-DD)
- `type`: Filter by transaction type (deposit, withdrawal)
- `page`: Page number for pagination

**Example:** `GET /savings/accounts/1/transactions?start_date=2024-01-01&type=deposit&page=1`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 100,
        "transaction_number": "TXN0000000100",
        "type": "deposit",
        "amount": "5000.00",
        "fee_amount": "0.00",
        "balance_before": "20000.00",
        "balance_after": "25000.00",
        "description": "Monthly savings deposit",
        "payment_method": "cash",
        "status": "completed",
        "transaction_date": "2024-01-15T10:30:00.000000Z"
      }
    ],
    "from": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 1
  }
}
```

---

## Loans Endpoints

*Note: These endpoints are architecturally ready but require additional controller implementation.*

### 1. Get Member Loans

**Endpoint:** `GET /loans`
**Authentication:** Required

### 2. Apply for Loan

**Endpoint:** `POST /loans/apply`
**Authentication:** Required

**Request Body:**
```json
{
  "loan_product_id": 1,
  "principal_amount": 50000,
  "repayment_period_months": 24,
  "purpose": "Business expansion",
  "guarantors": [
    {
      "guarantor_id": 2,
      "guaranteed_amount": 25000
    },
    {
      "guarantor_id": 3,
      "guaranteed_amount": 25000
    }
  ]
}
```

### 3. Get Loan Products

**Endpoint:** `GET /loans/products`
**Authentication:** Required

### 4. Approve Loan (Staff Only)

**Endpoint:** `POST /loans/{loanId}/approve`
**Authentication:** Required (Staff only)

### 5. Disburse Loan (Staff Only)

**Endpoint:** `POST /loans/{loanId}/disburse`
**Authentication:** Required (Staff only)

### 6. Make Loan Repayment

**Endpoint:** `POST /loans/{loanId}/repay`
**Authentication:** Required

### 7. Get Repayment Schedule

**Endpoint:** `GET /loans/{loanId}/schedule`
**Authentication:** Required

---

## Shares Endpoints

*Note: These endpoints are architecturally ready but require additional controller implementation.*

### 1. Get Member Shares

**Endpoint:** `GET /shares`
**Authentication:** Required

### 2. Purchase Shares

**Endpoint:** `POST /shares/purchase`
**Authentication:** Required

### 3. Get Dividend History

**Endpoint:** `GET /shares/dividends`
**Authentication:** Required

### 4. Get Share Certificates

**Endpoint:** `GET /shares/certificates`
**Authentication:** Required

---

## Reports Endpoints

*Note: These endpoints are architecturally ready but require additional controller implementation.*

### 1. Member Statement

**Endpoint:** `GET /reports/member-statement`
**Authentication:** Required

### 2. Savings Summary

**Endpoint:** `GET /reports/savings-summary`
**Authentication:** Required

### 3. Loans Summary

**Endpoint:** `GET /reports/loans-summary`
**Authentication:** Required

### 4. Financial Summary (Staff Only)

**Endpoint:** `GET /reports/financial-summary`
**Authentication:** Required (Staff only)

### 5. Trial Balance (Staff Only)

**Endpoint:** `GET /reports/trial-balance`
**Authentication:** Required (Staff only)

### 6. Balance Sheet (Staff Only)

**Endpoint:** `GET /reports/balance-sheet`
**Authentication:** Required (Staff only)

---

## Data Models

### User Model
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "member_number": "M000001",
  "role": "member",
  "status": "active",
  "phone": "+1234567890",
  "national_id": "ID123456789",
  "date_of_birth": "1990-01-15",
  "gender": "male",
  "address": "123 Main Street",
  "occupation": "Teacher",
  "monthly_income": "25000.00",
  "membership_date": "2024-01-15",
  "approved_at": "2024-01-16T09:00:00.000000Z",
  "created_at": "2024-01-15T08:00:00.000000Z",
  "updated_at": "2024-01-15T08:00:00.000000Z"
}
```

### Account Model
```json
{
  "id": 1,
  "account_number": "SA00000001",
  "member_id": 1,
  "savings_product_id": 1,
  "balance": "25000.00",
  "available_balance": "25000.00",
  "minimum_balance": "1000.00",
  "interest_earned": "1250.00",
  "last_interest_calculation": "2024-01-01",
  "maturity_date": null,
  "status": "active",
  "last_transaction_date": "2024-01-15T10:30:00.000000Z",
  "created_at": "2024-01-15T08:00:00.000000Z",
  "updated_at": "2024-01-15T10:30:00.000000Z"
}
```

### Transaction Model
```json
{
  "id": 100,
  "transaction_number": "TXN0000000100",
  "member_id": 1,
  "account_id": 1,
  "type": "deposit",
  "category": "savings",
  "amount": "5000.00",
  "fee_amount": "0.00",
  "net_amount": "5000.00",
  "balance_before": "20000.00",
  "balance_after": "25000.00",
  "description": "Monthly savings deposit",
  "payment_method": "cash",
  "payment_reference": "REF123456",
  "status": "completed",
  "transaction_date": "2024-01-15T10:30:00.000000Z",
  "value_date": "2024-01-15T10:30:00.000000Z",
  "processed_by": 1,
  "created_at": "2024-01-15T10:30:00.000000Z",
  "updated_at": "2024-01-15T10:30:00.000000Z"
}
```

### Savings Product Model
```json
{
  "id": 1,
  "name": "Compulsory Savings",
  "code": "CS001",
  "description": "Mandatory savings for all members",
  "type": "compulsory",
  "minimum_balance": "1000.00",
  "maximum_balance": null,
  "interest_rate": "5.00",
  "interest_calculation": "simple",
  "interest_payment_frequency": "annually",
  "minimum_monthly_contribution": "500.00",
  "maturity_period_months": null,
  "withdrawal_fee": "100.00",
  "allow_partial_withdrawals": false,
  "minimum_notice_days": 30,
  "is_active": true,
  "additional_rules": null,
  "created_at": "2024-01-15T08:00:00.000000Z",
  "updated_at": "2024-01-15T08:00:00.000000Z"
}
```

---

## Status Codes

### HTTP Status Codes Used

| Code | Meaning | Description |
|------|---------|-------------|
| 200 | OK | Request successful |
| 201 | Created | Resource created successfully |
| 400 | Bad Request | Invalid request parameters |
| 401 | Unauthorized | Authentication required |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation failed |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error |

### Application Status Fields

**User Status:**
- `pending_approval` - New registration awaiting approval
- `active` - Active member
- `inactive` - Temporarily inactive
- `suspended` - Account suspended

**Account Status:**
- `active` - Active account
- `inactive` - Temporarily inactive
- `dormant` - No activity for extended period
- `closed` - Permanently closed

**Transaction Status:**
- `pending` - Transaction initiated but not completed
- `completed` - Transaction successful
- `failed` - Transaction failed
- `reversed` - Transaction reversed

---

## Examples

### Complete Workflow Example

#### 1. Register New Member
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Alice Johnson",
    "email": "alice@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+1234567890",
    "national_id": "ID987654321",
    "date_of_birth": "1992-03-15",
    "gender": "female",
    "address": "789 Oak Street",
    "occupation": "Nurse",
    "monthly_income": 35000,
    "next_of_kin_name": "Bob Johnson",
    "next_of_kin_relationship": "Husband",
    "next_of_kin_phone": "+1234567899",
    "next_of_kin_address": "789 Oak Street"
  }'
```

#### 2. Admin Login and Approve Member
```bash
# Login as admin
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@sacco.com",
    "password": "password123"
  }'

# Approve member (using token from login)
curl -X POST http://localhost:8000/api/auth/approve-member/4 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -d '{
    "action": "approve"
  }'
```

#### 3. Member Login and Check Profile
```bash
# Login as approved member
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "alice@example.com",
    "password": "password123"
  }'

# Get profile
curl -X GET http://localhost:8000/api/auth/profile \
  -H "Authorization: Bearer YOUR_MEMBER_TOKEN"
```

#### 4. Check Available Savings Products
```bash
curl -X GET http://localhost:8000/api/savings/products \
  -H "Authorization: Bearer YOUR_MEMBER_TOKEN"
```

#### 5. Make First Deposit
```bash
curl -X POST http://localhost:8000/api/savings/deposit \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_MEMBER_TOKEN" \
  -d '{
    "account_id": 1,
    "amount": 10000,
    "payment_method": "bank_transfer",
    "payment_reference": "BANK_REF_001",
    "description": "Initial deposit"
  }'
```

#### 6. Check Account Balance
```bash
curl -X GET http://localhost:8000/api/savings/accounts \
  -H "Authorization: Bearer YOUR_MEMBER_TOKEN"
```

#### 7. View Transaction History
```bash
curl -X GET "http://localhost:8000/api/savings/accounts/1/transactions?start_date=2024-01-01" \
  -H "Authorization: Bearer YOUR_MEMBER_TOKEN"
```

### Error Handling Examples

#### Validation Error Response
```json
{
  "success": false,
  "message": "Validation errors",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."],
    "amount": ["The amount must be a number greater than 0."]
  }
}
```

#### Authentication Error Response
```json
{
  "success": false,
  "message": "Unauthorized - Please login first"
}
```

#### Insufficient Permissions Response
```json
{
  "success": false,
  "message": "Forbidden - Insufficient permissions",
  "required_roles": ["admin", "staff"],
  "user_role": "member"
}
```

### Testing with Postman

#### Environment Variables
```json
{
  "base_url": "http://localhost:8000/api",
  "auth_token": "{{token_from_login_response}}"
}
```

#### Collection Structure
1. **Authentication**
   - POST Register
   - POST Login
   - GET Profile
   - PUT Update Profile
   - POST Change Password
   - POST Logout

2. **Savings**
   - GET Products
   - GET Accounts
   - POST Deposit
   - POST Withdraw
   - GET Transactions

3. **Admin Operations**
   - POST Approve Member

---

## Security Considerations

### Authentication Security
- JWT tokens expire after 60 minutes
- Passwords are hashed using bcrypt
- Rate limiting prevents brute force attacks
- CORS policies restrict cross-origin requests

### Data Validation
- All inputs are validated and sanitized
- SQL injection protection via Eloquent ORM
- XSS protection through proper output encoding
- File upload restrictions and validation

### Financial Security
- Double-entry bookkeeping ensures data integrity
- Transaction audit trails for all operations
- Role-based access prevents unauthorized operations
- Balance verification on all transactions

### Best Practices
- Use HTTPS in production
- Implement proper logging for audit trails
- Regular security updates and patches
- Backup and disaster recovery procedures

---

## Rate Limiting Details

### Current Limits
- **Authentication endpoints**: 5 requests per minute
- **General API endpoints**: 60 requests per minute
- **Bulk operations**: 10 requests per minute

### Headers Returned
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1640995200
```

### Exceeding Limits
When rate limit is exceeded, the API returns:
```json
{
  "success": false,
  "message": "Too many requests. Please try again later.",
  "retry_after": 60
}
```

---

## Webhooks (Future Feature)

The API is designed to support webhooks for real-time notifications:

### Planned Webhook Events
- `member.registered` - New member registration
- `member.approved` - Member approved by admin
- `transaction.completed` - Transaction completed
- `loan.applied` - New loan application
- `loan.approved` - Loan approved
- `loan.disbursed` - Loan disbursed

### Webhook Payload Format
```json
{
  "event": "transaction.completed",
  "timestamp": "2024-01-15T10:30:00.000000Z",
  "data": {
    "transaction": { /* Transaction object */ },
    "account": { /* Account object */ },
    "member": { /* Member object */ }
  }
}
```

---

## SDK and Libraries (Future)

Planned SDK support for common programming languages:
- **PHP SDK** - Laravel/PHP integration
- **JavaScript SDK** - Node.js and browser support  
- **Python SDK** - Django/Flask integration
- **Mobile SDKs** - React Native, Flutter

---

This documentation provides comprehensive coverage of the SACCO REST API. For additional support or questions, please refer to the README.md file or contact the development team.