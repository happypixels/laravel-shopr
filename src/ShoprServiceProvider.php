<?php

namespace Happypixels\Shopr;

use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Models\Order;
use Happypixels\Shopr\Observers\OrderObserver;
use Happypixels\Shopr\Repositories\SessionCartRepository;
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
        $this->publishes([
            __DIR__ . '/../config/shopr.php' => config_path('shopr.php'),
        ], 'config');

        if (!class_exists('CreateOrderTables')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_order_tables.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_order_tables.php'),
            ], 'migrations');
        }

        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');

        $this->loadViewsFrom(__DIR__ . '/Views', 'shopr');

        // We manually register the events here rather than automatically registering the observer
        // because we want to be in control of when the events are fired.
        Event::listen('shopr.orders.created', function (Order $order) {
            (new OrderObserver)->created($order);
        });
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Cart::class, SessionCartRepository::class);
    }
}
