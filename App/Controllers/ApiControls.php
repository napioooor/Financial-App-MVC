<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Balance;

class ApiControls extends \Core\Controller {
    public function getExpenseLimitsAction(){
        return json_encode(User::getExpenseLimits(), JSON_UNSCAPED_UNICODE);
    }

    public function getPaymentLimitsAction(){
        return json_encode(User::getPaymentLimits(), JSON_UNSCAPED_UNICODE);
    }

    public function getCurrentMonthExpensesAction(){
        $date1 = date("Y-m-01");
        $date2 = date('Y-m-t', strtotime($date1));

        return json_encode(Balance::getUserExpenses($date1, $date2), JSON_UNSCAPED_UNICODE);
    }
}