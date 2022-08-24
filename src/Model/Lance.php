<?php

namespace Alura\Leilao\Model;

class Lance
{
    public function __construct(
        public readonly Usuario $usuario,
        public readonly float $valor
    ) {}
}
