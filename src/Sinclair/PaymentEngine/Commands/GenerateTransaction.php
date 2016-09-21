<?php

namespace Sinclair\PaymentEngine\Commands;

use Illuminate\Console\Command;
use Sinclair\PaymentEngine\Contracts\Engine;
use Sinclair\PaymentEngine\Contracts\PlanRepository;

class GenerateTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transaction:generate {plan} {--calculate=true} {--process=true}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually generate a transaction for a plan';

    /**
     * @var Engine
     */
    private $engine;

    /**
     * @var PlanRepository
     */
    private $planRepository;

    /**
     * Create a new command instance.
     *
     * @param Engine $engine
     * @param PlanRepository $planRepository
     */
    public function __construct( Engine $engine, PlanRepository $planRepository )
    {
        parent::__construct();

        $this->engine = $engine;
        $this->planRepository = $planRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $plan_id = $this->argument('plan');

        $plan = $this->planRepository->getById($plan_id);

        $this->engine->handleTransaction($plan, true == $this->option('calculate'), true == $this->option('process'));

        $result = $this->engine->getResult($plan_id);

        $result[ 'status' ] == true ?
            $this->info('Transaction successfully generated' . ( true == $this->option('process') ? ' and processed!' : '!' )) :
            $this->warn('Transaction failed! ' . $result[ 'message' ]);
    }
}