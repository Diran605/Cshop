<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $category = null;
        if (!empty($row['category'])) {
            $category = Category::where('name', $row['category'])->first();
        }

        $product = new Product([
            'name' => $row['name'],
            'category_id' => $category ? $category->id : null,
            'cost_price' => (float) ($row['cost_price'] ?? 0),
            'min_selling_price' => (float) ($row['min_selling_price'] ?? 0),
            'selling_price' => (float) ($row['selling_price'] ?? 0),
            'bulk_enabled' => strtolower($row['bulk_enabled'] ?? 'no') === 'yes' || strtolower($row['bulk_enabled'] ?? 'no') === 'true' || (int) ($row['bulk_enabled'] ?? 0) === 1,
            'bulk_type_id' => !empty($row['bulk_type_id']) ? (int) $row['bulk_type_id'] : null,
            'status' => in_array(strtolower($row['status'] ?? 'active'), ['active', 'yes', 'true', '1']) ? 'active' : 'inactive',
            'created_by' => Auth::id(),
        ]);

        $product->save();

        $openingStockQty = (int) ($row['opening_stock_qty'] ?? 0);
        $openingCostPrice = (float) ($row['opening_cost_price'] ?? 0);
        $expiryDate = !empty($row['expiry_date']) ? $row['expiry_date'] : null;

        if ($openingStockQty > 0) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            ProductStock::create([
                'branch_id' => (int) (Auth::user()->branch_id ?? 1),
                'product_id' => $product->id,
                'current_stock' => $openingStockQty,
                'minimum_stock' => 0,
                'cost_price' => $openingCostPrice,
            ]);

            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        return $product;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category' => 'nullable|string',
            'cost_price' => 'nullable|numeric|min:0',
            'min_selling_price' => 'nullable|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'bulk_enabled' => 'nullable|string',
            'bulk_type_id' => 'nullable|integer',
            'status' => 'nullable|string',
            'opening_stock_qty' => 'nullable|integer|min:0',
            'opening_cost_price' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
        ];
    }
}
