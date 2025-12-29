<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.
/////////////////////////////////////////////////////////////////////
## Error Handling

This API returns consistent JSON error responses.

Example:
{
  "error": "VALIDATION_ERROR",
  "message": "Validation failed",
  "details": { ... }
}


## Authentication (Important Note)

This microservice includes a basic authentication flow using Laravel Sanctum.

⚠️ This is **NOT required nor recommended** in a real microservices architecture.

### Why is authentication included?

Authentication was implemented in this service **for learning and demonstration purposes only**, in order to:

- Practice token-based authentication
- Understand how Sanctum works internally
- Secure endpoints during local development
- Simulate protected routes using `auth:sanctum`

### How it should work in a real system

In a real-world microservices architecture:

- Authentication should be handled by a dedicated **Auth Service**
- Tokens should be issued by that service
- This Product Service should only:
  - Validate incoming tokens
  - Trust the identity provided by the Auth Service
