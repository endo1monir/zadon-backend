<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Requests\Api\Vendor\GenerateTextRequest;
use App\Http\Traits\HandlesAiErrors;
use App\Models\Product;
use App\Support\AiAssistant;
use Illuminate\Http\JsonResponse;

class AiController extends BaseController
{
    use HandlesAiErrors;

    public function __construct(private AiAssistant $aiAssistant) {}

    public function generateText(GenerateTextRequest $request): JsonResponse
    {
        return $this->callAi(fn (): JsonResponse => $this->successReturn([
            'text' => $this->aiAssistant->generateText(
                $request->validated('prompt'),
                $request->validated('system_prompt'),
            ),
        ]));
    }

    /**
     * Suggest descriptions for one of the vendor's products. Nothing is saved;
     * the vendor reviews the suggestion and saves it through the product update endpoint.
     */
    public function productDescriptions(Product $product): JsonResponse
    {
        $product = $this->managedStore()->products()->findOrFail($product->id);

        return $this->callAi(fn (): JsonResponse => $this->successReturn([
            'product_id' => $product->id,
            'descriptions' => $this->aiAssistant->generateProductDescriptions($product),
        ]));
    }
}
