@php
    $configuredFavicon = \App\Models\SiteSetting::get('favicon');
    $faviconUrl = \App\Services\MediaService::url($configuredFavicon) ?: asset('img/logo.png');
@endphp

<link rel="icon" type="image/png" href="{{ $faviconUrl }}">
