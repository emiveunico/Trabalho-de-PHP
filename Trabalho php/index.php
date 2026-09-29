<?php
declare(strict_types=1);

require_once __DIR__ . '/Classes/Carteira.php';

session_start();

// Filtros de Mês e Ano
$mesAtual = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
$anoAtual = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');

$mesesNomes = [
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];

try {
    $carteira = new Carteira();
    $historicoCompleto = $carteira->getHistorico();
} catch (Exception $e) {
    $erroConexao = $e->getMessage();
    $historicoCompleto = [];
}

// Filtrar lançamentos detalhados do mês selecionado
$lancamentosDoMes = array_filter($historicoCompleto, function($item) use ($mesAtual, $anoAtual) {
    $dataItem = strtotime($item->getData());
    return (int)date('m', $dataItem) === $mesAtual && (int)date('Y', $dataItem) === $anoAtual;
});

// Calcular totalizadores por dia do mês (Estrutura da Planilha Diária)
$diasNoMes = cal_days_in_month(CAL_GREGORIAN, $mesAtual, $anoAtual);
$planilhaDiaria = [];
$saldoAcumulado = 0.0;

// Calcula o saldo inicial que veio dos meses anteriores
foreach ($historicoCompleto as $item) {
    $dataItem = strtotime($item->getData());
    $anoItem = (int)date('Y', $dataItem);
    $mesItem = (int)date('m', $dataItem);

    if ($anoItem < $anoAtual || ($anoItem === $anoAtual && $mesItem < $mesAtual)) {
        if ($item->getTipo() === 'Entrada') {
            $saldoAcumulado += $item->getValor();
        } else {
            $saldoAcumulado -= $item->getValor();
        }
    }
}

$saldoInicialDoMes = $saldoAcumulado;

// Monta as linhas da planilha dia a dia
for ($dia = 1; $dia <= $diasNoMes; $dia++) {
    $entradaDia = 0.0;
    $saidaDia = 0.0;
    $diarioDia = 0.0;

    foreach ($lancamentosDoMes as $item) {
        $diaItem = (int)date('d', strtotime($item->getData()));
        if ($diaItem === $dia) {
            if ($item->getTipo() === 'Entrada') {
                $entradaDia += $item->getValor();
            } elseif ($item->getTipo() === 'Saída') {
                $saidaDia += $item->getValor();
            } elseif ($item->getTipo() === 'Diário') {
                $diarioDia += $item->getValor();
            }
        }
    }

    $saldoAcumulado += ($entradaDia - $saidaDia - $diarioDia);

    $planilhaDiaria[$dia] = [
        'entrada' => $entradaDia,
        'saida' => $saidaDia,
        'diario' => $diarioDia,
        'saldo' => $saldoAcumulado
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyPocket - Controle Financeiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #121214; color: #e1e1e6; font-family: sans-serif; }
        .card-custom { background-color: #202024; border: 1px solid #323238; border-radius: 8px; }
        .table-custom { color: #c4c4cc; font-size: 0.9rem; }
        .table-custom th { background-color: #0d47a1; color: #ffffff; text-align: center; }
        .table-custom td { text-align: center; vertical-align: middle; }
        .header-section { background-color: #1e1e24; border-bottom: 1px solid #2e2e38; }
        .btn-blue { background-color: #0052cc; border-color: #0052cc; color: white; }
        .btn-blue:hover { background-color: #003d99; color: white; }
    </style>
</head>
<body>

<!-- Header de Navegação / Filtros -->
<div class="header-section p-3 mb-4">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <form method="GET" class="d-flex align-items-center gap-2">
            <label class="fw-bold">Mês:</label>
            <select name="mes" class="form-select form-select-sm bg-dark text-light border-secondary" onchange="this.form.submit()">
                <?php foreach ($mesesNomes as $num => $nome): ?>
                    <option value="<?= $num ?>" <?= $num === $mesAtual ? 'selected' : '' ?>><?= $nome ?></option>
                <?php endforeach; ?>
            </select>

            <label class="fw-bold ms-2">Ano:</label>
            <select name="ano" class="form-select form-select-sm bg-dark text-light border-secondary" onchange="this.form.submit()">
                <?php for ($a = 2024; $a <= 2030; $a++): ?>
                    <option value="<?= $a ?>" <?= $a === $anoAtual ? 'selected' : '' ?>><?= $a ?></option>
                <?php endfor; ?>
            </select>
        </form>

        <div class="text-secondary small">
            Saldo Inicial do Mês: <strong class="text-light">R$ <?= number_format($saldoInicialDoMes, 2, ',', '.') ?></strong>
        </div>
    </div>
</div>

<div class="container-fluid px-4">

    <?php if (isset($erroConexao)): ?>
        <div class="alert alert-danger">Erro de Conexão com o Banco: <?= htmlspecialchars($erroConexao) ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['erro'])): ?>
        <div class="alert alert-danger bg-danger text-light mb-4">
            <?= htmlspecialchars($_SESSION['erro']) ?>
        </div>
        <?php unset($_SESSION['erro']); ?>
    <?php endif; ?>

    <div class="row">
        <!-- Formulário Lateral (Novo Lançamento) -->
        <div class="col-lg-3 mb-4">
            <div class="card card-custom p-3">
                <h5 class="fw-bold mb-3">Novo Lançamento</h5>
                <form action="processar.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label small text-secondary">Tipo</label>
                        <select name="tipo" class="form-select bg-dark text-light border-secondary" required>
                            <option value="receita">Entrada (+)</option>
                            <option value="despesa">Saída (-)</option>
                            <option value="diario">Diário (-)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-secondary">Descrição</label>
                        <input name="descricao" type="text" class="form-control bg-dark text-light border-secondary" placeholder="Ex: Salário, Aluguel, Refeição" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-secondary">Valor (R$)</label>
                        <input name="valor" type="number" step="0.01" class="form-control bg-dark text-light border-secondary" placeholder="0.00" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-secondary">Data</label>
                        <input name="data" type="date" value="<?= date('Y-m-d') ?>" class="form-control bg-dark text-light border-secondary" required>
                    </div>
                    <button type="submit" class="btn btn-blue w-100 fw-bold">Registrar</button>
                </form>
            </div>
        </div>

        <!-- Tabela Superior (Planilha do Mês) -->
        <div class="col-lg-9 mb-4">
            <div class="card card-custom p-3">
                <h5 class="fw-bold text-primary mb-3">Planilha do Mês: <?= $mesesNomes[$mesAtual] ?> / <?= $anoAtual ?></h5>
                <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                    <table class="table table-dark table-striped table-hover table-custom mb-0">
                        <thead style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th>Dia</th>
                                <th>Entrada</th>
                                <th>Saída</th>
                                <th>Diário</th>
                                <th>Saldo Acumulado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($d = 1; $d <= $diasNoMes; $d++): 
                                $row = $planilhaDiaria[$d];
                            ?>
                            <tr>
                                <td class="fw-bold"><?= sprintf('%02d', $d) ?></td>
                                <td class="<?= $row['entrada'] > 0 ? 'text-success fw-bold' : 'text-secondary' ?>">
                                    <?= $row['entrada'] > 0 ? 'R$ ' . number_format($row['entrada'], 2, ',', '.') : '-' ?>
                                </td>
                                <td class="<?= $row['saida'] > 0 ? 'text-danger fw-bold' : 'text-secondary' ?>">
                                    <?= $row['saida'] > 0 ? 'R$ ' . number_format($row['saida'], 2, ',', '.') : '-' ?>
                                </td>
                                <td class="<?= $row['diario'] > 0 ? 'text-warning fw-bold' : 'text-secondary' ?>">
                                    <?= $row['diario'] > 0 ? 'R$ ' . number_format($row['diario'], 2, ',', '.') : '-' ?>
                                </td>
                                <td class="fw-bold text-info">
                                    R$ <?= number_format($row['saldo'], 2, ',', '.') ?>
                                </td>
                            </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela Inferior (Lançamentos Detalhados do Mês) -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card card-custom p-3">
                <h5 class="fw-bold mb-3">Lançamentos Detalhados do Mês</h5>
                <div class="table-responsive">
                    <table class="table table-dark table-hover table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Descrição</th>
                                <th>Tipo</th>
                                <th>Valor</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($lancamentosDoMes)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">Nenhum lançamento registrado para este mês.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($lancamentosDoMes as $item): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($item->getData())) ?></td>
                                    <td><?= htmlspecialchars($item->getDescricao()) ?></td>
                                    <td>
                                        <?php if ($item->getTipo() === 'Entrada'): ?>
                                            <span class="badge bg-success">Entrada</span>
                                        <?php elseif ($item->getTipo() === 'Saída'): ?>
                                            <span class="badge bg-danger">Saída</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Diário</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold <?= $item->getTipo() === 'Entrada' ? 'text-success' : 'text-danger' ?>">
                                        R$ <?= number_format($item->getValor(), 2, ',', '.') ?>
                                    </td>
   <td>
    <a href="editar.php?id=<?= $item->getId() ?>&mes=<?= $mesAtual ?>&ano=<?= $anoAtual ?>" 
       class="btn btn-sm btn-outline-secondary py-0 px-2 me-1">
        Editar
    </a>
    <a href="excluir.php?id=<?= $item->getId() ?>&mes=<?= $mesAtual ?>&ano=<?= $anoAtual ?>" 
       class="btn btn-sm btn-outline-danger py-0 px-2"
       onclick="return confirm('Tem certeza de que deseja excluir este lançamento?');">
        Excluir
    </a>
</td>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

</body>
</html>