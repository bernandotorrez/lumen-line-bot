<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use App\Models\CarModel;

use function PHPSTORM_META\map;

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

$router->get('/car-model/array', function() {
    $contents = json_decode(file_get_contents(base_path().'/public/carModelTemplate.json'));

    echo '<pre>';print_r($contents);
});

$router->get('/car-model', function() {
    $carModel = CarModel::all();

    // $contents = (object) array(
    //     'type' => 'carousel',
    //     'contents' => array(
    //         0 => (object) array(
    //             'type' => 'bubble',
    //             'hero' => (object) array(
    //                 'type' => 'image',
    //                 'url' => 'https://scdn.line-apps.com/n/channel_devcenter/img/fx/01_1_cafe.png',
    //                 'size' => 'full',
    //                 'aspectRatio' => '20:13',
    //                 'aspectMode' => 'cover',
    //                 'action' => (object) array(
    //                     'type' => 'uri',
    //                     'label' => 'Line',
    //                     'uri' => 'https://linecorp.com/'
    //                 )
    //             ),
    //             'body' => (object) array(
    //                 'type' => 'box',
    //                 'layout' => 'vertical',
    //                 'contents' => array(
    //                     0 => (object) array(
    //                         'type' => 'text',
    //                         'text' => 'Brown Cafe',
    //                         'weight' => 'bold',
    //                         'size' => 'xl',
    //                         'contents' => array()
    //                     )
    //                 )
    //             ),
    //             'footer' => (object) array(
    //                 'type' => 'box',
    //                 'layout' => 'vertical',
    //                 'flex' => 0,
    //                 'spacing' => 'sm',
    //                 'contents' => array(
    //                     0 => (object) array(
    //                         'type' => 'button',
    //                         'action' => (object) array(
    //                             'type' => 'message',
    //                             'label' => 'see more',
    //                             'text' => 'see more'
    //                         ),
    //                         'height' => 'sm',
    //                         'style' => 'link',
    //                     ),
    //                     1 => (object) array(
    //                         'type' => 'spacer',
    //                         'size' => 'sm'
    //                     )
    //                 )
    //             )
    //         )
    //     )
    // ); 

    $carModel = CarModel::all();
        
        foreach($carModel as $key => $model) {
            $contents[] = 
                (object) array(
                    'type' => 'bubble',
                    'hero' => (object) array(
                        'type' => 'image',
                        'url' => $model->img_url,
                        'size' => 'full',
                        'aspectRatio' => '20:13',
                        'aspectMode' => 'cover',
                        'action' => (object) array(
                            'type' => 'message',
                            'label' => $model->nama_model,
                            'text' => $model->nama_model
                        )
                    ),
                    'body' => (object) array(
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => array(
                            0 => (object) array(
                                'type' => 'text',
                                'text' => $model->nama_model,
                                'weight' => 'bold',
                                'size' => 'xl',
                                'contents' => array()
                            )
                        )
                    ),
                    'footer' => (object) array(
                        'type' => 'box',
                        'layout' => 'vertical',
                        'flex' => 0,
                        'spacing' => 'sm',
                        'contents' => array(
                            0 => (object) array(
                                'type' => 'button',
                                'action' => (object) array(
                                    'type' => 'message',
                                    'label' => 'see more',
                                    'text' => 'see more'
                                ),
                                'height' => 'sm',
                                'style' => 'link',
                            ),
                            1 => (object) array(
                                'type' => 'spacer',
                                'size' => 'sm'
                            )
                        )
                    )
                )
            ;

        }

        $content = (object) array(
            'type' => 'carousel',
            'contents' => $contents
        );

    echo '<pre>';print_r($content);
});

$router->post('/webhook', 'WebhookController');
