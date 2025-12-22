# API Specification - Product Microservice

**Version:** 1.2.0  
**Date:** 2024  
**Format:** OpenAPI 3.0

---

## 1. General Information

### 1.1 Base Server
```
http://api.example.com/v1
```

### 1.2 Content Type
- Request/Response: `application/json`
- Charset: `UTF-8`

### 1.3 Authentication
- **Current status:** Not specified (public endpoints)
- **Note:** For production, consider implementing authentication (JWT, API Keys, etc.)

---

## 2. Data Model

### 2.1 Entity: Product

```json
{
  "id": 0,
  "name": "string",
  "description": "string",
  "price": 0.00,
  "stock": 0,
  "active": true,
  "created_at": "2024-01-01T00:00:00Z",
  "updated_at": "2024-01-01T00:00:00Z",
  "image": "string"
}
```

**Data Types and Constraints:**
- `id`: integer (auto-generated, read-only, positive)
- `name`: string (required, minimum 3 characters, maximum 255 characters)
- `description`: string (optional, maximum 1000 characters, can be null or empty)
- `price`: decimal/float (required, > 0, precision: 2 decimals, maximum: 999999.99)
- `stock`: integer (required, >= 0, maximum: 2147483647)
- `active`: boolean (default: true, cannot be null)
- `created_at`: datetime ISO 8601 UTC (auto-generated, read-only, format: YYYY-MM-DDTHH:mm:ssZ)
- `updated_at`: datetime ISO 8601 UTC (auto-generated, automatically updated on each modification)
- `image`: string (optional, maximum 1000 characters, can be null or empty)

### 2.2 Creation DTO (ProductCreate)

```json
{
  "name": "string",
  "description": "string",
  "price": 0.00,
  "stock": 0
}
```

**Fields:**
- `name`: string (required)
- `description`: string (optional, can be null or omitted)
- `price`: decimal (required)
- `stock`: integer (required)

**Note:** 
- `active` is not included in the request (initialized to `true` by default)
- `id`, `created_at`, `updated_at` are auto-generated and not accepted in the request

### 2.3 Update DTO (ProductUpdate)

```json
{
  "name": "string",
  "description": "string",
  "price": 0.00,
  "stock": 0,
  "active": true
}
```

**Behavior:**
- All fields are optional
- Only sent fields are updated (partial update)
- Omitted fields maintain their current value
- Fields sent as `null` are set to null (except fields that do not allow null)
- `id`, `created_at` can never be updated
- `updated_at` is automatically updated

---

## 3. Business Rules

### 3.1 Field Validations

| Field | Rule | Error Code | Message |
|-------|------|------------|---------|
| `name` (creation) | Required | 422 | "The name is required" |
| `name` (creation/update) | Minimum 3 characters | 422 | "The name must have at least 3 characters" |
| `name` (creation/update) | Maximum 255 characters | 422 | "The name cannot exceed 255 characters" |
| `description` | Maximum 1000 characters | 422 | "The description cannot exceed 1000 characters" |
| `price` (creation) | Required | 422 | "The price is required" |
| `price` (creation/update) | Must be > 0 | 422 | "The price must be greater than 0" |
| `price` (creation/update) | Maximum 999999.99 | 422 | "The price cannot exceed 999999.99" |
| `stock` (creation) | Required | 422 | "The stock is required" |
| `stock` (creation/update) | Cannot be negative | 422 | "The stock cannot be negative" |
| `id` (path) | Must be a valid integer | 400 | "Invalid ID" |

### 3.2 Business Rules

1. **Inactive products cannot be purchased**
   - If `active = false`, the product must not be available for purchase
   - This validation applies in the purchase context (outside the scope of this microservice)
   - **Error code:** 409 Conflict
   - **Message:** "Cannot purchase an inactive product"

2. **Products with stock > 0 cannot be deleted**
   - When attempting DELETE, if `stock > 0`, must return error
   - Only products with `stock = 0` can be deleted
   - **Error code:** 409 Conflict
   - **Message:** "Cannot delete a product with stock greater than 0"

3. **Cannot set negative stock**
   - In PUT/PATCH, if `stock < 0`, must return validation error
   - **Error code:** 422 Unprocessable Entity
   - **Message:** "The stock cannot be negative"

4. **Cannot set price ≤ 0**
   - In PUT/PATCH, if `price <= 0`, must return validation error
   - **Error code:** 422 Unprocessable Entity
   - **Message:** "The price must be greater than 0"

5. **Auto-generated fields are not modifiable**
   - `id`: Can never be modified after creation
   - `created_at`: Can never be modified, maintains its original value
   - `updated_at`: Automatically updated on each modification, cannot be set manually

6. **Data types**
   - If a field is sent with incorrect type (e.g., `price` as string), return 400 Bad Request
   - **Message:** "Invalid data format in field {field}"

---

## 4. Endpoints

### 4.1 POST /products
**Description:** Creates a new product

**Request Body:**
```json
{
  "name": "Notebook",
  "description": "16GB RAM",
  "price": 1200,
  "stock": 10
}
```

**Validations applied:**
- `name`: required, minimum 3 characters, maximum 255 characters
- `price`: required, > 0, maximum 999999.99
- `stock`: required, >= 0
- `description`: optional, maximum 1000 characters

**Response 201 Created:**
```json
{
  "id": 1,
  "name": "Notebook",
  "description": "16GB RAM",
  "price": 1200.00,
  "stock": 10,
  "active": true,
  "created_at": "2024-01-01T12:00:00Z",
  "updated_at": "2024-01-01T12:00:00Z"
}
```

**Response 422 Unprocessable Entity:**
```json
{
  "error": "Validation Error",
  "message": "Validation errors",
  "details": [
    {
      "field": "name",
      "message": "The name must have at least 3 characters"
    },
    {
      "field": "price",
      "message": "The price must be greater than 0"
    }
  ]
}
```

**Response 400 Bad Request:**
```json
{
  "error": "Bad Request",
  "message": "Invalid JSON format"
}
```

---

### 4.2 GET /products
**Description:** Lists all active products with pagination and optional filters

**Query Parameters:**
- `page` (integer, optional): Page number (default: 1, minimum: 1)
- `limit` (integer, optional): Items per page (default: 10, minimum: 1, maximum: 100)
- `active` (boolean, optional): Filter by active status (default: true, only active if not specified)
- `min_price` (decimal, optional): Minimum price (>= min_price)
- `max_price` (decimal, optional): Maximum price (<= max_price)
- `stock_min` (integer, optional): Minimum stock (>= stock_min)
- `search` (string, optional): Search by name or description (partial search, case-insensitive)
- `sort` (string, optional): Field to sort by (default: "id", values: id, name, price, stock, created_at, updated_at)
- `order` (string, optional): Order (default: "asc", values: asc, desc)

**Example Request:**
```
GET /products?page=1&limit=20&active=true&min_price=100&max_price=2000&search=notebook&sort=price&order=desc
```

**Response 200 OK:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Notebook",
      "description": "16GB RAM",
      "price": 1200.00,
      "stock": 10,
      "active": true,
      "created_at": "2024-01-01T12:00:00Z",
      "updated_at": "2024-01-01T12:00:00Z"
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 1,
    "total_pages": 1
  }
}
```

**Behavior:**
- By default returns only active products (`active = true`)
- If `active=false` is specified, returns only inactive products
- If `active=both` is specified or parameter is omitted, returns only active products (default behavior)

**Response 400 Bad Request:**
```json
{
  "error": "Bad Request",
  "message": "Invalid query parameters",
  "details": [
    {
      "field": "page",
      "message": "Must be a positive integer"
    }
  ]
}
```

---

### 4.3 GET /products/{id}
**Description:** Gets a product by its ID (active or inactive)

**Path Parameters:**
- `id` (integer, required): Product ID

**Response 200 OK:**
```json
{
  "id": 1,
  "name": "Notebook",
  "description": "16GB RAM",
  "price": 1200.00,
  "stock": 10,
  "active": true,
  "created_at": "2024-01-01T12:00:00Z",
  "updated_at": "2024-01-01T12:00:00Z"
}
```

**Note:** This endpoint returns active and inactive products, as it is a direct search by ID.

**Response 404 Not Found:**
```json
{
  "error": "Not Found",
  "message": "Product with ID {id} not found"
}
```

**Response 400 Bad Request:**
```json
{
  "error": "Bad Request",
  "message": "Invalid ID"
}
```

---

### 4.4 PUT /products/{id}
**Description:** Updates product data (partial update)

**Path Parameters:**
- `id` (integer, required): Product ID

**Request Body:**
```json
{
  "name": "Updated Notebook",
  "description": "32GB RAM",
  "price": 1500,
  "stock": 5,
  "active": true
}
```

**Behavior:**
- Partial update: only sent fields are updated
- Omitted fields maintain their current value
- If a field is sent as `null`, it is set to null (except fields that do not allow null like `name`, `price`, `stock`)
- `id`, `created_at` can never be updated
- `updated_at` is automatically updated

**Validations:**
- `name`: if sent, minimum 3 characters, maximum 255 characters
- `price`: if sent, must be > 0, maximum 999999.99
- `stock`: if sent, cannot be negative
- `description`: if sent, maximum 1000 characters, can be null
- `active`: if sent, must be boolean

**Example: Update only price**
```json
{
  "price": 1300
}
```
Only the price is updated, other fields maintain their value.

**Response 200 OK:**
```json
{
  "id": 1,
  "name": "Updated Notebook",
  "description": "32GB RAM",
  "price": 1500.00,
  "stock": 5,
  "active": true,
  "created_at": "2024-01-01T12:00:00Z",
  "updated_at": "2024-01-01T13:00:00Z"
}
```

**Response 404 Not Found:**
```json
{
  "error": "Not Found",
  "message": "Product with ID {id} not found"
}
```

**Response 422 Unprocessable Entity:**
```json
{
  "error": "Validation Error",
  "message": "The price must be greater than 0"
}
```
or
```json
{
  "error": "Validation Error",
  "message": "The stock cannot be negative"
}
```
or for multiple errors:
```json
{
  "error": "Validation Error",
  "message": "Validation errors",
  "details": [
    {
      "field": "price",
      "message": "The price must be greater than 0"
    },
    {
      "field": "stock",
      "message": "The stock cannot be negative"
    }
  ]
}
```

**Response 400 Bad Request:**
```json
{
  "error": "Bad Request",
  "message": "Invalid ID or invalid JSON format"
}
```

---

### 4.5 DELETE /products/{id}
**Description:** Permanently deletes a product (only if stock = 0)

**Path Parameters:**
- `id` (integer, required): Product ID

**Business Validations:**
- The product must exist
- The product must have `stock = 0` (cannot delete if stock > 0)

**Response 204 No Content:**
(No response body, only status code)

**Response 404 Not Found:**
```json
{
  "error": "Not Found",
  "message": "Product with ID {id} not found"
}
```

**Response 409 Conflict:**
```json
{
  "error": "Conflict",
  "message": "Cannot delete a product with stock greater than 0"
}
```

**Response 400 Bad Request:**
```json
{
  "error": "Bad Request",
  "message": "Invalid ID"
}
```

**Note:** This is a physical deletion (hard delete). The product is permanently deleted from the database.

---

## 5. Error Handling

### 5.1 HTTP Status Codes

| Code | Description | Usage |
|------|-------------|-------|
| 200 | OK | Successful GET, successful PUT |
| 201 | Created | Successful POST |
| 204 | No Content | Successful DELETE |
| 400 | Bad Request | Invalid JSON format, invalid ID, incorrect data types, invalid query parameters |
| 404 | Not Found | Resource not found |
| 409 | Conflict | Business rule violation (stock > 0 on DELETE, inactive product on purchase) |
| 422 | Unprocessable Entity | Validation errors (price ≤ 0, negative stock, name < 3 characters, etc.) |
| 500 | Internal Server Error | Server error (unhandled) |

### 5.2 Error Response Format

**Standard structure:**
```json
{
  "error": "Error Type",
  "message": "Descriptive error message",
  "details": [] // Optional, for multiple validation errors
}
```

**Examples:**

**Simple error (400, 404, 409):**
```json
{
  "error": "Not Found",
  "message": "Product with ID 999 not found"
}
```

**Validation error (422):**
```json
{
  "error": "Validation Error",
  "message": "Validation errors",
  "details": [
    {
      "field": "name",
      "message": "The name must have at least 3 characters"
    },
    {
      "field": "price",
      "message": "The price must be greater than 0"
    }
  ]
}
```

**Server error (500):**
```json
{
  "error": "Internal Server Error",
  "message": "An unexpected error has occurred. Please try again later."
}
```

---

## 6. Special Cases and Edge Cases

### 6.1 Null Fields vs Omitted in PUT

- **Omitted field:** Maintains its current value
- **Field sent as `null`:** 
  - `description`: Set to null (allowed)
  - `name`, `price`, `stock`: Cannot be null, returns 422

### 6.2 Auto-generated Field Updates

- If attempting to send `id`, `created_at`, `updated_at` in PUT/POST:
  - These fields are ignored (not updated)
  - No error returned, simply ignored

### 6.3 Decimal Precision

- `price` is stored with 2 decimal precision
- If more than 2 decimals are sent, rounded to nearest
- Example: `1200.999` → `1201.00`

### 6.4 Text Search (search)

- Partial search (LIKE) in `name` and `description` fields
- Case-insensitive
- Example: `search=notebook` finds "Notebook", "NOTEBOOK", "My Notebook Pro"

### 6.5 Pagination

- If `page` or `limit` are invalid, use default values
- If `page` exceeds total pages, return empty array
- `total_pages` is calculated as: `ceil(total / limit)`

---

## 7. Additional Features Considered (Future)

### 7.1 Endpoints Not Included in v1.0.0

1. **PATCH /products/{id}**
   - Similar to PUT, but more semantically correct for partial updates
   - **Recommendation:** Keep PUT for partial updates in v1.0.0

2. **GET /products/inactive**
   - List inactive products
   - **Alternative:** Use `GET /products?active=false`

3. **POST /products/{id}/activate** and **POST /products/{id}/deactivate**
   - Specific endpoints to activate/deactivate
   - **Alternative:** Use PUT with `{"active": true/false}`

4. **GET /products/{id}/history**
   - Product change history (audit)
   - Requires history table

5. **Bulk Operations:**
   - `POST /products/bulk` - Create multiple products
   - `PUT /products/bulk` - Update multiple products

6. **GET /products/available**
   - List products available for purchase (active=true AND stock>0)
   - **Alternative:** Use `GET /products?active=true&stock_min=1`

---

## 8. Additional Technical Specifications

### 8.1 Data Formats

- **Date/Time:** ISO 8601 UTC (e.g., `2024-01-01T12:00:00Z`)
- **Decimals:** Dot as decimal separator (e.g., `1200.50`)
- **Booleans:** `true` or `false` (lowercase)

### 8.2 Limits and Restrictions

- **Maximum request body size:** 1MB
- **Maximum response time:** 5 seconds
- **Rate limiting:** Not specified (consider for production)

### 8.3 Versioning

- Current version: v1
- URL versioning: `/v1/products`
- Consider semantic versioning for future versions

---

## 9. Usage Examples

### 9.1 Complete Flow: Create, List, Update, Delete

**1. Create product:**
```bash
POST /products
{
  "name": "HP Laptop",
  "description": "Intel i7, 16GB RAM",
  "price": 1500,
  "stock": 5
}
→ 201 Created
```

**2. List products:**
```bash
GET /products?page=1&limit=10
→ 200 OK (list of active products)
```

**3. Get specific product:**
```bash
GET /products/1
→ 200 OK
```

**4. Update product (price only):**
```bash
PUT /products/1
{
  "price": 1400
}
→ 200 OK
```

**5. Reduce stock to 0:**
```bash
PUT /products/1
{
  "stock": 0
}
→ 200 OK
```

**6. Delete product (only if stock=0):**
```bash
DELETE /products/1
→ 204 No Content
```

### 9.2 Error Cases

**Attempt to delete product with stock:**
```bash
DELETE /products/1
→ 409 Conflict
{
  "error": "Conflict",
  "message": "Cannot delete a product with stock greater than 0"
}
```

**Create product with invalid data:**
```bash
POST /products
{
  "name": "AB",
  "price": -100,
  "stock": -5
}
→ 422 Unprocessable Entity
{
  "error": "Validation Error",
  "message": "Validation errors",
  "details": [
    {
      "field": "name",
      "message": "The name must have at least 3 characters"
    },
    {
      "field": "price",
      "message": "The price must be greater than 0"
    },
    {
      "field": "stock",
      "message": "The stock cannot be negative"
    }
  ]
}
```

---

## 10. Design Decisions Summary

### 10.1 Decisions Made

1. **GET /products returns only active by default**
   - Behavior: `active=true` by default
   - Justification: Most use cases require only active products

2. **GET /products/{id} returns active and inactive**
   - Justification: Direct search by ID should return the product regardless of its status

3. **PUT allows partial update**
   - Only sent fields are updated
   - Justification: More flexible and efficient

4. **DELETE is hard delete**
   - Physical deletion from database
   - Only if stock = 0

5. **Pagination included from v1.0.0**
   - Justification: Scalability and better user experience

### 10.2 Considerations for Future Versions

- Implement soft delete (mark as deleted instead of physical deletion)
- Add more detailed audit fields
- Implement product versioning
- Add categories/tags to products
- Implement images/multimedia
- Add statistics/reports endpoints

---

## 11. Implementation Checklist

### 11.1 Validations to Implement

- [ ] Required field validation in POST
- [ ] Minimum/maximum string length validation
- [ ] Numeric range validation (price > 0, stock >= 0)
- [ ] Data type validation
- [ ] Date/time format validation
- [ ] ID validation in path parameters

### 11.2 Business Rules to Implement

- [ ] Verify stock = 0 before DELETE
- [ ] Prevent price <= 0 in PUT
- [ ] Prevent negative stock in PUT
- [ ] Auto-generate and update timestamps
- [ ] Auto-generate IDs

### 11.3 Endpoints to Implement

- [ ] POST /products
- [ ] GET /products (with pagination and filters)
- [ ] GET /products/{id}
- [ ] PUT /products/{id}
- [ ] DELETE /products/{id}

### 11.4 Error Handling

- [ ] Handle 400 Bad Request
- [ ] Handle 404 Not Found
- [ ] Handle 409 Conflict
- [ ] Handle 422 Unprocessable Entity
- [ ] Handle 500 Internal Server Error
- [ ] Consistent error format

---

**End of Specification v1.2.0**
