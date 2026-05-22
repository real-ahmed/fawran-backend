# Vendor API Documentation (v1)

This document describes the Vendor endpoints for the Fawran Marketplace Backend.
Vendors (Stores, Restaurants) use these endpoints to manage their settings, roles, inventory, and orders.

**Base URL:** `/api/v1/vendor`  
**Authentication:** Bearer Token (JWT), obtained via `/api/v1/vendor/login`

> [!IMPORTANT]
> **Multi-Tenancy (Vendor Scoping):** 
> All protected Vendor API endpoints require the `X-Store-ID` (or `X-Vendor-ID`) HTTP Header to be passed with every request, or a `?store_id=` query parameter. The system uses this to automatically isolate data (like roles) to the specific vendor.

---

## Authentication

All protected endpoints require a JWT Bearer token in the `Authorization` header (`Authorization: Bearer {token}`).

### 1. Login
**POST** `/login`

Authenticates a vendor staff member and returns a JWT.

**Request Body:**
```json
{
    "email": "vendor@fawran.test",
    "password": "password"
}
```

### 2. Get Current Vendor Profile
**GET** `/me`

Retrieves the authenticated vendor staff's profile.

### 3. Refresh Token
**POST** `/refresh`

Refreshes the current JWT and returns a new one.

### 4. Logout
**POST** `/logout`

Invalidates the current JWT token.

---

## Roles & Permissions (Store-Level RBAC)

This module allows vendors to create custom roles (e.g., "Cashier", "Branch Manager") and assign specific permissions to their staff. The permissions are strictly scoped to the Vendor issuing the request.

> [!NOTE]
> All endpoints below require the authenticated user to have the `manage store roles` permission within the specified vendor scope.

### 1. List Available Permissions
**GET** `/roles/permissions`

Returns a list of all system-defined permissions that a vendor can assign to a role. They are grouped logically (e.g., "Dashboard", "Settings").

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Vendor permissions retrieved successfully",
    "data": {
        "view vendor dashboard": [
            "view vendor dashboard"
        ],
        "manage vendor settings": [
            "manage vendor settings"
        ]
    },
    "errors": null
}
```

### 2. List Roles
**GET** `/roles`

Returns all custom roles created within this specific vendor.

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Vendor roles retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Vendor Owner",
            "vendor_id": 1,
            "permissions": [
                "manage vendor settings",
                "manage vendor roles"
            ],
            "created_at": "2026-05-22T21:00:00.000000Z"
        }
    ],
    "errors": null
}
```

### 3. Create Role
**POST** `/roles`

Creates a new role for the vendor. The role name must be unique within this specific vendor.

**Request Body:**
```json
{
    "name": "Cashier",
    "permissions": [
        "view vendor orders"
    ]
}
```

### 4. Show Role
**GET** `/roles/{role_id}`

Retrieves details of a specific role belonging to the vendor.

### 5. Update Role
**PUT/PATCH** `/roles/{role_id}`

Updates the name or permissions of an existing vendor role.

**Request Body:**
```json
{
    "name": "Senior Cashier",
    "permissions": [
        "view vendor orders",
        "manage vendor products"
    ]
}
```

### 6. Delete Role
**DELETE** `/roles/{role_id}`

Deletes a custom role from the vendor. Users assigned to this role will lose its associated permissions.
