<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent leading-tight">
            {{ __('System Configuration Settings') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
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

            <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Company Settings -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-6">
                    <h3 class="text-md font-bold text-gray-800 border-b border-gray-100 pb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        Company Profile
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Company Name</label>
                            <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name'] ?? '') }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-gray-800">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Vendor Name</label>
                            <input type="text" name="vendor_name" value="{{ old('vendor_name', $settings['vendor_name'] ?? '') }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-gray-800">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Vendor CNIC</label>
                            <input type="text" name="vendor_cnic" value="{{ old('vendor_cnic', $settings['vendor_cnic'] ?? '') }}" placeholder="42101-1234567-8" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Company Address</label>
                            <textarea name="company_address" rows="2" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('company_address', $settings['company_address'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Google Drive Integration -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-6">
                    <h3 class="text-md font-bold text-gray-800 border-b border-gray-100 pb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                        Google Drive Integration
                    </h3>

                    <!-- OAuth Connect Buttons -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-4 border border-gray-200 rounded-xl bg-gray-50/50 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-gray-700">Google Drive</span>
                                @if($oauthConnected && $driveFolderId)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase tracking-wider">Connected</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-gray-100 text-gray-500 border border-gray-200 uppercase tracking-wider">Not Connected</span>
                                @endif
                            </div>
                            @if($oauthConnected && $driveFolderId)
                                <p class="text-xs text-gray-500 font-mono truncate">Folder: {{ $driveFolderId }}</p>
                            @endif
                            <a href="{{ route('google-oauth.connect', ['service' => 'drive', 'return_url' => route('settings.index')]) }}"
                               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-lg transition">
                                <svg class="w-4 h-4 mr-1.5" viewBox="0 0 24 24" fill="currentColor"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                                Connect Google Drive
                            </a>
                        </div>

                        <div class="p-4 border border-gray-200 rounded-xl bg-gray-50/50 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-gray-700">Google Sheets</span>
                                @if($oauthConnected && $spreadsheetId)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase tracking-wider">Connected</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-gray-100 text-gray-500 border border-gray-200 uppercase tracking-wider">Not Connected</span>
                                @endif
                            </div>
                            @if($oauthConnected && $spreadsheetId)
                                <p class="text-xs text-gray-500 font-mono truncate">Sheet: {{ $spreadsheetId }}</p>
                            @endif
                            <a href="{{ route('google-oauth.connect', ['service' => 'sheets', 'return_url' => route('settings.index')]) }}"
                               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-500 text-white text-xs font-bold rounded-lg transition">
                                <svg class="w-4 h-4 mr-1.5" viewBox="0 0 24 24" fill="currentColor"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                                Connect Google Sheets
                            </a>
                        </div>
                    </div>

                    @if($oauthConnected)
                        <div class="flex items-center">
                            <form action="{{ route('google-oauth.disconnect') }}" method="POST" onsubmit="return confirm('Disconnect Google account? Backups will fall back to local-only storage.')">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold rounded-lg border border-red-200 transition">
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Disconnect Google Account
                                </button>
                            </form>
                        </div>
                    @endif

                    <!-- Legacy manual settings (fallback) -->
                    <details class="mt-4">
                        <summary class="text-xs font-semibold text-gray-400 cursor-pointer hover:text-gray-600 uppercase tracking-wider">Manual Configuration (Fallback)</summary>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Root Folder ID</label>
                                <input type="text" name="google_root_folder_id" value="{{ old('google_root_folder_id', $settings['google_root_folder_id'] ?? '') }}" placeholder="e.g., 1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgVE2upms" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-mono">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Google Sheet ID</label>
                                <input type="text" name="google_sheet_id" value="{{ old('google_sheet_id', $settings['google_sheet_id'] ?? '') }}" placeholder="e.g., 1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgVE2upms" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-mono">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Credentials Path</label>
                                <input type="text" name="google_credentials_path" value="{{ old('google_credentials_path', $settings['google_credentials_path'] ?? config('google.credentials_path', '')) }}" placeholder="storage/app/google/credentials.json" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-mono">
                                <p class="mt-1 text-xs text-gray-400">Path to Google service account credentials JSON file (fallback when OAuth is not connected)</p>
                            </div>
                        </div>
                    </details>
                </div>

                <!-- Notification Settings -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-6">
                    <h3 class="text-md font-bold text-gray-800 border-b border-gray-100 pb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        Notification Settings
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">From Email Address</label>
                            <input type="email" name="notification_email_from" value="{{ old('notification_email_from', $settings['notification_email_from'] ?? '') }}" placeholder="notifications@yourdomain.com" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">From Name</label>
                            <input type="text" name="notification_email_name" value="{{ old('notification_email_name', $settings['notification_email_name'] ?? '') }}" placeholder="StackEstate" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div class="md:col-span-2">
                            <label class="inline-flex items-center space-x-3 cursor-pointer">
                                <input type="checkbox" name="notification_enabled" value="1" {{ ($settings['notification_enabled'] ?? '0') == '1' ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-semibold text-gray-700">Enable Email Notifications</span>
                            </label>
                            <p class="mt-1 text-xs text-gray-400 ml-6">Send email notifications for client creation, payments, and overdue installments</p>
                        </div>
                    </div>
                </div>

                <!-- Backup Settings -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-6">
                    <h3 class="text-md font-bold text-gray-800 border-b border-gray-100 pb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                        Backup & Recovery
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="inline-flex items-center space-x-3 cursor-pointer">
                                <input type="checkbox" name="backup_enabled" value="1" {{ ($settings['backup_enabled'] ?? '0') == '1' ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-semibold text-gray-700">Enable Automatic Backups</span>
                            </label>
                            <p class="mt-1 text-xs text-gray-400 ml-6">Run daily database backups automatically</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Retention Period (Days)</label>
                            <input type="number" name="backup_retention_days" value="{{ old('backup_retention_days', $settings['backup_retention_days'] ?? '30') }}" min="1" max="365" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <p class="mt-1 text-xs text-gray-400">Older backups will be automatically deleted</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Backup Schedule (Cron Expression)</label>
                            <input type="text" name="backup_schedule" value="{{ old('backup_schedule', $settings['backup_schedule'] ?? '0 2 * * *') }}" placeholder="0 2 * * *" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-mono">
                            <p class="mt-1 text-xs text-gray-400">Default: Daily at 2:00 AM. Format: minute hour day month weekday</p>
                        </div>
                </div>
            </div>

                <!-- Late Fees Settings -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-6">
                    <h3 class="text-md font-bold text-gray-800 border-b border-gray-100 pb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Late Fee Configuration
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="inline-flex items-center space-x-3 cursor-pointer">
                                <input type="checkbox" name="late_fee_enabled" value="1" {{ ($settings['late_fee_enabled'] ?? '0') == '1' ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-semibold text-gray-700">Enable Late Fees</span>
                            </label>
                            <p class="mt-1 text-xs text-gray-400 ml-6">Apply late fees on overdue installments</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Late Fee Rate (%)</label>
                            <input type="number" name="late_fee_rate" value="{{ old('late_fee_rate', $settings['late_fee_rate'] ?? '2') }}" min="0" max="100" step="0.1" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <p class="mt-1 text-xs text-gray-400">Percentage charged per period on overdue amount</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Fee Period</label>
                            <select name="late_fee_period" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="daily" {{ ($settings['late_fee_period'] ?? 'monthly') == 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="weekly" {{ ($settings['late_fee_period'] ?? 'monthly') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="monthly" {{ ($settings['late_fee_period'] ?? 'monthly') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-400">How often the late fee is applied</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Grace Period (Days)</label>
                            <input type="number" name="late_fee_grace_days" value="{{ old('late_fee_grace_days', $settings['late_fee_grace_days'] ?? '5') }}" min="0" max="90" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <p class="mt-1 text-xs text-gray-400">Days after due date before fee applies</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Gateway Settings -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-6">
                    <h3 class="text-md font-bold text-gray-800 border-b border-gray-100 pb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        Payment Gateway
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- JazzCash -->
                        <div class="space-y-4">
                            <h4 class="text-sm font-bold text-gray-600 flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 bg-red-100 text-red-600 rounded-lg text-xs font-bold">JC</span>
                                JazzCash
                            </h4>
                            <label class="inline-flex items-center space-x-3 cursor-pointer">
                                <input type="checkbox" name="jazzcash_enabled" value="1" {{ ($settings['jazzcash_enabled'] ?? '0') == '1' ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-semibold text-gray-700">Enabled</span>
                            </label>
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Merchant ID</label>
                                <input type="text" name="jazzcash_merchant_id" value="{{ old('jazzcash_merchant_id', $settings['jazzcash_merchant_id'] ?? '') }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                        </div>

                        <!-- Easypaisa -->
                        <div class="space-y-4">
                            <h4 class="text-sm font-bold text-gray-600 flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 bg-green-100 text-green-600 rounded-lg text-xs font-bold">EP</span>
                                Easypaisa
                            </h4>
                            <label class="inline-flex items-center space-x-3 cursor-pointer">
                                <input type="checkbox" name="easypaisa_enabled" value="1" {{ ($settings['easypaisa_enabled'] ?? '0') == '1' ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-semibold text-gray-700">Enabled</span>
                            </label>
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Merchant ID</label>
                                <input type="text" name="easypaisa_merchant_id" value="{{ old('easypaisa_merchant_id', $settings['easypaisa_merchant_id'] ?? '') }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Buttons -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl shadow-md transition text-sm">
                        Save All Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
