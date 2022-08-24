<?php
namespace App;

class Flash {
    const SUCCESS = 'success';
    const INFO = 'info';
    const WARNING = 'warning';

    public static function addMessage($message, $type = 'success'){
        if(! isset($_SESSION['flash_notification'])){
            $_SESSION['flash_notification'] = [];
        }

        $_SESSION['flash_notification'][] = [
            'body' => $message,
            'type' => $type
        ];
    }

    public static function getMessages(){
        if(isset($_SESSION['flash_notification'])){
            $messages = $_SESSION['flash_notification'];
            unset($_SESSION['flash_notification']);

            return $messages;
        }
    }
}