<?php

namespace Sinclair\PaymentEngine\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Sinclair\ApiFoundation\Traits\ApiFoundation;
use Sinclair\ApiFoundation\Transformers\DefaultTransformer;
use Sinclair\PaymentEngine\Contracts\ItemRepository;
use Sinclair\PaymentEngine\FormRequests\CreateItem;
use Sinclair\PaymentEngine\FormRequests\UpdateItem;

/**
 * Class ItemController
 * @package Sinclair\PaymentEngine\Controllers
 */
class ItemController extends Controller
{
    use ApiFoundation;

    /**
     * ItemController constructor.
     *
     * @param ItemRepository $repository
     * @param DefaultTransformer $transformer
     */
    public function __construct( ItemRepository $repository, DefaultTransformer $transformer )
    {
        $this->repository = $repository;

        $this->transformer = $transformer;
    }

    /**
     * @param CreateItem $request
     *
     * @return array|JsonResponse
     */
    public function store( CreateItem $request )
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
     * @param UpdateItem $request
     * @param $model
     *
     * @return array|JsonResponse
     */
    public function update( UpdateItem $request, $model )
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