<?php

namespace Alura\Leilao\Tests\Integration\Dao;

use DateTimeImmutable;
use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Dao\LeilaoDao;
use PHPUnit\Framework\TestCase;
use Alura\Leilao\Infra\ConnectionCreator;

class LeilaoDaoTest extends TestCase
{
    protected static \PDO $pdo;
    protected LeilaoDao $leilaoDao;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = new \PDO('sqlite::memory:');
        self::$pdo->exec('
            CREATE TABLE
            leiloes (
                id INTEGER PRIMARY KEY,
                descricao TEXT,
                finalizado BOOL,
                dataInicio TEXT
            );'
        );
    }

    public function setUp(): void
    {
        self::$pdo->beginTransaction();

        $leiloes = $this->leiloes();
        $this->leilaoDao = new LeilaoDao(self::$pdo);
        $this->leilaoDao->salvar($leiloes[0]);
        $this->leilaoDao->salvar($leiloes[1]);
    }

    public function leiloes(): array
    {
        $leilao1 = new Leilao(
            'Variante 0km',
            new DateTimeImmutable(),
            1
        );

        $leilao2 = new Leilao(
            'Fiat 147 0km',
            new DateTimeImmutable(),
            2
        );
        $leilao2->finalizar();

        return [
            $leilao1, $leilao2
        ];
    }

    public function testBuscaLeiloesNaoFinalizados()
    {
        $leiloes = $this->leilaoDao->naoFinalizados();

        self::assertEquals(1, $leiloes->count());
        self::assertContainsOnlyInstancesOf(Leilao::class, $leiloes);
        self::assertSame(
            'Variante 0km',
            $leiloes[0]->descricao
        );
    }

    public function testBuscaLeiloesFinalizados()
    {
        $leiloes = $this->leilaoDao->finalizados();

        self::assertEquals(1, $leiloes->count());
        self::assertContainsOnlyInstancesOf(Leilao::class, $leiloes);
        self::assertSame(
            'Fiat 147 0km',
            $leiloes[0]->descricao
        );
    }

    public function testAoAtualizarLeilaoStatusDeveSerAlterado()
    {
        $leilao = new Leilao(
            'Brasilia Amarela',
            new DateTimeImmutable(),
            3
        );

        $this->leilaoDao->salvar($leilao);
        $leilao->finalizar();
        $this->leilaoDao->atualizar($leilao);

        $leiloesFinalizados = $this->leilaoDao->finalizados();

        self::assertEquals(2, $leiloesFinalizados->count());
        self::assertSame('Brasilia Amarela', $leiloesFinalizados[1]->descricao);
    }

    public function tearDown(): void
    {
        self::$pdo->rollBack();
    }

}
