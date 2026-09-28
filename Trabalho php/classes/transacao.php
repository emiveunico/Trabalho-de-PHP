<?php 
declare(strict_types=1);

abstract class Transacao {
    private float $valor;
    private string $descricao;
    private string $data;

    public function __construct(float $valor, string $descricao, string $data) {
        $this->valor = $valor;
        $this->descricao = $descricao;
        $this->data = $data;
    }

    public function getValor(): float {
        return $this->valor;
    }

    public function getDescricao(): string {
        return $this->descricao;
    }

    public function getData(): string {
        return $this->data;
    }

    abstract public function getTipo(): string;
}