@foreach($js as $j)
<script src="{{ admin_asset ("$j") }}"></script>
@endforeach

<script src="https://cdn.jsdelivr.net/npm/svgaplayerweb@2.3.1/build/svga.min.js"></script>
