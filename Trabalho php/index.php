<?php
declare(strict_types=1);

require_once __DIR__ . '/classes/Carteira.php';
require_once __DIR__ . '/classes/receita.php';
require_once __DIR__ . '/classes/despesa.php';

session_start();



if (!isset($_SESSION['carteira'])) {
    $_SESSION['carteira'] = new Carteira();
}

$carteira = $_SESSION['carteira'];
$saldo = $carteira->getSaldo();
$historico = $carteira->getHistorico();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyPocket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
<div class="container mt-4">

    <h1>MyPocket 👝​</h1>

    <!-- Saldo -->
<div class="card bg-dark text-light mb-4">
        <div class="card-body">
            <h5>Saldo atual</h5>
            <h2>R$ <?= number_format($saldo, 2, ',', '.') ?></h2>
        </div>
    </div>
    <!-- Histórico -->
     <div class="card bg-dark text-light mb-4">
        <div class="card bg-dark text-light mb-4">
        <h5>Nova Transação</h5>
        <form action="processar.php" method="POST">
            <div class="mb-3">
                <label>Tipo</label>
                <select class="form-select bg-dark text-light border-secondary">
                    <option value="receita">Receita</option>
                    <option value="despesa">Despesa</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Valor</label>
                <input class="form-control bg-dark text-light border-secondary" name="valor" type="number" step="0.01">
            </div>
            <div class="mb-3">
                <label>Descrição</label>
                <input class="form-control bg-dark text-light border-secondary" name="descricao" type="text">
            </div>
            <div class="mb-3">
                <label>Data</label>
                <input class="form-control bg-dark text-light border-secondary" name="data" type="date">
            </div>
            <button type="submit" style="background-color: #9D00FF; border-color: #9D00FF;" class="btn text-light">Lançar</button>
        </form>
    </div>
</div>
<div class="card bg-dark text-light mb-4">
    <div class="card bg-dark text-light mb-4">
        <h5>Extrato</h5>
        <table class="table table-dark">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Descrição</th>
                    <th>Data</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historico as $transacao): ?>
                <tr>
                <td>
    <?php if ($transacao->getTipo() === 'Entrada'): ?>
        <span class="badge bg-success">Entrada</span>
    <?php else: ?>
        <span class="badge bg-danger">Saída</span>
    <?php endif; ?>
                </td>
                    <td><?= $transacao->getDescricao() ?></td>
                    <td><?= $transacao->getData() ?></td>
                    <td>R$ <?= number_format($transacao->getValor(), 2, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (isset($_SESSION['erro'])): ?>
    <div class="alert alert-danger bg-danger text-light">
        <?= $_SESSION['erro'] ?>
    </div>
    <?php unset($_SESSION['erro']); ?>
<?php endif; ?>

</div>
</body>
</html>