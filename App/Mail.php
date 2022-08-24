<?php
namespace App;

use Mailgun\Mailgun;
use App\Config;

class Mail {
    public static function send($to, $subject, $text, $html){
        $mg = Mailgun::create(Config::MAILGUN_API_KEY);

        $mg -> messages() -> send(Config::MAILGUN_DOMAIN, [
        'from'    => 'jan.napiorkowski@o2.pl',
        'to'      => $to,
        'subject' => $subject,
        'text'    => $text,
        'html'    => $html
        ]);
    }
}