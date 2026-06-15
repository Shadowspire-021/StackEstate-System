<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Notifications') }}
            </h2>
            <div class="flex items-center gap-2">
                @if(auth()->user()->unreadNotifications->count() > 0)
                    <form action="{{ route('notifications.markAllRead') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-xs bg-indigo-50 text-indigo-700 px-3 py-1.5 rounded-lg font-semibold hover:bg-indigo-100 transition cursor-pointer">
                            Mark All as Read
                        </button>
                    </form>
                @endif
                <span class="text-xs bg-gray-50 text-gray-500 px-2 py-0.5 rounded-md font-semibold">{{ $notifications->total() }} Total</span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @forelse($notifications as $notification)
                        <div class="flex items-start justify-between py-4 {{ !$loop->first ? 'border-t border-gray-100' : '' }}">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    @if($notification->unread())
                                        <span class="w-2 h-2 rounded-full bg-indigo-500 flex-shrink-0"></span>
                                    @endif
                                    <p class="text-sm {{ $notification->unread() ? 'font-semibold text-gray-900' : 'text-gray-600' }}">
                                        {{ $notification->data['message'] ?? 'Notification' }}
                                    </p>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="flex items-center gap-2 ml-4">
                                @if($notification->unread())
                                    <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold cursor-pointer">Mark Read</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-8">No notifications yet.</p>
                    @endforelse

                    <div class="mt-6">
                        {{ $notifications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
