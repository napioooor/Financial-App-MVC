<?php
namespace App\Controllers;

use \Core\View;
use App\Models\Post;

class Posts  extends \Core\Controller {
    public function indexAction(){
        $posts = Post::getAll();
        
        View::renderTemplate('Posts/index.html', [
            'posts' => $posts
        ]);
    }

    public function addNewAction(){
        echo 'Hello from the addNew action in the Posts Controller!';
    }

    public function editAction(){
        echo 'Hello from the index action in the Posts Controller!';
        echo '<p>Query string parameters: <pre>'.
        htmlspecialchars(print_r($this -> route_params, true)).'</pre></p>';
    }
}