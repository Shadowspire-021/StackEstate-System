<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-extrabold text-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent leading-tight">
                {{ __('Installment Plan Templates') }}
            </h2>
            <a href="{{ route('templates.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                + New Template
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif

            @if($templates->isEmpty())
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-indigo-50 flex items-center justify-center">
                        <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">No Templates Yet</h3>
                    <p class="text-sm text-gray-500 mb-6">Create your first installment plan template to get started.</p>
                    <a href="{{ route('templates.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                        Create Template
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($templates as $template)
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h3 class="font-bold text-gray-800">{{ $template->name }}</h3>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider mt-1
                                        {{ match($template->type) {
                                            'equal_split' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                                            'graduated' => 'bg-amber-50 text-amber-700 border border-amber-100',
                                            'balloon' => 'bg-purple-50 text-purple-700 border border-purple-100',
                                            default => 'bg-gray-50 text-gray-700 border border-gray-100',
                                        } }}">
                                        {{ $template->type_label }}
                                    </span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('templates.edit', $template) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold">Edit</a>
                                    <form action="{{ route('templates.destroy', $template) }}" method="POST" onsubmit="return confirm('Delete this template?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-500 hover:text-rose-700 text-sm font-semibold">Delete</button>
                                    </form>
                                </div>
                            </div>

                            @if($template->description)
                                <p class="text-sm text-gray-500 mb-4">{{ $template->description }}</p>
                            @endif

                            <div class="space-y-2 text-xs text-gray-600">
                                <div class="flex justify-between">
                                    <span class="font-semibold">Duration:</span>
                                    <span>{{ $template->duration_months }} months</span>
                                </div>

                                @if($template->type === 'graduated' && isset($template->config['start_percentage']))
                                    <div class="flex justify-between">
                                        <span class="font-semibold">Start %:</span>
                                        <span>{{ $template->config['start_percentage'] }}%</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-semibold">Increment %:</span>
                                        <span>{{ $template->config['increment_percentage'] ?? 0 }}%</span>
                                    </div>
                                @endif

                                @if($template->type === 'balloon' && isset($template->config['balloon_percentage']))
                                    <div class="flex justify-between">
                                        <span class="font-semibold">Balloon %:</span>
                                        <span>{{ $template->config['balloon_percentage'] }}%</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
