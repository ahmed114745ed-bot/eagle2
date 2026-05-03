<div class="grid-dropdown-actions">
    <a href="#" class="dropdown-toggle grid-action-toggle">
        <i class="fa fa-ellipsis-v"></i>
    </a>
    <ul class="dropdown-menu grid-dropdown-menu">
        @foreach($default as $action)
            <li>{!! $action->render() !!}</li>
        @endforeach

        @if(!empty($custom))
            @if(!empty($default))
                <li class="divider"></li>
            @endif

            @foreach($custom as $action)
                <li>{!! $action->render() !!}</li>
            @endforeach
        @endif
    </ul>
</div>

<style>
    .grid-dropdown-actions .dropdown-toggle {
        padding: 0 10px;
    }

    .grid-dropdown-menu {
        min-width: 70px !important;
        box-shadow: 0 2px 3px 0 rgba(0,0,0,.2);
        border-radius: 0;
        top: auto;
    }

    .ltr .grid-dropdown-menu{
        left: -65px;
    }

    .rtl .grid-dropdown-menu{
        right: -65px;
    }

    .grid-dropdown-menu .divider {
        height: 1px;
        margin: 8px 0;
        overflow: hidden;
        background-color: #e5e5e5;
    }
</style>

@yield('child')
