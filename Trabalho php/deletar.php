<?php
declare(strict_types=1);

require_once __DIR__ . '/classes/Carteira.php';

session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : 0;
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : 0;

if ($id > 0) {
    try {
        $carteira = new Carteira();
        $carteira->excluirTransacao($id);
    } catch (Exception $e) {
        $_SESSION['erro'] = "Erro ao excluir registo: " . $e->getMessage();
    }
}

$redirect = 'index.php';
if ($mes > 0 && $ano > 0) {
    $redirect .= "?mes={$mes}&ano={$ano}";
}

header("Location: {$redirect}");
exit;