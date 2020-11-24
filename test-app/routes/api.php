<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use Dingo\Api\Routing\Router;
use Illuminate\Http\Request;
use TestApp\Http\Controllers\DemoController;
use TestApp\Services\MyService;

/**
 * Api router instance.
 *
 * @var Router $api
 */
$api = app(Router::class);
$api->version(config('api.version'), function (Router $api) {
    $api->get('test', function () {
        return "ok";
    });

//    $api->get('test-order-by', DemoController::class.'@demo');
    $api->get('test-order-by', function(Request $request, MyService $service) {
        return ['results' => $service->getData($request->order_by)];
    });
});
