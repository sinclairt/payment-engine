<?php

namespace Sinclair\PaymentEngine\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Sinclair\ApiFoundation\Traits\ApiFoundation;
use Sinclair\ApiFoundation\Transformers\DefaultTransformer;
use Sinclair\PaymentEngine\Contracts\ChargeRepository;
use Sinclair\PaymentEngine\FormRequests\CreateCharge;
use Sinclair\PaymentEngine\FormRequests\UpdateCharge;

/**
 * Class ChargeController
 * @package Sinclair\PaymentEngine\Controllers
 */
class ChargeController extends Controller
{
    use ApiFoundation;

    /**
     * ChargeController constructor.
     *
     * @param ChargeRepository $repository
     * @param DefaultTransformer $transformer
     */
    public function __construct( ChargeRepository $repository, DefaultTransformer $transformer )
    {
        $this->repository = $repository;

        $this->transformer = $transformer;
    }

    /**
     * @param CreateCharge $request
     *
     * @return array|JsonResponse
     */
    public function store( CreateCharge $request )
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
     * @param UpdateCharge $request
     * @param $model
     *
     * @return array|JsonResponse
     */
    public function update( UpdateCharge $request, $model )
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