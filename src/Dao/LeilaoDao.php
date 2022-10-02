<?php

namespace Alura\Leilao\Dao;

use Alura\Leilao\Model\Leilao;
use Ds\Sequence;
use Ds\Vector;

class LeilaoDao
{
    function __construct(private \PDO $connection)
    {}

    public function salva(Leilao $leilao)
    {
        $sql = "INSERT INTO leiloes (descricao, finalizado, dataInicio) VALUES (?, ?, ?)";
        $stm = $this->connection->prepare($sql);
        $stm->execute([
            $leilao->descricao,
            $leilao->finalizado,
            $leilao->dataInicio->format('Y-m-d')
        ]);
    }

    public function naoFinalizados(): Sequence
    {
        return $this->recuperar(false);
    }

    public function finalizados(): Sequence
    {
        return $this->recuperar(true);
    }

    private function recuperar(bool $finalizado): Sequence
    {
        $sql = "SELECT * FROM leiloes WHERE finalizado = " . ($finalizado ? 1 : 0);
        $stm = $this->connection->query($sql, \PDO::FETCH_ASSOC);

        $leiloes = new Vector($stm->fetchAll());

        return $leiloes->map(function ($dadosLeilao) {
            return new Leilao(...$dadosLeilao);
        });
    }

    public function atualizar(Leilao $leilao)
    {
        $sql = 
           "UPDATE leiloes
            SET
                descricao = :descricao,
                dataInicio = :dataInicio,
                finalizado = :finalizado
            WHERE id = :id
            ";

        $stm = $this->connection->prepare($sql);
        $stm->execute([
            'descricao'  => $leilao->descricao,
            'dataInicio' => $leilao->dataInicio->format('Y-m-d'),
            'finalizado' => $leilao->isFinalizado(),
            'id'         => $leilao->id,
        ]);
    }
}
