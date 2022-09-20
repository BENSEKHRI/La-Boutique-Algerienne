<?php

namespace App\Classes;

use Mailjet\Client;
use Mailjet\Resources;


class MailClasses {
    private $api_key = '880402397eb447523fe82ee8d7e3c069';
    private $api_key_secret = '148544235a2e5a850d5609d143cb6194';

    public function send ($to_email, $to_name, $subject, $content) {
        $mj = new Client($this->api_key, $this->api_key_secret, true,['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "lounisbensekhri.dev@gmail.com",
                        'Name' => "La Boutique Algérienne"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => $to_name
                        ]
                    ],
                    'TemplateID' => 4049654,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content,
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();
    }
}