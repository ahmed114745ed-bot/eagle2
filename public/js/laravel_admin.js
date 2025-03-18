$(document).ready(function () {
    $('.view-description').click(function (e) {
        e.preventDefault();

        var description = $(this).data('description');

        $('#modalDescriptionTitle').text("Full Description");
        $('#modalDescriptionContent').text(description);

        $('#descriptionModal').modal('show');
    });

    $('.view-image').click(function (e) {
        e.preventDefault();
        var imgSrc = $(this).data('img');
        $('#modalImageContent').attr('src', imgSrc);
        $('#imageModal').modal('show');
    });
});

document.addEventListener("DOMContentLoaded", function() {
    console.log("🚀 جاري تحميل خريطة جوجل...");

    var script = document.createElement('script');
    script.src = "https://maps.googleapis.com/maps/api/js?key=AIzaSyB0R_qNSq-cTvO8K4IqjwSrclNAlWn9LhQ&libraries=drawing&callback=initMap";
    script.defer = true;

    script.onerror = function() {
        console.error("❌ فشل تحميل خريطة جوجل. تأكد من مفتاح API.");
    };

    document.head.appendChild(script);
});

var map, drawingManager;
var markers = [];
var polyline = null;

function initMap() {
    setTimeout(() => {
        var mapElement = document.getElementById('map');
        if (!mapElement) {
            console.error("❌ العنصر #map غير موجود في الصفحة.");
            return;
        }

        console.log("✅ عنصر الخريطة موجود، جاري التهيئة...");

        map = new google.maps.Map(mapElement, {
            center: { lat: 23.8103, lng: 90.4125 },
            zoom: 12
        });

        drawingManager = new google.maps.drawing.DrawingManager({
            drawingMode: google.maps.drawing.OverlayType.MARKER,
            drawingControl: true,
            drawingControlOptions: {
                position: google.maps.ControlPosition.TOP_CENTER,
                drawingModes: ['marker']
            }
        });
        
        drawingManager.setMap(map);
        polyline = new google.maps.Polyline({
            path: [],
            geodesic: true,
            strokeColor: "#FF0000",
            strokeOpacity: 1.0,
            strokeWeight: 2,
            map: map
        });

        google.maps.event.addListener(drawingManager, 'overlaycomplete', function(event) {
            if (event.type === 'marker') {
                var marker = event.overlay;
                markers.push(marker);
                updatePolyline();
                  
                updateCoordinates();
                 }
        });

        function updatePolyline() {
            var path = markers.map(marker => ({
                lat: marker.getPosition().lat(),
                lng: marker.getPosition().lng()
            }));
            polyline.setPath(path);
        }

        var resetButton = document.createElement("button");
        resetButton.textContent = "🔄 إعادة تعيين";
        resetButton.style.position = "absolute";
        resetButton.style.top = "10px";
        resetButton.style.right = "10px";
        resetButton.style.padding = "8px 12px";
        resetButton.style.backgroundColor = "#ff4d4d";
        resetButton.style.color = "#fff";
        resetButton.style.border = "none";
        resetButton.style.cursor = "pointer";
        resetButton.style.borderRadius = "5px";
        resetButton.onclick = function() {
            markers.forEach(marker => marker.setMap(null));
            markers = [];
            polyline.setPath([]);
            console.log("✅ تم إعادة تعيين الخريطة ومسح جميع البيانات.");
        };

        map.controls[google.maps.ControlPosition.TOP_RIGHT].push(resetButton);
    }, 2000);
}

function updateCoordinates() {
   
    var path = polyline.getPath().getArray().map(p => ({ lat: p.lat(), lng: p.lng() }));
    
    var formattedCoordinates = path.map(p => `(${p.lat}, ${p.lng})`).join(',');

    document.querySelector("[name=coordinates]").value = formattedCoordinates;

    document.getElementById("coord-display").innerText = formattedCoordinates;
}