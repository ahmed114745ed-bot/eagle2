<div class="card mt-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <strong>تحديد الدول المغطاة</strong>
        <button type="button" class="btn btn-sm btn-light" onclick="clearAllSelections()">
            <i class="fa fa-times"></i> إلغاء تحديد الكل
        </button>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <span class="badge" style="background-color: #4CAF50;">■</span> الدول التابعة لك
            <span class="badge" style="background-color: #e0e0e0; color: #333;">■</span> متاحة للتحديد
            <span class="badge" style="background-color: #B0BEC5; color: #fff;">■</span> محجوزة لمدير آخر
        </div>
        <div id="world-map" style="width: 100%; height: 480px;"></div>
        <hr>
        <div id="selected-countries-list" class="mt-2"></div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/jvectormap-next/jquery-jvectormap.css" rel="stylesheet">

<script>
(function() {
    if (window.__countryMapInitialized) return;
    window.__countryMapInitialized = true;

    function loadScriptsSequentially(scripts, callback) {
        if (!scripts.length) return callback();
        const [first, ...rest] = scripts;
        $.getScript(first)
            .done(() => loadScriptsSequentially(rest, callback))
            .fail((xhr, status, error) => {
            
                console.error('[Map Error] فشل تحميل:', first, error);
                window.location.reload();
            });
    }

    const scripts = [
        'https://cdn.jsdelivr.net/npm/jvectormap-next/jquery-jvectormap.min.js',
        'https://cdn.jsdelivr.net/npm/jvectormap-content/world-mill.js'
    ];

    loadScriptsSequentially(scripts, initWorldMap);

    function initWorldMap() {
        console.log('%c[Map Init] ✅ مكتبات الخريطة Loaded', 'color:#28a745; font-weight:bold;');

        $(document).ready(function() {
            const countries = {!! $countriesJson !!};
            const selectedCountries = {!! $selectedCountriesJson !!};
            const currentAreaManagerId = {{ $currentAreaManagerId ?? 'null' }};

            const countryMap = {};
            const regionColors = {};
            const myCountries = []; 

            countries.forEach(country => {
                if (!country.iso2) return;
                const iso = country.iso2.toUpperCase();
                countryMap[iso] = country;

                if (country.area_manager_id === currentAreaManagerId) {
                    regionColors[iso] = '#4CAF50';
                    myCountries.push(iso); 
                } else if (country.area_manager_id && 
                          (!country.area_manager || country.area_manager.default !== 1)) {
                    regionColors[iso] = '#B0BEC5';
                } else {
                    regionColors[iso] = '#e0e0e0';
                }
            });

            $('#world-map').empty();

            const $map = $('#world-map').vectorMap({
                map: 'world_mill',
                backgroundColor: '#f8f9fa',
                zoomOnScroll: false,
                regionStyle: {
                    initial: {
                        fill: '#e0e0e0',
                        stroke: '#ffffff',
                        "stroke-width": 1
                    },
                    hover: {
                        "fill-opacity": 0.8,
                        cursor: 'pointer'
                    },
                    selected: {
                        fill: '#2196F3'
                    }
                },
                series: {
                    regions: [{
                        values: regionColors,
                        scale: {
                            '#e0e0e0': '#e0e0e0',
                            '#4CAF50': '#4CAF50', 
                            '#B0BEC5': '#B0BEC5', 
                            '#2196F3': '#2196F3' 
                        },
                        normalizeFunction: 'polynomial'
                    }]
                },
                onRegionClick: function (e, code) {
    const mapObj = $('#world-map').vectorMap('get', 'mapObject');
    const iso = code.toUpperCase();
    const country = countryMap[iso];

    // منع التحديد إذا الدولة تابعة لمدير آخر
    if (
        country &&
        country.area_manager_id &&
        country.area_manager_id !== currentAreaManagerId &&
        (!country.area_manager || country.area_manager.default !== 1)
    ) {
        e.preventDefault();
        if (typeof toastr !== 'undefined') {
            toastr.warning('❌ لا يمكن تحديد هذه الدولة لأنها تابعة لمدير آخر.');
        }
        return;
    }

    // الحصول على الدول المحددة حاليًا
    let selectedRegions = mapObj.getSelectedRegions();
    const isSelected = selectedRegions.includes(code);

    if (isSelected) {
        // ✅ إذا كانت محددة → أزلها من القائمة
        selectedRegions = selectedRegions.filter(c => c !== code);
        mapObj.clearSelectedRegions(); // امسح التحديد القديم
        mapObj.setSelectedRegions(selectedRegions); // أعد التحديد الجديد بدون هذه الدولة

        // أعد اللون الأصلي للدولة بعد الإزالة
        if (country && country.area_manager_id === currentAreaManagerId) {
            mapObj.series.regions[0].setValues({ [iso]: '#4CAF50' }); // الأخضر = تخصك
        } else {
            mapObj.series.regions[0].setValues({ [iso]: '#e0e0e0' }); // الرمادي = عادي
        }

    } else {
        // ✅ إذا غير محددة → أضفها إلى التحديد
        selectedRegions.push(code);
        mapObj.setSelectedRegions(selectedRegions);
        mapObj.series.regions[0].setValues({ [iso]: '#2196F3' }); // الأزرق = جديد
    }

    // تحديث البيانات في الحقول والقوائم
    updateSelectedCountries(mapObj);
    updateCountryList(mapObj);
},

                onRegionTipShow: function(e, el, code) {
                    const c = countryMap[code.toUpperCase()];
                    if (c) {
                        let text = `<strong>${c.name}</strong>`;
                        
                        if (c.area_manager_id === currentAreaManagerId) {
                            text += `<br><small style="color:#4CAF50; font-weight:bold;">✅ تابعة لك بالفعل</small>`;
                        } else if (c.area_manager_id && (!c.area_manager || c.area_manager.default !== 1)) {
                            text += `<br><small style="color:red;">❌ تابعة لمدير آخر</small>`;
                        } else if (c.area_manager && c.area_manager.default === 1) {
                            text += `<br><small style="color:#2196F3;">📍 تابعة للمدير الافتراضي - يمكن اختيارها</small>`;
                        } else {
                            text += `<br><small style="color:#666;">📍 متاحة للتحديد</small>`;
                        }
                        
                        el.html(`<div style="padding:5px;">${text}</div>`);
                    }
                }
            });

            const mapObj = $('#world-map').vectorMap('get', 'mapObject');

            if (myCountries.length > 0) {
                console.log('✅ تحديد الدول التابعة للمستخدم:', myCountries);
                mapObj.setSelectedRegions(myCountries);
                
                myCountries.forEach(iso => {
                    mapObj.series.regions[0].setValues({ [iso]: '#2196F3' });
                });
                
                updateSelectedCountries(mapObj);
                updateCountryList(mapObj);
            }

            function updateSelectedCountries(mapObj) {
                const selected = mapObj.getSelectedRegions();
                const selectedData = selected.map(code => {
                    const c = countryMap[code];
                    return c ? { id: c.id, name: c.name, iso2: c.iso2 } : null;
                }).filter(Boolean);
                $('#covered-countries-input').val(JSON.stringify(selectedData));
                console.log('📝 تحديث covered_countries:', selectedData);
            }

            function updateCountryList(mapObj) {
                const selected = mapObj.getSelectedRegions();
                const html = selected.map(code => {
                    const c = countryMap[code];
                    if (!c) return '';
                    
                    const badgeColor = (c.area_manager_id === currentAreaManagerId) 
                        ? 'badge-success' 
                        : 'badge-primary';
                    
                    return `<span class="badge ${badgeColor} m-1" style="cursor:pointer;" onclick="removeCountry('${code}')">
                                ${c.name} <i class='fa fa-times'></i>
                            </span>`;
                }).join('');
                $('#selected-countries-list').html(html || '<span class="text-muted">لم يتم اختيار دول بعد</span>');
            }

            window.removeCountry = function(code) {
                const mapObj = $('#world-map').vectorMap('get', 'mapObject');
                const iso = code.toUpperCase();
                const country = countryMap[iso];
                
                mapObj.setSelectedRegions(mapObj.getSelectedRegions().filter(c => c !== code));
                
                if (country && country.area_manager_id === currentAreaManagerId) {
                    mapObj.series.regions[0].setValues({ [iso]: '#4CAF50' });
                } else {
                    mapObj.series.regions[0].setValues({ [iso]: '#e0e0e0' });
                }
                
                updateSelectedCountries(mapObj);
                updateCountryList(mapObj);
            };

            window.clearAllSelections = function() {
                const mapObj = $('#world-map').vectorMap('get', 'mapObject');
                const selectedRegions = mapObj.getSelectedRegions();
                
                selectedRegions.forEach(iso => {
                    const country = countryMap[iso];
                    if (country) {
                        if (country.area_manager_id === currentAreaManagerId) {
                            mapObj.series.regions[0].setValues({ [iso]: '#4CAF50' });
                        } else {
                            mapObj.series.regions[0].setValues({ [iso]: '#e0e0e0' });
                        }
                    }
                });
                
                mapObj.clearSelectedRegions();
                
                updateSelectedCountries(mapObj);
                updateCountryList(mapObj);
                
                console.log('🗑️ تم إلغاء تحديد جميع الدول');
            };
        });
    }
})();
</script>