<x-admin.layouts.app :title="'Detail Pesan'">
    <div class="mb-5"><a href="{{ route('admin.contact-messages.index') }}" class="text-sm text-slate-500">&larr; Kembali</a>
        <h1 class="mt-2 text-xl font-extrabold text-slate-900 dark:text-white">{{ $message->subject }}</h1>
        <p class="text-sm text-slate-500">Dari {{ $message->name }} &lt;{{ $message->email }}&gt; • {{ $message->created_at->translatedFormat('d M Y H:i') }}</p></div>

    <x-ui.card class="p-6">
        <dl class="grid gap-4 sm:grid-cols-2 text-sm">
            <div><dt class="text-xs uppercase text-slate-400">Nama</dt><dd class="font-medium">{{ $message->name }}</dd></div>
            <div><dt class="text-xs uppercase text-slate-400">Email</dt><dd class="font-medium"><a class="text-primary-600 hover:underline" href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.$message->subject) }}">{{ $message->email }}</a></dd></div>
            <div><dt class="text-xs uppercase text-slate-400">HP</dt><dd class="font-medium">@if($message->phone)<a class="text-primary-600 hover:underline" target="_blank" rel="noopener" href="https://wa.me/{{ preg_replace('/\D+/', '', preg_replace('/^0/', '62', $message->phone)) }}">{{ $message->phone }}</a>@else - @endif</dd></div>
            <div><dt class="text-xs uppercase text-slate-400">Subjek</dt><dd class="font-medium">{{ $message->subject }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-xs uppercase text-slate-400">Pesan</dt><dd class="mt-1 whitespace-pre-wrap rounded-lg bg-slate-50 p-4 dark:bg-slate-800">{{ $message->message }}</dd></div>
        </dl>

        <div class="mt-6 flex flex-wrap gap-2">
            @if($message->is_read)
                <form method="POST" novalidate action="{{ route('admin.contact-messages.unread',$message) }}">@csrf <x-ui.button variant="outline" size="sm" type="submit">Tandai Belum Dibaca</x-ui.button></form>
            @else
                <form method="POST" novalidate action="{{ route('admin.contact-messages.read',$message) }}">@csrf <x-ui.button variant="secondary" size="sm" type="submit">Tandai Sudah Dibaca</x-ui.button></form>
            @endif

            @if($message->is_archived)
                <form method="POST" novalidate action="{{ route('admin.contact-messages.unarchive',$message) }}">@csrf <x-ui.button variant="outline" size="sm" type="submit">Keluarkan dari Arsip</x-ui.button></form>
            @else
                <form method="POST" novalidate action="{{ route('admin.contact-messages.archive',$message) }}">@csrf <x-ui.button variant="outline" size="sm" type="submit">Arsipkan</x-ui.button></form>
            @endif

            <x-ui.button variant="danger" size="sm" onclick="confirmDialog({title:'Hapus pesan?', message:'Pindah ke Trash', formAction:'{{ route('admin.contact-messages.destroy',$message) }}', method:'DELETE'})">Hapus</x-ui.button>
        </div>
    </x-ui.card>

    <x-ui.card class="mt-5 p-6">
        <h2 class="font-bold">Status komunikasi</h2>
        <form method="POST" action="{{ route('admin.contact-messages.handling', $message) }}" class="mt-4 grid gap-4 sm:grid-cols-2">
            @csrf @method('PUT')
            <x-ui.select label="Status penanganan" name="handling_status" :value="old('handling_status', $message->handling_status)" :options="['new'=>'Baru','in_progress'=>'Sedang diproses','resolved'=>'Selesai']" :placeholder-option="false" />
            <x-ui.select label="Saluran respons" name="response_channel" :value="old('response_channel', $message->response_channel)" :options="['email'=>'Email','whatsapp'=>'WhatsApp','phone'=>'Telepon','in_person'=>'Tatap muka','other'=>'Lainnya']" placeholder="Belum ditentukan" />
            <div class="sm:col-span-2"><x-ui.textarea label="Catatan admin" name="admin_note" rows="3">{{ old('admin_note', $message->admin_note) }}</x-ui.textarea></div>
            <div class="sm:col-span-2"><x-ui.button type="submit">Simpan Status</x-ui.button></div>
        </form>
    </x-ui.card>
</x-admin.layouts.app>
