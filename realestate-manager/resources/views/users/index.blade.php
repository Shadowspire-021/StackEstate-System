<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="flex items-center justify-between">
                <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">User Management</h2>
                <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white px-5 py-2.5 rounded-xl font-semibold shadow-md shadow-indigo-200 transition-all cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New User
                </a>
            </div>

            @if(session('success'))
                <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-xl shadow-sm flex items-start gap-3">
                    <svg class="w-5 h-5 text-emerald-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <p class="text-sm text-emerald-800 font-medium">{{ session('success') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-xl shadow-sm flex items-start gap-3">
                    <svg class="w-5 h-5 text-rose-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <p class="text-sm text-rose-800 font-medium">{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 text-xs font-bold uppercase tracking-wider">
                                <th class="p-4 pl-6">Name</th>
                                <th class="p-4">Email</th>
                                <th class="p-4">Role</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 text-right pr-6">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($users as $user)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="p-4 pl-6 font-semibold text-gray-900">{{ $user->name }}</td>
                                <td class="p-4 text-gray-600">{{ $user->email }}</td>
                                <td class="p-4">
                                    @foreach($user->roles as $role)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $role->name === 'super_admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="p-4">
                                    @if($user->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Active</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">Inactive</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right pr-6 space-x-2">
                                    <a href="{{ route('users.edit', $user->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-50 hover:bg-indigo-100 text-indigo-600 transition cursor-pointer" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </a>
                                    @if($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-rose-50 hover:bg-rose-100 text-rose-600 transition cursor-pointer" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
