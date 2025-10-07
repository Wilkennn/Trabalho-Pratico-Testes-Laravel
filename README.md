# **Trabalho Prático de Teste de Software**

## **Demonstração do Framework PHPUnit com Laravel**

| | |
| :--- | :--- |
| **Instituição:** | Pontifícia Universidade Católica de Minas Gerais |
| **Curso:** | Engenharia de Software |
| **Professor:** | Cleiton Tavares |
| **Grupo:** | PHPUnit |
| **Integrantes:** | Arthur Freitas Jardim, Carlos Henrique Neimar, Luiz Filipe Nery , João Victor Temponi, Wilken Moreira |

-----

## **1. Introdução**

Este trabalho tem como objetivo descrever e demonstrar o processo de criação e execução de **testes automatizados** em um projeto desenvolvido com o framework **Laravel**, utilizando a ferramenta **PHPUnit**.

O principal propósito é compreender a importância dos testes para garantir a **qualidade, confiabilidade e manutenibilidade** do software. Ao validar as funcionalidades de forma automatizada, é possível reduzir a incidência de erros em produção, facilitar processos de refatoração e assegurar que as regras de negócio operem conforme o esperado.

## **2. O Framework PHPUnit no Ecossistema Laravel**

O **PHPUnit** é o framework de testes *de facto* para a linguagem PHP. Criado por Sebastian Bergmann, ele pertence à família **xUnit**, que estabeleceu os padrões para frameworks de testes de unidade automatizados. Seu objetivo central é permitir que desenvolvedores verifiquem o comportamento de pequenas porções de código (unidades, como métodos ou classes) de forma isolada.

No ecossistema **Laravel**, o PHPUnit já vem nativamente integrado e pré-configurado, simplificando drasticamente o processo de escrita de testes. As principais vantagens dessa integração são:

  - **Estrutura de Diretórios Pronta:** O Laravel já fornece os diretórios `/tests/Unit` para testes de unidade e `/tests/Feature` para testes funcionais e de integração.
  - **Classe `TestCase` Abstrata:** Uma classe base que inicializa a aplicação para cada teste, fornecendo métodos auxiliares (`helpers`) para simular requisições HTTP, interagir com o banco de dados e realizar asserções.
  - **Gerenciamento via Composer:** O PHPUnit é incluído como uma dependência de desenvolvimento no arquivo `composer.json`, facilitando sua instalação e atualização.

## **3. Fundamentos e Categorização dos Testes**

Os testes realizados com PHPUnit podem ser classificados sob diferentes perspectivas, que ajudam a entender seu propósito e escopo.

#### **i. Perspectiva: Técnicas de Teste**

  - **Teste de Caixa-Branca (White-Box Testing):** É a principal abordagem do PHPUnit. Os testes são escritos com pleno conhecimento da estrutura interna e da lógica do código-fonte.
  - **Justificativa:** O desenvolvedor precisa conhecer os métodos, os caminhos lógicos, os loops e as condicionais para criar casos de teste que cubram todos os cenários relevantes, incluindo os valores-limite e os fluxos de exceção.

#### **ii. Perspectiva: Níveis de Teste**

  - **Teste de Unidade (Unit Testing):** Focado em isolar e verificar a menor parte testável de um software, geralmente um método ou uma classe. No Laravel, esses testes residem em `/tests/Unit` e não inicializam o framework completo, sendo mais rápidos.
  - **Teste de Integração/Funcional (Feature Testing):** Focado em verificar a interação entre múltiplos componentes do sistema (Controller, Service, Model, Banco de Dados) para validar uma funcionalidade completa, como uma rota de API ou o envio de um formulário. Residem em `/tests/Feature`.

#### **iii. Perspectiva: Tipos de Teste**

  - **Testes Funcionais:** É o tipo predominante. O objetivo é garantir que o software se comporte de acordo com as especificações e regras de negócio definidas.
  - **Exemplo:** Um teste que valida se um cupom de desconto é aplicado corretamente ao preço final de um produto está verificando uma regra de negócio funcional.

## **4. Instalação e Execução dos Testes**

Como o PHPUnit já vem integrado ao Laravel, não há necessidade de instalação manual. A execução é centralizada através do **Artisan**, o utilitário de linha de comando do Laravel.

#### **Comandos de Execução**

1.  **Executar todos os testes do projeto:**

    ```bash
    php artisan test
    ```

2.  **Executar apenas uma classe de teste específica:**

    ```bash
    php artisan test --filter=UserTest
    ```

3.  **Executar um único método de teste:**

    ```bash
    php artisan test --filter=UserTest::test_usuario_pode_ser_cadastrado
    ```

#### **Exemplo de Saída no Terminal**

Uma execução bem-sucedida exibe um feedback claro sobre os testes que passaram:

```plaintext
PASS  Tests\Unit\DiscountServiceTest
✓ tiered discount with boundary values
✓ bulk discount with state transition

PASS  Tests\Feature\UserTest
✓ usuario pode ser cadastrado
✓ login com credenciais validas

Tests:  4 passed
Time:   0.87s
```

-----

## **5. Estratégias e Implementação dos Casos de Teste**

A seguir, são demonstradas diferentes estratégias de derivação de casos de teste aplicadas a um contexto prático em Laravel.

### **5.1. Teste de Unidade com Análise de Valor Limite**

Esta técnica foca em testar os valores que estão nas fronteiras das partições de equivalência, onde a probabilidade de encontrar erros é maior.

  - **Método Alvo:** `calculateTieredDiscount()`, um método que aplica descontos progressivos com base no valor total.

<!-- end list -->

```php
// Em tests/Unit/DiscountServiceTest.php

use App\Services\DiscountService;
use PHPUnit\Framework\TestCase;

class DiscountServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DiscountService();
    }

    /**
     * Testa os valores-limite para descontos progressivos.
     * - Exatamente R$ 100.00 deve ter R$ 10 de desconto.
     * - Um centavo abaixo (R$ 99.99) não deve ter desconto.
     */
    public function test_tiered_discount_with_boundary_values(): void
    {
        // Limite inferior exato
        $this->assertEquals(10.0, $this->service->calculateTieredDiscount(100.00));

        // Imediatamente abaixo do limite
        $this->assertEquals(0.0, $this->service->calculateTieredDiscount(99.99));
        
        // Outro limite
        $this->assertEquals(49.999, $this->service->calculateTieredDiscount(499.99));
    }
}
```

### **5.2. Teste Funcional de Registro de Usuário**

Este teste de integração verifica o fluxo completo de registro de um novo usuário, desde a requisição HTTP até a persistência no banco de dados.

  - **Objetivo:** Garantir que a rota `POST /register` cria um novo usuário e redireciona corretamente.

<!-- end list -->

```php
// Em tests/Feature/UserRegistrationTest.php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase; // Reseta o banco de dados a cada teste

    /**
     * Testa se um usuário pode ser criado com dados válidos.
     */
    public function test_usuario_pode_ser_cadastrado(): void
    {
        // 1. Ação: Envia uma requisição POST para a rota de registro
        $response = $this->post('/register', [
            'name' => 'Usuário Teste',
            'email' => 'teste@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // 2. Asserções (Verificações)
        // Garante que o usuário foi redirecionado após o sucesso
        $response->assertStatus(302); 
        $response->assertRedirect('/home');

        // Garante que o registro do usuário existe no banco de dados
        $this->assertDatabaseHas('users', [
            'email' => 'teste@example.com',
        ]);
    }
}
```

### **5.3. Teste Funcional de Autenticação (Adivinhação de Erros)**

A técnica de *Error Guessing* (Adivinhação de Erros) baseia-se na experiência para antecipar falhas comuns. No caso de um login, os erros mais comuns são credenciais inválidas.

  - **Objetivo:** Validar que um usuário com credenciais corretas consegue se autenticar e que um com credenciais incorretas é bloqueado.

<!-- end list -->

```php
// Em tests/Feature/AuthenticationTest.php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Cenário de sucesso: login com credenciais válidas.
     */
    public function test_login_com_credenciais_validas(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt($password = 'senha-super-segura')
        ]);

        // 2. Ação: Tenta fazer login
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        // 3. Asserções
        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($user); // Verifica se o usuário está autenticado
    }

    /**
     * Cenário de falha: login com senha incorreta.
     */
    public function test_login_falha_com_senha_invalida(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'senha-errada',
        ]);

        $response->assertSessionHasErrors('email'); // Verifica se há erro na sessão
        $this->assertGuest(); // Garante que o usuário não foi autenticado
    }
}
```

## **6. Conclusão**

Este trabalho prático demonstrou como a integração entre Laravel e PHPUnit oferece um ambiente robusto e produtivo para a automação de testes. A aplicação de diferentes estratégias, desde a **Análise de Valor Limite** em testes de unidade até a verificação de fluxos completos em **Testes Funcionais**, permite construir uma suíte de testes abrangente.

O uso sistemático de testes automatizados é uma prática indispensável no desenvolvimento de software moderno. Ele não apenas assegura que o sistema funcione conforme os requisitos, mas também fornece uma rede de segurança que aumenta a confiança da equipe para realizar melhorias e refatorações, resultando em um código de maior qualidade e mais fácil de manter a longo prazo.