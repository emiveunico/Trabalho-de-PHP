<?php
declare(strict_types=1);

require_once __DIR__ . '/classes/Carteira.php';

session_start();

try {
    $carteira = new Carteira();
    $saldo = $carteira->getSaldo();
    $historico = $carteira->getHistorico();
} catch (Exception $e) {
    $erroConexao = $e->getMessage();
    $saldo = 0.0;
    $historico = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyPocket 👝</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
<div class="container mt-4">

    <h1>MyPocket 👝</h1>

    <?php if (isset($erroConexao)): ?>
        <div class="alert alert-danger">Erro de Conexão com o Banco: <?= htmlspecialchars($erroConexao) ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['erro'])): ?>
        <div class="alert alert-danger bg-danger text-light mb-4">
            <?= htmlspecialchars($_SESSION['erro']) ?>
        </div>
        <?php unset($_SESSION['erro']); ?>
    <?php endif; ?>

    <!-- Saldo -->
    <div class="card bg-dark text-light border-secondary mb-4">
        <div class="card-body">
            <h5>Saldo atual</h5>
            <h2>R$ <?= number_format($saldo, 2, ',', '.') ?></h2>
        </div>
    </div>

    <!-- Formulário -->
    <div class="card bg-dark text-light border-secondary mb-4">
        <div class="card-body">
            <h5>Nova Transação</h5>
            <form action="processar.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-select bg-dark text-light border-secondary" required>
                        <option value="receita">Receita (Entrada)</option>
                        <option value="despesa">Despesa (Saída)</option>
                        <option value="diario">Gasto Diário</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Valor</label>
                    <input class="form-control bg-dark text-light border-secondary" name="valor" type="number" step="0.01" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descrição</label>
                    <input class="form-control bg-dark text-light border-secondary" name="descricao" type="text" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Data</label>
                    <input class="form-control bg-dark text-light border-secondary" name="data" type="date" required>
                </div>
                <button type="submit" style="background-color: #9D00FF; border-color: #9D00FF;" class="btn text-light">Lançar</button>
            </form>
        </div>
    </div>

    <!-- Extrato -->
    <div class="card bg-dark text-light border-secondary mb-4">
        <div class="card-body">
            <h5>Extrato</h5>
            <table class="table table-dark table-striped">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Descrição</th>
                        <th>Data</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($historico)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">Nenhuma transação registrada.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($historico as $transacao): ?>
                        <tr>
                            <td>
                                <?php if ($transacao->getTipo() === 'Entrada'): ?>
                                    <span class="badge bg-success">Entrada</span>
                                <?php elseif ($transacao->getTipo() === 'Saída'): ?>
                                    <span class="badge bg-danger">Saída</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Diário</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($transacao->getDescricao()) ?></td>
                            <td><?= date('d/m/Y', strtotime($transacao->getData())) ?></td>
                            <td>R$ <?= number_format($transacao->getValor(), 2, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</body>
</html>