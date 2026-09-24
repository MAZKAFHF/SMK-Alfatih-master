<x-admin.layouts.app :title="'Detail Pesan'">
    <div class="mb-5"><a href="{{ route('admin.contact-messages.index') }}" class="text-sm text-slate-500">&larr; Kembali</a>
        <h1 class="mt-2 text-xl font-extrabold text-slate-900 dark:text-white">{{ $message->subject }}</h1>
        <p class="text-sm text-slate-500">Dari {{ $message->name }} &lt;{{ $message->email }}&gt; • {{ $message->created_at->translatedFormat('d M Y H:i') }}</p></div>

    <x-ui.card class="p-6">
        <dl class="grid gap-4 sm:grid-cols-2 text-sm">
            <div><dt class="text-xs uppercase text-slate-400">Nama</dt><dd class="font-medium">{{ $message->name }}</dd></div>
            <div><dt class="text-xs uppercase text-slate-400">Email</dt><dd class="font-medium">{{ $message->email }}</dd></div>
            <div><dt class="text-xs uppercase text-slate-400">HP</dt><dd class="font-medium">{{ $message->phone ?? '-' }}</dd></div>
            <div><dt class="text-xs uppercase text-slate-400">Subjek</dt><dd class="font-medium">{{ $message->subject }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-xs uppercase text-slate-400">Pesan</dt><dd class="mt-1 whitespace-pre-wrap rounded-lg bg-slate-50 p-4 dark:bg-slate-800">{{ $message->message }}</dd></div>
        </dl>

        <div class="mt-6 flex flex-wrap gap-2">
            @if($message->is_read)
                <form method="POST" action="{{ route('admin.contact-messages.unread',$message) }}">@csrf <x-ui.button variant="outline" size="sm" type="submit">Tandai Belum Dibaca</x-ui.button></form>
            @else
                <form method="POST" action="{{ route('admin.contact-messages.read',$message) }}">@csrf <x-ui.button variant="secondary" size="sm" type="submit">Tandai Sudah Dibaca</x-ui.button></form>
            @endif

            @if($message->is_archived)
                <form method="POST" action="{{ route('admin.contact-messages.unarchive',$message) }}">@csrf <x-ui.button variant="outline" size="sm" type="submit">Keluarkan dari Arsip</x-ui.button></form>
            @else
                <form method="POST" action="{{ route('admin.contact-messages.archive',$message) }}">@csrf <x-ui.button variant="outline" size="sm" type="submit">Arsipkan</x-ui.button></form>
            @endif

            <x-ui.button variant="danger" size="sm" onclick="confirmDialog({title:'Hapus pesan?', message:'Pindah ke Trash', formAction:'{{ route('admin.contact-messages.destroy',$message) }}', method:'DELETE'})">Hapus</x-ui.button>
        </div>
    </x-ui.card>
</x-admin.layouts.app>
