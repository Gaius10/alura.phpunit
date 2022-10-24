<?php

namespace Alura\Leilao\Dao;

use Alura\Leilao\Model\Leilao;
use DateTimeImmutable;
use Ds\Sequence;
use Ds\Vector;
use PDO;

class LeilaoDao
{
    function __construct(private \PDO $connection)
    {}

    public function salvar(Leilao $leilao)
    {
        $sql = "INSERT INTO leiloes (descricao, finalizado, dataInicio) VALUES (?, ?, ?)";
        $stm = $this->connection->prepare($sql);
        $stm->execute([
            $leilao->descricao,
            $leilao->isFinalizado() ? 'true' : 'false',
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
        $sql = "SELECT * FROM leiloes WHERE finalizado = ?";
        $stm = $this->connection->prepare($sql);
        $stm->execute([ $finalizado ? 'true' : 'false' ]);

        $leiloes = new Vector($stm->fetchAll(PDO::FETCH_ASSOC));

        return $leiloes->map(function ($dadosLeilao) {
            return new Leilao(...$this->mapearValores($dadosLeilao));
        });
    }

    private function mapearValores(array $dados): array
    {
        $dadosMapeados = [];

        foreach ($dados as $key => $value) {
            $dadosMapeados[$key] = $this->mapear($key, $value);
        }

        return $dadosMapeados;
    }

    private function mapear($key, $value): mixed
    {
        if ($key == 'dataInicio')
            return new DateTimeImmutable($value);

        return $value;
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
            'finalizado' => $leilao->isFinalizado() ? 'true' : 'false',
            'id'         => $leilao->id,
        ]);
    }
}
