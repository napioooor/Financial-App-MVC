<?php
namespace App\Controllers;

use \Core\View;

class AddItem extends Authenticated {
    public function incomeAction(){
        View::renderTemplate('Items/income.html');
    }
}