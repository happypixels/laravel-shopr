<?php

namespace Happypixels\Shopr;

use Happypixels\Shopr\Cart\Drivers\SessionCart;
use Happypixels\Shopr\Cart\ShoppingCart;
use Happypixels\Shopr\Contracts\CartDriver;
use Happypixels\Shopr\Models\Order;
use Happypixels\Shopr\Models\OrderItem;
use Happypixels\Shopr\Money\Formatter;
use Happypixels\Shopr\Observers\OrderObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class ShoprServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPublishables();
        $this->registerRoutes();

        $this->publishMigration('CreateOrderTables', 'create_order_tables');
        $this->publishMigration('CreateDiscountCouponsTable', 'create_discount_coupons_table');

        $this->loadViewsFrom(__DIR__.'/Views', 'shopr');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'shopr');
        $this->mergeConfigFrom(__DIR__.'/../config/shopr.php', 'shopr');

        $this->registerEventListeners();
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(CartDriver::class, SessionCart::class);
        $this->app->singleton('shopr.cart', ShoppingCart::class);

        if (config('shopr.models.Order')) {
            $this->app->singleton(Order::class, config('shopr.models.Order'));
        }

        if (config('shopr.models.OrderItem')) {
            $this->app->singleton(OrderItem::class, config('shopr.models.OrderItem'));
        }

        if (config('shopr.money_formatter')) {
            $this->app->singleton(Formatter::class, config('shopr.money_formatter'));
        }
    }

    /**
     * Registers the event listeners used by the package.
     * We manually register the events here rather than automatically registering the observer
     * because we want to be in control of when the events are fired.
     *
     * @return void
     */
    protected function registerEventListeners()
    {
        Event::listen('shopr.orders.confirmed', function (Order $order) {
            (new OrderObserver)->confirmed($order);
        });
    }

    /**
     * Registers the routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        if (Shopr::restApiEnabled()) {
            $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
        }

        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');
    }

    /**
     * Registers the publishable assets.
     *
     * @return void
     */
    protected function registerPublishables()
    {
        $this->publishes([__DIR__.'/../config/shopr.php' => config_path('shopr.php')], 'config');
        $this->publishes([__DIR__.'/Views' => $this->app->resourcePath('views/vendor/shopr')], 'views');
        $this->publishes([__DIR__.'/../resources/lang' => resource_path('lang/vendor/shopr')], 'translations');
    }

    /**
     * Attempts to publish a migration file.
     *
     * @param  string $classname
     * @param  string $filename
     * @return bool
     */
    protected function publishMigration($classname, $filename)
    {
        if (class_exists($classname)) {
            return false;
        }

        $this->publishes([
            __DIR__.'/../database/migrations/'.$filename.'.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_'.$filename.'.php'),
        ], 'migrations');

        return true;
    }
}
