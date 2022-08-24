<?php

namespace Alura\Leilao\Service;

use Alura\Leilao\Model\Lance;
use Alura\Leilao\Model\Leilao;
use Ds\Sequence;

class Avaliador
{
    private Lance $melhorLance;
    private Lance $piorLance;
    private Sequence $ranking;

    public function avalia(Leilao $leilao): void
    {
        $this->melhorLance = $leilao->melhorLance();
        $this->piorLance   = $leilao->piorLance();
        $this->ranking     = $leilao->ranking();
    }

    public function maiorValor(): float
    {
        return $this->melhorLance->valor;
    }

    public function menorValor(): float
    {
        return $this->piorLance->valor;
    }

    public function topTres(): Sequence
    {
        return $this->ranking->slice(0, 3);
    }
}
