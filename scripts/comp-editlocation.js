// detect location

navigator.geolocation.getCurrentPosition((pos) => {

    let userLongitudeEditModal = document.querySelector('#longitude-editmodal');
    let userLatitudeEditModal = document.querySelector('#latitude-editmodal');

    mapboxgl.accessToken = 'pk.eyJ1IjoiamVsbHliZWFucy1zbHkiLCJhIjoiY2t4NmVnYXU5MnJkNjJ1cW92ZDN1b3hndiJ9.FgwIbfJQOkbfbc1OtJHv2Q';
    const mapEditModal = new mapboxgl.Map({
        container: 'map-editmodal',
        style: 'mapbox://styles/mapbox/satellite-streets-v9',
        center: currPositionEdit,
        zoom: 18
    });

    const marker2 = new mapboxgl.Marker().setLngLat(currPositionEdit).addTo(mapEditModal); 

    function add_marker(event){
        var coordinates = event.lngLat;
        userLongitudeEditModal.value = coordinates.lng;
        userLatitudeEditModal.value = coordinates.lat;

        marker2.setLngLat(coordinates).addTo(mapEditModal);

        // for modal distance
        const map_bEditModal = new mapboxgl.Map({
            container: 'map_b-editmodal',
            style: 'mapbox://styles/mapbox/satellite-streets-v9',
            center: [coordinates.lng, coordinates.lat],
            zoom: 18
        });

        const map_b_sizeEditModal = document.querySelector('.map_b_size-editmodal');

        // GeoJSON object to hold our measurement features
        const geojson = {
            'type': 'FeatureCollection',
            'features': []
        };

        // Used to draw a line between points
        const linestring = {
            'type': 'Feature',
            'geometry': {
                'type': 'LineString',
                'coordinates': []
            }
        };

        map_bEditModal.on('load', () => {
            map_bEditModal.addSource('geojson', {
                'type': 'geojson',
                'data': geojson
            });

            // Add styles to the map
            map_bEditModal.addLayer({
                id: 'measure-points',
                type: 'circle',
                source: 'geojson',
                paint: {
                    'circle-radius': 5,
                    'circle-color': '#000'
                },
                filter: ['in', '$type', 'Point']
            });

            map_bEditModal.addLayer({
                id: 'measure-lines',
                type: 'line',
                source: 'geojson',
                layout: {
                    'line-cap': 'round',
                    'line-join': 'round'
                },
                paint: {
                    'line-color': '#000',
                    'line-width': 2.5
                },
                filter: ['in', '$type', 'LineString']
            });

            map_bEditModal.on('click', (e) => {
                const features = map_bEditModal.queryRenderedFeatures(e.point, {
                    layers: ['measure-points']
                });

                // Remove the linestring from the group
                // so we can redraw it based on the points collection.
                if (geojson.features.length > 1) geojson.features.pop();

                // If a feature was clicked, remove it from the map.
                if (features.length) {
                    const id = features[0].properties.id;
                    geojson.features = geojson.features.filter(
                        (point) => point.properties.id !== id
                    );
                } else {
                    const point = {
                        'type': 'Feature',
                        'geometry': {
                            'type': 'Point',
                            'coordinates': [e.lngLat.lng, e.lngLat.lat]
                        },
                        'properties': {
                            'id': String(new Date().getTime())
                        }
                    };

                    geojson.features.push(point);
                }

                if (geojson.features.length > 1) {
                    linestring.geometry.coordinates = geojson.features.map(
                        (point) => point.geometry.coordinates
                    );

                    geojson.features.push(linestring);

                    // Populate the distanceContainer with total distance
                    const value = document.createElement('pre');
                    const distance = turf.length(linestring);
                    map_b_sizeEditModal.value = `${distance.toLocaleString()}km`;
                }

                map_bEditModal.getSource('geojson').setData(geojson);
            });
        });

        // for distance modal
        map_bEditModal.on('mousemove', (e) => {
            const features = map_bEditModal.queryRenderedFeatures(e.point, {
                layers: ['measure-points']
            });
            // Change the cursor to a pointer when hovering over a point on the map.
            // Otherwise cursor is a crosshair.
            map_bEditModal.getCanvas().style.cursor = features.length
                ? 'pointer'
                : 'crosshair';
        });
    }

    mapEditModal.on('click', add_marker);


    const geocoder = new MapboxGeocoder({
        accessToken: mapboxgl.accessToken, 
        mapboxgl: mapboxgl, 
        marker: false,
        zoom: 18
    });

    mapEditModal.addControl(geocoder);
});


// close modal
let editModalClose = document.querySelector('#editModalClose');
editModalClose.onclick = () => {
    let editModal = document.querySelector('.edit-modal');
    editModal.style.display = 'none';
}