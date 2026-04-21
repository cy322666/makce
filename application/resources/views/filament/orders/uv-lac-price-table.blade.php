<div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
    <div class="mb-3 flex flex-wrap items-baseline justify-between gap-2">
        <div>
            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">прайс уф-лака</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                текущий формат: {{ $current_format ?? '-' }},
                листов на печать: {{ $current_pages ?? '-' }},
                текущая сумма: {{ $current_rate ?? '-' }}
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border-separate border-spacing-0 text-sm">
            <thead>
                <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <th class="border-b border-gray-200 px-3 py-2 dark:border-gray-700">формат</th>
                    <th class="border-b border-gray-200 px-3 py-2 dark:border-gray-700">прайс</th>
                    <th class="border-b border-gray-200 px-3 py-2 dark:border-gray-700">поток</th>
                    <th class="border-b border-gray-200 px-3 py-2 dark:border-gray-700">тип лака</th>
                    <th class="border-b border-gray-200 px-3 py-2 dark:border-gray-700">диапазон</th>
                    <th class="border-b border-gray-200 px-3 py-2 dark:border-gray-700">статус</th>
                </tr>
            </thead>
            <tbody>
                @forelse (($rows ?? []) as $row)
                    <tr @class([
                        'bg-emerald-50 text-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-100' => $row['active'] ?? false,
                        'text-gray-800 dark:text-gray-200' => ! ($row['active'] ?? false),
                    ])>
                        <td class="border-b border-gray-100 px-3 py-2 dark:border-gray-800">{{ $row['format'] ?? '-' }}</td>
                        <td class="border-b border-gray-100 px-3 py-2 font-semibold dark:border-gray-800">{{ $row['price'] ?? '-' }}</td>
                        <td class="border-b border-gray-100 px-3 py-2 dark:border-gray-800">{{ $row['process_type'] ?? '-' }}</td>
                        <td class="border-b border-gray-100 px-3 py-2 dark:border-gray-800">{{ $row['lacquer_type'] ?? '-' }}</td>
                        <td class="border-b border-gray-100 px-3 py-2 dark:border-gray-800">{{ $row['run_range'] ?? '-' }}</td>
                        <td class="border-b border-gray-100 px-3 py-2 dark:border-gray-800">
                            {{ ($row['active'] ?? false) ? 'подходит' : 'прайс' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">
                            прайс уф-лака не найден
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
