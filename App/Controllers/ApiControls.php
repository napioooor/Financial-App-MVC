<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Balance;

class ApiControls extends \Core\Controller {
    public function getExpenseLimitsAction(){
        echo json_encode(User::getExpenseLimits(), JSON_UNESCAPED_UNICODE);
    }

    public function getPaymentLimitsAction(){
        echo json_encode(User::getPaymentLimits(), JSON_UNESCAPED_UNICODE);
    }

    public function getExpenseSumsAction(){
        $date1 = date("Y-m-01", strtotime($this -> route_params['date']));
        $date2 = date('Y-m-t', strtotime($date1));

        echo json_encode(Balance::getExpenseSums($date1, $date2), JSON_UNESCAPED_UNICODE);
    }

    public function getPaymentSumsAction(){
        $date1 = date("Y-m-01", strtotime($this -> route_params['date']));
        $date2 = date('Y-m-t', strtotime($date1));

        echo json_encode(Balance::getPaymentSums($date1, $date2), JSON_UNESCAPED_UNICODE);
    }
}