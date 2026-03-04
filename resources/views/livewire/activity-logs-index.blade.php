<div>
<div class="ui-page">
    <div class="ui-page-container">
        <div class="mb-6">
            <h2 class="ui-page-title">{{ __('Activity Logs') }}</h2>
            <div class="ui-page-subtitle">{{ __('Audit trail of system activities.') }}</div>
        </div>

        <div class="ui-card">
            <div class="ui-card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                    <div>
                        <label class="ui-label">{{ __('From') }}</label>
                        <input type="date" wire:model.live="date_from" class="mt-1 ui-input" />
                    </div>

                    <div>
                        <label class="ui-label">{{ __('To') }}</label>
                        <input type="date" wire:model.live="date_to" class="mt-1 ui-input" />
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Branch') }}</label>
                        <select wire:model.live="branch_id" class="mt-1 ui-select" @if (! $isSuperAdmin) disabled @endif>
                            @if ($isSuperAdmin)
                                <option value="0">{{ __('All') }}</option>
                            @endif
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="ui-label">{{ __('User') }}</label>
                        <select wire:model.live="user_id" class="mt-1 ui-select">
                            <option value="0">{{ __('All') }}</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Action') }}</label>
                        <input type="text" wire:model.debounce.300ms="action" class="mt-1 ui-input" placeholder="e.g. user.created" />
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Search') }}</label>
                        <input type="text" wire:model.debounce.300ms="search" placeholder="{{ __('Description / Subject') }}" class="mt-1 ui-input" />
                    </div>
                </div>

                <div class="mt-6 overflow-x-auto">
                    <div class="ui-table-wrap">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Branch') }}</th>
                                    <th>{{ __('Action') }}</th>
                                    <th>{{ __('Subject') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('User') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($logs as $log)
                                    <tr wire:key="act-{{ $log->id }}">
                                        <td>{{ optional($log->created_at)->format('Y-m-d H:i') }}</td>
                                        <td>{{ $log->branch?->name ?? '-' }}</td>
                                        <td class="font-medium text-slate-900">{{ $log->action }}</td>
                                        <td class="text-slate-700">
                                            @php
                                                $st = $log->subject_type ? class_basename($log->subject_type) : null;
                                                $sid = $log->subject_id ? ('#' . $log->subject_id) : null;
                                            @endphp
                                            {{ $st ? ($st . ' ' . ($sid ?? '')) : '-' }}
                                        </td>
                                        <td>{{ $log->description ?? '-' }}</td>
                                        <td>{{ $log->user?->name ?? '-' }}</td>
                                        <td>
                                            <button wire:click="openDetailModal({{ $log->id }})" class="ui-btn-link">
                                                {{ __('View') }}
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach

                                @if ($logs->isEmpty())
                                    <tr>
                                        <td colspan="7" class="ui-table-empty">{{ __('No activity found.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    @if ($logs->hasPages())
                        <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="text-sm text-slate-600">
                                {{ __('Showing') }} {{ $logs->firstItem() }} {{ __('to') }} {{ $logs->lastItem() }} {{ __('of') }} {{ $logs->total() }} {{ __('results') }}
                            </div>
                            {{ $logs->links('pagination::tailwind') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Audit Detail Modal -->
<div x-data="{ show: @entangle('show_detail_modal') }" x-show="show" x-cloak style="display: none;">
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg font-semibold leading-6 text-slate-900" id="modal-title">
                                {{ __('Activity Details') }}
                            </h3>
                            <div class="mt-4 space-y-4">
                                @if ($selected_log)
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-sm font-medium text-slate-500">{{ __('Date & Time') }}</label>
                                            <div class="mt-1 text-sm text-slate-900">
                                                {{ optional($selected_log->created_at)->format('Y-m-d H:i:s') }}
                                            </div>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-slate-500">{{ __('Branch') }}</label>
                                            <div class="mt-1 text-sm text-slate-900">
                                                {{ $selected_log->branch?->name ?? '-' }}
                                            </div>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-slate-500">{{ __('User') }}</label>
                                            <div class="mt-1 text-sm text-slate-900">
                                                {{ $selected_log->user?->name ?? '-' }}
                                            </div>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-slate-500">{{ __('IP Address') }}</label>
                                            <div class="mt-1 text-sm text-slate-900">
                                                {{ $selected_log->ip_address ?? '-' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="text-sm font-medium text-slate-500">{{ __('Action') }}</label>
                                        <div class="mt-1 p-2 bg-slate-50 rounded text-sm text-slate-900 font-mono">
                                            {{ $selected_log->action }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="text-sm font-medium text-slate-500">{{ __('Subject') }}</label>
                                        <div class="mt-1 text-sm text-slate-900">
                                            @php
                                                $st = $selected_log->subject_type ? class_basename($selected_log->subject_type) : null;
                                                $sid = $selected_log->subject_id ? ('#' . $selected_log->subject_id) : null;
                                            @endphp
                                            {{ $st ? ($st . ' ' . ($sid ?? '')) : '-' }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="text-sm font-medium text-slate-500">{{ __('Description') }}</label>
                                        <div class="mt-1 text-sm text-slate-900">
                                            {{ $selected_log->description ?? '-' }}
                                        </div>
                                    </div>

                                    @if ($selected_log->meta && is_array($selected_log->meta) && count($selected_log->meta) > 0)
                                        <div>
                                            <label class="text-sm font-medium text-slate-500">{{ __('Additional Details') }}</label>
                                            <div class="mt-1 p-3 bg-slate-50 rounded text-sm text-slate-900">
                                                <pre class="whitespace-pre-wrap font-mono text-xs">{{ json_encode($selected_log->meta, JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="button" wire:click="closeDetailModal" class="ui-btn-primary">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
