<?php

namespace Alura\Leilao\Model;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Ds\Sequence;
use Ds\Vector;
use InvalidArgumentException;

class Leilao
{
    public readonly Vector $lances;
    private Lance $ultimoLance;

    public function __construct(
        public readonly string $descricao,
        public readonly DateTimeInterface $dataInicio,
        public readonly int $id,
        private bool $finalizado = false
    ) {
        $this->lances = new Vector();
    }

    public function isFinalizado(): bool
    {
        return $this->finalizado;
    }

    public function recebeLance(Lance $lance)
    {
        if ($this->isRepeated($lance)) {
            throw new InvalidArgumentException('O mesmo usuário não pode realizar dois lances consecutivos');
        }

        if ($this->numeroDeLancesDe($lance->usuario) >= 5) {
            throw new InvalidArgumentException('Um usuário não pode realizar mais do que cinco lances');
        }

        $this->ultimoLance = $lance;
        $this->lances->push($lance);
    }

    private function numeroDeLancesDe(Usuario $usuario): int
    {
        return $this->lances->reduce(function ($contador, $lance) use ($usuario) {
            if ($lance->usuario == $usuario) $contador++;

            return $contador;
        }, 0);
    }

    private function isRepeated(Lance $lance)
    {
        return isset($this->ultimoLance) && 
            $this->ultimoLance->usuario == $lance->usuario;
    }

    public function melhorLance(): Lance
    {
        $reducer = function (?Lance $carry, Lance $current) {
            if ($carry?->valor > $current->valor)
                return $carry;

            return $current;
        };

        return $this->lances->reduce($reducer);
    }

    public function piorLance(): Lance
    {
        $reducer = function (?Lance $carry, Lance $current) {
            if (!is_null($carry) && $carry->valor < $current->valor)
                return $carry;

            return $current;
        };

        return $this->lances->reduce($reducer);
    }

    public function ranking(): Sequence
    {
        return $this->lances->sorted(function($l1, $l2) {
            return $l2->valor - $l1->valor;
        });
    }

    public function finalizar(): void
    {
        $this->finalizado = true;
    }

    public function isVencido(): bool
    {
        $idade = $this->dataInicio->diff(new DateTimeImmutable());
        return $idade->d > 7;
    }
}
