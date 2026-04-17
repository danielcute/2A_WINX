<?php
class AuthController {
    public function signin() {
        echo "<h1>Sign In Page</h1>";
        echo "<form method='POST'>";
        echo "<input type='email' name='email' placeholder='Email'><br>";
        echo "<input type='password' name='password' placeholder='Password'><br>";
        echo "<button type='submit'>Sign In</button>";
        echo "</form>";
        echo "<p><a href='/sinta/public/landing'>Back to Landing</a></p>";
    }
    
    public function signup() {
        echo "<h1>Sign Up Page</h1>";
        echo "<p><a href='/sinta/public/landing'>Back to Landing</a></p>";
    }
    
    public function logout() {
        session_destroy();
        header('Location: /sinta/public/landing');
        exit;
    }
}
?>