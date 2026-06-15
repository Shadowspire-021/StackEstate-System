<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl bg-gradient-to-r from-red-600 via-orange-600 to-yellow-600 bg-clip-text text-transparent leading-tight">
            {{ __('Failed Jobs') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div class="text-3xl font-black text-red-600">{{ $failedJobs->total() }}</div>
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Failed Jobs</div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div class="text-3xl font-black text-orange-600">{{ $failedJobs->where('queue', 'default')->count() }}</div>
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Default Queue</div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div class="text-3xl font-black text-yellow-600">{{ $failedJobs->where('queue', 'document-uploads')->count() }}</div>
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Document Queue</div>
                </div>
            </div>

            <!-- Jobs Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">Failed Jobs</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 text-xs font-semibold uppercase tracking-wider">
                                <th class="px-6 py-3">ID</th>
                                <th class="px-6 py-3">Queue</th>
                                <th class="px-6 py-3">Connection</th>
                                <th class="px-6 py-3">Payload</th>
                                <th class="px-6 py-3">Failed At</th>
                                <th class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($failedJobs as $job)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-mono text-gray-600">{{ $job->id }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 uppercase tracking-wider">
                                            {{ $job->queue }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $job->connection }}</td>
                                    <td class="px-6 py-4">
                                        <div class="text-xs font-mono text-gray-500 max-w-md truncate">
                                            {{ Str::limit($job->payload, 80) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $job->failed_at }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-2">
                                            <form action="{{ route('queue.retry', $job->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-indigo-600 hover:text-indigo-500 text-sm font-semibold">
                                                    Retry
                                                </button>
                                            </form>
                                            <form action="{{ route('queue.delete', $job->id) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this failed job?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-500 text-sm font-semibold">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-green-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span class="font-semibold">No failed jobs!</span>
                                            <span class="text-sm">All jobs are processing successfully.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($failedJobs->hasPages())
                    <div class="p-4 border-t border-gray-100">
                        {{ $failedJobs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
