@props([
    'skills' => ['Web Development', 'Software Engineering', 'UI/UX', 'Database', 'Problem Solving'],
])

{{-- Panel IDE premium RPL — navy, bukan terminal hacker --}}
<div class="reveal relative overflow-hidden rounded-2xl bg-navy-900 text-white shadow-soft">
    <div class="tech-grid pointer-events-none absolute inset-0" aria-hidden="true"></div>
    <x-motif-geometric class="absolute -right-8 -top-8 size-40 text-tech-500/20" />
    <div class="relative p-6 sm:p-8">
        <div class="flex items-center gap-1.5" aria-hidden="true">
            <span class="size-2.5 rounded-full bg-energy-500"></span>
            <span class="size-2.5 rounded-full bg-gold-500"></span>
            <span class="size-2.5 rounded-full bg-tech-500"></span>
            <span class="ml-2 font-mono text-xs text-slate-400">student — build-your-future</span>
        </div>
        <pre class="mt-4 overflow-x-auto font-mono text-[13px] leading-relaxed"><code><span class="text-tech-400">&lt;student&gt;</span>
  <span class="text-slate-400">&lt;skill&gt;</span><span class="text-white">Programming</span><span class="text-slate-400">&lt;/skill&gt;</span>
  <span class="text-slate-400">&lt;skill&gt;</span><span class="text-white">UI/UX</span><span class="text-slate-400">&lt;/skill&gt;</span>
  <span class="text-slate-400">&lt;skill&gt;</span><span class="text-white">Database</span><span class="text-slate-400">&lt;/skill&gt;</span>
<span class="text-tech-400">&lt;/student&gt;</span></code></pre>
        <div class="mt-5 flex flex-wrap gap-2">
            @foreach ($skills as $skill)
                <span class="rounded-full border border-white/15 bg-white/5 px-3 py-1 text-xs font-semibold text-slate-200">{{ $skill }}</span>
            @endforeach
        </div>
        <x-digital-pulse :steps="['Learn', 'Build', 'Impact']" class="mt-6 text-tech-400" />
    </div>
</div>
