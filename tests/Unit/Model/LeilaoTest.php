<?php

namespace Alura\Leilao\Tests\Unit\Model;

use Alura\Leilao\Model\Lance;
use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Model\Usuario;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class LeilaoTest extends TestCase
{
    private Leilao $leilao;

    protected function setUp(): void
    {
        $this->leilao = new Leilao(
            'Teste',
            new DateTimeImmutable('2022-01-01'),
            1
        );
    }

    public function lancesEValores()
    {
        $lanceJoao = new Lance(new Usuario('Joao'), 1000);
        $lanceMaria = new Lance(new Usuario('Maria'), 2000);

        return [
            [
                [ $lanceJoao, $lanceMaria ],
                [ $lanceJoao->valor, $lanceMaria->valor ]
            ]
        ];
    }

    /**
     * @dataProvider lancesEValores
     */
    public function testLeilaoDeveReceberTeste(array $lances, array $valores)
    {
        foreach ($lances as $lance) {
            $this->leilao->recebeLance($lance);
        }

        $lances = $this->leilao->lances;
        foreach($valores as $key => $valor) {
            static::assertEquals($valor, $lances[$key]->valor);
        }
    }

    public function testLeilaoNaoDeveAceitar2TestesConsecutivosDaMesmaPessoa()
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('O mesmo usuário não pode realizar dois lances consecutivos');

        $joao = new Usuario('Joao');

        $this->leilao->recebeLance(new Lance($joao, 1000));
        $this->leilao->recebeLance(new Lance($joao, 1200));
    }

    public function testLeilaoNaoDeveAceitarMaisDe5TestesPorUsuario()
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Um usuário não pode realizar mais do que cinco lances');

        $maria = new Usuario('Maria');
        $joao = new Usuario('João');

        $this->leilao->recebeLance(new Lance($maria, 1000));
        $this->leilao->recebeLance(new Lance($joao, 1200));
        $this->leilao->recebeLance(new Lance($maria, 2000));
        $this->leilao->recebeLance(new Lance($joao, 2200));
        $this->leilao->recebeLance(new Lance($maria, 3000));
        $this->leilao->recebeLance(new Lance($joao, 3200));
        $this->leilao->recebeLance(new Lance($maria, 4000));
        $this->leilao->recebeLance(new Lance($joao, 4200));
        $this->leilao->recebeLance(new Lance($maria, 5000));
        $this->leilao->recebeLance(new Lance($joao, 5200));

        $this->leilao->recebeLance(new Lance($maria, 6000));
        $this->leilao->recebeLance(new Lance($joao, 6200));

    }
}
