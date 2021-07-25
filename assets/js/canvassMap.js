var canvassMap = {
    map: null,
    center: [41.478039, -90.529118],
    api_key: canvass_settings.api_key,
    //api_key: '5bf0ae0235df45dd926e235ce2742fc5',
    nonMarker: null,
    onMarker: null,
    mode: 'view',

    load: function () {
        canvassMap.nonMarker = L.icon({
            iconUrl: '/wp-content/plugins/canvass/assets/img/non.png',
            //shadowUrl: 'leaf-shadow.png',

            iconSize:     [20, 20], // size of the icon
            //shadowSize:   [50, 64], // size of the shadow
            iconAnchor:   [10, 10], // point of the icon which will correspond to marker's location
            //shadowAnchor: [4, 62],  // the same for the shadow
            popupAnchor:  [2, 2] // point from which the popup should open relative to the iconAnchor
        })

        canvassMap.onMarker = L.icon({
            iconUrl: '/wp-content/plugins/canvass/assets/img/on.png',
            //shadowUrl: 'leaf-shadow.png',

            iconSize:     [20, 20], // size of the icon
            //shadowSize:   [50, 64], // size of the shadow
            iconAnchor:   [10, 10], // point of the icon which will correspond to marker's location
            //shadowAnchor: [4, 62],  // the same for the shadow
            popupAnchor:  [2, 2] // point from which the popup should open relative to the iconAnchor
        })

        canvassMap.map = L.map('map').setView(canvassMap.center, 15);
/*
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(canvassMap.map);
*/
        L.tileLayer.bing({
            "bingMapsKey": bing_canvass_params.bing_api_key,
            "imagerySet": "AerialWithLabels"
        }).addTo(canvassMap.map)

        canvassMap.placeExistingMarkers();

        canvassMap.map.on('click', canvassMap.mapClick);

        L.control.custom({
            position: 'topright',
            content : '<button type="button" class="btn btn-primary canvass-view-mode canvass-mode-button">'+
                    '    <i class="fa fa-eye"></i>'+
                    '</button>'+
                    '<button type="button" class="btn btn-primary canvass-add-mode canvass-mode-button">'+
                    '    <i class="fa fa-plus-square"></i>'+
                    '</button>'+
                    '<button type="button" class="btn btn-primary canvass-delete-mode canvass-mode-button">'+
                    '    <i class="fa fa-trash"></i>'+
                    '</button>',
            classes : 'btn-group-vertical btn-group-sm',
            style :
            {
                margin: '10px',
                padding: '0px 0 0 0',
                cursor: 'pointer',
            },
            datas :
            {
                'foo': 'bar',
            },
            events :
            {
                click: function(data) {
                    var target = data.target
                    if (target.tagName.toLowerCase() == 'i') {
                        target = target.parentNode
                    }

                    var mode = 'view'
                    var buttonClasses = target.classList
                    if (buttonClasses.contains('canvass-add-mode')) {
                        mode = 'add'
                    } else if (buttonClasses.contains('canvass-delete-mode')) {
                        mode = 'delete'
                    }
                    canvassMap.changeMode(mode, target)
                },
            }
        })
        .addTo(canvassMap.map)

        canvassMap.changeMode('view', document.querySelector('.canvass-view-mode'))
    },

    placeExistingMarkers() {
        var bounds = new L.LatLngBounds();

        canvass_settings.existing_markers.forEach(function(existingMarker) {
            var marker = L.marker(
                [existingMarker.latitude, existingMarker.longitude],
                {icon: canvassMap.nonMarker}
            )
            marker.bindPopup(existingMarker.fullAddress)
            marker.addTo(canvassMap.map)
            marker.on('click', function() {
                if (canvassMap.mode == 'add') {
                    canvassMap.markerConvert(marker)
                }
                if (canvassMap.mode == 'delete') {
                    canvassMap.markerDelete(marker)
                }
            })
            bounds.extend(marker.getLatLng());
        });

        if (canvass_settings.existing_markers.length>0) {
            canvassMap.map.fitBounds(bounds);
        }
    },

    changeMode(mode, target) {
        console.log('Changing map mode', mode);

        canvassMap.map.closePopup()

        var modeButtons = document.querySelectorAll('.canvass-mode-button').forEach(function(button) {
            button.classList.add('btn-primary')
            button.classList.remove('btn-default')
        })

        target.classList.add('btn-default')
        target.classList.remove('btn-primary')

        canvassMap.mode = mode
    },

    mapClick: function (e) {
        if (canvassMap.mode == 'view') {
            canvassMap.mapClickView(e)
            return;
        }

        if (canvassMap.mode == 'add') {
            canvassMap.mapClickAdd(e)
            return;
        }

        if (canvassMap.mode == 'delete') {
            //canvassMap.mapClickDelete(e)
            return;
        }
    },

    mapClickView: function (e) {
        return;
    },

    mapClickAdd: function (e) {
        canvassMap.markerPlace(e.latlng)
        canvassMap.markerStore(e.latlng, 'add')
    },

    mapClickDelete: function (e) {
        canvassMap.markerDelete(e.latlng)
    },

    markerPlace(latlng) {
        var marker = L.marker(
            latlng,
            {icon: canvassMap.onMarker}
        )
        marker.addTo(canvassMap.map)
        return marker
    },

    markerDelete(marker) {
        canvassMap.map.removeLayer(marker)
        canvassMap.markerStore(marker._latlng, 'remove')
    },

    markerConvert(marker) {
        canvassMap.markerStore(marker._latlng, 'add')
        marker.setIcon(canvassMap.onMarker)
        return marker
    },

    markerStore(latlng, mode) {
        var consoleMessage = 'Storing new location'
        if (mode == 'remove') {
            consoleMessage = 'Removing existing location'
        }
        console.log(consoleMessage, latlng);

        var url = canvass_settings.ajax_url
        url += '?action='+canvass_settings.storage_action
        url += '&latitude='+latlng['lat']
        url += '&longitude='+latlng['lng']
        url += '&mode='+mode
        var requestGet = new Request(url, {
            method: 'GET'
        });
        fetch(requestGet)
            .then(res => res.text())
            .then(html => console.log(html))
            .catch(err => console.error(err))
    }
}
window.onload = function funLoad() {
    canvassMap.load();
}