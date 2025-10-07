# Trabalho Prático de Teste de Software
## Demonstração do Framework PHPUnit com Laravel

---

**Instituição:** Pontifícia Universidade Católica de Minas Gerais  
**Curso:** Engenharia de Software  
**Professor:** Cleiton Tavares  
**Grupo:** PHPUnit  

**Integrantes:**
- Arthur Freitas Jardim
- Carlos Henrique Neimar
- Luiz Filipe Nery
- João Victor Temponi
- Wilken Moreira

---

## 1. Introdução

Este trabalho tem como objetivo descrever e demonstrar o processo de criação e execução de testes automatizados num projeto desenvolvido com o framework Laravel, utilizando a ferramenta PHPUnit.

O principal propósito é compreender a importância dos testes para garantir a qualidade, confiabilidade e manutenibilidade do software. Ao validar as funcionalidades de forma automatizada, é possível reduzir a incidência de erros em produção, facilitar processos de refatoração e assegurar que as regras de negócio operem conforme o esperado.

## 2. O Framework PHPUnit no Ecossistema Laravel

O PHPUnit é o framework de testes de facto para a linguagem PHP. Criado por Sebastian Bergmann, ele pertence à família xUnit, que estabeleceu os padrões para frameworks de testes de unidade automatizados. O seu objetivo central é permitir que os desenvolvedores verifiquem o comportamento de pequenas porções de código (unidades, como métodos ou classes) de forma isolada.

No ecossistema Laravel, o PHPUnit já vem nativamente integrado e pré-configurado, simplificando drasticamente o processo de escrita de testes. As principais vantagens dessa integração são:

- **Estrutura de Diretórios Pronta:** O Laravel já fornece os diretórios `/tests/Unit` para testes de unidade e `/tests/Feature` para testes funcionais e de integração.
- **Classe TestCase Abstrata:** Uma classe base que inicializa a aplicação para cada teste, fornecendo métodos auxiliares (helpers) para simular requisições HTTP, interagir com o banco de dados e realizar asserções.
- **Gerenciamento via Composer:** O PHPUnit é incluído como uma dependência de desenvolvimento no ficheiro `composer.json`, facilitando a sua instalação e atualização.

## 3. Fundamentos e Categorização dos Testes

Os testes realizados com PHPUnit podem ser classificados sob diferentes perspetivas, que ajudam a entender o seu propósito e escopo.

### i. Perspetiva: Técnicas de Teste

**Teste de Caixa-Branca (White-Box Testing):** É a principal abordagem do PHPUnit. Os testes são escritos com pleno conhecimento da estrutura interna e da lógica do código-fonte.

**Justificativa:** O desenvolvedor precisa de conhecer os métodos, os caminhos lógicos e as condicionais para criar casos de teste que cubram todos os cenários relevantes, incluindo os valores-limite e os fluxos de exceção, como foi feito na nossa classe `DiscountService`.

### ii. Perspetiva: Níveis de Teste

- **Teste de Unidade (Unit Testing):** Focado em isolar e verificar a menor parte testável de um software, geralmente um método ou uma classe. No nosso projeto, o ficheiro `tests/Unit/DiscountServiceTest.php` é um exemplo puro.
- **Teste de Integração/Funcional (Feature Testing):** Focado em verificar a interação entre múltiplos componentes do sistema (Controller, Service, Model, Banco de Dados) para validar uma funcionalidade completa. Residem em `/tests/Feature`.

### iii. Perspetiva: Tipos de Teste

**Testes Funcionais:** É o tipo predominante. O objetivo é garantir que o software se comporte de acordo com as especificações e regras de negócio definidas.

**Exemplo:** Um teste que valida se um cupom de desconto é aplicado corretamente ao preço final de um produto está a verificar uma regra de negócio funcional.

## 4. Instalação e Execução do Projeto

Este projeto utiliza o Laravel Sail, o ambiente de desenvolvimento oficial do Laravel baseado em Docker. Garanta que tem o Docker Desktop instalado.

### 1. Clonar o Repositório

```bash
git clone [URL_DO_SEU_REPOSITORIO]
cd [NOME_DA_PASTA_DO_PROJETO]
```

### 2. Iniciar os Contêineres

```bash
# Copia o ficheiro de ambiente e inicia os contêineres Docker
cp .env.example .env
./vendor/bin/sail up -d
```

### 3. Instalar Dependências e Preparar a Aplicação

```bash
# Instala as dependências do PHP
./vendor/bin/sail composer install

# Gera a chave da aplicação
./vendor/bin/sail artisan key:generate
```

### 4. Preparar o Banco de Dados

```bash
# Executa as migrations para criar as tabelas e os seeders para popular com dados
./vendor/bin/sail artisan migrate:fresh --seed
```

### 5. Executar os Testes

Para rodar a suíte de testes completa, execute:

```bash
./vendor/bin/sail artisan test
```

**Exemplo de Saída no Terminal:**

```
   PASS  Tests\Unit\DiscountServiceTest
  ✓ it applies correct discount at all boundaries
  ✓ it covers all paths of the loyalty discount cause effect graph
  ✓ it transitions discount rate correctly across all states
  ✓ it handles common user errors when applying coupon
  ✓ it calculates the final price for multiple complex scenarios

  Tests:  5 passed
  Time:   0.35s
```

## 5. Estratégias e Implementação dos Casos de Teste

A seguir, são demonstradas 5 estratégias de derivação de casos de teste aplicadas à classe `DiscountService`.

### 5.1. Análise de Valor Limite

Esta técnica foca em testar os valores que estão nas fronteiras das partições de equivalência, onde a probabilidade de encontrar erros é maior.

**Método Alvo:** `calculateTieredDiscount()`, que aplica descontos progressivos com base no valor.

```php
// Em tests/Unit/DiscountServiceTest.php
public function it_applies_correct_discount_at_all_boundaries(): void
{
    // Limite inferior exato da primeira faixa
    $this->assertEquals(5.0, $this->service->calculateTieredDiscount(100.00));

    // Imediatamente abaixo do limite
    $this->assertEquals(0.0, $this->service->calculateTieredDiscount(99.99));
    
    // Outro limite
    $this->assertEquals(24.9995, $this->service->calculateTieredDiscount(499.99));
}
```

### 5.2. Grafo de Causa e Efeito

Esta técnica é ideal para testar combinações de múltiplas condições de entrada (causas) que produzem um resultado (efeito). O processo envolve identificar as causas, os efeitos, visualizar a lógica num grafo ou fluxograma e, finalmente, derivar os casos de teste numa Tabela de Decisão.

**Método Alvo:** `calculateLoyaltyDiscountRate()`, onde o status do cliente e se é a sua primeira compra determinam o desconto.

**Causas:**
- C1: Cliente é membro (`isMember`)
- C2: É a primeira compra (`isFirstPurchase`)

**Efeitos:**
- E1: Desconto de 15%
- E2: Desconto de 10%
- E3: Desconto de 5%
- E4: Desconto de 0%

**Fluxograma Lógico do Grafo:**

```
          [ Início ]
               |
               v
      +-----------------+
      |  isMember? (C1) |
      +-----------------+
         |            |
      (Sim)|         (Não)|
         v            v
+-----------------+  +-----------------+
| isFirstPurchase?|  | isFirstPurchase?|
|      (C2)       |  |      (C2)       |
+-----------------+  +-----------------+
   |         |          |         |
(Sim)|      (Não)|      (Sim)|      (Não)|
   v         v          v         v
+-------+ +-------+  +-------+  +------+
| 15%   | | 10%   |  | 5%    |  | 0%   |
|(E1)   | |(E2)   |  |(E3)   |  |(E4)  |
+-------+ +-------+  +-------+  +------+
```

**Tabela de Decisão:**

| Regra | C1: isMember | C2: isFirstPurchase | Efeito (Desconto) | Caso de Teste no Código |
| :--- | :---: | :---: | :---: | :--- |
| 1 | true | true | 15% (E1) | Cenário 1 |
| 2 | true | false | 10% (E2) | Cenário 2 |
| 3 | false | true | 5% (E3) | Cenário 3 |
| 4 | false | false | 0% (E4) | Cenário 4 |

Cada linha da Tabela de Decisão se traduz diretamente num caso de teste:

```php
// Em tests/Unit/DiscountServiceTest.php
public function it_covers_all_paths_of_the_loyalty_discount_cause_effect_graph(): void
{
    // Cenário 1: Membro E Primeira Compra -> Efeito: 15%
    $this->assertEquals(0.15, $this->service->calculateLoyaltyDiscountRate(true, true));

    // Cenário 2: Membro E NÃO Primeira Compra -> Efeito: 10%
    $this->assertEquals(0.10, $this->service->calculateLoyaltyDiscountRate(true, false));

    // Cenário 3: NÃO Membro E Primeira Compra -> Efeito: 5%
    $this->assertEquals(0.05, $this->service->calculateLoyaltyDiscountRate(false, true));

    // Cenário 4: NÃO Membro E NÃO Primeira Compra -> Efeito: 0%
    $this->assertEquals(0.0, $this->service->calculateLoyaltyDiscountRate(false, false));
}
```

### 5.3. Teste de Transição de Estado

Verifica se o sistema se comporta corretamente ao transitar entre diferentes "estados" lógicos.

**Método Alvo:** `calculateBulkDiscountRate()`, onde a quantidade de itens muda o estado do desconto (sem desconto, 10%, 15%).

```php
// Em tests/Unit/DiscountServiceTest.php
public function it_transitions_discount_rate_correctly_across_all_states(): void
{
    // Estado 1: Sem desconto (4 itens)
    $this->assertEquals(0.0, $this->service->calculateBulkDiscountRate(4));
    // Transição para o Estado 2 (5-9 itens)
    $this->assertEquals(0.10, $this->service->calculateBulkDiscountRate(5));
    // Transição para o Estado 3 (10+ itens)
    $this->assertEquals(0.15, $this->service->calculateBulkDiscountRate(10));
}
```

### 5.4. Error-Guessing (Adivinhação de Erros)

Baseia-se na experiência para antecipar falhas comuns, como entradas de utilizador mal formatadas.

**Método Alvo:** `applyFixedCoupon()`, que deve ser robusto a erros de digitação.

```php
// Em tests/Unit/DiscountServiceTest.php
public function it_handles_common_user_errors_when_applying_coupon(): void
{
    // Erro Adivinhado 1: Espaços em branco acidentais.
    $this->assertEquals(10.0, $this->service->applyFixedCoupon(' PROMO10 '));
    
    // Erro Adivinhado 2: Caixa de texto diferente.
    $this->assertEquals(10.0, $this->service->applyFixedCoupon('promo10'));
    
    // Erro Adivinhado 3: Cupom inexistente.
    $this->assertEquals(0.0, $this->service->applyFixedCoupon('CUPOM_INVALIDO'));
}
```

### 5.5. Teste Funcional Sistémico

Verifica o fluxo completo e a integração de várias regras de negócio para produzir um resultado final.

**Método Alvo:** `getFinalPrice()`, que orquestra todos os outros cálculos e aplica a regra principal (o maior desconto prevalece).

```php
// Em tests/Unit/DiscountServiceTest.php
public function it_calculates_the_final_price_for_multiple_complex_scenarios(): void
{
    // Cenário 1: Desconto de volume é o maior
    $finalPrice1 = $this->service->getFinalPrice(
        baseAmount: 800.0,
        itemCount: 12,
        isMember: true,
        isFirstPurchase: false,
        couponCode: 'PROMO50'
    );
    $this->assertEquals(680.0, $finalPrice1);

    // Cenário 2: Desconto de lealdade é o maior
    $finalPrice2 = $this->service->getFinalPrice(
        baseAmount: 200.0,
        itemCount: 4,
        isMember: true,
        isFirstPurchase: true,
        couponCode: 'PROMO10'
    );
    $this->assertEquals(170.0, $finalPrice2);
}
```

## 6. Conclusão

Este trabalho prático demonstrou como a integração entre Laravel e PHPUnit oferece um ambiente robusto e produtivo para a automação de testes. A aplicação de diferentes estratégias, desde a Análise de Valor Limite em testes de unidade até a verificação de fluxos completos em Testes Funcionais Sistémicos, permite construir uma suíte de testes abrangente.

O uso sistemático de testes automatizados é uma prática indispensável no desenvolvimento de software moderno. Ele não apenas assegura que o sistema funcione conforme os requisitos, mas também fornece uma rede de segurança que aumenta a confiança da equipa para realizar melhorias e refatorações, resultando num código de maior qualidade e mais fácil de manter a longo prazo.