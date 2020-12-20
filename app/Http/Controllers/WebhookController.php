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
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;

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
            $message .= "Perkenalkan namaku Poru!, disini aku akan membantu kamu seputar : Dealer, Model Mobil, dan Type Model Mobil Porsche!";
            $textMessageBuilder = new TextMessageBuilder($message);
     
            // merge all message
            $multiMessageBuilder = new MultiMessageBuilder();
            $multiMessageBuilder->add($textMessageBuilder);
     
            // send reply message
            $this->bot->replyMessage($event['replyToken'], $multiMessageBuilder);
            $this->mainMenuTemplate($event['replyToken']);
            $this->dealerTemplate($event['replyToken']);
     
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
            $this->mainMenuTemplate($event['replyToken']);
        } else if($userMessage == 'dealer') {
            $this->dealerTemplate($event['replyToken']);
        } else if($userMessage == 'car-model') {
            $this->carModelTemplate($event['replyToken']);
        } else {
            $message = 'no keyword found';
            $textMessageBuilder = new TextMessageBuilder($message);
            $this->bot->replyMessage($event['replyToken'], $textMessageBuilder);
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

    private function mainMenuTemplate($replyToken)
    {
        $template = file_get_contents(base_path().'/public/mainMenuTemplate.json');
        $result = $this->httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
            'replyToken' => $replyToken,
            'messages'   => [
                [
                    'type'     => 'flex',
                    'altText'  => 'Main Menu',
                    'contents' => json_decode($template)
                ]
            ],
        ]);
        return $result;
    }

    public function dealerTemplate($replyToken)
    {
        $template = file_get_contents(base_path().'/public/dealerTemplate.json');
        $result = $this->httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
            'replyToken' => $replyToken,
            'messages'   => [
                [
                    'type'     => 'flex',
                    'altText'  => 'Porsche Dealer',
                    'contents' => json_decode($template)
                ]
            ],
        ]);
        return $result;
    }

    public function carModelTemplate($replyToken)
    {

        $carModel = CarModel::all();

        $string = '';

        foreach($carModel as $model) {
            $arrayModel .= new CarouselColumnTemplateBuilder($model->nama_model, '', $model->img_url,[
                new UriTemplateActionBuilder('See More', $model->img_url),
            ]);
        }

        $arrayModel = [$string];

        $carouselTemplateBuilder = new CarouselTemplateBuilder($arrayModel);

        $textMessageBuilder = new TemplateMessageBuilder('Car-Model', $carouselTemplateBuilder);

        return $this->bot->replyMessage($replyToken, $textMessageBuilder);
    }
}