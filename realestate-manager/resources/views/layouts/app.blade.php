<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <!-- Loading Overlay Spinner -->
        <div id="action-loader" style="display: none;" class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm">
            <div class="flex flex-col items-center space-y-4 p-6 bg-white rounded-2xl shadow-xl border border-gray-100">
                <svg class="animate-spin text-indigo-600" width="40" height="40" style="width: 40px; height: 40px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-700 tracking-wider">Processing Action... Please wait</span>
            </div>
        </div>

        <!-- Custom Premium Confirmation Modal -->
        <div id="confirm-modal" class="fixed inset-0 z-[9998] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm hidden transition-all duration-300 opacity-0">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 transform scale-95 transition-all duration-300">
                <div class="flex items-center space-x-3 text-red-600 mb-4" id="confirm-modal-icon-container">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <h3 class="font-extrabold text-lg" id="confirm-modal-title">Action Confirmation</h3>
                </div>
                <p class="text-sm text-gray-500 mb-6 leading-relaxed" id="confirm-modal-body">Are you sure you want to perform this action?</p>
                <div class="flex justify-end gap-3">
                    <button id="confirm-modal-cancel" class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition duration-150">Cancel</button>
                    <button id="confirm-modal-submit" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-xl transition duration-150 text-sm">Proceed</button>
                </div>
            </div>
        </div>

        <script>
            let pendingForm = null;

            document.addEventListener('submit', function(e) {
                const form = e.target;
                
                // Check if it's one of our audited/confirmed forms
                if (form.classList.contains('delete-form') || form.classList.contains('restore-form') || form.classList.contains('rollback-form')) {
                    if (pendingForm === form) {
                        // Already confirmed, let it submit (showing loader)
                        document.getElementById('action-loader').style.display = 'flex';
                        return;
                    }
                    
                    e.preventDefault();
                    pendingForm = form;
                    
                    // Customize modal depending on form class
                    const modal = document.getElementById('confirm-modal');
                    const title = document.getElementById('confirm-modal-title');
                    const body = document.getElementById('confirm-modal-body');
                    const iconContainer = document.getElementById('confirm-modal-icon-container');
                    const submitBtn = document.getElementById('confirm-modal-submit');
                    
                    if (form.classList.contains('delete-form')) {
                        title.innerText = 'Delete Client?';
                        body.innerText = '⚠️ WARNING: This will soft-delete the client from active records. All payment details will remain logged, but the client will be marked as Deleted.';
                        submitBtn.className = 'px-4 py-2 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-xl transition duration-150 text-sm';
                        iconContainer.className = 'flex items-center space-x-3 text-red-600 mb-4';
                        iconContainer.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg><h3 class="font-extrabold text-lg" id="confirm-modal-title">Delete Client?</h3>';
                    } else if (form.classList.contains('restore-form')) {
                        title.innerText = 'Restore Client?';
                        body.innerText = '🔄 Do you want to restore this client back to active status? This will make them fully queryable again.';
                        submitBtn.className = 'px-4 py-2 bg-green-600 hover:bg-green-500 text-white font-semibold rounded-xl transition duration-150 text-sm';
                        iconContainer.className = 'flex items-center space-x-3 text-green-600 mb-4';
                        iconContainer.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.2M9 11l3-3 3 3m-3-3v12"></path></svg><h3 class="font-extrabold text-lg" id="confirm-modal-title">Restore Client?</h3>';
                    } else if (form.classList.contains('rollback-form')) {
                        title.innerText = 'Rollback to this version?';
                        body.innerText = '⚠️ WARNING: This will revert the client and property details in the database to this historical version. Are you sure you want to proceed?';
                        submitBtn.className = 'px-4 py-2 bg-yellow-600 hover:bg-yellow-500 text-white font-semibold rounded-xl transition duration-150 text-sm';
                        iconContainer.className = 'flex items-center space-x-3 text-yellow-600 mb-4';
                        iconContainer.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.334 4z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0011 16V8a1 1 0 00-1.6-.8l-5.334 4z"></path></svg><h3 class="font-extrabold text-lg" id="confirm-modal-title">Rollback Changes?</h3>';
                    }
                    
                    // Show modal
                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        modal.classList.remove('opacity-0');
                        modal.classList.add('opacity-100');
                        modal.querySelector('div').classList.remove('scale-95');
                        modal.querySelector('div').classList.add('scale-100');
                    }, 50);
                } else {
                    // Standard form - just show loader
                    if (!form.getAttribute('target')) {
                        document.getElementById('action-loader').style.display = 'flex';
                    }
                }
            });

            // Modal button listeners
            document.getElementById('confirm-modal-cancel').addEventListener('click', function() {
                closeConfirmModal();
            });

            document.getElementById('confirm-modal-submit').addEventListener('click', function() {
                if (pendingForm) {
                    pendingForm.submit();
                }
                closeConfirmModal();
            });

            function closeConfirmModal() {
                const modal = document.getElementById('confirm-modal');
                modal.classList.remove('opacity-100');
                modal.classList.add('opacity-0');
                modal.querySelector('div').classList.remove('scale-100');
                modal.querySelector('div').classList.add('scale-95');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    pendingForm = null;
                }, 300);
            }
        </script>
        @stack('scripts')
    </body>
</html>
