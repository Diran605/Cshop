<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\StockInReceipt;
use App\Models\StockInItem;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockInImport implements ToCollection, WithHeadingRow, WithValidation
{
    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {
            foreach ($rows as $row) {
                $product = Product::where('name', $row['product_name'])->first();

                if (!$product) {
                    continue;
                }

                $totalCost = (float) $row['unit_cost'] * (int) $row['quantity'];

                $receipt = StockInReceipt::create([
                    'date' => $row['date'],
                    'notes' => $row['notes'] ?? null,
                    'total_quantity' => (int) $row['quantity'],
                    'total_cost' => $totalCost,
                    'created_by' => Auth::id(),
                ]);

                StockInItem::create([
                    'stock_in_receipt_id' => $receipt->id,
                    'product_id' => $product->id,
                    'quantity' => (int) $row['quantity'],
                    'remaining_quantity' => (int) $row['quantity'],
                    'cost_price' => (float) $row['unit_cost'],
                    'line_total' => $totalCost,
                    'supplier_name' => $row['supplier'] ?? null,
                    'batch_ref_no' => $row['batch_ref_no'] ?? null,
                    'expiry_date' => !empty($row['expiry_date']) ? $row['expiry_date'] : null,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function rules(): array
    {
        return [
            'date' => 'required|date',
            'product_name' => 'required|string|exists:products,name',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'required|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'batch_ref_no' => 'nullable|string|max:100',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ];
    }
}
