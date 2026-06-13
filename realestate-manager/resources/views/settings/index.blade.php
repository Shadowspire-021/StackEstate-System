<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent leading-tight">
            {{ __('System Configuration Settings') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

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
                <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <h3 class="text-md font-bold text-gray-800 border-b border-gray-100 pb-3 flex items-center mb-6">
                            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            Company Profile Info
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Company Name</label>
                                <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name'] ?? '') }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-gray-800">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Company Address</label>
                                <textarea name="company_address" rows="3" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('company_address', $settings['company_address'] ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-md font-bold text-gray-800 border-b border-gray-100 pb-3 flex items-center mb-6">
                            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Vendor Profile Info
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Vendor Name</label>
                                <input type="text" name="vendor_name" value="{{ old('vendor_name', $settings['vendor_name'] ?? '') }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-gray-800">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Vendor CNIC</label>
                                <input type="text" name="vendor_cnic" value="{{ old('vendor_cnic', $settings['vendor_cnic'] ?? '') }}" placeholder="42101-1234567-8" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Form Buttons -->
                    <div class="flex justify-end space-x-4 pt-4 border-t border-gray-100">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl shadow-md transition text-sm">
                            Save System Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
