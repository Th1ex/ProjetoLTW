<?php
// includes/flash.php
// Gerencia mensagens flash usando sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Adiciona uma mensagem flash para ser exibida.
 *
 * @param string $message Texto da mensagem
 * @param string $type Tipo da mensagem (e.g., 'success', 'error', 'info')
 */
function flash(string $message, string $type = 'info'): void {
    $_SESSION['flashes'][] = ['type' => $type, 'message' => $message];
}

/**
 * Retorna todas as mensagens flash pendentes e limpa a sessão.
 *
 * @return array Lista de mensagens ['type' => ..., 'message' => ...]
 */
function get_flashes(): array {
    if (empty($_SESSION['flashes'])) {
        return [];
    }
    $flashes = $_SESSION['flashes'];
    unset($_SESSION['flashes']);
    return $flashes;
}
