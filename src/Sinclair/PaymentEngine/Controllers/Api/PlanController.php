<?php

namespace Sinclair\PaymentEngine\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Sinclair\ApiFoundation\Traits\ApiFoundation;
use Sinclair\ApiFoundation\Transformers\DefaultTransformer;
use Sinclair\PaymentEngine\Contracts\PlanRepository;
use Sinclair\PaymentEngine\FormRequests\CreatePlan;
use Sinclair\PaymentEngine\FormRequests\UpdatePlan;

/**
 * Class PlanController
 * @package Sinclair\PaymentEngine\Controllers
 */
class PlanController extends Controller
{
    use ApiFoundation;

    /**
     * PlanController constructor.
     *
     * @param PlanRepository $repository
     * @param DefaultTransformer $transformer
     */
    public function __construct( PlanRepository $repository, DefaultTransformer $transformer )
    {
        $this->repository = $repository;

        $this->transformer = $transformer;
    }

    /**
     * @param CreatePlan $request
     *
     * @return array|JsonResponse
     */
    public function store( CreatePlan $request )
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
     * @param UpdatePlan $request
     * @param $model
     *
     * @return array|JsonResponse
     */
    public function update( UpdatePlan $request, $model )
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