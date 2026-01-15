<style>
    .box.box-solid {
        border: 1px solid #d2d6de;
        border-radius: 3px;
        box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    }

    .settings-sidebar {
        margin: 20px;
    }

    .box-header .box-title {
        font-size: 18px;
    }

    .settings-menu {
        display: flex;
        gap: 5px;
    }

    .settings-menu button {
        flex: 1;
        padding: 10px 15px;
        border: 1px solid #d2d6de;
        cursor: pointer;
        font-size: 14px;
        text-align: center;
        transition: all 0.3s;
        border-radius: 3px;
    }
</style>

<div class="box box-solid">
    @php $currentRoute = url()->current(); @endphp

    <div class="settings-sidebar">
        <div class="settings-menu">
            @foreach([
                ['url' => admin_url('pk-events'), 'label' => __('Pk Events')],
                ['url' => admin_url('target-events'), 'label' => __('Target Events')],
                ['url' => admin_url('weekly-events-new'), 'label' => __('Weekly Events')],
                ['url' => admin_url('event-period'), 'label' => __('Event Period')],
            ] as $tab)
                <button
                    type="button"
                    onclick="window.location.href='{{ $tab['url'] }}'"
                    class="{{ Str::contains($currentRoute, $tab['url']) ? 'active' : '' }}">
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </div>
    </div>
</div>
