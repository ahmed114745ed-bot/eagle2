@foreach($js as $j)
<script src="{{ admin_asset ("$j") }}"></script>
@endforeach

<script src="https://cdn.jsdelivr.net/npm/svgaplayerweb@2.3.1/build/svga.min.js"
        onerror="this.onerror=null; this.src='';"></script>
<script>
    $(document).ready(function () {
        console.log('done');
        $('.view-lang').click(function (e) {
            e.preventDefault();

            var lang = $(this).data('lang');
            var value = $(this).data('value');

            $('#modalLangTitle').text(lang + " Content");
            $('#modalLangContent').text(value);

            $('#langModal').modal('show');
        });
    });
</script>
