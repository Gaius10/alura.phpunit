<?php

namespace Alura\Leilao\Tests\Service;

use Ds\Vector;
use Ds\Sequence;
use DateTimeImmutable;
use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Dao\LeilaoDao;
use PHPUnit\Framework\TestCase;
use Alura\Leilao\Service\Encerrador;
use Alura\Leilao\Service\EnviadorDeEmail;

class EncerradorTest extends TestCase
{
    protected Encerrador $encerrador;
    protected Sequence $leiloes;
    protected LeilaoDao $leilaoDao;
    protected EnviadorDeEmail $enviadorDeEmail;

    public function setUp(): void
    {
        $fiat147 = new Leilao(
            'Fiat 147 0Km',
            new DateTimeImmutable('8 days ago'),
            1
        );
        $variant = new Leilao(
            'Variant 1972 0Km',
            new DateTimeImmutable('10 days ago'),
            2
        );

        $this->leiloes = new Vector();
        $this->leiloes->push($fiat147);
        $this->leiloes->push($variant);

        $this->leilaoDao = $this->createMock(LeilaoDao::class);
        $this->leilaoDao->method('naoFinalizados')
            ->willReturn($this->leiloes);
        $this->leilaoDao->expects(self::exactly(2))
            ->method('atualizar')
            ->withConsecutive(
                [$fiat147],
                [$variant]
            );

        $this->enviadorDeEmail = self::createMock(EnviadorDeEmail::class);
    }

    public function createEncerrador()
    {
        return new Encerrador($this->enviadorDeEmail);
    }

    public function testLeiloesComMaisDeUmaSemanaDevemSerEncerrados()
    {
        $this->createEncerrador()->encerrar($this->leilaoDao);

        self::assertCount(2, $this->leiloes);
        self::assertTrue($this->leiloes[0]->isFinalizado());
        self::assertTrue($this->leiloes[1]->isFinalizado());
    }

    public function testDeveContinuarOProcessamentoAoEncontrarErroAoEnviarEmail()
    {
        $e = new \DomainException('Erro ao enviar e-mail');
        $this->enviadorDeEmail->expects(self::exactly(2))
            ->method('notificarTerminoLeilao')
            ->willThrowException($e);

        $this->createEncerrador()->encerrar($this->leilaoDao);
    }

    public function testSoDeveEnviarLeilaoPorEmailAposFinalizado()
    {
        $this->enviadorDeEmail->expects(self::exactly(2))
            ->method('notificarTerminoLeilao')
            ->willReturnCallback(function (Leilao $leilao) {
                static::assertTrue($leilao->isFinalizado());
            });
        
        $this->createEncerrador()->encerrar($this->leilaoDao);
    }
}
