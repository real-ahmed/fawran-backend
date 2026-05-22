# Admin API Documentation (v1)

This document describes the Admin endpoints for the Fawran Marketplace Backend.

**Base URL:** `/api/v1/admin`
**Authentication:** Bearer Token (JWT), obtained via `/api/v1/admin/login`

---

## Authentication

All protected endpoints require a JWT Bearer token in the `Authorization` header (`Authorization: Bearer {token}`).

### 1. Login
**POST** `/login`

Authenticates an admin and returns a JWT.

**Request Body:**
```json
{
    "email": "admin@fawran.test",
    "password": "password"
}
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Logged in successfully",
    "data": {
        "access_token": "eyJ0e...",
        "token_type": "bearer",
        "expires_in": 3600
    },
    "errors": null
}
```

### 2. Get Current Admin
**GET** `/me`

Retrieves the authenticated admin's profile.

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Profile retrieved",
    "data": {
        "id": 1,
        "name": "Super Admin",
        "email": "admin@fawran.test"
    },
    "errors": null
}
```

### 3. Refresh Token
**POST** `/refresh`

Refreshes the current JWT and returns a new one. Old token is invalidated.

**Response (200 OK):**
*(Same structure as Login)*

### 4. Logout
**POST** `/logout`

Invalidates the current JWT token.

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Successfully logged out",
    "data": null,
    "errors": null
}
```

---

## Delivery Zones

Delivery Zones represent physical areas where vendors operate and couriers deliver. They are defined mathematically by Polygons (a series of geographic coordinates).

### 1. Create a Delivery Zone
**POST** `/delivery-zones`

Creates a new delivery zone. The coordinates must form a valid polygon (at least 3 points, the backend automatically closes the polygon by joining the last point to the first).

**Request Body:**
```json
{
    "name": {
        "en": "Riyadh Central",
        "ar": "وسط الرياض"
    },
    "is_active": true,
    "coordinates": [
        {"lat": 24.711, "lng": 46.671},
        {"lat": 24.715, "lng": 46.678},
        {"lat": 24.708, "lng": 46.685}
    ]
}
```

**Response (201 Created):**
```json
{
    "success": true,
    "message": "Delivery zone created successfully",
    "data": {
        "id": 1,
        "name": {
            "en": "Riyadh Central",
            "ar": "وسط الرياض"
        },
        "is_active": true,
        "geometry": {
            "type": "Polygon",
            "coordinates": [
                [
                    [46.671, 24.711],
                    [46.678, 24.715],
                    [46.685, 24.708],
                    [46.671, 24.711] 
                ]
            ]
        }
    },
    "errors": null
}
```
*(Notice how the `geometry` is returned in standard GeoJSON format, and the backend automatically closed the polygon).*

### 2. List Delivery Zones
**GET** `/delivery-zones`

Returns all delivery zones along with their GeoJSON geometry data.

### 3. Update Delivery Zone
**PUT/PATCH** `/delivery-zones/{id}`

Updates an existing zone. All fields are optional (you can update just the `name`, or just the `coordinates`).

**Request Body Example:**
```json
{
    "is_active": false
}
```

### 4. Delete Delivery Zone
**DELETE** `/delivery-zones/{id}`

Deletes the zone. Note: This will cascade delete any pivot relationships (like Admin assignments).
