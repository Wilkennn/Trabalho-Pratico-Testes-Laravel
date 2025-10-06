<?php

namespace App\Http\Controllers;

use App\Services\DiscountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DiscountController extends Controller
{
    /**
     * Calcula o preço final com base nas regras de desconto.
     *
     * @param Request $request
     * @param DiscountService $discountService
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculate(Request $request, DiscountService $discountService)
    {
        $validator = Validator::make($request->all(), [
            'base_amount' => 'required|numeric|min:0',
            'is_member' => 'required|boolean',
            'is_first_purchase' => 'required|boolean',
            'coupon_code' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        $finalPrice = $discountService->getFinalPrice(
            $validated['base_amount'],
            $validated['is_member'],
            $validated['is_first_purchase'],
            $validated['coupon_code'] ?? null
        );

        return response()->json([
            'original_price' => (float) $validated['base_amount'],
            'final_price' => $finalPrice,
        ]);
    }
}
