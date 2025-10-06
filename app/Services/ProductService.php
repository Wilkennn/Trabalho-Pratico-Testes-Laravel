<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

/**
 * Classe de serviço para encapsular a lógica de negócio de produtos.
 */
class ProductService
{
    /**
     * Obtém todos os produtos.
     */
    public function getAllProducts(): Collection
    {
        return Product::all();
    }

    /**
     * Cria um novo produto com os dados fornecidos.
     *
     * @param array $data Dados validados para o novo produto.
     */
    public function createProduct(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Atualiza um produto existente.
     *
     * @param array $data Dados validados para a atualização.
     */
    public function updateProduct(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh(); // Retorna o modelo atualizado.
    }

    /**
     * Apaga um produto.
     */
    public function deleteProduct(Product $product): void
    {
        $product->delete();
    }
}
