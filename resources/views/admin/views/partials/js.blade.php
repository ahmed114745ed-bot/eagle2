@foreach($js as $j)
<script src="{{ admin_asset ("$j") }}">
      

</script>
@endforeach


<script src="https://cdn.jsdelivr.net/npm/svgaplayerweb@2.3.1/build/svga.min.js"
        onerror="this.onerror=null; this.src='';"></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.10.5/viewer.min.js"></script>
 <script src="https://cdn..net/npm/svgaplayerweb@2.3.1/build/svga.min.js"></script>


<script>
    $(document).ready(function () {
        $('.view-lang').click(function (e) {
            e.preventDefault();

            var lang = $(this).data('lang');
            var value = $(this).data('value');

            $('#modalLangTitle').text(lang + " Content");
            $('#modalLangContent').text(value);

            $('#langModal').modal('show');
        });
    });


    function initDynamicFieldsScript() {
            $("#add_field").off('click').on("click", function() {
                var newField = `
                    <div class="dynamic-field-group" style="margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                        <input type="number" name="dynamic_fields[]" class="form-control" placeholder="أدخل قيمة رقمية" style="flex: 1;">
                        <button type="button" class="btn btn-danger remove-field">حذف</button>
                    </div>
                `;
                $("#dynamic_fields_container").append(newField);
            });
    
            $(document).off("click", ".remove-field").on("click", ".remove-field", function() {
                $(this).closest(".dynamic-field-group").remove();
            });
    
            function toggleFields() {
                var type = $("#box_type").val();
                if (type == "0") {
                    $("#coins").closest(".form-group").show();
                    $("#users_field").closest(".form-group").hide();
                    $("#duration_field").closest(".form-group").hide();
                    $("#dynamic_fields_container").show();
                    $("#add_field").show();
                } else {
                    $("#coins").closest(".form-group").show();
                    $("#users_field").closest(".form-group").show();
                    $("#duration_field").closest(".form-group").show();
                    $("#dynamic_fields_container").hide();
                    $("#add_field").hide();
                }
            }
    
            $("#box_type").off('change').on("change", toggleFields);
            toggleFields();
        }
    
        // عند تحميل الصفحة أول مرة
        $(document).ready(function () {
            initDynamicFieldsScript();
        });
    
        // عند تنقلك بين الصفحات باستخدام pjax
        $(document).on('pjax:end', function () {
            initDynamicFieldsScript();
        });
</script>
