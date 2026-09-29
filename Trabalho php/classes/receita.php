<?php
declare(strict_types=1);

require_once __DIR__ . '/Transacao.php';

class Receita extends Transacao {
    public function __construct(float $valor, string $descricao, string $data, ?int $id = null) {
        parent::__construct($valor, $descricao, $data, $id);
    }

    public function getTipo(): string {
        return 'Entrada';
    }
}