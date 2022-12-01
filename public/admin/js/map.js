"use strict";

App.initLeaflet = function () {
  var area = $('[name="area"]').val(),
      lat = $('[name="lat"]'),
      lng = $('[name="lng"]');
  App.initLeafletMap();

  if ($('#leaflet-map').hasClass('drawable')) {
    App.initFreeDraw(area);
  }

  if (lat && lng) {
    App.initLeafletMarker();
  }
};

App.initLeafletMarker = function () {
  var pos = App.initMapCenter();
  App.st.marker = L.marker(pos, {
    draggable: $('#leaflet-map').hasClass('draggable') ? true : false,
    autoPan: true
  }).addTo(App.st.map);
  App.st.marker.on('dragend', function () {
    var newLatLng = App.st.marker.getLatLng();
    App.changeLanLngValues(newLatLng);
  });
};

App.changeLanLngValues = function (latLng) {
  $('[name="lat"]').val(latLng.lat), $('[name="lng"]').val(latLng.lng);
};

App.initLeafletMap = function () {
  var center = App.initMapCenter();
  $('#city_id').on('change', function () {
    App.st.map.setView(App.initMapCenter(), 13);
  });
  App.st.map = L.map('leaflet-map').setView(center, 13);
  L.gridLayer.googleMutant({
    type: 'roadmap' // 'roadmap', 'satellite', 'terrain' and 'hybrid'

  }).addTo(App.st.map);
};

App.initFreeDraw = function (data) {
  if (!data) {
    data = '[]';
  }

  data = JSON.parse(data);
  App.st.drawnItems = L.featureGroup().addTo(App.st.map);
  App.st.drawControl = new L.Control.Draw({
    edit: {
      featureGroup: App.st.drawnItems,
      poly: {
        allowIntersection: false
      }
    },
    draw: {
      polygon: {
        allowIntersection: false,
        showArea: true
      },
      polyline: false,
      rectangle: false,
      circle: false,
      marker: false,
      circlemarker: false
    }
  });
  App.st.editControl = new L.Control.Draw({
    edit: {
      featureGroup: App.st.drawnItems,
      poly: {
        allowIntersection: false
      }
    },
    draw: false
  });

  if (App.st.action != 'view') {
    App.st.map.addControl(App.st.drawControl);
  }

  App.st.map.on("draw:created", function (event) {
    var layer = event.layer;
    App.st.drawnItems.addLayer(layer);
    var json = App.st.drawnItems.toGeoJSON();

    if ("features" in json && json.features.length && "geometry" in json.features[0]) {
      json = json.features[0].geometry.coordinates[0];
      json = json.map(function (point) {
        return [point[1], point[0]];
      });
      $('[name="area"]').val(JSON.stringify(json)).change();
      var latLng = App.st.drawnItems.getBounds().getCenter();
      App.moveMarker(latLng);
    }

    App.st.map.removeControl(App.st.drawControl);
    App.st.map.addControl(App.st.editControl);
  });
  App.st.map.on("draw:deleted", function (e) {
    App.st.map.removeControl(App.st.editControl);
    App.st.map.addControl(App.st.drawControl);
    $('[name="area"]').val(null).change();
  });
  App.st.map.on("draw:edited", function () {
    var json = App.st.drawnItems.toGeoJSON();

    if ("features" in json && json.features.length && "geometry" in json.features[0]) {
      json = json.features[0].geometry.coordinates[0];
      json = json.map(function (point) {
        return [point[1], point[0]];
      });
      $('[name="area"]').val(JSON.stringify(json)).change();
      var latLng = App.st.drawnItems.getBounds().getCenter();
      App.moveMarker(latLng);
    }
  });

  if (data.length) {
    console.log(data);
    var polygon = L.polygon(data);
    polygon.addTo(App.st.drawnItems);
    App.st.map.fitBounds(polygon.getBounds());

    if (App.st.action != 'view') {
      App.st.map.removeControl(App.st.drawControl);
      App.st.map.addControl(App.st.editControl);
    }
  }
};

App.initMapCenter = function () {
  var center = {
    lat: 50.4506,
    lng: 30.5230
  };
  var latEl = $('[name="lat"]'),
      lngEl = $('[name="lng"]'),
      latCityEl = $('[name="city-lat"]'),
      lngCityEl = $('[name="city-lng"]');

  if (latCityEl.length && lngCityEl.length) {
    if (latCityEl.val() && lngCityEl.val()) {
      center = {
        lat: Number(latCityEl.val()),
        lng: Number(lngCityEl.val())
      };
    }
  }

  if (latEl.length && lngEl.length) {
    if (latEl.val() && lngEl.val()) {
      center = {
        lat: Number(latEl.val()),
        lng: Number(lngEl.val())
      };
    }
  }

  return center;
};

App.moveMarker = function (pos) {
  if (App.st.map) App.st.map.setView(pos, App.st.map.getZoom());
  if (App.st.marker) App.st.marker.setLatLng(pos);
  App.changeLanLngValues(pos);
  $('[name="lat"]').change(), $('[name="lng"]').change();
}, App.refreshMap = function (o) {
  console.log(o);
  var geocoder = new google.maps.Geocoder();

  if (o.address) {
    var address = o.address;

    if ($('[name="city_name"]').length) {
      address += ' ' + $('[name="city_name"]').val();
    } else {
      if ($('[name="city_id"]').length) {
        var selects = $('[name="city_id"]').select2('data');
        address += ' ' + selects[0].text;
      }
    }

    geocoder.geocode({
      'address': address
    }, function (results, status) {
      if (status == 'OK') {
        console.log(results[0]);
        var pos = {
          lat: results[0].geometry.location.lat(),
          lng: results[0].geometry.location.lng()
        };
        App.moveMarker(pos);
      }
    });
  }
};

App.initMap = function () {};

App.showOtherAreas = function (items) {
  App.st.otherItems = L.featureGroup().addTo(App.st.map);
  items.map(function (item) {
    if (item.area.length) {
      var polygon = L.polygon(JSON.parse(item.area));
      polygon.setStyle({
        fillColor: '#f3aa17',
        fillOpacity: 0.1,
        color: '#f3aa17',
        opacity: 0.25
      });
      polygon.addTo(App.st.otherItems);
      polygon.bringToBack();
      polygon.bindPopup("#".concat(item.id, ". ").concat(item.name));
    }
  });
};