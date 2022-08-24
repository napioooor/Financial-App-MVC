<?php
namespace App\Controllers;

use \Core\View;
use \App\Auth;

class Home extends \Core\Controller {
    protected function before(){
        //echo "(before) ";
    }

    protected function after(){
        //echo " (after)";
    }

    public function indexAction(){
        View::renderTemplate('Home/index.html');
    }
}