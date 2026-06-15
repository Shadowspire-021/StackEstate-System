<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent leading-tight">
            {{ __('Edit Unit') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl shadow-sm">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-6">
                <form action="{{ route('units.update', $unit->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <h3 class="text-md font-bold text-gray-800 border-b border-gray-100 pb-3 flex items-center mb-6">
                            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Edit Unit Details
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Property *</label>
                                <select name="property_id" required class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="">-- Select Property --</option>
                                    @foreach($properties as $property)
                                        <option value="{{ $property->id }}" {{ old('property_id', $unit->property_id) == $property->id ? 'selected' : '' }}>
                                            {{ $property->property_type }} - {{ $property->plot_number }} ({{ $property->block_name }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Unit Number *</label>
                                <input type="text" name="unit_number" value="{{ old('unit_number', $unit->unit_number) }}" placeholder="e.g., A-101" required class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-gray-800">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Floor Number</label>
                                <input type="number" name="floor_number" value="{{ old('floor_number', $unit->floor_number) }}" placeholder="e.g., 1" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Size (Sq. Yards)</label>
                                <input type="number" step="0.01" name="size" value="{{ old('size', $unit->size) }}" placeholder="e.g., 120" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Price (Rs.)</label>
                                <input type="number" name="price" value="{{ old('price', $unit->price) }}" placeholder="e.g., 5000000" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Status *</label>
                                <select name="status" required class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="available" {{ old('status', $unit->status) == 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="booked" {{ old('status', $unit->status) == 'booked' ? 'selected' : '' }}>Booked</option>
                                    <option value="sold" {{ old('status', $unit->status) == 'sold' ? 'selected' : '' }}>Sold</option>
                                    <option value="reserved" {{ old('status', $unit->status) == 'reserved' ? 'selected' : '' }}>Reserved</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Form Buttons -->
                    <div class="flex justify-between pt-4 border-t border-gray-100">
                        <form action="{{ route('units.destroy', $unit->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this unit?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-xl shadow-md transition text-sm">
                                Delete Unit
                            </button>
                        </form>

                        <div class="flex space-x-4">
                            <a href="{{ route('units.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition">
                                Cancel
                            </a>
                            <button type="submit" form="edit-unit-form" class="inline-flex items-center px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl shadow-md transition text-sm">
                                Update Unit
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
