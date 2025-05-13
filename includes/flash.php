<?php
/*  Uso:
    set_flash('error', 'Texto da mensagem');
    set_flash('success', 'Feito com sucesso');
    // depois faz header('Location: …');
*/
function set_flash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type'=>$type, 'msg'=>$msg];
}
/* imprime e apaga a mensagem, se existir */
function flash(): void {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo "<div class='flash {$f['type']}'>{$f['msg']}</div>";
    }
}
