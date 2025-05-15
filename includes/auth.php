<?php
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        flash('Precisas de iniciar sessão.', 'erro');
        header('Location: ../pages/login.php');
        exit();
    }
}
