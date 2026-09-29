<?php 
declare(strict_types=1);

abstract class Transacao {
    private ?int $id;
    private float $valor;
    private string $descricao;
    private string $data;

    public function __construct(float $valor, string $descricao, string $data, ?int $id = null) {
        $this->valor = $valor;
        $this->descricao = $descricao;
        $this->data = $data;
        $this->id = $id;
    }

    public function getId(): ?int {
        return $this->id;
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