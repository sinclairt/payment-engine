<?php

use Symfony\Component\HttpFoundation\Response;

require_once 'DbTestCase.php';

/**
 * Class ApiTest
 */
abstract class ApiTest extends DbTestCase
{
    /**
     * @var
     */
    protected $baseUri;

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * @var
     */
    public $model;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->migrate(__DIR__ . '/migrations');

        $this->migrate(__DIR__ . '/../vendor/laravel/laravel/database/migrations');

        $this->faker = Faker\Factory::create();

        $this->artisan('vendor:publish');

        $this->app[ 'router' ]->group([ 'middleware' => 'api' ], function ()
        {
            require __DIR__ . '/../vendor/laravel/laravel/routes/payment_engine.php';
        });
    }

    /**
     *
     */
    public function test_i_can_get_rows()
    {
        $this->createDummies(20);

        $response = $this->get($this->baseUri)->response;

        $content = json_decode($response->content());

        $this->assertEquals(15, sizeof($content->data));

        $this->assertEquals(200, $response->status());

        $this->checkStructure($content);

        $this->assertObjectHasAttribute('next', $content->links);

        foreach ( $this->model->limit(15)
                              ->get() as $key => $plan )
            $this->checkAttributes($plan, $content, $key);
    }

    /**
     *
     */
    public function test_i_can_still_get_rows_if_i_apply_no_filters()
    {
        $this->createDummies(20);

        $response = $this->json('POST', $this->baseUri . '/filter', [])->response;

        $content = json_decode($response->content());

        $this->checkStructure($content);

        $this->assertEquals(15, sizeof($content->data));
    }

    /**
     *
     */
    public function test_i_can_get_a_single_row()
    {
        $model = collect($this->createDummies(1))->first();

        $response = $this->json('GET', $this->baseUri . '/' . $model->id)->response;

        $this->compareItem($response, $model);
    }

    /**
     *
     */
    public function test_i_can_do_not_get_a_single_change_when_i_supply_a_non_existent_id()
    {
        $response = $this->json('GET', $this->baseUri . '/1')->response;

        $this->assertEquals(\Illuminate\Http\JsonResponse::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    /**
     *
     */
    public function test_i_can_create_a_row()
    {
        $data = $this->createData();

        $response = $this->json('POST', $this->baseUri, $data)->response;

        $model = $this->model->find(1);

        $this->compareItem($response, $model);
    }

    /**
     *
     */
    public function test_i_can_update_a_row()
    {
        $model = collect($this->createDummies(1))->first();

        $data = $this->updateData();

        $response = $this->json('PATCH', $this->baseUri . '/' . $model->id, $data)->response;

        $this->compareItem($response, $model->fresh());
    }

    /**
     *
     */
    public function test_i_can_delete_a_row()
    {
        $model = collect($this->createDummies(1))->first();

        $response = $this->json('DELETE', $this->baseUri . '/' . $model->id)->response;

        $this->compareItem($response, $model->fresh());
    }

    /**
     *
     */
    public function test_i_can_restore_a_row()
    {
        $model = collect($this->createDummies(1))->first();

        $response = $this->json('GET', $this->baseUri . '/' . $model->id . '/restore')->response;

        $this->compareItem($response, $model->fresh());
    }

    /**
     * @param $content
     */
    protected function checkStructure( $content )
    {
        $this->assertObjectHasAttribute('data', $content);
        $this->assertObjectHasAttribute('links', $content);
        $this->assertObjectHasAttribute('meta', $content);
        $this->assertObjectHasAttribute('pagination', $content->meta);
        $this->assertObjectHasAttribute('total', $content->meta->pagination);
        $this->assertObjectHasAttribute('count', $content->meta->pagination);
        $this->assertObjectHasAttribute('per_page', $content->meta->pagination);
        $this->assertObjectHasAttribute('current_page', $content->meta->pagination);
        $this->assertObjectHasAttribute('total_pages', $content->meta->pagination);
        $this->assertObjectHasAttribute('self', $content->links);
        $this->assertObjectHasAttribute('first', $content->links);
        $this->assertObjectHasAttribute('last', $content->links);
    }

    /**
     * @param $plan
     * @param $content
     * @param $key
     */
    protected function checkAttributes( $plan, $content, $key = 0 )
    {
        $db = json_decode($plan->toJson());

        $api = $content->data[ $key ]->attributes;

        foreach ( $this->attributes as $attribute )
            $this->assertEquals($db->$attribute, $api->$attribute);

        $this->assertTrue($content->data[ $key ]->id == $db->id);
    }

    /**
     * @param $count
     *
     * @return mixed
     */
    abstract protected function createDummies( $count = 20);

    /**
     * @param array $attributes
     *
     * @return array
     */
    abstract protected function createData($attributes = []);

    /**
     * @param array $attributes
     *
     * @return mixed
     */
    abstract protected function updateData($attributes = []);

    /**
     * @param Response $response
     * @param $model
     */
    protected function compareItem( $response, $model )
    {
        $content = json_decode($response->getContent());

        $this->assertObjectHasAttribute('data', $content);

        $db = json_decode($model->toJson());

        foreach ( $this->attributes as $attribute )
            $this->assertEquals($db->$attribute, $content->data->$attribute);
    }
}