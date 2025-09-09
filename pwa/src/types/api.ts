// API Response Types
export interface ApiResponse<T = any> {
  success: boolean;
  message?: string;
  data?: T;
  errors?: Record<string, string[]>;
}

// User & Auth Types
export interface User {
  id: number;
  name: string;
  email: string;
  role: 'member' | 'admin' | 'staff_level_1' | 'staff_level_2' | 'staff_level_3';
  status: 'active' | 'pending_approval' | 'suspended' | 'inactive';
  membership_date?: string;
  account_verified_at?: string;
  created_at: string;
  updated_at?: string;
  membership?: Membership;
  profile?: IndividualProfile | VslaProfile | MfiProfile;
}

export interface Membership {
  id: string;
  user_id: number;
  profile_type: string;
  profile_id: number;
  approval_status: 'pending' | 'approved' | 'rejected';
  approved_by_level_1?: number;
  approved_at_level_1?: string;
  approved_by_level_2?: number;
  approved_at_level_2?: string;
  approved_by_level_3?: number;
  approved_at_level_3?: string;
  created_at: string;
  updated_at?: string;
  profile?: IndividualProfile | VslaProfile | MfiProfile;
}

export interface IndividualProfile {
  id: number;
  phone?: string;
  national_id?: string;
  date_of_birth?: string;
  gender?: 'male' | 'female' | 'other';
  address?: string;
  occupation?: string;
  monthly_income?: string;
  referee?: number;
  next_of_kin_name?: string;
  next_of_kin_relationship?: string;
  next_of_kin_phone?: string;
  next_of_kin_address?: string;
  emergency_contact_name?: string;
  emergency_contact_phone?: string;
  employer_name?: string;
  bank_name?: string;
  bank_account_number?: string;
  additional_notes?: string;
  profile_photo_path?: string;
  id_copy_path?: string;
  signature_path?: string;
  created_at: string;
  updated_at?: string;
}

export interface VslaProfile {
  id: number;
  village: string;
  sub_county: string;
  district: string;
  membership_count: number;
  registration_certificate: string;
  constitution_copy: string;
  resolution_minutes: string;
  executive_contacts: Array<{
    name: string;
    position: string;
    phone: string;
  }>;
  recommendation_lc1: string;
  recommendation_cdo: string;
  created_at: string;
  updated_at?: string;
}

export interface MfiProfile {
  id: number;
  contact_person?: string;
  contact_number?: string;
  address?: string;
  membership_count?: number;
  registration_certificate?: string;
  board_members?: Array<{
    name: string;
    position: string;
    phone: string;
  }>;
  bylaws_copy?: string;
  resolution_minutes?: string;
  operating_license?: string;
  created_at: string;
  updated_at?: string;
}

export interface LoginResponse {
  token: string;
  token_type: string;
  expires_in: number;
  user: User;
}

export interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  phone: string;
  national_id: string;
  date_of_birth: string;
  gender: string;
  address: string;
  occupation: string;
  monthly_income: number;
  next_of_kin_name: string;
  next_of_kin_relationship: string;
  next_of_kin_phone: string;
  next_of_kin_address: string;
  emergency_contact_name?: string;
  emergency_contact_phone?: string;
  employer_name?: string;
  bank_name?: string;
  bank_account_number?: string;
  referee?: string;
}

export interface ProfileUpdateData {
  name?: string;
  phone?: string;
  address?: string;
  occupation?: string;
  monthly_income?: number;
  next_of_kin_name?: string;
  next_of_kin_relationship?: string;
  next_of_kin_phone?: string;
  next_of_kin_address?: string;
  emergency_contact_name?: string;
  emergency_contact_phone?: string;
  employer_name?: string;
  bank_name?: string;
  bank_account_number?: string;
}

// Savings Types
export interface SavingsAccount {
  id: number;
  account_number: string;
  balance: number;
  available_balance: number;
  minimum_balance: number;
  interest_earned: number;
  interest_rate: number;
  status: 'active' | 'dormant' | 'closed';
  last_transaction_date?: string;
  maturity_date?: string;
  created_at: string;
  savings_product: SavingsProduct;
}

export interface SavingsProduct {
  id: number;
  name: string;
  description: string;
  type: 'compulsory' | 'voluntary' | 'fixed_deposit' | 'target';
  minimum_balance: number;
  maximum_balance?: number;
  interest_rate: number;
  interest_calculation_method: 'simple' | 'compound';
  compounding_frequency?: 'daily' | 'monthly' | 'quarterly' | 'annually';
  withdrawal_fee?: number;
  deposit_fee?: number;
  minimum_deposit?: number;
  maximum_deposit?: number;
  features: string[];
  is_active: boolean;
}

export interface Transaction {
  id: number;
  transaction_number: string;
  type: 'deposit' | 'withdrawal' | 'interest' | 'fee' | 'transfer';
  category: string;
  amount: number;
  fee_amount?: number;
  net_amount: number;
  balance_before: number;
  balance_after: number;
  description: string;
  payment_method?: string;
  status: 'completed' | 'pending' | 'failed' | 'cancelled';
  transaction_date: string;
  processed_by?: string;
  reference?: string;
  metadata?: Record<string, any>;
}

// Loans Types
export interface Loan {
  id: number;
  loan_number: string;
  product_name: string;
  principal_amount: number;
  outstanding_balance: number;
  interest_rate: number;
  term_months: number;
  monthly_payment: number;
  next_payment_date: string;
  next_payment_amount: number;
  payments_made: number;
  payments_remaining: number;
  status: 'pending' | 'approved' | 'disbursed' | 'active' | 'paid' | 'overdue' | 'defaulted';
  application_date: string;
  approval_date?: string;
  disbursement_date?: string;
  purpose: string;
  guarantors?: LoanGuarantor[];
  repayments?: LoanRepayment[];
  loan_product: LoanProduct;
}

export interface LoanProduct {
  id: number;
  name: string;
  description: string;
  min_amount: number;
  max_amount: number;
  interest_rate: number;
  max_term_months: number;
  processing_fee_rate?: number;
  insurance_rate?: number;
  guarantors_required: number;
  collateral_required: boolean;
  requirements: string[];
  is_active: boolean;
}

export interface LoanApplication {
  product_id: number;
  amount: number;
  term_months: number;
  purpose: string;
  guarantor_ids?: number[];
}

export interface LoanGuarantor {
  id: number;
  guarantor_id: number;
  guarantor_name: string;
  amount_guaranteed: number;
  status: 'pending' | 'accepted' | 'declined';
  response_date?: string;
}

export interface LoanRepayment {
  id: number;
  amount: number;
  principal_amount: number;
  interest_amount: number;
  payment_date: string;
  payment_method: string;
  reference?: string;
}

export interface RepaymentSchedule {
  payment_number: number;
  payment_date: string;
  principal_amount: number;
  interest_amount: number;
  total_payment: number;
  remaining_balance: number;
  status: 'pending' | 'paid' | 'overdue';
}

// Shares Types
export interface SharesAccount {
  id: number;
  total_shares: number;
  share_value: number;
  total_value: number;
  dividends_earned: number;
  last_dividend_date?: string;
  certificates: ShareCertificate[];
}

export interface ShareCertificate {
  id: number;
  certificate_number: string;
  shares_count: number;
  purchase_date: string;
  purchase_price: number;
}

export interface Dividend {
  id: number;
  year: number;
  rate: number;
  amount: number;
  shares_eligible: number;
  payment_date: string;
  status: 'declared' | 'paid';
}

export interface SharePurchase {
  shares: number;
  amount: number;
  payment_method: string;
}

// Reports Types
export interface MemberStatement {
  member: User;
  period: {
    from: string;
    to: string;
  };
  summary: {
    opening_balance: number;
    total_deposits: number;
    total_withdrawals: number;
    interest_earned: number;
    closing_balance: number;
  };
  transactions: Transaction[];
}

export interface SavingsSummary {
  total_balance: number;
  total_interest_earned: number;
  accounts_count: number;
  monthly_growth: number;
  accounts: SavingsAccount[];
}

export interface LoansSummary {
  total_outstanding: number;
  total_paid: number;
  active_loans_count: number;
  next_payment_amount: number;
  next_payment_date?: string;
  loans: Loan[];
}

// Error Types
export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
  status?: number;
}
