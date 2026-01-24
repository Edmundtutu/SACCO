# PWA Member Portal - Multi-Tenant Integration Guide

## Overview

This document outlines the necessary changes to integrate the PWA Member Portal with the newly redesigned multi-tenant SACCO backend system.

## Key Changes in Backend Architecture

### 1. Tenant Context Resolution

The backend now operates in a **multi-tenant mode** where:
- Every user belongs to exactly ONE tenant (SACCO)
- Data isolation is enforced at the row level using `tenant_id`
- Tenant identification is through **JWT tokens**, NOT domains/subdomains

### 2. Authentication Flow Changes

#### Previous Flow (Single Tenant)
```
User → Login → JWT Token → Access Resources
```

#### New Flow (Multi-Tenant)
```
User → Select SACCO → Login → JWT Token (with tenant_id) → Access Resources
```

## Required PWA Changes

### Phase 1: Registration Flow

#### 1.1 Add Tenant Selection
Before registration, users must select which SACCO they want to join.

**New Registration Endpoint:**
```http
POST /api/auth/register
Content-Type: application/json

{
  "tenant_id": 1,                    // ← NEW: Required field
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  // ... rest of fields remain the same
}
```

**Response:**
```json
{
  "success": true,
  "message": "Registration successful. Your membership is pending approval.",
  "data": {
    "membership_id": "M000001",
    "status": "pending_approval",
    "tenant": {
      "id": 1,
      "name": "Kilimo SACCO",
      "code": "SAC000001"
    }
  }
}
```

#### 1.2 SACCO Discovery/Selection Screen

Create a new screen for users to discover and select their SACCO:

**Endpoint to get available SACCOs:**
```http
GET /api/tenants/public
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "sacco_name": "Kilimo SACCO",
      "sacco_code": "SAC000001",
      "slug": "kilimo-sacco",
      "description": "Agricultural savings and credit society"
    },
    // ... more SACCOs
  ]
}
```

**UI/UX Recommendation:**
- Add a landing page before registration
- Display list of available SACCOs
- Allow search/filter by name or location
- Show SACCO details (name, description, location)
- "Join This SACCO" button that proceeds to registration

### Phase 2: Login Flow

#### 2.1 Updated Login Endpoint

The login endpoint remains the same, but the response now includes tenant information:

**Endpoint:**
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

**New Response Format:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "member_number": "M000001",
      "role": "member",
      "status": "active"
    },
    "tenant": {                      // ← NEW: Tenant information
      "id": 1,
      "name": "Kilimo SACCO",
      "code": "SAC000001",
      "status": "active"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

#### 2.2 Store Tenant Information

**Update your auth store/service to save:**
```javascript
// Previous (single-tenant)
localStorage.setItem('token', response.data.token);
localStorage.setItem('user', JSON.stringify(response.data.user));

// New (multi-tenant)
localStorage.setItem('token', response.data.token);
localStorage.setItem('user', JSON.stringify(response.data.user));
localStorage.setItem('tenant', JSON.stringify(response.data.tenant)); // ← NEW
```

### Phase 3: API Request Headers

#### 3.1 JWT Token Usage

**No changes required** - Continue using JWT token in Authorization header:
```javascript
headers: {
  'Authorization': `Bearer ${token}`,
  'Content-Type': 'application/json'
}
```

The backend will automatically:
1. Extract `tenant_id` from the JWT token
2. Set the tenant context
3. Apply data isolation

#### 3.2 Special Cases: X-Tenant-ID Header

**ONLY** use the `X-Tenant-ID` header in these specific scenarios:

1. **Public tenant information requests:**
```javascript
// Getting public SACCO information
fetch('/api/tenants/info', {
  headers: {
    'X-Tenant-ID': tenantId,
    'Content-Type': 'application/json'
  }
});
```

2. **Registration/Invitation flows** (before authentication):
```javascript
// Already covered by tenant_id in request body
// No special header needed
```

**⚠️ IMPORTANT:** Never send `X-Tenant-ID` header with authenticated requests. The backend will reject it for security reasons.

### Phase 4: UI Updates

#### 4.1 Display Tenant Branding

Show the SACCO name and branding throughout the app:

```javascript
// In your app header/navbar
const DisplayTenantInfo = () => {
  const tenant = JSON.parse(localStorage.getItem('tenant'));
  
  return (
    <div className="tenant-info">
      <img src={tenant.logo_url} alt={tenant.name} />
      <span>{tenant.name}</span>
    </div>
  );
};
```

#### 4.2 Handle Tenant Status

The backend may return tenant status errors. Handle them gracefully:

```javascript
// Error handling in your HTTP client
if (error.response?.status === 403) {
  const message = error.response.data.message;
  
  if (message.includes('suspended') || message.includes('trial')) {
    // Show tenant-specific error message
    showTenantStatusError(message);
    // Optionally log user out
    logout();
  }
}
```

### Phase 5: Offline Support

If your PWA supports offline mode, ensure tenant data is cached:

```javascript
// Service worker cache strategy
const TENANT_CACHE = 'tenant-v1';

// Cache tenant info after login
caches.open(TENANT_CACHE).then(cache => {
  cache.put('/tenant-info', new Response(
    JSON.stringify(tenantData)
  ));
});
```

### Phase 6: Migration Strategy

For existing PWA deployments:

#### 6.1 Check for Missing Tenant Data

```javascript
// On app load
const checkTenantContext = () => {
  const token = localStorage.getItem('token');
  const tenant = localStorage.getItem('tenant');
  
  if (token && !tenant) {
    // User has old token without tenant info
    // Force re-login to get new token with tenant_id
    logout();
    router.push('/login?reason=update_required');
  }
};
```

#### 6.2 Token Refresh

When refreshing tokens, tenant information remains in the token:

```javascript
// No changes needed - tenant_id is preserved in refreshed tokens
POST /api/auth/refresh
Authorization: Bearer <old_token>

// Response includes new token with same tenant_id
```

## Testing Checklist

Before deploying PWA changes:

- [ ] Users can discover and select SACCOs before registration
- [ ] Registration includes `tenant_id` in request
- [ ] Login response includes tenant information
- [ ] Tenant information is stored locally
- [ ] Tenant branding is displayed throughout app
- [ ] Suspended tenant errors are handled gracefully
- [ ] Users with old tokens are prompted to re-login
- [ ] Offline mode works with tenant context
- [ ] All API calls use Bearer token (no X-Tenant-ID in authenticated requests)

## Breaking Changes Summary

| Change | Impact | Required Action |
|--------|--------|-----------------|
| Registration requires `tenant_id` | HIGH | Add SACCO selection screen |
| Login response includes `tenant` object | MEDIUM | Update auth storage logic |
| Tenant status validation | MEDIUM | Add error handling |
| Email uniqueness per tenant | LOW | Update validation messages |

## Support & Migration Assistance

For questions or assistance with PWA integration:
1. Review the API documentation: `/openapi.yaml`
2. Test with the updated Postman collection
3. Check tenant-specific error codes in API responses

## Example: Complete Authentication Flow

```javascript
// 1. User selects SACCO
const tenants = await fetch('/api/tenants/public').then(r => r.json());
const selectedTenant = tenants.data[0];

// 2. Registration
const registerResponse = await fetch('/api/auth/register', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    tenant_id: selectedTenant.id,  // ← Include tenant
    name: 'John Doe',
    email: 'john@example.com',
    password: 'password123',
    password_confirmation: 'password123',
    // ... other fields
  })
});

// 3. Login
const loginResponse = await fetch('/api/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'john@example.com',
    password: 'password123'
  })
});

const { user, tenant, token } = loginResponse.data;

// 4. Store credentials and tenant
localStorage.setItem('token', token);
localStorage.setItem('user', JSON.stringify(user));
localStorage.setItem('tenant', JSON.stringify(tenant));  // ← Store tenant

// 5. Make authenticated requests
const response = await fetch('/api/accounts', {
  headers: {
    'Authorization': `Bearer ${token}`,  // ← Only this header needed
    'Content-Type': 'application/json'
  }
});
// Backend automatically applies tenant scope from JWT
```

## Conclusion

The multi-tenant architecture provides strong data isolation while maintaining a simple integration model for the PWA. The primary changes are:

1. Add SACCO selection before registration
2. Store and display tenant information
3. Handle tenant-specific errors
4. Continue using JWT for all authenticated requests

No complex domain/subdomain handling is required in the PWA!
