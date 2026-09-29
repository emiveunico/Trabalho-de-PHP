<?php
declare(strict_types=1);

require_once __DIR__ . '/classes/Carteira.php';

session_start();

$carteira = new Carteira();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $tipo = $_POST['tipo'] ?? '';
    $valor = (float)($_POST['valor'] ?? 0);
    $descricao = $_POST['descricao'] ?? '';
    $data = $_POST['data'] ?? '';
    $mes = (int)($_POST['mes'] ?? date('m'));
    $ano = (int)($_POST['ano'] ?? date('Y'));

    try {
        if ($tipo === 'receita') {
            $transacao = new Receita($valor, $descricao, $data, $id);
        } elseif ($tipo === 'despesa') {
            $transacao = new Despesa($valor, $descricao, $data, $id);
        } elseif ($tipo === 'diario') {
            $transacao = new Diario($valor, $descricao, $data, $id);
        } else {
            throw new Exception('Tipo de transação inválido.');
        }

        $carteira->atualizarTransacao($id, $transacao);
        header("Location: index.php?mes={$mes}&ano={$ano}");
        exit;
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// Carrega a transação para preencher o formulário
$transacao = $carteira->getTransacaoPorId($id);

if (!$transacao) {
    header("Location: index.php?mes={$mes}&ano={$ano}");
    exit;
}

// Determinar valor do select de tipo
$tipoAtual = 'receita';
if ($transacao instanceof Despesa) {
    $tipoAtual = 'despesa';
} elseif ($transacao instanceof Diario) {
    $tipoAtual = 'diario';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Lançamento - MyPocket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #121214; color: #e1e1e6; font-family: sans-serif; }
        .card-custom { background-color: #202024; border: 1px solid #323238; border-radius: 8px; }
        .btn-blue { background-color: #0052cc; border-color: #0052cc; color: white; }
        .btn-blue:hover { background-color: #003d99; color: white; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">

<div class="container" style="max-width: 500px;">
    <div class="card card-custom p-4">
        <h4 class="fw-bold mb-3 text-light">Editar Lançamento</h4>

        <?php if (isset($erro)): ?>
            <div class="alert alert-danger bg-danger text-light mb-3"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form action="editar.php" method="POST">
            <input type="hidden" name="id" value="<?= $transacao->getId() ?>">
            <input type="hidden" name="mes" value="<?= $mes ?>">
            <input type="hidden" name="ano" value="<?= $ano ?>">

            <div class="mb-3">
                <label class="form-label small text-secondary">Tipo</label>
                <select name="tipo" class="form-select bg-dark text-light border-secondary" required>
                    <option value="receita" <?= $tipoAtual === 'receita' ? 'selected' : '' ?>>Entrada (+)</option>
                    <option value="despesa" <?= $tipoAtual === 'despesa' ? 'selected' : '' ?>>Saída (-)</option>
                    <option value="diario" <?= $tipoAtual === 'diario' ? 'selected' : '' ?>>Diário (-)</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label small text-secondary">Descrição</label>
                <input name="descricao" type="text" value="<?= htmlspecialchars($transacao->getDescricao()) ?>" class="form-control bg-dark text-light border-secondary" required>
            </div>

            <div class="mb-3">
                <label class="form-label small text-secondary">Valor (R$)</label>
                <input name="valor" type="number" step="0.01" value="<?= $transacao->getValor() ?>" class="form-control bg-dark text-light border-secondary" required>
            </div>

            <div class="mb-3">
                <label class="form-label small text-secondary">Data</label>
                <input name="data" type="date" value="<?= $transacao->getData() ?>" class="form-control bg-dark text-light border-secondary" required>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-blue w-100 fw-bold">Salvar Alterações</button>
                <a href="index.php?mes=<?= $mes ?>&ano=<?= $ano ?>" class="btn btn-outline-secondary w-100">Cancelar</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>