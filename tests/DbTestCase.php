<?php

require_once 'Models/Dummy.php';

use Illuminate\Filesystem\ClassFinder;
use Illuminate\Filesystem\Filesystem;

/**
 * Class DbTestCase
 */
abstract class DbTestCase extends \Illuminate\Foundation\Testing\TestCase
{

    /**
     * @var \Faker\Generator
     */
    protected $faker;

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

        $this->baseUrl = 'http://payment-engine.local';
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
        $app = require __DIR__ . '/../vendor/laravel/laravel/bootstrap/app.php';

        $app->register(\Sinclair\PaymentEngine\PaymentEngineServiceProvider::class);

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

        $this->app[ 'config' ]->set('database.default', 'sqlite');
        $this->app[ 'config' ]->set('database.connections.sqlite.database', ':memory:');

        $this->faker = Faker\Factory::create();

//        $this->migrate();
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

    /**
     * @param array $attributes
     *
     * @param int $count
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    protected function createPlan( $attributes = [], $count = 1 )
    {
        $plans = collect();
        for ( $i = 0; $i < $count; $i++ )
        {
            $plannable = Dummy::create([
                'first_name'        => $this->faker->firstName,
                'last_name'         => $this->faker->lastName,
                'type'              => $this->faker->creditCardType,
                'billing_address_1' => $this->faker->streetAddress,
                'billing_address_2' => '',
                'billing_city'      => $this->faker->city,
                'billing_postcode'  => $this->faker->postcode,
                'billing_state'     => '',
                'billing_country'   => $this->faker->country,
                'billing_phone'     => $this->faker->phoneNumber,
                'shipping_address1' => $this->faker->streetAddress,
                'shipping_address2' => '',
                'shipping_city'     => $this->faker->city,
                'shipping_postcode' => $this->faker->postcode,
                'shipping_state'    => '',
                'shipping_country'  => $this->faker->country,
                'shipping_phone'    => $this->faker->phoneNumber,
                'company'           => $this->faker->company,
                'email'             => $this->faker->email,
            ]);

            $data = [
                'plannable_type'    => get_class($plannable),
                'plannable_id'      => $plannable->id,
                'card_number'       => 1234567891234567,
                'card_starts_at'    => \Carbon\Carbon::now()
                                                     ->subYear()
                                                     ->toDateTimeString(),
                'card_expires_at'   => \Carbon\Carbon::now()
                                                     ->addYears(2)
                                                     ->toDateTimeString(),
                'card_cvv'          => $this->faker->numberBetween(100, 999),
                'card_type'         => $this->faker->creditCardType,
                'card_issue_number' => 1,
                'currency'          => 'GBP',
                'last_failed_at'    => null,
            ];

            $data = array_replace($data, $this->randomSchedule($attributes), $attributes);

            $plans->push(app('PlanRepository')
                ->add($data)
                ->fresh());
        }

        return $plans;
    }

    /**
     * @param array $attributes
     * @param int $count
     *
     * @return \Illuminate\Support\Collection
     */
    protected function createCharge( $attributes = [], $count = 1 )
    {
        $charges = collect();
        for ( $i = 0; $i < $count; $i++ )
        {
            $data = [
                'plan_id'     => array_get($attributes, 'plan_id', $this->createPlan($attributes)
                                                                        ->first()->id),
                'amount'      => $this->faker->randomFloat(2, 0, 100),
                'description' => $this->faker->sentence
            ];

            $data = array_replace($data, $this->createWeeklySchedule(), $attributes);

            $charges->push(app('ChargeRepository')
                ->add($data)
                ->fresh());
        }

        return $charges;
    }

    /**
     * @return array
     */
    protected function createWeeklySchedule()
    {
        return [
            'minute'      => 0,
            'hour'        => 1,
            'day_of_week' => 3,
            'frequency'   => 'weekly'
        ];
    }

    /**
     * @var array
     */
    private $requiredFields = [
        'is_minutely' => [],
        'is_hourly'   => [ 'minute' ],
        'is_daily'    => [ 'hour', 'minute' ],
        'is_weekly'   => [ 'day_of_week', 'hour', 'minute' ],
        'is_monthly'  => [ 'day_of_month', 'hour', 'minute' ],
        'is_annually' => [ 'month_of_year', 'day_of_month', 'hour', 'minute' ],
        'is_adhoc'    => [ 'year', 'month_of_year', 'day_of_month', 'hour', 'minute' ]
    ];

    /**
     * @var array
     */
    private $cronKeys = [
        'minute',
        'hour',
        'day_of_month',
        'month_of_year',
        'day_of_week',
        'year',
    ];

    /**
     * @param array $attributes
     *
     * @return array
     */
    protected function randomSchedule( $attributes = [] )
    {
        $data = [
            'minute'        => $this->faker->numberBetween(0, 59),
            'hour'          => $this->faker->numberBetween(0, 23),
            'day_of_week'   => $this->faker->numberBetween(0, 6),
            'day_of_month'  => $this->faker->numberBetween(1, 28),
            'month_of_year' => $this->faker->numberBetween(1, 12),
            'year'          => $this->faker->numberBetween(date('Y'), date('Y') + 3),
            'frequency'     => $this->faker->randomElement([ 'weekly', 'monthly', 'annually', 'adhoc' ]),
            'starts_at'     => $this->faker->boolean ? \Carbon\Carbon::now()
                                                                     ->addWeeks($this->faker->numberBetween(0, 6))
                                                                     ->toDateTimeString() : null,
            'expires_at'    => $this->faker->boolean ? \Carbon\Carbon::now()
                                                                     ->addMonths($this->faker->numberBetween(2, 24))
                                                                     ->toDateTimeString() : null,

        ];

        $schedule = array_replace($data, $attributes);

        foreach ( [ 'minute', 'hour', 'day_of_week', 'day_of_month', 'month_of_year', 'year' ] as $field )
            if ( !in_array($field, $this->requiredFields[ 'is_' . $schedule[ 'frequency' ] ]) )
                $schedule[ $field ] = null;

        $cron = [];

        foreach ( $this->cronKeys as $key => $field )
            $cron[ $key ] = is_null($schedule[ $field ]) ? '*' : $schedule[ $field ];

        if ( \Cron\CronExpression::isValidExpression(implode(' ', $cron)) )
            return $schedule;

        return $this->randomSchedule($attributes);
    }

    /**
     * @param array $attributes
     *
     * @return array
     */
    protected function createTransaction( $attributes = [] )
    {
        $data = [
            'plan_id'           => $this->createPlan($attributes)->first()->id,
            'reference'         => implode(' ', $this->faker->words()),
            'is_success'        => $success = $this->faker->boolean,
            'is_failure'        => !$success,
            'gateway_response'  => null,
            'card_number'       => 1234567891234567,
            'card_starts_at'    => \Carbon\Carbon::now()
                                                 ->subYear()
                                                 ->toDateTimeString(),
            'card_expires_at'   => \Carbon\Carbon::now()
                                                 ->addYears(2)
                                                 ->toDateTimeString(),
            'card_cvv'          => $this->faker->numberBetween(100, 999),
            'card_type'         => $this->faker->creditCardType,
            'card_issue_number' => 1,
            'currency'          => 'GBP',
        ];

        return array_replace($data, $attributes);
    }
}