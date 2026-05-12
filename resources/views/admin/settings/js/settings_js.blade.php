<script>
    $(document).on('change', '.libraryRealTime', function () {
        $(this).closest('form').submit();
    });

    function reseting(colorid, value) {
        $('#' + colorid).val(value);

        if (colorid === 'background_type') {
            if (value === 'image') {
                $('#background_image_group').show();
                $('#background_image_preview').show();
                $('#background_color_group').hide();
                $('#gradient_group').hide();
            }
        }
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const units = ['wealth', 'attraction', 'charge', 'rooms', 'cp'];

        units.forEach(unit => {
            const expInput = document.getElementById(`${unit}_exp`);
            const priceInput = document.getElementById(`${unit}_gift_price`);
            const resultSpan = document.getElementById(`${unit}_exp_result`);

            if (expInput && priceInput && resultSpan) {
                function updateExpResult() {
                    const expRate = parseFloat(expInput.value);
                    const giftPrice = parseFloat(priceInput.value);

                    if (!isNaN(expRate) && !isNaN(giftPrice)) {
                        const totalExp = expRate * giftPrice;
                        resultSpan.textContent = `= ${totalExp} EXP`;
                    } else {
                        resultSpan.textContent = '';
                    }
                }

                expInput.addEventListener('input', updateExpResult);
                priceInput.addEventListener('input', updateExpResult);
                updateExpResult();
            }
        });
    });

    function select_brand_image(name, obj) {
        $('#brand_image').val(name)
        $('.image_success').removeClass('border-success')
        $(obj).addClass('border-success')
        toastr.success('Brand image chosen successfully');
    }

    function choose_image() {
        var fileInput = document.getElementById('brand_background_image');
        var image = fileInput.files[0];

        if (!image) {
            toastr.error('Please select an image first.');
            return;
        }

        var formData = new FormData();
        formData.append('image', image);
        formData.append('_token', "{{ csrf_token() }}");

        $.ajax({
            url: "{{ route('admin.save_image') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                console.log('Uploading image is true');
                toastr.success('Library preference saved!');
            },
            error: function (xhr) {
                console.error('Failed to Upload image');
                toastr.error('Failed to Upload image');
            }
        });
    }
</script>

<script>
    function updateLibrary(selectedLibrary, inputName) {
        let data = {
            _token: "{{ csrf_token() }}",
        };
        data[inputName] = selectedLibrary;

        $.ajax({
            url: "{{ route('admin.update-agora-zego') }}",
            type: "POST",
            data: data,
            success: function (response) {
                console.log("Library updated via AJAX:", response);
                toastr.success('Library preference saved!');
            },
            error: function (xhr) {
                console.error("AJAX Error:", xhr.responseText);
                toastr.error('Failed to update library');
            }
        });
    }

    function updateSwitches() {
        $(".custom-radio").each(function () {
            if ($(this).is(":checked")) {
                $(this).next(".switch").addClass("active");
            } else {
                $(this).next(".switch").removeClass("active");
            }
        });
    }

    function updatePaymentSwitches() {
        $(".custom-payment-radio").each(function () {
            if ($(this).is(":checked")) {
                $(this).next(".switch").addClass("active");
            } else {
                $(this).next(".switch").removeClass("active");
            }
        });
    }

    $(document).on("change", ".custom-radio", function () {
        let selectedLibrary = $(this).val();
        let inputName = $(this).attr('name');
        console.log("Selected library:", selectedLibrary);
        console.log("library Name:", inputName);
        // Skip AJAX for live_library - it has its own form that submits to update-library route
        if (inputName !== 'live_library') {
            updateLibrary(selectedLibrary, inputName);
        }
        updateSwitches();
    });

    $(document).on("change", ".custom-payment-radio", function () {
        updatePaymentSwitches();
    });

    $(document).on("click", ".switch", function () {
        const radio = $(this).prev(".custom-radio");

        if (!radio.prop("checked")) {
            $("input[name='library']").prop("checked", false);
            $(".switch").removeClass("active");

            radio.prop("checked", true).trigger("change");
        }
    });

    $(document).ready(function () {
        updateSwitches();
        updatePaymentSwitches();
    });
</script>

<script>
    document.addEventListener('openSettingsTab', function(e) {
    showSection(e.detail);
});
    function previewImage(event) {
        let file = event.target.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function (e) {
                let preview = document.getElementById('imagePreview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    }

    function previewFavIcon(event) {
        let file = event.target.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function (e) {
                let preview = document.getElementById('favIconPreview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    }

    // document.addEventListener("DOMContentLoaded", function () {

    //     function getQueryParam(name) {
    //         const urlParams = new URLSearchParams(window.location.search);
    //         return urlParams.get(name);
    //     }

    //     const activeTab = getQueryParam("tab") || request("tab")|| "brandSettings" 
    //     showSection(activeTab);

    //     if (activeTab === "workSettings") {
    //         const type = getQueryParam("type") || "Experience";
    //         showInnerContent(type);

    //         document.querySelectorAll(".inner-settings-menu button").forEach(btn => {
    //             btn.classList.remove("active");
    //         });

    //         const correctBtn = document.querySelector(`.inner-settings-menu button[onclick="changeInnerTab('${type}')"]`);
    //         if (correctBtn) correctBtn.classList.add("active");
    //     }

    //     document.querySelectorAll(".settings-menu button").forEach(btn => {
    //         btn.addEventListener("click", function () {
    //             const sectionId = btn.getAttribute("onclick").match(/'(.+?)'/)[1];
    //             showSection(sectionId);
    //         });
    //     });

    //     document.querySelectorAll(".inner-settings-menu button").forEach(btn => {
    //         btn.addEventListener("click", function () {
    //             const type = btn.getAttribute("onclick").match(/'(.+?)'/)[1];
    //             changeInnerTab(type);
    //         });
    //     });
    // });

    // function showSection(sectionId) {
    //     document.querySelectorAll('.settings-section').forEach(section => {
    //         section.classList.remove('active');
    //     });

    //     const section = document.getElementById(sectionId);
    //     if (section) section.classList.add('active');

    //     document.querySelectorAll('input[name="current_tab"]').forEach(input => {
    //         input.value = sectionId;
    //     });

    //     const url = new URL(window.location);
    //     url.searchParams.set("tab", sectionId);
    //     window.history.pushState({}, "", url);

    //     if (sectionId === 'workSettings') {
    //         const type = getQueryParam("type") || "Experience";
    //         showInnerContent(type);

    //         document.querySelectorAll(".inner-settings-menu button").forEach(btn => {
    //             btn.classList.remove("active");
    //         });

    //         const correctBtn = document.querySelector(`.inner-settings-menu button[onclick="changeInnerTab('${type}')"]`);
    //         if (correctBtn) correctBtn.classList.add("active");
    //     }
    // }
function getQueryParam(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

// Define functions in global scope FIRST (before initialization)
window.showSection = function(sectionId) {
    // Show/hide sections
    document.querySelectorAll('.settings-section').forEach(section => {
        section.classList.remove('active');
    });
    const section = document.getElementById(sectionId);
    if (section) section.classList.add('active');
    const hashValue = window.location.hash.substring(1);
    // Update all forms with current tab information
    document.querySelectorAll('input[name="current_tab"]').forEach(input => {
        input.value = sectionId;
    });

    

    // Add current_tab as hidden input to all forms in settings (including both settings-form and no-background-form)
    document.querySelectorAll('.settings-form, .no-background-form, form[action*="admin"]').forEach(form => {
        let tabInput = form.querySelector('input[name="current_tab"]');
        if (!tabInput) {
            tabInput = document.createElement('input');
            tabInput.type = 'hidden';
            tabInput.name = 'current_tab';
            form.appendChild(tabInput);
        }
        tabInput.value = sectionId;

        // Add inner tab type if exists
        if (sectionId === 'workSettings') {
            let typeInput = form.querySelector('input[name="inner_tab_type"]');
            const currentType = getQueryParam('type') || 'Experience';
            if (!typeInput) {
                typeInput = document.createElement('input');
                typeInput.type = 'hidden';
                typeInput.name = 'inner_tab_type';
                form.appendChild(typeInput);
            }
            typeInput.value = currentType;
        }
    });

    // Update URL without reloading
    const url = new URL(window.location);
    url.searchParams.set("tab", sectionId);
    window.history.pushState({}, "", url);

    // Highlight the active main tab button
    document.querySelectorAll(".settings-menu button").forEach(btn => {
        btn.classList.remove("active");
    });
    const activeBtn = document.querySelector(`.settings-menu button[onclick="showSection('${sectionId}')"]`);
    if (activeBtn) activeBtn.classList.add("active");

    // Handle inner tabs if 'workSettings'
    if (sectionId === 'workSettings') {
        const type = getQueryParam("type") || "Experience";
        showInnerContent(type);

        document.querySelectorAll(".inner-settings-menu button").forEach(btn => {
            btn.classList.remove("active");
        });

        const correctBtn = document.querySelector(`.inner-settings-menu button[onclick="changeInnerTab('${type}')"]`);
        if (correctBtn) correctBtn.classList.add("active");
    }
}


window.changeInnerTab = function(type) {
    const url = new URL(window.location);
    url.searchParams.set("tab", "workSettings");
    url.searchParams.set("type", type);
    window.history.pushState({}, "", url);

        document.querySelectorAll(".inner-settings-menu button").forEach(btn =>
            btn.classList.remove("active")
        );
        document.querySelector(`.inner-settings-menu button[onclick="changeInnerTab('${type}')']`)
            ?.classList.add("active");

        // Update all forms in workSettings with inner tab type
        document.querySelectorAll('#workSettings .settings-form').forEach(form => {
            let typeInput = form.querySelector('input[name="inner_tab_type"]');
            if (!typeInput) {
                typeInput = document.createElement('input');
                typeInput.type = 'hidden';
                typeInput.name = 'inner_tab_type';
                form.appendChild(typeInput);
            }
            typeInput.value = type;
        });

    showInnerContent(type);
}

window.showInnerContent = function(type) {
    document.querySelectorAll(".inner-tab-content").forEach(content => {
        content.style.display = "none";
    });

    const section = document.getElementById(type + "_tab");
    if (section) section.style.display = "block";
}

window.openFullScreen = function(imgElement) {
    var modal = document.getElementById("imageModal");
    var modalImg = document.getElementById("fullImage");

    modal.style.display = "block";
    modalImg.src = imgElement.src;
}

window.closeFullScreen = function() {
    document.getElementById("imageModal").style.display = "none";
}

window.toggleBackgroundInput = function() {
    const type = document.getElementById("background_type").value;
    document.getElementById("background_color_group").style.display = type === "color" ? "block" : "none";
    document.getElementById("background_image_group").style.display = type === "image" ? "block" : "none";
    document.getElementById("gradient_group").style.display = type === "gradient" ? "block" : "none";
}

window.toggleBodyThemeSection = function(isChecked) {
    var group = document.getElementById('background_body_theme_group');
    if (group) {
        group.style.display = isChecked ? 'block' : 'none';
    }
    // If unchecked, also hide the sub-fields
    if (!isChecked) {
        var colorGroup = document.getElementById('background_body_theme_color_group');
        var imageGroup = document.getElementById('background_body_theme_image_group');
        var gradientGroup = document.getElementById('background_body_theme_gradient_group');
        if (colorGroup) colorGroup.style.display = 'none';
        if (imageGroup) imageGroup.style.display = 'none';
        if (gradientGroup) gradientGroup.style.display = 'none';
    } else {
        // If checked, show the correct sub-field based on current select value
        toggleBodyThemeBackgroundInput();
    }
}

window.toggleBodyThemeBackgroundInput = function() {
    var select = document.getElementById('background_body_theme');
    if (!select) return;
    var type = select.value;
    var colorGroup = document.getElementById('background_body_theme_color_group');
    var imageGroup = document.getElementById('background_body_theme_image_group');
    var gradientGroup = document.getElementById('background_body_theme_gradient_group');
    if (colorGroup) colorGroup.style.display = type === 'color' ? 'block' : 'none';
    if (imageGroup) imageGroup.style.display = type === 'image' ? 'block' : 'none';
    if (gradientGroup) gradientGroup.style.display = type === 'gradient' ? 'block' : 'none';
}

// Listen for bootstrap-switch changes on is_body_theme_enabled
$(document).on('switchChange.bootstrapSwitch', 'input[name="is_body_theme_enabled"]', function(event, state) {
    toggleBodyThemeSection(state);
});

// Also listen for regular change event as fallback
$(document).on('change', 'input[name="is_body_theme_enabled"]', function() {
    toggleBodyThemeSection(this.checked);
});

window.toggleBrandBackgroundInput = function() {
    const type = document.getElementById("brand_background_type").value;
    document.getElementById("brand_background_color_group").style.display = type === "color" ? "block" : "none";
    document.getElementById("brand_background_image_group").style.display = type === "image" ? "block" : "none";
}

window.updateBackgroundValue = async function() {
    const type = document.getElementById("background_type").value;
    const hiddenInput = document.getElementById("app_background");

        if (type === "color") {
            hiddenInput.value = document.getElementById("background_color").value;
        } else if (type === "image") {
            const fileInput = document.getElementById("background_image");
            if (fileInput.files.length > 0) {
                try {
                    const base64String = await getBase64(fileInput.files[0]);
                    hiddenInput.value = base64String;
                } catch (error) {
                    console.error("Error converting image:", error);
                    hiddenInput.value = "";
                }
            } else {
                hiddenInput.value = '';
            }
        }
    }

    function getBase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = () => resolve(reader.result);
            reader.onerror = error => reject(error);
        });
    }

// Initialize settings tabs - supports both regular page load and pjax navigation
(function initSettingsTabs() {
    function doInit() {
        const activeTab = getQueryParam("tab") || "brandSettings";
        showSection(activeTab);
    }

    if (document.readyState === 'loading') {
        document.addEventListener("DOMContentLoaded", doInit);
    } else {
        doInit();
    }
})();
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let resetButton = document.getElementById('resetColors');

        let resetApColorSettingpButton = document.getElementById('resetAppColorsSettings');

        if (resetApColorSettingpButton) {
            resetApColorSettingpButton.addEventListener('click', function () {
                document.getElementById('app_primary_color').value = "#32e5ac";
                document.getElementById('background_color').value = "#FFFFFF";
                document.getElementById('background_type').value = "color";
                document.getElementById('bottom_color').value = "#FFFFFF";
                document.getElementById('reset').value = 1;
                document.getElementById('active_color').value = "#33FFAA";
                document.getElementById('inactive_color').value = "#D1CECE";
                document.getElementById('text_header_color').value = "#000000";
                document.getElementById('button_text_color').value = "#FFFFFF";

                document.querySelector('#appSettings form').submit();
            });
        }

        if (resetButton) {
            resetButton.addEventListener('click', function () {
                let colorInputs = {
                    'primary_color': "#00FFCC",
                    'secondary_color': "#FFFFFF",
                    'text_primary_color': "#fdf8f8",
                    'text_secondary_color': "#000000",
                    'box_background_color': "#969696",
                    'table_background_color': "#c88213"
                };

                Object.keys(colorInputs).forEach(id => {
                    let input = document.getElementById(id);
                    if (input) {
                        input.value = colorInputs[id];
                    }
                });

                document.getElementById('brand_background_type').value = "image";

                document.getElementById('brand_background_color_group').style.display = 'none';
                document.getElementById('brand_background_image_group').style.display = 'block';

                let hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'brand_background_image_reset';
                hiddenInput.value = '1';
                document.getElementById('themeSettingsForm').appendChild(hiddenInput);

                const imagePreviewContainer = document.querySelector(
                    '#brand_background_image_group .mt-2');
                if (imagePreviewContainer) {
                    imagePreviewContainer.style.display = 'block';
                }

                document.getElementById('themeSettingsForm').submit();
            });
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll('input[type="color"]').forEach(input => {
            input.addEventListener("input", function () {
                this.style.background = this.value;
                this.value = this.value;
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.copy-button').forEach(function (button) {
            button.addEventListener('click', function () {
                const targetId = this.getAttribute('data-copy-target');
                const input = document.getElementById(targetId);
                if (input) {
                    input.select();
                    input.setSelectionRange(0, 99999);
                    document.execCommand('copy');
                }
            });
        });
    });
</script>

<script>

    $('.colorpicker-element').colorpicker({
        align: 'left',
        horizontal: true
    });

    document.addEventListener("DOMContentLoaded", function () {
        const firstRow = document.querySelector('.content .row');
        if (firstRow) {
            const firstDiv = firstRow.querySelector('div');
            if (firstDiv && firstDiv.classList.contains('col-md-12')) {
                firstDiv.classList.add('col-sm-6');
            }
        }
    });
    (
        function () {
            const tabButtons = document.querySelectorAll('#landPageSettings .tab-btn');
            const panes = document.querySelectorAll('#landPageSettings .tab-pane');

            function activateTab(btn) {
                tabButtons.forEach(b => {
                    b.classList.remove('active');
                    b.setAttribute('aria-selected', 'false');
                });

                panes.forEach(p => {
                    p.classList.remove('show', 'active');
                    p.setAttribute('aria-hidden', 'true');
                });

                btn.classList.add('active');
                btn.setAttribute('aria-selected', 'true');

                const target = btn.getAttribute('data-target');
                if (!target) return;

                const pane = document.querySelector(target);
                if (pane) {
                    pane.classList.add('show', 'active');
                    pane.setAttribute('aria-hidden', 'false');

                    const firstInput = pane.querySelector('input, select, textarea, button');
                    if (firstInput) {
                        firstInput.focus({preventScroll: true});
                    }
                }
            }

            tabButtons.forEach(btn => {
                btn.addEventListener('click', e => {
                    e.preventDefault();
                    activateTab(btn);

                    if (window.innerWidth < 768) {
                        const tabContent = document.querySelector('#landPageSettings .tab-content');
                        if (tabContent) {
                            tabContent.scrollIntoView({behavior: 'smooth'});
                        }
                    }

                    const target = btn.getAttribute('data-target');
                    if (target) {
                        history.replaceState(null, null, target);
                    }
                });
            });

            const initiallyActive = document.querySelector('#landPageSettings .tab-btn.active') || tabButtons[0];
            if (initiallyActive) {
                activateTab(initiallyActive);
            }

            function checkHash() {
                if (location.hash) {
                    const btn = document.querySelector('#landPageSettings .tab-btn[data-target="' + location.hash + '"]');
                    if (btn) {
                        activateTab(btn);
                    }
                }
            }

            window.addEventListener('hashchange', checkHash);
            checkHash();
        })();
</script>

<script>
    function toggleBackgroundInput() {
        let type = document.querySelector('[name="background_type"]').value;
        document.getElementById('background_color_group').style.display = (type === 'color') ? 'block' : 'none';
        document.getElementById('background_image_group').style.display = (type === 'image') ? 'block' : 'none';
        document.getElementById('gradient_group').style.display = (type === 'gradient') ? 'block' : 'none';
    }
</script>

<script>
    $(document).ready(function () {
        $('.select2-country').select2({
            placeholder: "{{ __('Select a country') }}",
            allowClear: true,
            width: '100%'
        });

        $('.settings-form').on('submit', function(e) {
            const btn = $(this).find('.btn-save');
            const originalText = btn.html();
            
            btn.prop('disabled', true);
            btn.html('<i class="fas fa-spinner fa-spin"></i> {{ __("Saving...") }}');
            
            setTimeout(() => {
                btn.prop('disabled', false);
                btn.html(originalText);
            }, 5000);
        });
    });

document.addEventListener('DOMContentLoaded', function() {
    const exchangeRateInput = document.getElementById('coin_exp');
    const diamondInput = document.querySelector('.user_coin_input');
    const exchangeRateHidden = document.querySelector('.exchange_rate');
    const resultSpan = document.querySelector('.exp_result');

    function calculateExchange() {
        const rate = parseFloat(exchangeRateInput.value) || 0;
        const diamonds = parseFloat(diamondInput.value) || 0;

        exchangeRateHidden.value = rate;

        if (rate > 0 && diamonds > 0) {
            const result = (diamonds * rate).toFixed(2);
            resultSpan.textContent = result + ' coins';
            resultSpan.style.display = 'inline-block';
        } else {
            resultSpan.textContent = '';
            resultSpan.style.display = 'none';
        }
    }

    exchangeRateInput.addEventListener('input', calculateExchange);
    diamondInput.addEventListener('input', calculateExchange);

    calculateExchange();
});
</script>
