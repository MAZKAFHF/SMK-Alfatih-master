<x-portal.layouts.app :title="'Notifikasi'">
<h1 class="font-display text-xl font-extrabold">Notifikasi</h1>
<ul class="mt-4 space-y-2">@forelse($items as $n)<li class="rounded-xl border p-4"><div class="flex justify-between gap-3"><strong>{{ $n->title }}</strong><span class="text-xs text-slate-400">{{ $n->created_at->diffForHumans() }}</span></div><p class="text-sm text-slate-500">{{ $n->message }}</p><form method="POST" novalidate action="{{ route('portal.notifications.read', $n) }}">@csrf<button class="mt-2 text-sm font-semibold text-primary-700">Tandai dibaca & buka</button></form></li>@empty<li><x-ui.empty-state title="Belum ada notifikasi" /></li>@endforelse</ul>
<div class="mt-4">{{ $items->links() }}</div>
</x-portal.layouts.app>
