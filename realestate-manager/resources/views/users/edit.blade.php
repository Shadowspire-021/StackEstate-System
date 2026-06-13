<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="flex items-center justify-between">
                <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Edit User</h2>
                <a href="{{ route('users.index') }}" class="text-gray-500 hover:text-gray-800 font-medium transition cursor-pointer">
                    &larr; Back to Users
                </a>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden p-8">
                <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PATCH')
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition shadow-sm">
                        @error('name') <p class="mt-1 text-sm text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition shadow-sm">
                        @error('email') <p class="mt-1 text-sm text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Password <span class="text-xs font-normal text-gray-400">(leave blank to keep current)</span></label>
                            <input type="password" name="password" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition shadow-sm">
                            @error('password') <p class="mt-1 text-sm text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition shadow-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 items-end">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Role</label>
                            <select name="role" required class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition shadow-sm">
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ (old('role', $user->roles->first()->name ?? '') === $role->name) ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role') <p class="mt-1 text-sm text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="flex items-center space-x-3 mb-3 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 w-5 h-5">
                                <span class="text-sm font-bold text-gray-700">Account is Active</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-md transition cursor-pointer">
                            Update User
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
