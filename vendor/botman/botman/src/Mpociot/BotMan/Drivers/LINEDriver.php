<?php

namespace Mpociot\BotMan\Drivers;

use Mpociot\BotMan\User;
use Mpociot\BotMan\Answer;
use Mpociot\BotMan\Message;
use Mpociot\BotMan\Question;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use Mpociot\BotMan\Messages\Message as IncomingMessage;

if (!function_exists('hash_equals')) {
    defined('USE_MB_STRING') or define('USE_MB_STRING', function_exists('mb_strlen'));
    function hash_equals($knownString, $userString)
    {
        $strlen = function ($string) {
            if (USE_MB_STRING) {
                return mb_strlen($string, '8bit');
            }
            return strlen($string);
        };
        // Compare string lengths
        if (($length = $strlen($knownString)) !== $strlen($userString)) {
            return false;
        }
        $diff = 0;
        // Calculate differences
        for ($i = 0; $i < $length; $i++) {
            $diff |= ord($knownString[$i]) ^ ord($userString[$i]);
        }
        return $diff === 0;
    }
}

class LINEDriver extends Driver
{
    protected $source;
    protected $userId;
    protected $replyToken;
    protected $timestamp;
    protected $message;
    protected $messageid;

    /**
     * @param Request $request
     * @return void
     */
    public function buildPayload(Request $request)
    {
        // This method receives the incoming HTTP Request and allows you
        // to read the driver relevant information from it.
       
        if ($request->server->get('REQUEST_METHOD') !== 'POST') {
            http_response_code(405);
            error_log("Method not allowed");
            exit();
        }
        $entityBody = $request->getContent();

        if (strlen($entityBody) === 0) {
            http_response_code(400);
            error_log("Missing request body");
            exit();
        }
        if (!hash_equals($this->sign($entityBody), $request->server->get('HTTP_X_LINE_SIGNATURE'))) {
            http_response_code(400);
            error_log("Invalid signature value");
            exit();
        }
        $data = json_decode($entityBody, true);
        if (!isset($data['events'])) {
            http_response_code(400);
            error_log("Invalid request body: missing events property");
            exit();
        }
        
        if(isset($data['events'][0]) && !empty($data['events'][0])) {
            $this->payload        = Collection::make((array) $data['events'][0]);
            error_log($this->payload);
            $this->event          = $this->payload->get('type'); 
            $this->source         = $this->payload->get('source');
            $this->userId         = $this->source['userId'];
            $this->replyToken     = $this->payload->get('replyToken');
            $this->timestamp      = $this->payload->get('timestamp');
            $this->message        = $this->payload->get('message');
            $this->messageid      = $this->message['id'];
        }
    }

    private function sign($body)
    {
        $hash = hash_hmac('sha256', $body, $this->config->get('channelSecret'), true);
        $signature = base64_encode($hash);
        return $signature;
    }

    /**
     * Determine if the request is for this driver.
     *
     * @return bool
     */
    public function matchesRequest()
    {
        // This method detects if the incoming HTTP request should be handled with this driver class.
        if($this->event == "join" || $this->message['type'] == 'text' 
            || $this->message['type'] == 'location') {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the chat message(s).
     *
     * @return array
     */
    public function getMessages()
    {
        error_log("getMessages");
        // Return the message(s) that are inside the incoming request.
        if($this->source['type'] == 'group') {
            $recipientId = $this->source['groupId'];
        }
        else if($this->source['type'] == 'room') {
            $recipientId = $this->source['roomId'];
        }
        else {
            $recipientId = $this->userId;
        }
        $messageString = strtolower($this->message['text']);
        error_log("message : ".$messageString);
        $message = new Message($messageString, $this->userId, $recipientId);
        $message->addExtras("source", $this->source['type']);
        return [$message];
    }

    /**
     * @return bool
     */
    public function isBot()
    {
        // If the custom messaging service also sends HTTP requests for the bot 
        // replies, you need to handle this here.
        return false;
    }

    /**
     * @return bool
     */
    public function isConfigured()
    {
        ! is_null($this->config->get('channelAccessToken'));
    }


    private function generateHeader() {
        $header = array(
                "Content-Type: application/json",
                'Authorization: Bearer '.$this->config->get('channelAccessToken'),
            );

        return $header;
    }
    /**
     * Retrieve User information.
     * @param Message $matchingMessage
     * @return UserInterface
     */
    public function getUser(Message $matchingMessage)
    {
        // Return a user object with as many information as you want.
        $header = $this->generateHeader();
        $source = $matchingMessage->getExtras("source");
        if($source == 'group') {
            $url = 'https://api.line.me/v2/bot/group/'.$matchingMessage->getChannel().'/'.'member/'.$matchingMessage->getUser();
        }
        else if($source == 'room') {
            $url = 'https://api.line.me/v2/bot/room/'.$matchingMessage->getChannel().'/'.'member/'.$matchingMessage->getUser();
        }
        else {
            $url = 'https://api.line.me/v2/bot/profile/'.$matchingMessage->getChannel();  
        }
        
        $userData = $this->http->get($url, [], $header, true);

        return new User($matchingMessage->getUser(), $userData['displayName'], "");
    }

    /**
     * @param Message $matchingMessage
     *
     * @return Answer
     */
    public function getConversationAnswer(Message $message)
    {
        // Return the given answer, when inside a conversation.
        return Answer::create($message->getMessage());
    }

    /**
     * @param string|Question $message
     * @param Message $matchingMessage
     * @param array $additionalParameters
     * @return $this
     */
    public function reply($message, $matchingMessage, $additionalParameters = [])
    {
    	// Send a reply to the messaging service.
    	// Replies can either be strings, Question objects or IncomingMessage objects.
        
        $header = $this->generateHeader();
        
        $additionalParameters = array(
                                'replyToken' => $this->replyToken,
                                'messages' => $message
                                );
        $this->http->post('https://api.line.me/v2/bot/message/reply', [], $additionalParameters, $header);
    }

    /**
     * Return the driver name.
     *
     * @return string
     */
    public function getName()
    {
        return 'LINEDriver';
    }

    public function leaveChat()
    {
        $url = '';
        if($this->source['type'] == "group") {
             $groupId = $this->source['groupId'];
             $url = 'https://api.line.me/v2/bot/group/'.$groupId.'/leave';
        }
        else if($this->source['type'] == "room") {
            $roomId = $this->source['roomId'];
             $url = 'https://api.line.me/v2/bot/group/'.$roomId.'/leave';
        }
       
        if($url != '')
            $this->http->get($url, [], $this->generateHeader());
    }

     /**
     * Low-level method to perform driver specific API requests.
     *
     * @param string $endpoint
     * @param array $parameters
     * @param Message $matchingMessage
     * @return Response
     */
    public function sendRequest($endpoint, array $parameters, Message $matchingMessage)
    {
        $header = $this->generateHeader();
        
        $parameters = array_replace_recursive([
            'replyToken' => $this->replyToken,
        ], $parameters);

        return $this->http->post($endpoint, [], $parameters, $header);
    }

}

?>