<?php
class PageController {
    public function about() {
        $page = 'about';
        include VIEW_PATH . '/user/about.php';
    }
    
    public function contact() {
        $page = 'contact';
        include VIEW_PATH . '/user/contact.php';
    }
}