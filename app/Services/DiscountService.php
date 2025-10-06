<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Classe responsável por calcular diferentes tipos de descontos.
 * O objetivo é servir como um exemplo didático para a aplicação de diversas técnicas de teste de software.
 */
class DiscountService
{
    /**
     * MÉTODO 1: Alvo das técnicas de Particionamento de Equivalência e Análise do Valor Limite.
     * Calcula um desconto por faixas de valor.
     * - Compras < R$100: 0% de desconto.
     * - Compras >= R$100 e < R$500: 5% de desconto.
     * - Compras >= R$500: 10% de desconto.
     *
     * @param float $amount O valor da compra.
     * @return float O valor do desconto.
     */
    public function calculateTieredDiscount(float $amount): float
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('O valor da compra não pode ser negativo.');
        }

        if ($amount >= 500) {
            return $amount * 0.10;
        }

        if ($amount >= 100) {
            return $amount * 0.05;
        }

        return 0.0;
    }

    /**
     * MÉTODO 2: Alvo da técnica Grafo de Causa e Efeito.
     * Calcula um desconto com base em duas condições (causas) que geram um efeito.
     * - Causa 1: O utilizador é um membro do clube de fidelidade.
     * - Causa 2: É a primeira compra do utilizador.
     *
     * Efeitos (Taxas de desconto):
     * - Membro e Primeira Compra: 15%
     * - Membro, mas não é Primeira Compra: 10%
     * - Não Membro e Primeira Compra: 5%
     * - Não Membro e não é Primeira Compra: 0%
     *
     * @param bool $isMember Se o utilizador é membro.
     * @param bool $isFirstPurchase Se é a primeira compra.
     * @return float A taxa de desconto (ex: 0.15 para 15%).
     */
    public function calculateLoyaltyDiscountRate(bool $isMember, bool $isFirstPurchase): float
    {
        if ($isMember && $isFirstPurchase) {
            return 0.15;
        }

        if ($isMember && !$isFirstPurchase) {
            return 0.10;
        }

        if (!$isMember && $isFirstPurchase) {
            return 0.05;
        }

        return 0.0;
    }

    /**
     * MÉTODO 3: Alvo da técnica de Teste de Transição de Estado.
     * Calcula um desconto progressivo com base na quantidade de itens comprados.
     * O "estado" do sistema muda conforme a quantidade de itens atravessa certos limiares.
     * - Estado 1 (1-4 itens): 5% de desconto.
     * - Estado 2 (5-9 itens): 10% de desconto.
     * - Estado 3 (10+ itens): 15% de desconto.
     *
     * @param int $itemCount O número de itens.
     * @return float A taxa de desconto por volume.
     */
    public function calculateBulkDiscountRate(int $itemCount): float
    {
        if ($itemCount >= 10) {
            return 0.15;
        }
        
        if ($itemCount >= 5) {
            return 0.10;
        }
        
        if ($itemCount >= 1) {
            return 0.05;
        }

        return 0.0;
    }
    
    /**
     * MÉTODO 4: Alvo da técnica de Error-Guessing (Adivinhação de Erro).
     * Aplica um desconto fixo com base num código de cupom.
     * A implementação é propositadamente sensível a erros comuns.
     *
     * @param string|null $couponCode O código inserido pelo utilizador.
     * @return float O valor do desconto fixo.
     */
    public function applyFixedCoupon(?string $couponCode): float
    {
        // A implementação espera um formato exato, tornando-a frágil.
        if ($couponCode === 'PROMO10') {
            return 10.0;
        }

        if ($couponCode === 'PROMO50') {
            return 50.0;
        }

        return 0.0;
    }

    /**
     * MÉTODO 5: Alvo para o Teste Funcional Sistêmico.
     * Orquestra os outros métodos para calcular o preço final de um produto.
     * Simula uma funcionalidade completa do sistema ("Calcular Preço Final").
     *
     * A ordem da lógica de negócio é:
     * 1. Começa com o preço base.
     * 2. Aplica descontos percentuais cumulativos (fidelidade + volume).
     * 3. Sobre o novo valor, subtrai o desconto por faixa de valor (Particionamento).
     * 4. Por fim, subtrai o valor de um cupom fixo (Adivinhação de Erro).
     *
     * @param float $baseAmount O preço inicial do produto.
     * @param int $itemCount O número de itens.
     * @param bool $isMember Se o utilizador é membro.
     * @param bool $isFirstPurchase Se é a primeira compra.
     * @param string|null $couponCode O código do cupom.
     * @return float O preço final a ser pago.
     */
    public function getFinalPrice(float $baseAmount, int $itemCount, bool $isMember, bool $isFirstPurchase, ?string $couponCode): float
    {
        // 1. Acumula descontos percentuais
        $loyaltyRate = $this->calculateLoyaltyDiscountRate($isMember, $isFirstPurchase);
        $bulkRate = $this->calculateBulkDiscountRate($itemCount);
        $totalPercentageDiscount = $loyaltyRate + $bulkRate;
        
        $amountAfterPercentageDiscounts = $baseAmount * (1 - $totalPercentageDiscount);

        // 2. Calcula e subtrai o desconto por faixa sobre o novo valor
        $tieredDiscount = $this->calculateTieredDiscount($amountAfterPercentageDiscounts);
        $amountAfterTiered = $amountAfterPercentageDiscounts - $tieredDiscount;

        // 3. Subtrai o cupom fixo
        $couponDiscount = $this->applyFixedCoupon($couponCode);
        $finalPrice = $amountAfterTiered - $couponDiscount;

        // Regra de negócio: o preço nunca pode ser negativo.
        return max(0, $finalPrice);
    }
}

