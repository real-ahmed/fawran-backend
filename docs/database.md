# 🗄️ Fully Normalized Multi-Vendor Marketplace DB Schema
**Strict Zero-Null Enterprise Architecture**

This document outlines the complete database architecture. **Architectural Rule:** NO `NULL` values are permitted. Optional attributes, future timestamps, and mutually exclusive fields are strictly isolated into `1:0..1` extension tables.

---

## 🖼️ 0. Media & Engagement

**`media` Table**
| Column | Type | Properties | Description |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc | |
| `model_type` | VARCHAR(255) | Polymorphic | e.g. `App\Models\Store` |
| `model_id` | BIGINT | Polymorphic | |
| `file_path` | VARCHAR(500) | | Full path or CDN URL |
| `file_type` | ENUM | `'image'`, `'video'`, `'document'` | |
| `order` | TINYINT | Default: 0 | Display order |
| `is_primary` | BOOLEAN | Default: false | Main thumbnail |
| `created_at` | TIMESTAMP | | |

**`ratings` Table**
| Column | Type | Properties | Description |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc | |
| `user_id` | BIGINT | FK → users.id | |
| `model_type` | VARCHAR(255) | Polymorphic | Store or Courier |
| `model_id` | BIGINT | Polymorphic | |
| `order_id` | BIGINT | FK → orders.id | |
| `rating` | TINYINT | | 1–5 stars |
| `created_at` | TIMESTAMP | | |

**`rating_comments` Table (Zero-Null Extension)**
| Column | Type | Properties | Description |
| :--- | :--- | :--- | :--- |
| `rating_id` | BIGINT | PK, FK → ratings.id | |
| `comment` | TEXT | | Written feedback |

**`favorites` Table**
| Column | Type | Properties | Description |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc | |
| `user_id` | BIGINT | FK → users.id | |
| `model_type` | VARCHAR(255) | Polymorphic | Store or Product |
| `model_id` | BIGINT | Polymorphic | |
| `created_at` | TIMESTAMP | | |

**`saved_items` Table (Wishlist)**
| Column | Type | Properties | Description |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc | |
| `user_id` | BIGINT | FK → users.id | |
| `store_item_id` | BIGINT | FK → store_items.id | |
| `created_at` | TIMESTAMP | | |

---

## 👤 1. Users & Authentication

**`users` Table**
| Column | Type | Properties | Description |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc | |
| `name` | VARCHAR(255) | | |
| `email` | VARCHAR(255) | UNIQUE | |
| `phone` | VARCHAR(20) | UNIQUE | |
| `is_active` | BOOLEAN | Default: true | |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |

**`local_accounts` Table (Zero-Null Extension)**
| Column | Type | Properties | Description |
| :--- | :--- | :--- | :--- |
| `user_id` | BIGINT | PK, FK → users.id | |
| `password` | VARCHAR(255) | Hashed | |
| `created_at` | TIMESTAMP | | |

**`social_accounts` Table**
| Column | Type | Properties | Description |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc | |
| `user_id` | BIGINT | FK → users.id | |
| `provider_name` | ENUM | `'google'`, `'facebook'`, `'apple'`| |
| `provider_id` | VARCHAR(255) | | |
| `created_at` | TIMESTAMP | | |

**`user_addresses` Table**
| Column | Type | Properties | Description |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc | |
| `user_id` | BIGINT | FK → users.id | |
| `latitude` | DECIMAL(10,8) | | |
| `longitude` | DECIMAL(11,8) | | |
| `formatted_address` | VARCHAR(255) | | |
| `building_number` | VARCHAR(50) | | |
| `phone` | VARCHAR(20) | | |
| `created_at` | TIMESTAMP | | |

**`address_unit_details` Table (Zero-Null Extension)**
| Column | Type | Properties | Description |
| :--- | :--- | :--- | :--- |
| `address_id` | BIGINT | PK, FK → user_addresses.id | Exists only for apartments/offices |
| `floor_number` | VARCHAR(50) | | |
| `apartment_number` | VARCHAR(50) | | |

**`address_notes` Table (Zero-Null Extension)**
| Column | Type | Properties | Description |
| :--- | :--- | :--- | :--- |
| `address_id` | BIGINT | PK, FK → user_addresses.id | |
| `notes` | TEXT | | "Leave at door", etc. |

**`couriers` Table**
| Column | Type | Properties | Description |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc | |
| `user_id` | BIGINT | FK → users.id | |
| `vehicle_type` | ENUM | `'motorcycle'`, `'bicycle'`, `'car'`| |
| `plate_number` | VARCHAR(50) | | |
| `is_online` | BOOLEAN | Default: false | |
| `created_at` | TIMESTAMP | | |

**`courier_locations` Table (Zero-Null Extension)**
| Column | Type | Properties | Description |
| :--- | :--- | :--- | :--- |
| `courier_id` | BIGINT | PK, FK → couriers.id | Exists only if GPS data transmitted |
| `latitude` | DECIMAL(10,8) | | |
| `longitude` | DECIMAL(11,8) | | |
| `located_at` | TIMESTAMP | | |

**`courier_documents` Table (Zero-Null Extension)**
| Column | Type | Properties | Description |
| :--- | :--- | :--- | :--- |
| `courier_id` | BIGINT | PK, FK → couriers.id | |
| `criminal_record_file` | VARCHAR | | Path to the uploaded criminal record / background check |
| `contract_number` | VARCHAR | UNIQUE | System-generated unique identifier for the physical contract |

**`admins` Table**
| Column | Type | Properties | Description |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc | |
| `name` | VARCHAR(255) | | |
| `email` | VARCHAR(255) | UNIQUE | |
| `password` | VARCHAR(255) | Hashed | |
| `is_active` | BOOLEAN | Default: true | |
| `created_at` | TIMESTAMP | | |

---

## 🏪 2. Catalog, Stores & Options

**`categories` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `name` | JSON | `{"en": "..."}` |
| `is_active` | BOOLEAN | Default: true |

**`category_hierarchies` Table (Zero-Null Extension)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `child_category_id` | BIGINT | PK, FK → categories.id |
| `parent_category_id`| BIGINT | FK → categories.id |

**`category_icons` Table (Zero-Null Extension)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `category_id` | BIGINT | PK, FK → categories.id |
| `icon_class` | VARCHAR(255) | e.g. `'fas fa-burger'` |

**`brands` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `name` | JSON | |
| `is_active` | BOOLEAN | Default: true |

**`stores` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `name` | JSON | |
| `latitude` | DECIMAL(10,8) | |
| `longitude` | DECIMAL(11,8) | |
| `average_rating` | DECIMAL(3,2) | Default: 0.00 |
| `total_reviews` | INT | Default: 0 |
| `is_active` | BOOLEAN | Default: true |
| `is_open` | BOOLEAN | Default: false |
| `created_at` | TIMESTAMP | |

**`store_descriptions` Table (Zero-Null Extension)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `store_id` | BIGINT | PK, FK → stores.id |
| `description` | TEXT | |

**`store_custom_commissions` Table (Zero-Null Extension)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `store_id` | BIGINT | PK, FK → stores.id |
| `commission_percentage`| DECIMAL(5,2) | |

**`store_working_hours` Table**
| Column | Type | Properties | Description |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc | |
| `store_id` | BIGINT | FK → stores.id | |
| `day_of_week` | TINYINT | | 0 (Sun) to 6 (Sat) |
| `open_time` | TIME | | |
| `close_time` | TIME | | |

**`store_staff` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `user_id` | BIGINT | FK → users.id |
| `store_id`| BIGINT | FK → stores.id |
| `created_at`| TIMESTAMP | |

**`master_products` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `category_id` | BIGINT | FK → categories.id |
| `name` | JSON | |
| `unit_type` | ENUM | `'piece'`, `'kg'`, `'gram'`, `'portion'` |
| `is_active` | BOOLEAN | Default: true |
| `created_at` | TIMESTAMP | |

**`master_product_descriptions` Table (Zero-Null Extension)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `master_product_id` | BIGINT | PK, FK → master_products.id |
| `description` | JSON | |

**`retail_product_details` Table (Retail Only)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `master_product_id` | BIGINT | PK, FK → master_products.id |
| `brand_id` | BIGINT | FK → brands.id |
| `sku_barcode` | VARCHAR(100) | UNIQUE |

**`store_items` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `store_id` | BIGINT | FK → stores.id |
| `master_product_id` | BIGINT | FK → master_products.id |
| `price` | DECIMAL(10,2) | |
| `is_available` | BOOLEAN | Default: true |

**`store_item_inventory` Table (Retail Only)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `store_item_id` | BIGINT | PK, FK → store_items.id |
| `current_stock` | DECIMAL(10,3) | |
| `low_stock_threshold` | DECIMAL(10,3) | |

**`restaurant_dish_details` Table (Restaurants Only)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `store_item_id` | BIGINT | PK, FK → store_items.id |
| `preparation_time` | SMALLINT | In minutes |

**`product_options` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `store_item_id` | BIGINT | FK → store_items.id |
| `name` | JSON | |
| `is_required` | BOOLEAN | Default: false |
| `max_selections` | TINYINT | Default: 1 |

**`product_option_values` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `product_option_id`| BIGINT | FK → product_options.id |
| `name` | JSON | |
| `additional_price` | DECIMAL(8,2) | Default: 0.00 |
| `is_available` | BOOLEAN | Default: true |

**`order_item_options` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `order_item_id` | BIGINT | FK → order_items.id |
| `product_option_id`| BIGINT | FK → product_options.id |
| `product_option_value_id`| BIGINT | FK → product_option_values.id |
| `additional_price` | DECIMAL(8,2) | |

---

## 🔥 3. Geofencing, Hot Zones & P2P

**`delivery_zones` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `name` | JSON | |
| `polygon` | GEOMETRY | |
| `is_active` | BOOLEAN | Default: true |

**`delivery_zone_vehicle_fees` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `delivery_zone_id` | BIGINT | FK → delivery_zones.id |
| `vehicle_type` | VARCHAR | ENUM: VehicleType |
| `base_delivery_fee`| DECIMAL(8,2) | |
| `fee_per_km` | DECIMAL(6,2) | |
| `max_delivery_fee` | DECIMAL(8,2) | |
| UNIQUE | | `(delivery_zone_id, vehicle_type)` |

**`store_delivery_zones` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `store_id` | BIGINT | FK → stores.id |
| `delivery_zone_id` | BIGINT | FK → delivery_zones.id |
| `min_order_amount` | DECIMAL(10,2) | |
| `estimated_delivery_time`| INT | |

**`hot_zones` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `center_latitude` | DECIMAL(10,8) | |
| `center_longitude` | DECIMAL(11,8) | |
| `radius_meters` | INT | |
| `intensity` | ENUM | `'low'`, `'medium'`, `'high'` |
| `is_active` | BOOLEAN | Default: true |
| `starts_at` | TIMESTAMP | |

**`manual_hot_zones` Table (Zero-Null Extension)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `hot_zone_id` | BIGINT | PK, FK → hot_zones.id |
| `name` | JSON | |

**`auto_hot_zones` Table (Zero-Null Extension)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `hot_zone_id` | BIGINT | PK, FK → hot_zones.id |
| `order_count` | INT | |
| `expires_at` | TIMESTAMP | |

**`p2p_deliveries` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `sender_id` | BIGINT | FK → users.id |
| `delivery_zone_id` | BIGINT | FK → delivery_zones.id |
| `pickup_latitude` | DECIMAL(10,8) | |
| `pickup_longitude` | DECIMAL(11,8) | |
| `pickup_address` | VARCHAR(255) | |
| `dropoff_latitude` | DECIMAL(10,8) | |
| `dropoff_longitude` | DECIMAL(11,8) | |
| `dropoff_address` | VARCHAR(255) | |
| `recipient_name` | VARCHAR(255) | |
| `recipient_phone` | VARCHAR(20) | |
| `distance_km` | DECIMAL(8,3) | |
| `delivery_fee` | DECIMAL(10,2) | |
| `status` | ENUM | `'pending'`, `'accepted'`, `'picked_up'`, `'delivered'`, `'cancelled'` |
| `created_at` | TIMESTAMP | |

**`p2p_descriptions` Table (Zero-Null Extension)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `p2p_delivery_id` | BIGINT | PK, FK → p2p_deliveries.id |
| `description` | TEXT | |

**`p2p_assignments` Table (Zero-Null Extension)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `p2p_delivery_id` | BIGINT | PK, FK → p2p_deliveries.id |
| `courier_id` | BIGINT | FK → couriers.id |
| `fee_share` | DECIMAL(10,2) | |

**`p2p_pickups` Table (Zero-Null Extension)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `p2p_delivery_id` | BIGINT | PK, FK → p2p_deliveries.id |
| `picked_up_at` | TIMESTAMP | |

**`p2p_dropoffs` Table (Zero-Null Extension)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `p2p_delivery_id` | BIGINT | PK, FK → p2p_deliveries.id |
| `delivered_at` | TIMESTAMP | |

**`p2p_payments` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `p2p_delivery_id` | BIGINT | FK → p2p_deliveries.id |
| `amount` | DECIMAL(10,2) | |
| `payment_method` | ENUM | `'credit_card'`, `'wallet'`, `'cod'` |
| `status` | ENUM | `'pending'`, `'successful'`, `'failed'`, `'refunded'` |
| `created_at` | TIMESTAMP | |

**`p2p_payment_gateways` Table (Zero-Null Extension)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `p2p_payment_id` | BIGINT | PK, FK → p2p_payments.id |
| `gateway_transaction_id` | VARCHAR(255) | |

**`p2p_status_logs` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `p2p_delivery_id` | BIGINT | FK → p2p_deliveries.id |
| `status` | ENUM | New Status |
| `created_at` | TIMESTAMP | |

**`p2p_status_log_notes` Table (Zero-Null Extension)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `p2p_status_log_id`| BIGINT | PK, FK → p2p_status_logs.id |
| `note` | TEXT | |

---

## 🧠 4. Smart Order Engine

**`orders` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `order_type` | ENUM | `'delivery'`, `'pickup'`, `'in_store'` |
| `total_products` | DECIMAL(10,2) | |
| `status` | ENUM | `'pending'`, `'processing'`, `'out_for_delivery'`, `'delivered'`, `'cancelled'` |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

**`order_customers` Table (Zero-Null Extension)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `order_id` | BIGINT | PK, FK → orders.id |
| `customer_id` | BIGINT | FK → users.id |

**`order_deliveries` Table (Zero-Null Extension)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `order_id` | BIGINT | PK, FK → orders.id |
| `address_id` | BIGINT | FK → user_addresses.id |
| `delivery_zone_id`| BIGINT | FK → delivery_zones.id |
| `total_delivery_fee`| DECIMAL(10,2) | |

**`sub_orders` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `order_id` | BIGINT | FK → orders.id |
| `store_id` | BIGINT | FK → stores.id |
| `sub_total` | DECIMAL(10,2) | |
| `status` | ENUM | `'pending'`, `'accepted'`, `'preparing'`, `'ready_for_pickup'` |
| `created_at` | TIMESTAMP | |

**`order_items` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `sub_order_id` | BIGINT | FK → sub_orders.id |
| `store_item_id` | BIGINT | FK → store_items.id |
| `quantity` | DECIMAL(10,3) | |
| `unit_price` | DECIMAL(10,2) | |
| `options_price` | DECIMAL(10,2) | Default: 0.00 |
| `created_at` | TIMESTAMP | |

**`order_item_notes` Table (Zero-Null Extension)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `order_item_id` | BIGINT | PK, FK → order_items.id |
| `notes` | TEXT | |

**`deliveries` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `order_id` | BIGINT | FK → orders.id |
| `courier_id` | BIGINT | FK → couriers.id |
| `fee_share` | DECIMAL(10,2) | |
| `status` | ENUM | `'heading_to_stores'`, `'picking_up'`, `'heading_to_customer'`, `'completed'` |
| `created_at` | TIMESTAMP | |

**`delivery_pickups` Table (Zero-Null Extension)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `delivery_id` | BIGINT | PK, FK → deliveries.id |
| `picked_up_at` | TIMESTAMP | |

**`delivery_dropoffs` Table (Zero-Null Extension)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `delivery_id` | BIGINT | PK, FK → deliveries.id |
| `delivered_at` | TIMESTAMP | |

---

## 📦 5. Inventory & Procurement

**`suppliers` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `store_id` | BIGINT | FK → stores.id |
| `name` | VARCHAR(255) | |
| `phone` | VARCHAR(20) | |
| `created_at` | TIMESTAMP | |

**`purchase_orders` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `store_id` | BIGINT | FK → stores.id |
| `supplier_id` | BIGINT | FK → suppliers.id |
| `total_cost` | DECIMAL(10,2) | |
| `status` | ENUM | `'pending'`, `'received'` |
| `created_at` | TIMESTAMP | |

**`purchase_items` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `purchase_order_id`| BIGINT | FK → purchase_orders.id |
| `store_item_id` | BIGINT | FK → store_items.id |
| `quantity` | DECIMAL(10,3) | |
| `cost_price` | DECIMAL(10,2) | |
| `created_at` | TIMESTAMP | |

**`stock_movements` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `store_id` | BIGINT | FK → stores.id |
| `store_item_id` | BIGINT | FK → store_items.id |
| `quantity` | DECIMAL(10,3) | |
| `type` | ENUM | `'sale'`, `'purchase'`, `'return'`, `'adjustment'` |
| `reference_type` | VARCHAR(255) | Polymorphic |
| `reference_id` | BIGINT | Polymorphic |
| `created_at` | TIMESTAMP | |

**`stock_movement_notes` Table (Zero-Null Extension)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `stock_movement_id`| BIGINT | PK, FK → stock_movements.id|
| `notes` | TEXT | |

---

## 💵 6. Payments, Cash & Settlements

**`payments` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `order_id` | BIGINT | FK → orders.id |
| `amount` | DECIMAL(10,2) | |
| `payment_method` | ENUM | `'cod'`, `'credit_card'`, `'wallet'`, `'pos_cash'`, `'pos_card'` |
| `status` | ENUM | `'pending'`, `'successful'`, `'failed'`, `'refunded'` |
| `created_at` | TIMESTAMP | |

**`payment_gateways` Table (Zero-Null Extension)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `payment_id` | BIGINT | PK, FK → payments.id |
| `gateway_transaction_id`| VARCHAR(255) | |

**`courier_cash_collections` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `courier_id` | BIGINT | FK → couriers.id |
| `source_type` | VARCHAR(255) | Polymorphic (`Order` / `P2pDelivery`) |
| `source_id` | BIGINT | Polymorphic |
| `amount_collected` | DECIMAL(10,2) | |
| `courier_fee_share`| DECIMAL(10,2) | |
| `amount_owed_to_platform`| DECIMAL(10,2) | |
| `is_settled` | BOOLEAN | Default: false |
| `collected_at` | TIMESTAMP | |

**`settlements` Table (Couriers & Stores)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `settlement_type` | ENUM | `'courier'`, `'store'` |
| `target_id` | BIGINT | Courier ID or Store ID |
| `period_start` | DATE | |
| `period_end` | DATE | |
| `total_gross` | DECIMAL(10,2) | |
| `total_deductions`| DECIMAL(10,2) | |
| `total_net_exchange`| DECIMAL(10,2) | |
| `status` | ENUM | `'pending'`, `'completed'`, `'disputed'` |
| `created_at` | TIMESTAMP | |

**`settlement_items` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `settlement_id` | BIGINT | FK → settlements.id |
| `reference_type` | VARCHAR(255) | Polymorphic |
| `reference_id` | BIGINT | Polymorphic |
| `amount` | DECIMAL(10,2) | |

**`settlement_executions` Table (Zero-Null Extension)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `settlement_id` | BIGINT | PK, FK → settlements.id |
| `admin_id` | BIGINT | FK → admins.id |
| `execution_method`| ENUM | `'cash'`, `'wallet'`, `'bank'` |
| `executed_at` | TIMESTAMP | |

**`settlement_notes` Table (Zero-Null Extension)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `settlement_id` | BIGINT | PK, FK → settlements.id |
| `notes` | TEXT | |

**`wallets` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `user_id` | BIGINT | FK → users.id |
| `balance` | DECIMAL(10,2) | Default: 0.00 |
| `created_at` | TIMESTAMP | |

**`wallet_transactions` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `wallet_id` | BIGINT | FK → wallets.id |
| `amount` | DECIMAL(10,2) | |
| `type` | ENUM | `'deposit'`, `'withdrawal'`, `'refund'`... |
| `reference_type` | VARCHAR(255) | Polymorphic |
| `reference_id` | BIGINT | Polymorphic |
| `created_at` | TIMESTAMP | |

**`payout_requests` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `user_id` | BIGINT | FK → users.id |
| `amount` | DECIMAL(10,2) | |
| `bank_details` | TEXT | Encrypted |
| `status` | ENUM | `'pending'`, `'transferred'`, `'rejected'` |
| `created_at` | TIMESTAMP | |

**`payout_executions` Table (Zero-Null Extension)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `payout_request_id`| BIGINT | PK, FK → payout_requests.id|
| `admin_id` | BIGINT | FK → admins.id |
| `executed_at` | TIMESTAMP | |

**`refund_requests` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `customer_id` | BIGINT | FK → users.id |
| `order_id` | BIGINT | FK → orders.id |
| `total_amount` | DECIMAL(10,2) | |
| `reason` | TEXT | |
| `resolution` | ENUM | `'wallet_credit'`, `'gateway_refund'` |
| `status` | ENUM | `'pending'`, `'approved'`, `'rejected'`, `'processed'` |
| `created_at` | TIMESTAMP | |

**`refund_items` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `refund_request_id`| BIGINT | FK → refund_requests.id |
| `order_item_id` | BIGINT | FK → order_items.id |
| `quantity_returned`| DECIMAL(10,3) | |
| `refund_amount` | DECIMAL(10,2) | |
| `created_at` | TIMESTAMP | |

---

## ⚙️ 7. Platform & RBAC

**`platform_wallets` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `total_revenue` | DECIMAL(12,2) | Default: 0.00 |
| `current_balance` | DECIMAL(12,2) | Default: 0.00 |
| `updated_at` | TIMESTAMP | |

**`order_commissions` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `order_id` | BIGINT | FK → orders.id |
| `store_id` | BIGINT | FK → stores.id |
| `store_commission_percentage`| DECIMAL(5,2) | |
| `store_commission_amount` | DECIMAL(10,2) | |
| `app_delivery_share` | DECIMAL(10,2) | |
| `net_platform_profit`| DECIMAL(10,2) | |
| `created_at` | TIMESTAMP | |

**`system_settings` Table**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `key` | VARCHAR(100) | UNIQUE |
| `value` | TEXT | |
| `group` | VARCHAR(50) | |
| `updated_at` | TIMESTAMP | |

**`roles` Table (Spatie)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `name` | VARCHAR(255) | |
| `guard_name` | VARCHAR(255) | |

**`permissions` Table (Spatie)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `id` | BIGINT | PK, Auto Inc |
| `name` | VARCHAR(255) | |
| `guard_name` | VARCHAR(255) | |

**`model_has_roles` Table (Spatie Pivot)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `role_id` | BIGINT | FK → roles.id |
| `model_type` | VARCHAR(255) | Polymorphic |
| `model_id` | BIGINT | Polymorphic |

**`role_has_permissions` Table (Spatie Pivot)**
| Column | Type | Properties |
| :--- | :--- | :--- |
| `permission_id` | BIGINT | FK → permissions.id |
| `role_id` | BIGINT | FK → roles.id |