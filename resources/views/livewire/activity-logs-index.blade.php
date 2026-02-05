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
                                    </tr>
                                @endforeach

                                @if ($logs->isEmpty())
                                    <tr>
                                        <td colspan="6" class="ui-table-empty">{{ __('No activity found.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
