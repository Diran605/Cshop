<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\SalesReceipt;
use App\Models\SalesReceiptItem;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesImport implements ToCollection, WithHeadingRow, WithValidation
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

                $totalAmount = (float) $row['unit_price'] * (int) $row['quantity'];

                $receipt = SalesReceipt::create([
                    'date' => $row['date'],
                    'customer_name' => $row['customer_name'] ?? null,
                    'payment_method' => strtolower($row['payment_method'] ?? 'cash'),
                    'amount_paid' => (float) ($row['amount_paid'] ?? $totalAmount),
                    'change_due' => max(0, (float) ($row['amount_paid'] ?? $totalAmount) - $totalAmount),
                    'notes' => $row['notes'] ?? null,
                    'sub_total' => $totalAmount,
                    'discount_total' => 0,
                    'grand_total' => $totalAmount,
                    'total_quantity' => (int) $row['quantity'],
                    'created_by' => Auth::id(),
                ]);

                SalesReceiptItem::create([
                    'sales_receipt_id' => $receipt->id,
                    'product_id' => $product->id,
                    'quantity' => (int) $row['quantity'],
                    'unit_price' => (float) $row['unit_price'],
                    'total_price' => $totalAmount,
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
            'unit_price' => 'required|numeric|min:0',
            'customer_name' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string',
            'amount_paid' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ];
    }
}
