<?php

namespace Alura\Leilao\Tests\Unit\Service;

use Alura\Leilao\Model\Lance;
use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Model\Usuario;
use PHPUnit\Framework\TestCase;
use Alura\Leilao\Service\Avaliador;
use DateTimeImmutable;

class AvaliadorTest extends TestCase
{
    private Avaliador $leiloeiro;

    protected function setUp(): void
    {
        $this->leiloeiro = new Avaliador();
    }

    public function arrange()
    {
        // Arrange - Given
        $leilao = new Leilao(
            'Fiat 147 0KM',
            new DateTimeImmutable('2022-01-01'),
            1
        );

        $maria    = new Usuario('Maria');
        $joao     = new Usuario('João');
        $anderson = new Usuario('Anderson');

        $leilao->recebeLance(new Lance($joao,     2000));
        $leilao->recebeLance(new Lance($maria,    2500));
        $leilao->recebeLance(new Lance($anderson, 1500));

        return [
            [$leilao]
        ];
    }

    /**
     * @dataProvider arrange
     */
    public function testMaiorValorIsOk(Leilao $leilao)
    {
        $this->leiloeiro->avaliar($leilao);
        self::assertEquals(2500, $this->leiloeiro->maiorValor());
    }

    /**
     * @dataProvider arrange
     */
    public function testMenorValorIsOk(Leilao $leilao)
    {
        $this->leiloeiro->avaliar($leilao);
        self::assertEquals(1500, $this->leiloeiro->menorValor());
    }

    /**
     * @dataProvider arrange
     */
    public function testTopTresIsOk(Leilao $leilao)
    {
        $this->leiloeiro->avaliar($leilao);
        $topTres = $this->leiloeiro->topTres();

        self::assertCount(3, $topTres);

        self::assertEquals(2500, $topTres[0]->valor);
        self::assertEquals(2000, $topTres[1]->valor);
        self::assertEquals(1500, $topTres[2]->valor);
    }
}
