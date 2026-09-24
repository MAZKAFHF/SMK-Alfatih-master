<x-admin.layouts.app :title="'Pesan Masuk'">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div><h1 class="text-xl font-extrabold">Pesan Masuk</h1><p class="text-sm text-slate-500">Inbox kontak — {{ $unreadCount }} belum dibaca.</p></div>
        <div class="flex gap-2">
            @if($trashedCount>0)<x-ui.button variant="ghost" size="sm" href="{{ route('admin.contact-messages.trash') }}">Trash ({{ $trashedCount }})</x-ui.button>@endif
        </div>
    </div>

    <x-ui.card class="mb-6 p-4">
        <form method="GET" action="{{ route('admin.contact-messages.index') }}" class="flex flex-col gap-3 sm:flex-row">
            <div class="flex-1"><x-ui.input name="search" placeholder="Cari nama/email/subjek..." value="{{ request('search') }}" /></div>
            <div class="sm:w-36">
                <x-ui.select name="status" :value="request('status')" :options="['unread'=>'Belum dibaca','read'=>'Sudah dibaca','archived'=>'Arsip']" placeholder="Semua"><option value="">Semua</option></x-ui.select>
            </div>
            <x-ui.button type="submit" variant="secondary">Cari</x-ui.button>
        </form>
    </x-ui.card>

    @if($messages->isEmpty())
        <x-ui.card class="p-6"><x-ui.empty-state title="Tidak ada pesan" description="Pesan kontak akan muncul di sini." /></x-ui.card>
    @else
        <div class="space-y-3">
            @foreach($messages as $msg)
                <x-ui.card class="p-4 {{ $msg->is_read ? 'opacity-90' : 'ring-2 ring-primary-200 dark:ring-primary-800' }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $msg->name }}</p>
                                @if(!$msg->is_read)<span class="rounded-full bg-primary-600 px-2 py-0.5 text-[11px] font-bold text-white">Baru</span>@endif
                                @if($msg->is_archived)<span class="rounded-full bg-slate-200 px-2 py-0.5 text-[11px] text-slate-600">Arsip</span>@endif
                            </div>
                            <p class="text-sm text-slate-600 dark:text-slate-400">{{ $msg->subject }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ $msg->email }} • {{ $msg->created_at->translatedFormat('d M Y H:i') }}</p>
                        </div>
                        <div class="flex shrink-0 gap-1">
                            <x-ui.button variant="outline" size="sm" href="{{ route('admin.contact-messages.show',$msg) }}">Buka</x-ui.button>
                        </div>
                    </div>
                </x-ui.card>
            @endforeach
        </div>
        <div class="mt-6">{{ $messages->links() }}</div>
    @endif
</x-admin.layouts.app>
