<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @php
        $state = $getRecord()->quotation_file_path;
        $vendor = $getRecord()->vendor;
        $quoteNumber = $getRecord()->quotation_number;
    @endphp

    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800/50">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Quotation Details
        </h3>

        <div class="mt-2 space-y-2">
            <p><strong>Vendor:</strong> {{ $vendor?->name ?? 'N/A' }}</p>
            <p><strong>Quotation #:</strong> {{ $quoteNumber ?? 'N/A' }}</p>

            @if ($state)
                <div class="mt-4">
                    <a href="{{ Storage::url($state) }}" 
                       target="_blank" 
                       class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-500 focus:ring-4 focus:outline-none focus:ring-primary-300">
                        <x-heroicon-o-document-arrow-down class="w-5 h-5"/>
                        View & Download Quotation
                    </a>
                </div>
            @else
                <p class="mt-4 text-sm text-gray-500">No quotation document was uploaded.</p>
            @endif
        </div>
    </div>
</x-dynamic-component>