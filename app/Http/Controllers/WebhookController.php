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

class WebhookController extends Controller
{
    private $bot;
    private $request;
    private $response;
    private $logger;
    private $user;
    private $eventLogRepository;
    private $lineUserRepository;

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
 
        // create bot object
        $httpClient = new CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
        $this->bot  = new LINEBot($httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);
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
                        if(method_exists($this, $event['type'].'Callback')){
                            $this->{$event['type'].'Callback'}($event);
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
            $message  = "Salam kenal, " . $profile['displayName'] . "!\n";
            $message .= "Silakan kirim pesan \"MULAI\" untuk memulai kuis Tebak Kode.";
            $textMessageBuilder = new TextMessageBuilder($message);
     
            // create sticker message
            $stickerMessageBuilder = new StickerMessageBuilder(1, 3);
     
            // merge all message
            $multiMessageBuilder = new MultiMessageBuilder();
            $multiMessageBuilder->add($textMessageBuilder);
            $multiMessageBuilder->add($stickerMessageBuilder);
     
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
        $userMessage = $event['message']['text'];
        if(true)
        {
            if(strtolower($userMessage) == 'mulai')
            {
                $message = 'A';
                $textMessageBuilder = new TextMessageBuilder($message);
                $this->bot->replyMessage($event['replyToken'], $textMessageBuilder);
            } else {
                $message = 'no keyword found';
                $textMessageBuilder = new TextMessageBuilder($message);
                $this->bot->replyMessage($event['replyToken'], $textMessageBuilder);
            }
     
            // if user already begin test
        } else {
            $message = 'user kosong';
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
}