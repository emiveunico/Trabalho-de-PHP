<?php 
declare(strict_types=1);

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Receita.php';
require_once __DIR__ . '/Despesa.php';
require_once __DIR__ . '/Diario.php';

class Carteira {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function salvarTransacao(Transacao $transacao): void {
        if ($transacao instanceof Despesa || $transacao instanceof Diario) {
            if ($transacao->getValor() > $this->getSaldo()) {
                throw new Exception('Saldo insuficiente para realizar este lançamento.');
            }
        }

        $tipoBanco = 'receita';
        if ($transacao instanceof Despesa) {
            $tipoBanco = 'despesa';
        } elseif ($transacao instanceof Diario) {
            $tipoBanco = 'diario';
        }

        $stmt = $this->db->prepare(
            "INSERT INTO transacoes (tipo, valor, descricao, data_transacao) VALUES (:tipo, :valor, :descricao, :data_transacao)"
        );

        $stmt->execute([
            ':tipo'           => $tipoBanco,
            ':valor'          => $transacao->getValor(),
            ':descricao'      => $transacao->getDescricao(),
            ':data_transacao' => $transacao->getData()
        ]);
    }

    public function excluirTransacao(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM transacoes WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public function getSaldo(): float {
        $stmt = $this->db->query("
            SELECT 
                SUM(CASE WHEN tipo = 'receita' THEN valor ELSE -valor END) AS saldo_total 
            FROM transacoes
        ");
        $result = $stmt->fetch();
        return (float) ($result['saldo_total'] ?? 0.0);
    }

    public function getHistorico(): array {
        $stmt = $this->db->query("SELECT * FROM transacoes ORDER BY data_transacao DESC, id DESC");
        $rows = $stmt->fetchAll();

        $historico = [];
        foreach ($rows as $row) {
            $id = (int)$row['id'];
            $valor = (float)$row['valor'];
            $descricao = $row['descricao'];
            $data = $row['data_transacao'];

            if ($row['tipo'] === 'receita') {
                $historico[] = new Receita($valor, $descricao, $data, $id);
            } elseif ($row['tipo'] === 'despesa') {
                $historico[] = new Despesa($valor, $descricao, $data, $id);
            } elseif ($row['tipo'] === 'diario') {
                $historico[] = new Diario($valor, $descricao, $data, $id);
            }
        }

        return $historico;
    }
}