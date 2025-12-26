# Especificación API - Microservicio de Productos

**Versión:** 1.2.0  
**Fecha:** 2024  
**Formato:** OpenAPI 3.0

---

## 1. Información General

### 1.1 Servidor Base
```
http://api.example.com/v1
```

### 1.2 Tipo de Contenido
- Request/Response: `application/json`
- Charset: `UTF-8`

### 1.3 Autenticación
- **Estado actual:** No especificado (endpoints públicos)
- **Nota:** Para producción, considerar implementar autenticación (JWT, API Keys, etc.)

---

## 2. Modelo de Datos

### 2.1 Entidad: Product

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

**Tipos de Datos y Restricciones:**
- `id`: integer (autogenerado, read-only, positivo)
- `name`: string (requerido, mínimo 3 caracteres, máximo 255 caracteres)
- `description`: string (opcional, máximo 1000 caracteres, puede ser null o vacío)
- `price`: decimal/float (requerido, > 0, precisión: 2 decimales, máximo: 999999.99)
- `stock`: integer (requerido, >= 0, máximo: 2147483647)
- `active`: boolean (default: true, no puede ser null)
- `created_at`: datetime ISO 8601 UTC (autogenerado, read-only, formato: YYYY-MM-DDTHH:mm:ssZ)
- `updated_at`: datetime ISO 8601 UTC (autogenerado, se actualiza automáticamente en cada modificación)
- `image` : string (opcional, máximo 1000 caracteres, puede ser null o vacío)

### 2.2 DTO de Creación (ProductCreate)

```json
{
  "name": "string",
  "description": "string",
  "price": 0.00,
  "stock": 0
}
```

**Campos:**
- `name`: string (requerido)
- `description`: string (opcional, puede ser null o omitirse)
- `price`: decimal (requerido)
- `stock`: integer (requerido)

**Nota:** 
- `active` no se incluye en el request (se inicializa en `true` por defecto)
- `id`, `created_at`, `updated_at` son autogenerados y no se aceptan en el request

### 2.3 DTO de Actualización (ProductUpdate)

```json
{
  "name": "string",
  "description": "string",
  "price": 0.00,
  "stock": 0,
  "active": true
}
```

**Comportamiento:**
- Todos los campos son opcionales
- Solo los campos enviados se actualizan (actualización parcial)
- Los campos omitidos mantienen su valor actual
- Los campos enviados como `null` se establecen a null (excepto campos que no permiten null)
- `id`, `created_at` nunca se pueden actualizar
- `updated_at` se actualiza automáticamente

---

## 3. Reglas de Negocio

### 3.1 Validaciones de Campos

| Campo | Regla | Código Error | Mensaje |
|-------|-------|--------------|---------|
| `name` (creación) | Requerido | 422 | "El nombre es requerido" |
| `name` (creación/actualización) | Mínimo 3 caracteres | 422 | "El nombre debe tener al menos 3 caracteres" |
| `name` (creación/actualización) | Máximo 255 caracteres | 422 | "El nombre no puede exceder 255 caracteres" |
| `description` | Máximo 1000 caracteres | 422 | "La descripción no puede exceder 1000 caracteres" |
| `price` (creación) | Requerido | 422 | "El precio es requerido" |
| `price` (creación/actualización) | Debe ser > 0 | 422 | "El precio debe ser mayor que 0" |
| `price` (creación/actualización) | Máximo 999999.99 | 422 | "El precio no puede exceder 999999.99" |
| `stock` (creación) | Requerido | 422 | "El stock es requerido" |
| `stock` (creación/actualización) | No puede ser negativo | 422 | "El stock no puede ser negativo" |
| `id` (path) | Debe ser un número entero válido | 400 | "ID inválido" |

### 3.2 Reglas de Negocio

1. **Productos inactivos no pueden ser comprados**
   - Si `active = false`, el producto no debe estar disponible para compra
   - Esta validación se aplica en el contexto de compras (fuera del alcance de este microservicio)
   - **Código de error:** 409 Conflict
   - **Mensaje:** "No se puede comprar un producto inactivo"

2. **Productos con stock > 0 no pueden ser eliminados**
   - Al intentar DELETE, si `stock > 0`, debe retornar error
   - Solo se permite eliminar productos con `stock = 0`
   - **Código de error:** 409 Conflict
   - **Mensaje:** "No se puede eliminar un producto con stock mayor a 0"

3. **No se puede establecer stock negativo**
   - En PUT/PATCH, si `stock < 0`, debe retornar error de validación
   - **Código de error:** 422 Unprocessable Entity
   - **Mensaje:** "El stock no puede ser negativo"

4. **No se puede establecer precio ≤ 0**
   - En PUT/PATCH, si `price <= 0`, debe retornar error de validación
   - **Código de error:** 422 Unprocessable Entity
   - **Mensaje:** "El precio debe ser mayor que 0"

5. **Campos autogenerados no modificables**
   - `id`: Nunca se puede modificar después de la creación
   - `created_at`: Nunca se puede modificar, mantiene su valor original
   - `updated_at`: Se actualiza automáticamente en cada modificación, no se puede establecer manualmente

6. **Tipos de datos**
   - Si se envía un campo con tipo incorrecto (ej: `price` como string), retornar 400 Bad Request
   - **Mensaje:** "Formato de datos inválido en el campo {campo}"

---

## 4. Endpoints

### 4.1 POST /products
**Descripción:** Crea un nuevo producto

**Request Body:**
```json
{
  "name": "Notebook",
  "description": "16GB RAM",
  "price": 1200,
  "stock": 10
}
```

**Validaciones aplicadas:**
- `name`: requerido, mínimo 3 caracteres, máximo 255 caracteres
- `price`: requerido, > 0, máximo 999999.99
- `stock`: requerido, >= 0
- `description`: opcional, máximo 1000 caracteres

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
  "message": "Errores de validación",
  "details": [
    {
      "field": "name",
      "message": "El nombre debe tener al menos 3 caracteres"
    },
    {
      "field": "price",
      "message": "El precio debe ser mayor que 0"
    }
  ]
}
```

**Response 400 Bad Request:**
```json
{
  "error": "Bad Request",
  "message": "Formato JSON inválido"
}
```

---

### 4.2 GET /products
**Descripción:** Lista todos los productos activos con paginación y filtros opcionales

**Query Parameters:**
- `page` (integer, opcional): Número de página (default: 1, mínimo: 1)
- `limit` (integer, opcional): Cantidad de items por página (default: 10, mínimo: 1, máximo: 100)
- `active` (boolean, opcional): Filtrar por estado activo (default: true, solo activos si no se especifica)
- `min_price` (decimal, opcional): Precio mínimo (>= min_price)
- `max_price` (decimal, opcional): Precio máximo (<= max_price)
- `stock_min` (integer, opcional): Stock mínimo (>= stock_min)
- `search` (string, opcional): Búsqueda por nombre o descripción (búsqueda parcial, case-insensitive)
- `sort` (string, opcional): Campo por el cual ordenar (default: "id", valores: id, name, price, stock, created_at, updated_at)
- `order` (string, opcional): Orden (default: "asc", valores: asc, desc)

**Ejemplo de Request:**
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

**Comportamiento:**
- Por defecto retorna solo productos activos (`active = true`)
- Si se especifica `active=false`, retorna solo productos inactivos
- Si se especifica `active=both` o se omite el parámetro, retorna solo activos (comportamiento por defecto)

**Response 400 Bad Request:**
```json
{
  "error": "Bad Request",
  "message": "Parámetros de consulta inválidos",
  "details": [
    {
      "field": "page",
      "message": "Debe ser un número entero positivo"
    }
  ]
}
```

---

### 4.3 GET /products/{id}
**Descripción:** Obtiene un producto por su ID (activo o inactivo)

**Path Parameters:**
- `id` (integer, requerido): ID del producto

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

**Nota:** Este endpoint retorna productos activos e inactivos, ya que es una búsqueda directa por ID.

**Response 404 Not Found:**
```json
{
  "error": "Not Found",
  "message": "Producto con ID {id} no encontrado"
}
```

**Response 400 Bad Request:**
```json
{
  "error": "Bad Request",
  "message": "ID inválido"
}
```

---

### 4.4 PUT /products/{id}
**Descripción:** Actualiza los datos de un producto (actualización parcial)

**Path Parameters:**
- `id` (integer, requerido): ID del producto

**Request Body:**
```json
{
  "name": "Notebook Actualizado",
  "description": "32GB RAM",
  "price": 1500,
  "stock": 5,
  "active": true
}
```

**Comportamiento:**
- Actualización parcial: solo los campos enviados se actualizan
- Los campos omitidos mantienen su valor actual
- Si un campo se envía como `null`, se establece a null (excepto campos que no permiten null como `name`, `price`, `stock`)
- `id`, `created_at` nunca se pueden actualizar
- `updated_at` se actualiza automáticamente

**Validaciones:**
- `name`: si se envía, mínimo 3 caracteres, máximo 255 caracteres
- `price`: si se envía, debe ser > 0, máximo 999999.99
- `stock`: si se envía, no puede ser negativo
- `description`: si se envía, máximo 1000 caracteres, puede ser null
- `active`: si se envía, debe ser boolean

**Ejemplo: Actualizar solo el precio**
```json
{
  "price": 1300
}
```
Solo se actualiza el precio, los demás campos mantienen su valor.

**Response 200 OK:**
```json
{
  "id": 1,
  "name": "Notebook Actualizado",
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
  "message": "Producto con ID {id} no encontrado"
}
```

**Response 422 Unprocessable Entity:**
```json
{
  "error": "Validation Error",
  "message": "El precio debe ser mayor que 0"
}
```
o
```json
{
  "error": "Validation Error",
  "message": "El stock no puede ser negativo"
}
```
o para múltiples errores:
```json
{
  "error": "Validation Error",
  "message": "Errores de validación",
  "details": [
    {
      "field": "price",
      "message": "El precio debe ser mayor que 0"
    },
    {
      "field": "stock",
      "message": "El stock no puede ser negativo"
    }
  ]
}
```

**Response 400 Bad Request:**
```json
{
  "error": "Bad Request",
  "message": "ID inválido o formato JSON inválido"
}
```

---

### 4.5 DELETE /products/{id}
**Descripción:** Elimina un producto permanentemente (solo si stock = 0)

**Path Parameters:**
- `id` (integer, requerido): ID del producto

**Validaciones de Negocio:**
- El producto debe existir
- El producto debe tener `stock = 0` (no se puede eliminar si stock > 0)

**Response 204 No Content:**
(Sin cuerpo de respuesta, solo status code)

**Response 404 Not Found:**
```json
{
  "error": "Not Found",
  "message": "Producto con ID {id} no encontrado"
}
```

**Response 409 Conflict:**
```json
{
  "error": "Conflict",
  "message": "No se puede eliminar un producto con stock mayor a 0"
}
```

**Response 400 Bad Request:**
```json
{
  "error": "Bad Request",
  "message": "ID inválido"
}
```

**Nota:** Esta es una eliminación física (hard delete). El producto se elimina permanentemente de la base de datos.

---

## 5. Manejo de Errores

### 5.1 Códigos de Estado HTTP

| Código | Descripción | Uso |
|--------|-------------|-----|
| 200 | OK | GET exitoso, PUT exitoso |
| 201 | Created | POST exitoso |
| 204 | No Content | DELETE exitoso |
| 400 | Bad Request | Formato JSON inválido, ID inválido, tipos de datos incorrectos, parámetros de consulta inválidos |
| 404 | Not Found | Recurso no encontrado |
| 409 | Conflict | Violación de regla de negocio (stock > 0 en DELETE, producto inactivo en compra) |
| 422 | Unprocessable Entity | Errores de validación (precio ≤ 0, stock negativo, name < 3 caracteres, etc.) |
| 500 | Internal Server Error | Error del servidor (no controlado) |

### 5.2 Formato de Respuestas de Error

**Estructura estándar:**
```json
{
  "error": "Error Type",
  "message": "Mensaje descriptivo del error",
  "details": [] // Opcional, para errores de validación múltiples
}
```

**Ejemplos:**

**Error simple (400, 404, 409):**
```json
{
  "error": "Not Found",
  "message": "Producto con ID 999 no encontrado"
}
```

**Error de validación (422):**
```json
{
  "error": "Validation Error",
  "message": "Errores de validación",
  "details": [
    {
      "field": "name",
      "message": "El nombre debe tener al menos 3 caracteres"
    },
    {
      "field": "price",
      "message": "El precio debe ser mayor que 0"
    }
  ]
}
```

**Error de servidor (500):**
```json
{
  "error": "Internal Server Error",
  "message": "Ha ocurrido un error inesperado. Por favor, intente nuevamente más tarde."
}
```

---

## 6. Casos Especiales y Edge Cases

### 6.1 Campos Null vs Omitidos en PUT

- **Campo omitido:** Mantiene su valor actual
- **Campo enviado como `null`:** 
  - `description`: Se establece a null (permitido)
  - `name`, `price`, `stock`: No pueden ser null, retorna 422

### 6.2 Actualización de Campos Autogenerados

- Si se intenta enviar `id`, `created_at`, `updated_at` en PUT/POST:
  - Estos campos son ignorados (no se actualizan)
  - No retorna error, simplemente se ignoran

### 6.3 Precisión Decimal

- `price` se almacena con precisión de 2 decimales
- Si se envía más de 2 decimales, se redondea al más cercano
- Ejemplo: `1200.999` → `1201.00`

### 6.4 Búsqueda por Texto (search)

- Búsqueda parcial (LIKE) en campos `name` y `description`
- Case-insensitive
- Ejemplo: `search=notebook` encuentra "Notebook", "NOTEBOOK", "My Notebook Pro"

### 6.5 Paginación

- Si `page` o `limit` son inválidos, usar valores por defecto
- Si `page` excede el total de páginas, retornar array vacío
- `total_pages` se calcula como: `ceil(total / limit)`

---

## 7. Funcionalidades Adicionales Consideradas (Futuras)

### 7.1 Endpoints No Incluidos en v1.0.0

1. **PATCH /products/{id}**
   - Similar a PUT, pero más semánticamente correcto para actualizaciones parciales
   - **Recomendación:** Mantener PUT para actualizaciones parciales en v1.0.0

2. **GET /products/inactive**
   - Lista productos inactivos
   - **Alternativa:** Usar `GET /products?active=false`

3. **POST /products/{id}/activate** y **POST /products/{id}/deactivate**
   - Endpoints específicos para activar/desactivar
   - **Alternativa:** Usar PUT con `{"active": true/false}`

4. **GET /products/{id}/history**
   - Historial de cambios del producto (auditoría)
   - Requiere tabla de historial

5. **Bulk Operations:**
   - `POST /products/bulk` - Crear múltiples productos
   - `PUT /products/bulk` - Actualizar múltiples productos

6. **GET /products/available**
   - Lista productos disponibles para compra (active=true AND stock>0)
   - **Alternativa:** Usar `GET /products?active=true&stock_min=1`

---

## 8. Especificaciones Técnicas Adicionales

### 8.1 Formatos de Datos

- **Fecha/Hora:** ISO 8601 UTC (ej: `2024-01-01T12:00:00Z`)
- **Decimales:** Punto como separador decimal (ej: `1200.50`)
- **Booleanos:** `true` o `false` (lowercase)

### 8.2 Límites y Restricciones

- **Tamaño máximo de request body:** 1MB
- **Tiempo máximo de respuesta:** 5 segundos
- **Rate limiting:** No especificado (considerar para producción)

### 8.3 Versionado

- Versión actual: v1
- Versionado en URL: `/v1/products`
- Considerar versionado semántico para futuras versiones

---

## 9. Ejemplos de Uso

### 9.1 Flujo Completo: Crear, Listar, Actualizar, Eliminar

**1. Crear producto:**
```bash
POST /products
{
  "name": "Laptop HP",
  "description": "Intel i7, 16GB RAM",
  "price": 1500,
  "stock": 5
}
→ 201 Created
```

**2. Listar productos:**
```bash
GET /products?page=1&limit=10
→ 200 OK (lista de productos activos)
```

**3. Obtener producto específico:**
```bash
GET /products/1
→ 200 OK
```

**4. Actualizar producto (solo precio):**
```bash
PUT /products/1
{
  "price": 1400
}
→ 200 OK
```

**5. Reducir stock a 0:**
```bash
PUT /products/1
{
  "stock": 0
}
→ 200 OK
```

**6. Eliminar producto (solo si stock=0):**
```bash
DELETE /products/1
→ 204 No Content
```

### 9.2 Casos de Error

**Intentar eliminar producto con stock:**
```bash
DELETE /products/1
→ 409 Conflict
{
  "error": "Conflict",
  "message": "No se puede eliminar un producto con stock mayor a 0"
}
```

**Crear producto con datos inválidos:**
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
  "message": "Errores de validación",
  "details": [
    {
      "field": "name",
      "message": "El nombre debe tener al menos 3 caracteres"
    },
    {
      "field": "price",
      "message": "El precio debe ser mayor que 0"
    },
    {
      "field": "stock",
      "message": "El stock no puede ser negativo"
    }
  ]
}
```

---

## 10. Resumen de Decisiones de Diseño

### 10.1 Architecture

- Controllers: HTTP layer
- Services: business logic
- Models: persistence
- Resources: API output formatting

### 10.2 Decisiones Tomadas

1. **GET /products retorna solo activos por defecto**
   - Comportamiento: `active=true` por defecto
   - Justificación: La mayoría de casos de uso requieren solo productos activos

2. **GET /products/{id} retorna activos e inactivos**
   - Justificación: Búsqueda directa por ID debe retornar el producto independientemente de su estado

3. **PUT permite actualización parcial**
   - Solo campos enviados se actualizan
   - Justificación: Más flexible y eficiente

4. **DELETE es hard delete**
   - Eliminación física de la base de datos
   - Solo si stock = 0

5. **Paginación incluida desde v1.0.0**
   - Justificación: Escalabilidad y mejor experiencia de usuario

### 10.3 Consideraciones para Futuras Versiones

- Implementar soft delete (marcar como eliminado en lugar de eliminar físicamente)
- Agregar campos de auditoría más detallados
- Implementar versionado de productos
- Agregar categorías/tags a productos
- Implementar imágenes/multimedia
- Agregar endpoints de estadísticas/reportes

---

## 11. Checklist de Implementación

### 11.1 Validaciones a Implementar

- [ ] Validación de campos requeridos en POST
- [ ] Validación de longitud mínima/máxima de strings
- [ ] Validación de rango numérico (price > 0, stock >= 0)
- [ ] Validación de tipos de datos
- [ ] Validación de formato de fecha/hora
- [ ] Validación de ID en path parameters

### 11.2 Reglas de Negocio a Implementar

- [ ] Verificar stock = 0 antes de DELETE
- [ ] Prevenir precio <= 0 en PUT
- [ ] Prevenir stock negativo en PUT
- [ ] Auto-generar y actualizar timestamps
- [ ] Auto-generar IDs

### 11.3 Endpoints a Implementar

- [ ] POST /products
- [ ] GET /products (con paginación y filtros)
- [ ] GET /products/{id}
- [ ] PUT /products/{id}
- [ ] DELETE /products/{id}

### 11.4 Manejo de Errores

- [ ] Manejo de 400 Bad Request
- [ ] Manejo de 404 Not Found
- [ ] Manejo de 409 Conflict
- [ ] Manejo de 422 Unprocessable Entity
- [ ] Manejo de 500 Internal Server Error
- [ ] Formato consistente de errores

## 12 Testing Strategy

- Business logic is tested at service level
- Controllers are kept thin
- Database assertions ensure data integrity
---

## 13 Security

- Authentication: Laravel Sanctum
- Authorization: Policies
- Protected routes via middleware

**Fin de la Especificación v1.2.0**