@props([
    'head' => [],
    'empty' => null,
    'striped' => false,
])

<div class="ctl-table-wrap">
    <table {{ $attributes->merge(['class' => 'ctl-table']) }}>
        @if (count($head) > 0)
            <thead>
                <tr>
                    @foreach ($head as $heading)
                        <th scope="col">{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif

        <tbody>
            {{ $slot }}
        </tbody>
    </table>

    @if ($empty)
        {{ $empty }}
    @endif
</div>
