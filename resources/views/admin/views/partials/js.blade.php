@foreach($js as $j)
<script src="{{ admin_asset ("$j") }}">
      

</script>
@endforeach


<script src="https://cdn.jsdelivr.net/npm/svgaplayerweb@2.3.1/build/svga.min.js"
        onerror="this.onerror=null; this.src='';"></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.10.5/viewer.min.js"></script>
 <!-- <script src="https://cdn..net/npm/svgaplayerweb@2.3.1/build/svga.min.js"></script> -->
 <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>


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
        $("#add_field").off("click").on("click", function() {
            var newField = '<div class="dynamic-field-group" style="margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">' +
                '<input type="number" name="dynamic_fields[]" class="form-control" placeholder="' + window.translations.add_placeholder + '" style="flex: 1;">' +
                '<button type="button" class="btn btn-danger remove-field">' + window.translations.delete_text + '</button>' +
            '</div>';
            $("#dynamic_fields_container").append(newField);
        });
        
                $(document).off("click", ".remove-field").on("click", ".remove-field", function() {
                    $(this).closest(".dynamic-field-group").remove();
                });
        
                function toggleFields() {
                    var type = $("#box_type").val();
                    if (type == "1" || type == 1 ) {
                        $("#users_field").closest(".form-group").show();
                        $("label[for='users']").show();
                        $('#users_field').closest('.input-group').find('.input-group-addon').show();
                        $("#users_field").show();


                        $("#duration_field").closest(".form-group").show();
                        $("label[for='duration']").show();
                        $('#duration_field').closest('.input-group').find('.input-group-addon').show();
                        $('#duration_field').closest('.col-sm-8').find('.help-block').show();
                        $("#duration_field").closest(".form-group").css("display", "block");
                        $("#duration_field").show();


                        $("#dynamic_fields_container").hide();
                        $("#add_field").hide();
                    } else {
                        $("#users_field").closest(".form-group").hide();
                        $("label[for='users']").hide();
                        $('#users_field').closest('.input-group').find('.input-group-addon').hide();
                        $("#users_field").hide();


                        $("#duration_field").hide();
                        $("label[for='duration']").hide();
                        $('#duration_field').closest('.input-group').find('.input-group-addon').hide();
                        $('#duration_field').closest('.col-sm-8').find('.help-block').hide();
                        $("#duration_field").closest(".form-group").css("display", "none");




                        $("#dynamic_fields_container").show();
                        $("#add_field").show();
                    }
                }
                $(document).on("change", "#box_type", toggleFields);

                $("#box_type").off("change").on("change", toggleFields);
                toggleFields();
            }
        
            $(document).ready(function () {
                initDynamicFieldsScript();
            });
        
            // $(document).on("pjax:end", function () {
            //     initDynamicFieldsScript();
            // });



   

        function initPhoneInput() {
        document.querySelectorAll("#phone-input").forEach(function(input) {
            if (!input.classList.contains('iti-initialized')) {
                const parentDiv = input.parentElement;
                parentDiv.style.position = 'relative';

                const iti = window.intlTelInput(input, {
                    separateDialCode: true,
                    preferredCountries: ["eg"],
                    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
                });

                document.head.insertAdjacentHTML('beforeend', `
                    <style>
                        .iti { width: 100%; }
                        .iti__flag-container { z-index: 99; }
                        #phone-input {
                            padding-left: 90px !important;
                            width: 50%;
                        }
                        .fields-group .form-group { overflow: visible; }
                    </style>
                `);

                input.classList.add('iti-initialized');

                const form = input.closest('form');
                if (form && !form.classList.contains('phone-init')) {
                    form.addEventListener('submit', function () {
                        if (iti) {
                            const dialCode = iti.getSelectedCountryData().dialCode;
                            const nationalNumber = input.value.replace(/\s/g, '');

                            const hiddenInput = document.createElement('input');
                            hiddenInput.name = 'phone_code';
                            hiddenInput.value = `+${dialCode}`;
                            form.appendChild(hiddenInput);

                            input.value = nationalNumber;
                        }
                    });
                    form.classList.add('phone-init');
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', initPhoneInput);
    document.addEventListener('pjax:complete', function () {
        setTimeout(initPhoneInput, 100);
    });

    $(document).on('click', '.add-form-row', function () {
        setTimeout(initPhoneInput, 100);
    });
</script>
