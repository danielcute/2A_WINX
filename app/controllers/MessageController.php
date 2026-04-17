<?php
class MessageController {
    public function index() {
        $page = 'messages';
        include VIEW_PATH . '/user/messages.php';
    }
}