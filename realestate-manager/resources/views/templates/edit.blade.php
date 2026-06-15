<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent leading-tight">
            {{ __('Edit Installment Plan Template') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if($errors->any())
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('templates.update', $template) }}" method="POST" class="space-y-6" x-data="templateForm()">
                @csrf
                @method('PUT')

                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-6">
                    <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-3">Template Details</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Template Name</label>
                            <input type="text" name="name" value="{{ old('name', $template->name) }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Plan Type</label>
                            <select name="type" x-model="type" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="equal_split">Equal Split</option>
                                <option value="graduated">Graduated (Increasing)</option>
                                <option value="balloon">Balloon Payment</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Description (Optional)</label>
                        <textarea name="description" rows="2" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('description', $template->description) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Duration (Months)</label>
                        <input type="number" name="duration_months" value="{{ old('duration_months', $template->duration_months) }}" min="1" max="360" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                    </div>
                </div>

                <!-- Type-Specific Configuration -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-6" x-show="type === 'graduated' || type === 'balloon'">
                    <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-3">Type Configuration</h3>

                    <!-- Graduated Config -->
                    <div x-show="type === 'graduated'" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Start Percentage (%)</label>
                                <input type="number" name="config[start_percentage]" value="{{ old('config.start_percentage', $template->config['start_percentage'] ?? 50) }}" min="1" max="100" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <p class="text-xs text-gray-400 mt-1">First installment as % of equal split amount</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Increment Percentage (%)</label>
                                <input type="number" name="config[increment_percentage]" value="{{ old('config.increment_percentage', $template->config['increment_percentage'] ?? 10) }}" min="0" max="100" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <p class="text-xs text-gray-400 mt-1">How much each next installment increases</p>
                            </div>
                        </div>
                    </div>

                    <!-- Balloon Config -->
                    <div x-show="type === 'balloon'" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Balloon Payment Percentage (%)</label>
                            <input type="number" name="config[balloon_percentage]" value="{{ old('config.balloon_percentage', $template->config['balloon_percentage'] ?? 40) }}" min="1" max="90" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <p class="text-xs text-gray-400 mt-1">Final installment as % of total amount</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <a href="{{ route('templates.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-800">Cancel</a>
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                        Update Template
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function templateForm() {
            return {
                type: '{{ old("type", $template->type) }}'
            }
        }
    </script>
</x-app-layout>
