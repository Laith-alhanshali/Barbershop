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

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Barbershop Application - Deployment Instructions

This Laravel application uses **Filament v4** for the admin panel and **Spatie Laravel Permission** with **Filament Shield** for role-based access control.

### Roles

The application supports the following roles:
- `super_admin` - Full system access
- `admin` - Administrative access
- `receptionist` - Reception desk operations
- `barber` - Barber-specific features

### Production Bootstrap Setup

After deploying to production, you need to ensure there is at least one super admin user to access the system. Use the `ProductionBootstrapSeeder` for this purpose.

#### Required Environment Variables

Add the following keys to your production `.env` file:

```env
# Bootstrap Admin User (for initial setup)
BOOTSTRAP_ADMIN_NAME="Super Admin"
BOOTSTRAP_ADMIN_EMAIL=admin@example.com
BOOTSTRAP_ADMIN_PASSWORD=YourSecurePasswordHere
```

**Note:**
- `BOOTSTRAP_ADMIN_NAME` is optional (defaults to "Super Admin")
- `BOOTSTRAP_ADMIN_EMAIL` is **required**
- `BOOTSTRAP_ADMIN_PASSWORD` is **required**

#### Running the Bootstrap Seeder

After setting the environment variables, run this command **once** in production:

```bash
php artisan db:seed --class=ProductionBootstrapSeeder
```

This seeder will:
- Ensure the `super_admin` role exists (guard: `web`)
- Create a new user if the email doesn't exist
- **Never overwrite** the password if the user already exists
- Assign the `super_admin` role if not already assigned
- Display clear success or warning messages

#### Security Best Practices

🔒 **Important Security Notes:**

1. **Change the password immediately** after your first login
2. **Optionally remove** the `BOOTSTRAP_ADMIN_*` keys from `.env` after the initial setup
3. Never commit `.env` files to version control
4. Use strong, unique passwords for production environments

#### Re-running the Seeder

The seeder is **production-safe** and can be run multiple times:
- If the user already exists, it will **not** change their password
- It will only ensure the `super_admin` role is assigned
- If email/password are missing from `.env`, it will skip with a warning

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
