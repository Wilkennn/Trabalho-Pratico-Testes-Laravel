<?php

namespace Tests\Unit;

use App\Services\DiscountService;
use PHPUnit\Framework\TestCase;

class DiscountServiceTest extends TestCase
{
    private DiscountService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DiscountService();
    }

    /**
     * @test
     * @description Teste para o método calculateTieredDiscount.
     * @technique Análise do Valor Limite
     * Este teste verifica os limites exatos onde a lógica de desconto muda.
     * Testamos o valor 499.99 (que deve ter 5% de desconto) e 500.00 (que deve ter 10%).
     */
    public function it_applies_correct_discount_at_the_500_boundary()
    {
        // Testando o limite superior da faixa de 5%
        $this->assertEquals(24.9995, $this->service->calculateTieredDiscount(499.99), "Falhou no limite inferior de R$500");

        // Testando o limite exato da faixa de 10%
        $this->assertEquals(50, $this->service->calculateTieredDiscount(500.00), "Falhou no limite exato de R$500");
    }

    /**
     * @test
     * @description Teste para o método calculateLoyaltyDiscountRate.
     * @technique Grafo de Causa e Efeito
     * Este teste cobre um caminho específico do grafo: quando ambas as causas
     * ('é membro' e 'é primeira compra') são verdadeiras, o efeito esperado é um desconto de 15%.
     */
    public function it_returns_15_percent_when_user_is_member_and_it_is_the_first_purchase()
    {
        // Causa 1 (é membro) = true
        // Causa 2 (primeira compra) = true
        $isMember = true;
        $isFirstPurchase = true;

        // Efeito esperado = 0.15
        $this->assertEquals(0.15, $this->service->calculateLoyaltyDiscountRate($isMember, $isFirstPurchase));
    }

    /**
     * @test
     * @description Teste para o método calculateBulkDiscountRate.
     * @technique Teste de Transição de Estado
     * Este teste verifica a transição de estado entre a faixa de 5-9 itens e 10+ itens.
     * A transição ocorre exatamente quando a contagem de itens passa de 9 para 10.
     */
    public function it_transitions_discount_rate_correctly_from_9_to_10_items()
    {
        // Estado anterior (9 itens) -> deve retornar 10% de desconto
        $this->assertEquals(0.10, $this->service->calculateBulkDiscountRate(9));

        // Transição para o novo estado (10 itens) -> deve retornar 15% de desconto
        $this->assertEquals(0.15, $this->service->calculateBulkDiscountRate(10));
    }

    /**
     * @test
     * @description Teste para o método applyFixedCoupon.
     * @technique Error-Guessing (Adivinhação de Erro)
     * Este teste é baseado na "adivinhação" de um erro comum do utilizador:
     * inserir o código do cupom com espaços em branco acidentais no início ou no fim.
     */
    public function it_fails_to_apply_coupon_with_leading_or_trailing_whitespace()
    {
        $couponWithWhitespace = ' PROMO10 ';
        
        // Esperamos que o método, por ser frágil, não reconheça o cupom e retorne 0.
        $this->assertEquals(0.0, $this->service->applyFixedCoupon($couponWithWhitespace));
    }

    /**
     * @test
     * @description Teste para o método getFinalPrice.
     * @technique Teste Funcional Sistêmico
     * Este teste simula um cenário de ponta a ponta, orquestrando todas as outras
     * lógicas para verificar se o resultado final está correto.
     */
    public function it_calculates_the_final_price_for_a_complex_scenario()
    {
        // Cenário: Um membro fiel (não é a primeira compra) compra 12 itens
        // com um preço base de R$ 800 e usa um cupom de R$ 50.

        $baseAmount = 800.0;
        $itemCount = 12;
        $isMember = true;
        $isFirstPurchase = false;
        $couponCode = 'PROMO50';

        // Lógica esperada:
        // 1. Desconto de lealdade (membro, não primeira compra): 10%
        // 2. Desconto de volume (12 itens >= 10): 15%
        // 3. Desconto percentual total: 10% + 15% = 25%
        // 4. Preço após descontos percentuais: 800 * (1 - 0.25) = 600
        // 5. Desconto por faixa (valor 600 >= 500): 10% de 600 = 60
        // 6. Preço após desconto por faixa: 600 - 60 = 540
        // 7. Desconto do cupom fixo: 50
        // 8. Preço final: 540 - 50 = 490

        $finalPrice = $this->service->getFinalPrice($baseAmount, $itemCount, $isMember, $isFirstPurchase, $couponCode);

        $this->assertEquals(490.0, $finalPrice);
    }
}
