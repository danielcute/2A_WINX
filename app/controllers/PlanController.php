<?php
class PlanController {
    public function index() {
        $page = 'plans';
        include VIEW_PATH . '/user/plans.php';
    }
}