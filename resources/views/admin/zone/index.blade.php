<div class="container">
    <h2 class="text-center mb-4">تحديد المنطقة على الخريطة</h2>

    <form action="{{ route('admin.zones.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name">اسم المنطقة:</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <div class="form-group">
            <label for="coordinates">الإحداثيات (GeoJSON):</label>
            <textarea class="form-control" id="coordinates" name="coordinates" rows="4" readonly required></textarea>
        </div>

        <div id="map" style="height: 400px; border: 1px solid #ccc; margin-top: 10px;"></div>

        <button type="submit" class="btn btn-primary mt-3">حفظ</button>
    </form>
</div>

<!-- تحميل سكريبت خريطة جوجل -->
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=drawing&callback=initMap" defer></script>

<script>
    function initMap() {
        var map = new google.maps.Map(document.getElementById('map'), {
            center: { lat: 23.8103, lng: 90.4125 }, // يمكنك تغيير الإحداثيات حسب منطقتك
            zoom: 12
        });

        var drawingManager = new google.maps.drawing.DrawingManager({
            drawingMode: google.maps.drawing.OverlayType.POLYGON,
            drawingControl: true,
            drawingControlOptions: {
                position: google.maps.ControlPosition.TOP_CENTER,
                drawingModes: ['polygon']
            }
        });

        drawingManager.setMap(map);

        google.maps.event.addListener(drawingManager, 'overlaycomplete', function(event) {
            var geoJson = event.overlay.getPath().getArray().map(p => ({
                lat: p.lat(),
                lng: p.lng()
            }));

            document.getElementById("coordinates").value = JSON.stringify(geoJson);
            console.log("تم تحديث الإحداثيات:", geoJson);
        });
    }
</script>