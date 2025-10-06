<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    protected ProductService $productService;

    /**
     * Injeta o serviço de produtos no controller.
     */
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Exibe uma lista de todos os produtos.
     */
    public function index(): JsonResponse
    {
        $products = $this->productService->getAllProducts();
        return response()->json($products);
    }

    /**
     * Armazena um novo produto no banco de dados.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $product = $this->productService->createProduct($validatedData);

        return response()->json($product, Response::HTTP_CREATED);
    }

    /**
     * Exibe um produto específico.
     * O Laravel injeta o modelo automaticamente (Route-Model Binding).
     */
    public function show(Product $product): JsonResponse
    {
        return response()->json($product);
    }

    /**
     * Atualiza um produto específico no banco de dados.
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'base_price' => 'sometimes|required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $updatedProduct = $this->productService->updateProduct($product, $validatedData);

        return response()->json($updatedProduct);
    }

    /**
     * Remove um produto específico do banco de dados.
     */
    public function destroy(Product $product): Response
    {
        $this->productService->deleteProduct($product);

        return response()->noContent(); // Retorna uma resposta 204 No Content.
    }
}
