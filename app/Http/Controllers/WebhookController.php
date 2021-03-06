<?php

namespace App\Http\Controllers;

use App\Models\CarModel;
use Illuminate\Http\Request;

use Illuminate\Http\Response;
use Illuminate\Log\Logger;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use App\Repository\Eloquent\EventLogRepository;
use App\Repository\Eloquent\LineUserRepository;
use LINE\LINEBot\MessageBuilder\RawMessageBuilder;

class WebhookController extends Controller
{
    private $bot;
    private $request;
    private $response;
    private $logger;
    private $user;
    private $eventLogRepository;
    private $lineUserRepository;
    private $httpClient;

    public function __construct(
        Request $request,
        Response $response,
        Logger $logger,
        EventLogRepository $eventLogRepository,
        LineUserRepository $lineUserRepository
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->logger = $logger;
        $this->eventLogRepository = $eventLogRepository;
        $this->lineUserRepository = $lineUserRepository;
        $this->httpClient = new CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
        $this->bot  = new LINEBot($this->httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);
    }

    public function __invoke()
    {
        // get request
        $body = $this->request->all();
     
        // debuging data
        $this->logger->debug('Body', $body);
     
        // save log
        $signature = $this->request->server('HTTP_X_LINE_SIGNATURE') ?: '-';
        $this->eventLogRepository->saveLog($signature, json_encode($body, true));

        return $this->handleEvents();
    }

    private function handleEvents()
    {
        $data = $this->request->all();
     
        if(is_array($data['events'])){
            foreach ($data['events'] as $event)
            {
                // skip group and room event
                if(! isset($event['source']['userId'])) continue;
     
                // get user data from database
                $this->user = $this->lineUserRepository->getUser($event['source']['userId']);
     
                // if user not registered
                // if(!this->user)
                if(!$this->user) $this->followCallback($event);
                else {
                    // respond event
                    if($event['type'] == 'message'){
                        if(method_exists($this, $event['message']['type'].'Message')){
                            $this->{$event['message']['type'].'Message'}($event);
                        }
                    } else {
                        if(method_exists($this, $event['message']['type'].'Callback')){
                            $this->{$event['message']['type'].'Callback'}($event);
                        }
                    }
                }
            }
        }
     
     
        $this->response->setContent("No events found!");
        $this->response->setStatusCode(200);
        return $this->response;
    }

    private function followCallback($event)
    {
        $res = $this->bot->getProfile($event['source']['userId']);
        if ($res->isSucceeded())
        {
            $profile = $res->getJSONDecodedBody();
     
            // create welcome message
            $message  = "Hi, " . $profile['displayName'] . "!\n";
            $message .= "Perkenalkan namaku Poru! \n";
            $message .= 'Silahkan klik Menu yang Poru kasih dibawah ya biar Poru bisa bantu kamu!';
            $textMessageBuilder = new TextMessageBuilder($message);
            $menuMessageBuilder = $this->mainMenuTemplate();
     
            // merge all message
            $multiMessageBuilder = new MultiMessageBuilder();
            $multiMessageBuilder->add($textMessageBuilder);
            $multiMessageBuilder->add($menuMessageBuilder);
     
            // send reply message
            $this->bot->replyMessage($event['replyToken'], $multiMessageBuilder);
     
            // save user data
            $this->lineUserRepository->saveUser(
                $profile['userId'],
                $profile['displayName']
            );
     
        }
    }

    private function textMessage($event)
    {
        $userMessage = strtolower($event['message']['text']);

        if($userMessage == 'menu') {
            $this->bot->replyMessage($event['replyToken'], $this->mainMenuTemplate());
        } else if($userMessage == 'dealer') {
            $this->bot->replyMessage($event['replyToken'], $this->dealerTemplate());
        } else if($userMessage == 'car-model') {
            $this->bot->replyMessage($event['replyToken'], $this->carModelTemplate());
        } else {
            $message = "Duh, maaf banget nih, Poru ga bisa ngenalin Keyword nya \n";
            $message .= "Mungkin kamu bisa coba pilih Keyword nya di bawah ini : ";
            $textMessageBuilder = new TextMessageBuilder($message);
            $menuMessageBuilder = $this->mainMenuTemplate();
     
            $multiMessageBuilder = new MultiMessageBuilder();
            $multiMessageBuilder->add($textMessageBuilder);
            $multiMessageBuilder->add($menuMessageBuilder);
            $this->bot->replyMessage($event['replyToken'], $multiMessageBuilder);
        }
    }

    private function stickerMessage($event)
    {
        // create sticker message
        $stickerMessageBuilder = new StickerMessageBuilder(1, 106);
     
        // create text message
        $message = 'sticker';
        $textMessageBuilder = new TextMessageBuilder($message);
     
        // merge all message
        $multiMessageBuilder = new MultiMessageBuilder();
        $multiMessageBuilder->add($stickerMessageBuilder);
        $multiMessageBuilder->add($textMessageBuilder);
     
        // send message
        $this->bot->replyMessage($event['replyToken'], $multiMessageBuilder);
    }

    private function mainMenuTemplate()
    {
        $template = new RawMessageBuilder([
            'type'     => 'flex',
            'altText'  => 'Main Menu',
            'contents' => json_decode(file_get_contents(base_path().'/public/mainMenuTemplate.json'))
        ]);

        return $template;
    }

    public function dealerTemplate()
    {
        $template = new RawMessageBuilder([
            'type'     => 'flex',
            'altText'  => 'Main Menu',
            'contents' => json_decode(file_get_contents(base_path().'/public/dealerTemplate.json'))
        ]);

        return $template;
    }

    public function carModelTemplate()
    {

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
                                    'label' => 'See '.$model->nama_model.' Types',
                                    'text' => 'car-type-'.$model->nama_model
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

        $template = new RawMessageBuilder([
            'type'     => 'flex',
            'altText'  => 'Main Menu',
            'contents' => $content
        ]);

        return $template;
    }
}