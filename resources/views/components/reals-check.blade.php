@php

$realsAvailable = \App\Support\DynamicReals::isAvailable();
@endphp

@if($realsAvailable)
{{ $slot }}
@endif
