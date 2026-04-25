<div>
    {{-- Product Dropdown --}}
    <div>
        @if (!$selectedProduct)
            <div class="flex gap-2 mb-2">
                <div class="w-1/3">
                    <select wire:model.live="categoryId" class="ui-select h-[42px]">
                        <option value="">{{ __('All Categories') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-data="{ open: false }" @click.away="open = false" class="relative w-2/3">
                    <input type="text" wire:model.live.debounce.300ms="search" @focus = " open = true "
                        @keydown = "open = true " class="ui-input h-[42px] w-full" placeholder="Search product..."
                        autocomplete="off" />

                    @if (count($searchableProducts) > 0)
                        <div x-show="open" x-transition style="display: none;"
                            class="border border-slate-300 rounded-md max-h-60 overflow-y-auto absolute z-50 bg-white w-full shadow-xl mt-1">
                            @foreach ($searchableProducts as $product)
                                <button type="button" @click="open = false"
                                    wire:click="selectProduct({{ $product->id }})"
                                    class="w-full text-left px-3 py-2 hover:bg-slate-100 border-b border-slate-100 last:border-b-0">
                                    <div class="font-medium">{{ $product->name }}</div>
                                    <div class="text-xs text-slate-500">
                                        {{ $product->category?->name ?? 'Uncategorized' }}</div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if ($selectedProduct)
            <div class="mb-2 p-2 bg-green-50 border border-green-200 rounded-md">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="font-medium text-green-800">{{ $selectedProduct->name }}</span>
                        <span class="text-xs text-green-600 ml-2">{{ $selectedProduct->category?->name }}</span>
                    </div>
                    <button type="button" wire:click="clearSelection"
                        class="text-green-700 hover:text-green-900 text-sm">Clear</button>
                </div>
            </div>
        @endif
    </div>

    {{-- Batches Panel --}}
    @if ($selectedProduct && count($batches) > 0)
        <div class="mt-4 p-3 border border-slate-200 rounded-lg bg-slate-50">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-semibold text-slate-700">Available Batches ({{ count($batches) }})</h4>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="toggleFifo"
                        class="text-xs px-2 py-1 rounded border {{ $isFifo ? 'bg-purple-100 text-purple-700 border-purple-200' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-100' }}">
                        Auto (FIFO)
                    </button>
                    <div class="flex items-center gap-1">
                        <button type="button" wire:click="prevBatch"
                            class="px-2 py-1 bg-white border border-slate-300 rounded text-slate-600 hover:bg-slate-100 disabled:opacity-50"
                            @if ($isFifo || !$selectedBatchId) disabled @endif>&larr;</button>
                        <button type="button" wire:click="nextBatch"
                            class="px-2 py-1 bg-white border border-slate-300 rounded text-slate-600 hover:bg-slate-100 disabled:opacity-50"
                            @if ($isFifo || !$selectedBatchId) disabled @endif>&rarr;</button>
                    </div>
                </div>
            </div>

            <div class="space-y-2 max-h-40 overflow-y-auto pr-1">
                @foreach ($batches as $batch)
                    @php
                        $borderColor = 'border-slate-200';
                        $bgColor = 'bg-white';
                        $textColor = 'text-slate-800';

                        if ($batch['is_expired']) {
                            if ($batch['has_clearance_discount']) {
                                $borderColor = 'border-amber-400';
                                $bgColor = 'bg-amber-50';
                                $textColor = 'text-amber-900';
                            } else {
                                $borderColor = 'border-red-300';
                                $bgColor = 'bg-red-50';
                                $textColor = 'text-red-800';
                            }
                        } elseif ($batch['is_near_expiry']) {
                            $borderColor = 'border-yellow-300';
                            $bgColor = 'bg-yellow-50';
                            $textColor = 'text-yellow-800';
                        } else {
                            $borderColor = 'border-green-200';
                        }

                        if ($selectedBatchId === $batch['id']) {
                            $borderColor = 'border-purple-500 shadow-sm ring-1 ring-purple-500';
                        }

                        // Expired without clearance = blocked
                        $isBlocked = $batch['is_expired'] && !$batch['has_clearance_discount'];
                    @endphp

                    <div @if (!$isBlocked) wire:click="selectBatch({{ $batch['id'] }})" @endif
                        class="{{ $isBlocked ? 'cursor-not-allowed opacity-50' : 'cursor-pointer hover:opacity-80' }} p-2 rounded border {{ $borderColor }} {{ $bgColor }} transition-opacity">
                        <div class="flex items-center justify-between">
                            <div class="font-medium text-sm {{ $textColor }}">
                                {{ $batch['batch_ref'] }}
                                @if ($batch['is_expired'] && $batch['has_clearance_discount'])
                                    <span
                                        class="ml-1 text-[10px] uppercase bg-amber-200 text-amber-800 px-1 py-0.5 rounded font-bold">Clearance</span>
                                @elseif ($batch['is_expired'])
                                    <span
                                        class="ml-1 text-[10px] uppercase bg-red-100 text-red-600 px-1 py-0.5 rounded font-bold">Expired</span>
                                @elseif ($batch['is_near_expiry'])
                                    <span
                                        class="ml-1 text-[10px] uppercase bg-yellow-100 text-yellow-600 px-1 py-0.5 rounded font-bold">Expiring
                                        Soon</span>
                                @endif
                            </div>
                            <div class="text-sm font-mono {{ $textColor }}">{{ $batch['remaining_qty'] }} left
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-1 text-xs text-slate-500">
                            <div>Exp: {{ $batch['expiry_date'] ?? 'N/A' }}</div>
                            @if ($batch['has_clearance_discount'])
                                <div class="font-semibold text-amber-700">Clearance: XAF
                                    {{ number_format((float) $batch['clearance_price'], 2) }} (max
                                    {{ $batch['clearance_qty'] }})</div>
                            @elseif ($batch['cost_price'])
                                <div>Cost: {{ number_format((float) $batch['cost_price'], 2) }}</div>
                            @endif
                        </div>
                        @if ($isBlocked)
                            <div class="mt-1 text-[10px] text-red-500 italic">No clearance discount — send to clearance
                                first</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @elseif ($selectedProduct)
        <div class="mt-4 p-3 border border-slate-200 rounded-lg bg-orange-50 text-orange-800 text-sm">
            No stock batches available for this product.
        </div>
    @endif
</div>
