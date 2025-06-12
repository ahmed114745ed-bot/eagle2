<div class="box-body no-padding">
    <div class="nav-scroll-container">
        @if ($alert == true)
            <div 
            style=" color: var(--inverse-color) !important;"
            class="alert alert-warning d-flex justify-content-between align-items-center" role="alert">
            <div  style=" color: var(--inverse-color) !important;">
                {{ __('vip_alert_message') }}
            </div>
            <a href="{{ url("admin/ovip/{$level}/edit") }}" class="btn btn-sm btn
                 style="
                   background-color: var(--primary-color) !important;
                       color: var(--inverse-color) !important;

                "
                >
                    {{ __('vip_alert_button') }}
                </a>
            </div>
        @endif
        <ul class="nav nav-pills">
            @if ($types)

            @foreach($types as $type => $name)
                @php
                    $selectedType = request()->get('type', $types->keys()->first());
                @endphp
                <li class="{{ $selectedType == $type ? 'active' : '' }}">
                    <a href="{{ request()->fullUrlWithQuery(['type' => $type]) }}" class="privilege_tab">
                        {{ __($name ?? 'test') }}
                    </a>
                </li>
            @endforeach
            @endif
        </ul>
    </div>
</div>

<style>
    
    .nav-pills>li.active>a, .nav-pills>li.active>a:focus, .nav-pills>li.active>a:hover {

    background-color:  var(--primary-color);
}
    .nav-scroll-container {
        overflow-x: auto;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
    }
    .nav-pills>li.active>a, .nav-pills>li.active>a:hover, .nav-pills>li.active>a:focus {
    border-top-color:var(--primary-color);

    }
    .nav-pills {
        display: inline-flex;
        padding: 10px 0;
    }

    .nav-pills li {
        display: inline-block;
    }
</style>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const urlParams = new URLSearchParams(window.location.search);

        // Only append type if it's not already set
        if (!urlParams.has('type')) {
            const firstType = "{{ $types->keys()->first() }}"; // From your Blade variable
            const newUrl = new URL(window.location.href);

            newUrl.searchParams.set('type', firstType);
            window.history.replaceState({}, '', newUrl); // Change URL without reload

            // Optionally trigger a reload if needed
            location.reload();
        }
    });
</script>
