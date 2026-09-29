<?php
declare(strict_types=1);

require_once __DIR__ . '/classes/Carteira.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$tipo            = $_POST['tipo'] ?? '';
$valor           = (float) ($_POST['valor'] ?? 0);
$descricao       = $_POST['descricao'] ?? '';
$categoria       = $_POST['categoria'] ?? 'Geral';
$metodoPagamento = $_POST['metodo_pagamento'] ?? 'Dinheiro';
$data            = $_POST['data'] ?? '';

try {
    $carteira = new Carteira();

    if ($tipo === 'receita') {
        $transacao = new Receita($valor, $descricao, $categoria, $metodoPagamento, $data);
    } elseif ($tipo === 'despesa') {
        $transacao = new Despesa($valor, $descricao, $categoria, $metodoPagamento, $data);
    } elseif ($tipo === 'diario') {
        $transacao = new Diario($valor, $descricao, $categoria, $metodoPagamento, $data);
    } else {
        throw new Exception('Tipo de transação inválido.');
    }

    $carteira->salvarTransacao($transacao);

} catch (Exception $e) {
    $_SESSION['erro'] = $e->getMessage();
}

header('Location: index.php');
exit;