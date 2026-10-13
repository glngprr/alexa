# Alexa — Project Specification

## 1. Project Overview

Alexa is a premium lifestyle e-commerce website for a fictional tumbler brand.

The project is developed as a university e-commerce course project.

Main requirements:
- E-commerce product catalog
- Product variants
- Authentication
- Shopping cart
- Checkout
- Payment sandbox
- Order management
- Shipment tracking
- Admin management
- Security

The application uses a monolithic Laravel architecture.

---

## 2. Technology Stack

Backend:
- Laravel
- PHP

Database:
- MySQL

Frontend:
- Laravel Blade
- HTML
- CSS
- JavaScript

Local development:
- Laragon

Payment:
- Midtrans Sandbox

Architecture:
- Monolithic Laravel application

---

## 3. Brand

Brand name:

Alexa

Brand positioning:

Premium lifestyle, refined, minimal, elegant.

Brand voice:
- refined
- considered
- understated
- functional
- timeless
- premium

Avoid exaggerated marketing claims such as:
- "best product"
- "number one quality"
- "cheapest"
- excessive exclamation marks

---

## 4. Product Collections

### Alexa Core

Positioning:
Everyday / minimal / elegant

Tagline:
"Designed for the everyday."

Capacity:
500 ml

Material:
18/8 stainless steel

Lid:
Screw cap

Variants:
1. Core Midnight
2. Core Sage
3. Core Ivory
4. Core Lavender

Price:
Rp899.000

---

### Alexa Active

Positioning:
Movement / active lifestyle

Tagline:
"Made to move."

Capacity:
600 ml

Material:
18/8 stainless steel

Lid:
Sport spout with integrated carry handle

Variants:
1. Active Snow
2. Active Eclipse
3. Active Sky
4. Active Blush

Price:
Rp1.099.000

---

### Alexa Trek

Positioning:
Travel / outdoor / durability

Tagline:
"Built for the journey."

Capacity:
750 ml

Material:
18/8 stainless steel

Lid:
Wide-mouth with integrated carry handle

Variants:
1. Trek Midnight
2. Trek Steel
3. Trek Sand
4. Trek Onyx

Price:
Rp1.299.000

---

## 5. Product Data Model

The 12 color variants are not separate products.

There are:
- 3 products
- 12 product variants

Structure:

Collection
→ Product
→ Product Variant

Product-level information:
- collection
- name
- description
- capacity
- material
- lid type
- weight
- status

Variant-level information:
- color
- SKU
- price
- stock
- status
- image

---

## 6. Variant SKUs

Core:
- ALX-CORE-MID
- ALX-CORE-SAG
- ALX-CORE-IVY
- ALX-CORE-LAV

Active:
- ALX-ACT-SNW
- ALX-ACT-ECL
- ALX-ACT-SKY
- ALX-ACT-BLS

Trek:
- ALX-TRK-MID
- ALX-TRK-STL
- ALX-TRK-SND
- ALX-TRK-ONX

Initial stock:
20 units per variant.

---

## 7. Main Database Tables

- users
- collections
- products
- product_variants
- carts
- cart_items
- addresses
- orders
- order_items
- payments
- shipments

Use Laravel migrations as the database schema source of truth.

Do not manually create application tables in database management software.

---

## 8. Main Relationships

User:
- hasOne Cart
- hasMany Addresses
- hasMany Orders

Collection:
- hasMany Products

Product:
- belongsTo Collection
- hasMany ProductVariants

ProductVariant:
- belongsTo Product

Cart:
- belongsTo User
- hasMany CartItems

CartItem:
- belongsTo Cart
- belongsTo ProductVariant

Order:
- belongsTo User
- belongsTo Address
- hasMany OrderItems
- hasOne Payment
- hasOne Shipment

OrderItem:
- belongsTo Order
- belongsTo ProductVariant

Payment:
- belongsTo Order

Shipment:
- belongsTo Order

---

## 9. Customer Flow

Homepage
→ Collection
→ Product Detail
→ Select Color
→ Quantity
→ Add to Cart
→ Cart
→ Checkout
→ Login/Register if required
→ Shipping Address
→ Order Summary
→ Payment
→ Payment Sandbox
→ Order Confirmation
→ My Orders
→ Order Detail
→ Shipment Tracking

Guests can browse products.

Authentication is required for cart and checkout.

---

## 10. Order Status

Order statuses:

- pending
- processing
- packed
- shipped
- delivered
- cancelled

Payment statuses:

- pending
- paid
- failed
- expired
- cancelled

Shipment statuses:

- pending
- shipped
- in_transit
- delivered

Payment, order, and shipment status must remain separate.

---

## 11. Security Requirements

The application must include:

- Password hashing
- Authentication
- Admin authorization
- Customer ownership checks
- Input validation
- CSRF protection
- SQL injection protection
- XSS protection
- Mass assignment protection
- Secrets stored in .env
- Server-side price calculation
- Server-side stock validation
- Stock must not become negative

Never trust prices, totals, stock values, or payment status supplied by the client.

---

## 12. Architecture

The application uses a monolithic Laravel architecture.

Request flow:

Browser
→ Route
→ Middleware
→ Controller
→ Service when appropriate
→ Eloquent
→ MySQL
→ Blade

External payment and courier APIs are allowed.

They do not change the application into a microservices architecture.

---

## 13. Feature Priority

### P0 — Mandatory

- Product catalog
- Product detail
- Product variants
- Authentication
- Shopping cart
- Checkout
- Payment sandbox
- Order management
- Shipment tracking
- Admin
- Security

### P1 — Optional

- Search
- Filter
- Wishlist
- Reviews
- Voucher
- Email notification

### P2 — Optional

- Indonesian/English language
- IDR/USD currency
- Advanced UI polish

Core purchasing flow must be completed before optional features.

---

## 14. Development Principle

Implement the project incrementally.

Do not implement unrelated features together.

Each feature should be:
1. implemented
2. tested
3. verified
4. then used as the foundation for the next feature.

Use Laravel conventions whenever practical.

Avoid unnecessary abstraction and overengineering.