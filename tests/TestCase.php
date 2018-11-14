<?php

namespace Happypixels\Shopr\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Illuminate\Database\Schema\Blueprint;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return ['Happypixels\Shopr\ShoprServiceProvider'];
    }

    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
        $this->withFactories(__DIR__.'/../database/factories');

        // Set a dummy app key in order to encrypt cookies.
        config(['app.key' => 'base64:wbvPP9pBOwifnwu84BeKAVzmwM4TLvkVFowLaPAi6nA=']);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('test_shoppables', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->decimal('price', 9, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        TestShoppable::create(['title' => 'Test product', 'price' => 500]);

        include_once __DIR__ . '/../database/migrations/create_order_tables.php.stub';
        (new \CreateOrderTables())->up();
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default config.
        $app['config']->set('shopr', [
            'templates' => [
                'order-confirmation' => 'test',
            ],

            'tax' => 25,

            'gateways' => [
                'stripe' => [
                    'publishable_key' => 'stripePublishableKey',
                    'api_key' => 'stripeSecretKey'
                ],

                'klarna_checkout' => [
                    'username' => 'PK02481_a7283092381e',
                    'secret' => 'VZ5FDWw6boJPgto0',
                ]
            ]
        ]);
    }
}
