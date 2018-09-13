# laravel-shopr
A developer-friendly e-commerce foundation for your Laravel app. 
All the features you need for your webshop but without sacrificing you as a developer. 
Full documentation here: https://laravel-shopr.happypixels.se

**Some of the features included:**
* Shopping cart
* Any model can be shoppable
* Checkout process with payment solutions out of the box
* Cart to Order conversion
* Automated order emails to the customer and administrators
* A simple REST API for managing the cart and checkout
* And more

## Documentation
Full documentation: https://laravel-shopr.happypixels.se  
Demo application: https://github.com/happypixels/laravel-shopr-demo

## Requirements
* PHP 7.1+
* Laravel 5.5+
* MySQL 5.7+

## Installation
Install the package via Composer:
```bash
composer require happypixels/laravel-shopr
```

Publish and run the migrations:
```bash
php artisan vendor:publish --provider="Happypixels\Shopr\ShoprServiceProvider" --tag="migrations"
php artisan migrate
```

Publish and review the configuration:
```bash
php artisan vendor:publish --provider="Happypixels\Shopr\ShoprServiceProvider" --tag="config"
```

After this, refer to the [extensive documentation](https://laravel-shopr.happypixels.se) to get started.

## Contributing
Found a bug or have a feature request? [Open an issue on Github](https://github.com/happypixels/laravel-shopr/issues).   
Found a security-related issue? Please email mattias@happypixels.se.