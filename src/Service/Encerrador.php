<?php

namespace Alura\Leilao\Service;

use Alura\Leilao\Dao\LeilaoDao;
use DomainException;

class Encerrador
{
    function __construct(private EnviadorDeEmail $enviadorDeEmail) {}

    public function encerrar(LeilaoDao $dao)
    {
        $leiloes = $dao->naoFinalizados();

        foreach ($leiloes as $leilao) {
            try {
                if ($leilao->isVencido()) {
                    $leilao->finalizar();
                    $dao->atualizar($leilao);
                    $this->enviadorDeEmail->notificarTerminoLeilao($leilao);
                }
            } catch (DomainException $e) {
                error_log('Erro encontrado: ' . $e->getMessage());
            }
        }
    }
}
