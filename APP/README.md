# SACCO REST API

A comprehensive Laravel REST API for managing Savings and Credit Cooperative Organizations (SACCOs). This API provides complete functionality for managing members, savings accounts, loans, shares, transactions, and financial reporting with proper double-entry bookkeeping.

## Features

✅ **Completed Features:**
- **Authentication & Authorization**: JWT-based authentication with role-based access control
- **Member Management**: Registration, approval, profiles with KYC information  
- **Savings Accounts**: Multiple savings products, deposits, withdrawals with fees
- **Account Management**: Compulsory, voluntary, and fixed deposit accounts
- **Database Design**: Comprehensive schema with proper relationships
- **Double-Entry Bookkeeping**: General ledger for financial accuracy
- **Role-Based Access**: Member, Admin, Staff, Loan Officer, Accountant roles

🚧 **Planned Features:**
- Loan management (application, approval, disbursement, repayment)
- Guarantorship system for loans
- Share management and dividend distribution
- Comprehensive financial reports
- Member statements and certificates

## Tech Stack

- **Backend**: Laravel 9.x
- **Database**: SQLite (for demo), easily configurable for MySQL/PostgreSQL
- **Authentication**: JWT with tymon/jwt-auth
- **API**: RESTful API with JSON responses
- **Documentation**: Comprehensive API documentation

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd SACCO-API/APP
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Set up environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   # SQLite (default)
   touch database/database.sqlite
   
   # Run migrations and seed data
   php artisan migrate:fresh --seed --seeder=SaccoDataSeeder
   ```

5. **Start the development server**
   ```bash
   php artisan serve
   ```

## Database Schema

### Core Entities

#### Users Table (Enhanced for SACCO)
- **Purpose**: Central user management for all member types
- **Key Fields**: member_number, role, status, personal info, KYC data
- **Roles**: member, admin, staff, loan_officer, accountant

#### Savings Products
- **Types**: compulsory, voluntary, fixed_deposit, special
- **Features**: Interest rates, withdrawal policies, minimum balances
- **Configuration**: Flexible product rules and fees

#### Accounts
- **Purpose**: Member savings accounts linked to products
- **Features**: Balance tracking, interest calculation, transaction history
- **Status**: active, inactive, dormant, closed

#### Loan Products
- **Types**: personal, emergency, development, school_fees, business
- **Configuration**: Interest rates, terms, guarantor requirements
- **Risk Management**: Savings ratios, collateral requirements

#### Transactions
- **Types**: deposit, withdrawal, transfer, loan_disbursement, loan_repayment
- **Features**: Double-entry bookkeeping, reversal capability
- **Audit Trail**: Complete transaction history with staff tracking

#### General Ledger
- **Purpose**: Double-entry bookkeeping for financial accuracy
- **Features**: Automatic posting, trial balance, financial statements
- **Accounts**: Complete chart of accounts (Assets, Liabilities, Equity, Income, Expenses)

## API Endpoints

### Authentication

```http
POST /api/auth/register          # Register new member
POST /api/auth/login             # Login user
POST /api/auth/logout            # Logout user
POST /api/auth/refresh           # Refresh JWT token
GET  /api/auth/profile           # Get user profile
PUT  /api/auth/profile           # Update profile
POST /api/auth/change-password   # Change password
POST /api/auth/approve-member/{id} # Approve member (Admin only)
```

### Savings & Accounts

```http
GET  /api/savings/accounts                    # Get member accounts
POST /api/savings/deposit                     # Make deposit
POST /api/savings/withdraw                    # Make withdrawal
GET  /api/savings/products                    # Get savings products
GET  /api/savings/accounts/{id}/transactions  # Get account transactions
```

### Loans (Planned)

```http
GET  /api/loans                    # Get member loans
POST /api/loans/apply             # Apply for loan
POST /api/loans/{id}/approve      # Approve loan (Staff only)
POST /api/loans/{id}/disburse     # Disburse loan (Staff only)
POST /api/loans/{id}/repay        # Make loan payment
GET  /api/loans/products          # Get loan products
```

### Shares (Planned)

```http
GET  /api/shares                  # Get member shares
POST /api/shares/purchase         # Purchase shares
GET  /api/shares/dividends        # Get dividend history
GET  /api/shares/certificates     # Get share certificates
```

### Reports (Planned)

```http
GET  /api/reports/member-statement    # Member statement
GET  /api/reports/financial-summary   # Financial summary (Staff only)
GET  /api/reports/trial-balance       # Trial balance (Staff only)
GET  /api/reports/balance-sheet       # Balance sheet (Staff only)
```

## Sample Data

The system comes with pre-populated sample data:

### Admin Account
- **Email**: admin@sacco.com
- **Password**: password123
- **Role**: Administrator

### Loan Officer Account  
- **Email**: loans@sacco.com
- **Password**: password123
- **Role**: Loan Officer

### Sample Members
- 3 test members with active accounts
- Various savings products configured
- Sample chart of accounts for bookkeeping

## API Usage Examples

### 1. Register a New Member

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+1234567890",
    "national_id": "ID123456",
    "date_of_birth": "1990-01-01",
    "gender": "male",
    "address": "123 Main St",
    "occupation": "Teacher",
    "monthly_income": 25000,
    "next_of_kin_name": "Jane Doe",
    "next_of_kin_relationship": "Spouse",
    "next_of_kin_phone": "+1234567891",
    "next_of_kin_address": "123 Main St"
  }'
```

### 2. Login

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@sacco.com",
    "password": "password123"
  }'
```

### 3. Make a Deposit

```bash
curl -X POST http://localhost:8000/api/savings/deposit \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{
    "account_id": 1,
    "amount": 5000,
    "payment_method": "cash",
    "description": "Monthly savings"
  }'
```

## Security Features

- **JWT Authentication**: Secure token-based authentication
- **Role-Based Access Control**: Different permissions for different user types
- **Input Validation**: Comprehensive request validation
- **SQL Injection Protection**: Laravel's Eloquent ORM protection
- **Password Hashing**: Bcrypt password hashing
- **CORS Support**: Configurable cross-origin resource sharing

## Financial Features

### Double-Entry Bookkeeping
- Automatic general ledger posting for all transactions
- Trial balance generation
- Financial statement preparation
- Audit trail maintenance

### Interest Calculation
- Configurable interest rates per product
- Simple and compound interest support
- Automatic interest posting
- Interest payment scheduling

### Fee Management
- Configurable transaction fees
- Processing fees for loans
- Withdrawal fees for savings
- Fee income tracking

## SACCO Business Logic

### Membership Workflow
1. Member registers online
2. Admin reviews and approves/rejects
3. Approved member can open accounts
4. Member starts saving and becomes eligible for loans

### Savings Workflow  
1. Member opens account with specific product
2. Makes deposits (cash, bank transfer, mobile money)
3. Earns interest based on product terms
4. Can withdraw subject to product rules

### Loan Workflow (Planned)
1. Member applies for loan with guarantors
2. System validates eligibility (savings history, guarantors)
3. Loan committee reviews and approves
4. Loan is disbursed to member account
5. Member makes scheduled repayments
6. System tracks penalties for late payments

## Configuration

### Environment Variables

```env
# Database
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite

# JWT Configuration
JWT_SECRET=your-secret-key
JWT_TTL=60

# App Configuration
APP_NAME="SACCO API"
APP_ENV=local
APP_KEY=base64:generated-key
APP_DEBUG=true
APP_URL=http://localhost
```

### SACCO Settings
- Customize savings products in the seeder
- Configure loan products and eligibility rules
- Set up chart of accounts for your organization
- Customize member registration fields

## Development Roadmap

### Phase 1 (Completed)
- ✅ Authentication & User Management
- ✅ Database Schema Design  
- ✅ Savings Account Management
- ✅ Basic Transaction Processing
- ✅ Double-Entry Bookkeeping Foundation

### Phase 2 (In Progress)
- 🚧 Complete Loan Management System
- 🚧 Guarantorship Implementation
- 🚧 Share Management
- 🚧 Dividend Distribution

### Phase 3 (Planned)
- 📋 Advanced Reporting
- 📋 Mobile Money Integration
- 📋 SMS Notifications
- 📋 Backup & Recovery
- 📋 Multi-branch Support

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Support

For support and questions:
- Check the API documentation
- Review the code examples
- Open an issue on the repository

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

**Built with ❤️ for SACCO organizations worldwide**