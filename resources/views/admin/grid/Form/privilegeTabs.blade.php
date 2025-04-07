<div class="box-body no-padding">
    <div class="nav-scroll-container">
        <ul class="nav nav-pills">
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
        </ul>
    </div>
</div>

<style>
    .nav-pills>li.active>a, .nav-pills>li.active>a:focus, .nav-pills>li.active>a:hover {
   
    background-color:  var(--primary-color);;
}
    .nav-scroll-container {
        overflow-x: auto;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
    }

    .nav-pills {
        display: inline-flex;
        padding: 10px 0;
    }

    .nav-pills li {
        display: inline-block;
    }
</style>
