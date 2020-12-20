<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use App\Models\CarModel;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/key', function() {
    return \Illuminate\Support\Str::random(32);
});

$router->get('/car-model', function() {
    $carModel = CarModel::all();

    $contents = array();

    foreach($carModel as $model) {
        $content = array(
            'type' => 'bubble',
            'hero' => array(
                'type' => 'image',
                'url' => $model->img_url,
                'size' => 'full',
                'aspectRatio' => '20:13',
                'aspectMode' => 'cover',
                'action' => array(
                    'type' => 'uri',
                    'label' => $model->nama_model,
                    'uri' => $model->nama_model
                )
            ),
            'body' => array(
                'type' => 'box',
                'layout' => 'vertical',
                'content' => array(
                    0 => array(
                        'type' => 'text',
                        'text' => $model->nama_model,
                        'weight' => 'bold',
                        'size' => 'xl',
                        'content' => [],
                    )
                )
            ),
            'footer' => array(
                'type' => 'box',
                'layout' => 'vertical',
                'flex' => 0,
                'spacing' => 'sm',
                'content' => array(
                    0 => array(
                        'type' => 'button',
                        'action' => array(
                            'type' => 'message',
                            'label' => 'See Type Model',
                            'text' => $model->nama_model
                        ),
                        'height' => 'sm',
                        'style' => 'link'
                    ),
                    'type' => 'spacer',
                    'size' => 'sm'
                )
            )
        );

        array_push($contents, $content);
    }

    echo json_encode($contents);
});

$router->post('/webhook', 'WebhookController');
