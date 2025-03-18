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

var map;
var circles = [];
var activePath = [];
var lines = [];
var lastCircle = null;
var polygon = null;

// إعدادات الدوائر (لون وحجم)
var circleOptions = {
    radius: 170,
    strokeColor: "#FF0000",
    strokeOpacity: 1.0,
    strokeWeight: 2,
    fillColor: "#FF0000",
    fillOpacity: 0.8
};

// إعدادات المضلع عند الإغلاق
var polygonOptions = {
    strokeColor: "#FF0000",
    strokeOpacity: 1.0,
    strokeWeight: 2,
    fillColor: "#FFCCCC",
    fillOpacity: 0.5
};

function initMap() {
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

    
    map.addListener('click', function(event) {
        addCircle(event.latLng);
        updateCoordinates();
    });

    
    // التحقق من وجود الزر قبل إضافته لمنع التكرار
    if (!document.getElementById("reset-map-btn")) {
        var resetButton = document.createElement("button");
        resetButton.textContent = "🔄 إعادة تعيين";
        resetButton.id = "reset-map-btn";
        resetButton.style.cssText = "position:absolute;top:10px;right:10px;padding:8px 12px;background-color:#ff4d4d;color:#fff;border:none;cursor:pointer;border-radius:5px;";
        resetButton.onclick = resetMap;
        map.controls[google.maps.ControlPosition.TOP_RIGHT].push(resetButton);
    }
}

function addCircle(position) {
    if (circles.length > 2 && isSamePosition(position, circles[0].getCenter())) {
        closePolygon();
        return;
    }

    var circle = new google.maps.Circle({
        center: position,
        radius: circleOptions.radius,
        strokeColor: circleOptions.strokeColor,
        strokeOpacity: circleOptions.strokeOpacity,
        strokeWeight: circleOptions.strokeWeight,
        fillColor: circleOptions.fillColor,
        fillOpacity: circleOptions.fillOpacity,
        map: map
    });

    circles.push(circle);

    circle.addListener('click', function() {
        connectToLastPoint(position);
        updateCoordinates();
    });

    if (lastCircle) {
        drawLine(lastCircle.getCenter(), position);
    }

    lastCircle = circle;
    activePath.push(position);
}

function connectToLastPoint(position) {
    if (lastCircle) {
        drawLine(lastCircle.getCenter(), position);
        lastCircle = { getCenter: () => position };
        activePath.push(position);
    }

    if (circles.length > 2 && isSamePosition(position, circles[0].getCenter())) {
        closePolygon();
    }
}

function drawLine(start, end) {
    var line = new google.maps.Polyline({
        path: [start, end],
        geodesic: true,
        strokeColor: "#FF0000",
        strokeOpacity: 1.0,
        strokeWeight: 2,
        map: map
    });

    lines.push(line);
}

function closePolygon() {
    if (polygon) {
        polygon.setMap(null);
    }

    polygon = new google.maps.Polygon({
        paths: activePath,
        strokeColor: polygonOptions.strokeColor,
        strokeOpacity: polygonOptions.strokeOpacity,
        strokeWeight: polygonOptions.strokeWeight,
        fillColor: polygonOptions.fillColor,
        fillOpacity: polygonOptions.fillOpacity,
        map: map
    });

    lines.forEach(line => line.setMap(null));
    lines = [];

    console.log("✅ المضلع تم إغلاقه بنجاح!");
    updateCoordinates();
}

function resetMap() {
    circles.forEach(circle => circle.setMap(null));
    circles = [];

    lines.forEach(line => line.setMap(null));
    lines = [];

    if (polygon) {
        polygon.setMap(null);
        polygon = null;
    }

    activePath = [];
    lastCircle = null;
    document.getElementById("coord-display").innerText = "";
    document.querySelector("[name=coordinates]").value = "";
}

function updateCoordinates() {
    var formattedCoordinates = activePath.map(p => `(${p.lat()}, ${p.lng()})`).join(', ');
    document.querySelector("[name=coordinates]").value = formattedCoordinates;
    document.getElementById("coord-display").innerText = formattedCoordinates;
}

function isSamePosition(pos1, pos2) {
    return pos1.lat().toFixed(6) === pos2.lat().toFixed(6) && pos1.lng().toFixed(6) === pos2.lng().toFixed(6);
}