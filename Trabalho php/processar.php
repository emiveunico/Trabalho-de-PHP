<?php
declare(strict_types=1);

require_once __DIR__ . '/classes/Carteira.php';
require_once __DIR__ . '/classes/receita.php';
require_once __DIR__ . '/classes/despesa.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
// Se ainda não existe uma carteira na sessão, cria uma nova
if (!isset($_SESSION['carteira'])) {
    $_SESSION['carteira'] = new Carteira();


}

$carteira = $_SESSION['carteira'];

$tipo      = $_POST['tipo'];
$valor     = (float) $_POST['valor'];
$descricao = $_POST['descricao'];
$data      = $_POST['data'];

try {
    if ($tipo === 'receita') {
        $receita = new Receita($valor, $descricao, $data);
        $carteira->adicionarReceita($receita);
    } else {
        $despesa = new Despesa($valor, $descricao, $data);
        $carteira->adicionarDespesa($despesa);
    }
} catch (Exception $e) {
    $_SESSION['erro'] = $e->getMessage();
}
$_SESSION['carteira'] = $carteira;

header('Location: index.php');
exit;