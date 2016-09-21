<?php

namespace Sinclair\PaymentEngine\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Sinclair\PaymentEngine\Contracts\Engine;
use Sinclair\PaymentEngine\Contracts\PlanRepository;
use Sinclair\PaymentEngine\Contracts\TransactionRepository;

class ProcessTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transaction:process {transaction?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually process a transaction';

    /**
     * @var Engine
     */
    private $engine;

    /**
     * @var TransactionRepository
     */
    private $transactionRepository;

    /**
     * Create a new command instance.
     *
     * @param Engine $engine
     * @param TransactionRepository $transactionRepository
     */
    public function __construct( Engine $engine, TransactionRepository $transactionRepository )
    {
        parent::__construct();

        $this->engine = $engine;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach ( $this->getTransactions() as $transaction )
            $this->processTransaction($transaction);
    }

    /**
     * @param $transaction
     */
    protected function processTransaction( $transaction )
    {
        $this->engine->processTransaction($transaction);

        $result = $this->engine->getResult($transaction->id, 'transactions');

        $result[ 'status' ] == true ?
            $this->info('Transaction ' . $transaction->id . ' successfully processed!') :
            $this->warn('Transaction ' . $transaction->id . 'failed! ' . $result[ 'message' ]);
    }

    /**
     * @return Collection
     */
    protected function singleTransaction()
    {
        return collect([ $this->transactionRepository->getById($this->argument('transaction')) ]);
    }

    /**
     * @return Collection
     */
    protected function failedTransactions()
    {
        return $this->transactionRepository->getFailed();
    }

    /**
     * @return Collection
     */
    protected function getTransactions()
    {
        return !is_null($this->argument('transaction')) ? $this->singleTransaction() : $this->failedTransactions();
    }
}