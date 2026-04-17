<?php
class EventController {
    public function detail() {
        $page = 'plans';
        $id = $_GET['id'] ?? 1;
        include VIEW_PATH . '/user/event-detail.php';
    }
}