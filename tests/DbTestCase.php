<?php

use Illuminate\Filesystem\ClassFinder;
use Illuminate\Filesystem\Filesystem;

/**
 * Class DbTestCase
 */
abstract class DbTestCase extends \Illuminate\Foundation\Testing\TestCase
{
    /**
     * @var mixed
     */
    protected $baseUrl;

    /**
     * DbTestCase constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->baseUrl = env('APP_URL');
    }

    /**
     * Creates the application.
     *
     * Needs to be implemented by subclasses.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication()
    {
        copy(__DIR__ . '/../src/config/multi-tenancy.php', __DIR__ . '/../vendor/laravel/laravel/config/multi-tenancy.php');

        $app = require __DIR__ . '/../vendor/laravel/laravel/bootstrap/app.php';

//        $app->register(\Sinclair\MultiTenancy\Providers\MultiTenancyServiceProvider::class);

        $app->make('Illuminate\Contracts\Console\Kernel')
            ->bootstrap();

        return $app;
    }

    /**
     * Setup DB before each test.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->migrate();
    }

    /**
     * run package database migrations
     *
     * @param string $path
     */
    public function migrate( $path = __DIR__ . "/../src/migrations" )
    {
        $fileSystem = new Filesystem;
        $classFinder = new ClassFinder;

        foreach ( $fileSystem->files($path) as $file )
        {
            $fileSystem->requireOnce($file);
            $migrationClass = $classFinder->findClass($file);

            ( new $migrationClass )->up();
        }
    }
}