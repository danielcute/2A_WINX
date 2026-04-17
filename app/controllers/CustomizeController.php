<?php
class CustomizeController {
    public function index() {
        $page = 'customize';
        $occasion = $_GET['occasion'] ?? 'wedding';
        include VIEW_PATH . '/user/customize.php';
    }
}