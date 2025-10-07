<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Services\DiscountService;
use PHPUnit\Framework\TestCase;

class DiscountServiceTest extends TestCase
{
    private DiscountService $service;

    /**
     * Prepara o ambiente para cada teste.
     * Este método é chamado automaticamente pelo PHPUnit antes de cada teste.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DiscountService();
    }

    /**
     * @test
     * @description Testa a aplicação de descontos por faixa de preço.
     * @technique Análise de Valor Limite
     * Verifica se o desconto é aplicado corretamente nos valores que definem
     * a mudança de faixa (as fronteiras), como R$99.99, R$100.00, R$499.99 e R$500.00.
     */
    public function it_applies_correct_discount_at_all_boundaries(): void
    {
        // --- Teste da fronteira de R$100 ---
        // Imediatamente abaixo do limite (deve ter 0% de desconto)
        $this->assertEquals(0.0, $this->service->calculateTieredDiscount(99.99), "Falhou abaixo do limite de R$100");
        // No limite exato (deve ter 5% de desconto)
        $this->assertEquals(5.0, $this->service->calculateTieredDiscount(100.00), "Falhou no limite exato de R$100");

        // --- Teste da fronteira de R$500 ---
        // Imediatamente abaixo do limite (deve ter 5% de desconto)
        $this->assertEquals(24.9995, $this->service->calculateTieredDiscount(499.99), "Falhou abaixo do limite de R$500");
        // No limite exato (deve ter 10% de desconto)
        $this->assertEquals(50.0, $this->service->calculateTieredDiscount(500.00), "Falhou no limite exato de R$500");
    }

    /**
     * @test
     * @description Testa o desconto de lealdade baseado em múltiplas condições.
     * @technique Grafo de Causa e Efeito
     * Valida todas as combinações lógicas entre as "causas" (ser membro, ser primeira compra)
     * para garantir que o "efeito" (a taxa de desconto) está correto em cada cenário.
     */
    public function it_covers_all_paths_of_the_loyalty_discount_cause_effect_graph(): void
    {
        // Cenário 1: Membro E Primeira Compra -> Efeito: 15%
        $this->assertEquals(0.15, $this->service->calculateLoyaltyDiscountRate(true, true), "Falhou para Membro + Primeira Compra");

        // Cenário 2: Membro E NÃO Primeira Compra -> Efeito: 10%
        $this->assertEquals(0.10, $this->service->calculateLoyaltyDiscountRate(true, false), "Falhou para Membro + Compra Recorrente");

        // Cenário 3: NÃO Membro E Primeira Compra -> Efeito: 5%
        $this->assertEquals(0.05, $this->service->calculateLoyaltyDiscountRate(false, true), "Falhou para Não Membro + Primeira Compra");

        // Cenário 4: NÃO Membro E NÃO Primeira Compra -> Efeito: 0%
        $this->assertEquals(0.0, $this->service->calculateLoyaltyDiscountRate(false, false), "Falhou para Não Membro + Compra Recorrente");
    }

    /**
     * @test
     * @description Testa o desconto por volume.
     * @technique Teste de Transição de Estado
     * Verifica se o sistema transita corretamente entre os "estados" de desconto
     * (ex: de 0% para 10%) quando a quantidade de itens atinge um novo patamar.
     */
    public function it_transitions_discount_rate_correctly_across_all_states(): void
    {
        // Estado 1: Sem desconto
        $this->assertEquals(0.0, $this->service->calculateBulkDiscountRate(4), "Falhou no estado 'sem desconto'");

        // Transição para o Estado 2 (5-9 itens)
        $this->assertEquals(0.10, $this->service->calculateBulkDiscountRate(5), "Falhou na transição para 5 itens");
        $this->assertEquals(0.10, $this->service->calculateBulkDiscountRate(9), "Falhou no final do estado de 10%");

        // Transição para o Estado 3 (10+ itens)
        $this->assertEquals(0.15, $this->service->calculateBulkDiscountRate(10), "Falhou na transição para 10 itens");
    }

    /**
     * @test
     * @description Testa a aplicação de cupons fixos.
     * @technique Error-Guessing (Adivinhação de Erro)
     * Simula erros comuns que um utilizador poderia cometer ao inserir um cupom,
     * como espaços extras ou letras minúsculas, para testar a robustez do código.
     */
    public function it_handles_common_user_errors_when_applying_coupon(): void
    {
        // Erro Adivinhado 1: Espaços em branco acidentais.
        $this->assertEquals(10.0, $this->service->applyFixedCoupon(' PROMO10 '), "Falhou ao tratar espaços em branco");
        
        // Erro Adivinhado 2: Caixa de texto diferente (minúsculas).
        $this->assertEquals(10.0, $this->service->applyFixedCoupon('promo10'), "Falhou ao tratar letras minúsculas");
        
        // Erro Adivinhado 3: Cupom inexistente.
        $this->assertEquals(0.0, $this->service->applyFixedCoupon('CUPOM_INVALIDO'), "Falhou ao tratar cupom inválido");
        
        // Erro Adivinhado 4: Entrada nula.
        $this->assertEquals(0.0, $this->service->applyFixedCoupon(null), "Falhou ao tratar entrada nula");
    }

    /**
     * @test
     * @description Testa o cálculo do preço final com todas as regras de negócio.
     * @technique Teste Funcional Sistêmico
     * Simula cenários complexos para validar o fluxo completo do cálculo,
     * garantindo que todas as lógicas de desconto são orquestradas corretamente.
     */
    public function it_calculates_the_final_price_for_multiple_complex_scenarios(): void
    {
        // --- Cenário 1: Desconto de volume é o maior ---
        $finalPrice1 = $this->service->getFinalPrice(
            baseAmount: 800.0,
            itemCount: 12, // Ativa 15% de desconto por volume
            isMember: true,
            isFirstPurchase: false, // Ativa 10% de desconto de lealdade
            couponCode: 'PROMO50' // Ativa R$50 de desconto fixo
        );

        // Desconto por volume (15% de 800 = 120) é o maior.
        // Preço final: 800 - 120 = 680
        $this->assertEquals(680.0, $finalPrice1, "Falhou no cenário 1 onde o desconto por volume é maior");

        // --- Cenário 2: Desconto de lealdade é o maior ---
        $finalPrice2 = $this->service->getFinalPrice(
            baseAmount: 200.0,
            itemCount: 4, // Não ativa desconto por volume
            isMember: true,
            isFirstPurchase: true, // Ativa 15% de desconto de lealdade
            couponCode: 'PROMO10' // Ativa R$10 de desconto fixo
        );

        // Desconto de lealdade (15% de 200 = 30) é o maior.
        // Preço final: 200 - 30 = 170
        $this->assertEquals(170.0, $finalPrice2, "Falhou no cenário 2 onde o desconto de lealdade é maior");
    }
}

