<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #121212;
        color: white;
        display: flex;
    }

    .settings-sidebar {
        width: 250px;
        background: var(--secondary-color);
        min-height: 400px;
        padding: 20px;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.5);
    }

    .rtl .settings-sidebar {
        margin-left: 400px;
    }

    .ltr .settings-sidebar {
        margin-right: 400px;
    }

    .settings-sidebar h2 {
        text-align: center;
    }

    .settings-menu button {
        display: block;
        width: 100%;
        text-align: right;
        padding: 15px;
        background: var(--primary-color);
        color: white;
        border: none;
        margin-bottom: 5px;
        cursor: pointer;
        font-size: 16px;
    }

    form {
        background: #222;
        padding: 20px;
        border-radius: 5px;
    }

    label {
        display: block;
        margin: 10px 0 5px;
    }

    input,
    select {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        background: #333;
        border: 1px solid #444;
        color: white;
    }

    .sidebar-menu>li>a {
        padding-left: 0; !important;
    }
</style>
</head>

<body>
    <div class="all-page">
        @php $currentRoute = url()->current(); @endphp

        <div class="settings-sidebar">
            <h2>{{ __('Settings') }}</h2>
            <div class="settings-menu">
                @foreach([
                    ['url' => admin_url('event-period'), 'label' => __('Event Period')],
                    ['url' => admin_url('weekly-events-new'), 'label' => __('Weekly Events')],
                    ['url' => admin_url('target-events'), 'label' => __('Target Events')],
                    ['url' => admin_url('pk-events'), 'label' => __('Pk Events')],
                ] as $tab)
                    <button
                        type="button"
                        onclick="window.location.href='{{ $tab['url'] }}'"
                        style="{{ Str::contains($currentRoute, $tab['url']) ? 'background: var(--primary-color); color: var(--text-secondary-color);' : 'background: var(--box-background-color); color: white;' }}">
                        {{ $tab['label'] }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</body>
