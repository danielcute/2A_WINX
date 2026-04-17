<?php
class ProfileController {
    public function index() {
        $page = 'profile';
        
        $user = [
            'id' => 1,
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'email' => 'maria@email.com',
            'phone' => '+63 917 123 4567',
            'city' => 'Bacolod City',
            'birthday' => '1992-03-18',
            'avatar' => 'assets/img/elarie.jpg',
            'member_since' => '2022-06-15',
            'is_premium' => true,
            'events_count' => 7,
            'rating' => 4.9,
            'total_spent' => '₱750k'
        ];
        
        include VIEW_PATH . '/user/profile.php';
    }
}