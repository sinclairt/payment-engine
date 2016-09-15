<?php

namespace Sinclair\PaymentEngine\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Sinclair\ApiFoundation\Traits\ApiFoundation;
use Sinclair\ApiFoundation\Transformers\DefaultTransformer;
use Sinclair\PaymentEngine\Contracts\TransactionRepository;
use Sinclair\PaymentEngine\FormRequests\CreateTransaction;
use Sinclair\PaymentEngine\FormRequests\UpdateTransaction;

/**
 * Class TransactionController
 * @package Sinclair\PaymentEngine\Controllers
 */
class TransactionController extends Controller
{
    use ApiFoundation;

    /**
     * TransactionController constructor.
     *
     * @param TransactionRepository $repository
     * @param DefaultTransformer $transformer
     */
    public function __construct( TransactionRepository $repository, DefaultTransformer $transformer )
    {
        $this->repository = $repository;

        $this->transformer = $transformer;
    }

    /**
     * @param CreateTransaction $request
     *
     * @return array|JsonResponse
     */
    public function store( CreateTransaction $request )
    {
        try
        {
            $model = $this->repository->add($request->all());

            return $this->item($model);
        }
        catch ( \Exception $exception )
        {
            return new JsonResponse([ 'message' => $exception->getMessage() ], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @param UpdateTransaction $request
     * @param $model
     *
     * @return array|JsonResponse
     */
    public function update( UpdateTransaction $request, $model )
    {
        try
        {
            $model = $this->repository->update($request->all(), $model);

            return $this->item($model);
        }
        catch ( \Exception $exception )
        {
            return new JsonResponse([ 'message' => $exception->getMessage() ], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

}