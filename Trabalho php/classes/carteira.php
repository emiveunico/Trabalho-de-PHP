<?php 
declare(strict_types=1);

class Carteira {
    private float $saldo;
    private array $historico;

    public function __construct() {
        $this->saldo = 0.0;
        $this->historico = [];
    }
    public function adicionarReceita(Receita $receita): void {
    $this->saldo += $receita->getValor();
    $this->historico[] = $receita;
    }

    public function adicionarDespesa(Despesa $despesa): void {
        if ($despesa->getValor() > $this->saldo) {
            throw new Exception('Saldo insuficiente.');
        }
        $this->saldo -= $despesa->getValor();
        $this->historico[] = $despesa;
    }

    public function getSaldo(): float {
        return $this->saldo;
    }

    public function getHistorico(): array {
        return $this->historico;
    }

}


