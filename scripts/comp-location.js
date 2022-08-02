// detect location
let currPosition = [];

navigator.geolocation.getCurrentPosition((pos) => {
    currPosition.push(pos.coords.longitude);
    currPosition.push(pos.coords.latitude);
    
    let userLongitude = document.querySelector('#longitude');
    let userLatitude = document.querySelector('#latitude');

    let userLongitudeAddModal = document.querySelector('#longitude-addmodal');
    let userLatitudeAddModal = document.querySelector('#latitude-addmodal');

    mapboxgl.accessToken = 'pk.eyJ1IjoiamVsbHliZWFucy1zbHkiLCJhIjoiY2t4NmVnYXU5MnJkNjJ1cW92ZDN1b3hndiJ9.FgwIbfJQOkbfbc1OtJHv2Q';
    const map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/satellite-streets-v9',
        center: currPosition,
        zoom: 18
    });

    const mapAddModal = new mapboxgl.Map({
        container: 'map-addmodal',
        style: 'mapbox://styles/mapbox/satellite-streets-v9',
        center: currPosition,
        zoom: 18
    });

    const marker = new mapboxgl.Marker().setLngLat(currPosition).addTo(map); 
    const marker2 = new mapboxgl.Marker().setLngLat(currPosition).addTo(mapAddModal); 

    function add_marker(event){
        var coordinates = event.lngLat;
        userLongitude.value = coordinates.lng;
        userLatitude.value = coordinates.lat;

        userLongitudeAddModal.value = coordinates.lng;
        userLatitudeAddModal.value = coordinates.lat;

        marker.setLngLat(coordinates).addTo(map);
        marker2.setLngLat(coordinates).addTo(mapAddModal);

        // for distance
        const map_b = new mapboxgl.Map({
            container: 'map_b',
            style: 'mapbox://styles/mapbox/satellite-streets-v9',
            center: [coordinates.lng, coordinates.lat],
            zoom: 18
        });

        // for modal distance
        const map_bAddModal = new mapboxgl.Map({
            container: 'map_b-addmodal',
            style: 'mapbox://styles/mapbox/satellite-streets-v9',
            center: [coordinates.lng, coordinates.lat],
            zoom: 18
        });

        const map_b_size = document.querySelector('.map_b_size');
        const map_b_sizeAddModal = document.querySelector('.map_b_size-addmodal');

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


        map_b.on('load', () => {
            map_b.addSource('geojson', {
                'type': 'geojson',
                'data': geojson
            });

            // Add styles to the map
            map_b.addLayer({
                id: 'measure-points',
                type: 'circle',
                source: 'geojson',
                paint: {
                    'circle-radius': 5,
                    'circle-color': '#000'
                },
                filter: ['in', '$type', 'Point']
            });

            map_b.addLayer({
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

            map_b.on('click', (e) => {
                const features = map_b.queryRenderedFeatures(e.point, {
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

                    map_b_size.value = `${distance.toLocaleString()}km`;
                }

                map_b.getSource('geojson').setData(geojson);
            });
        });

        map_bAddModal.on('load', () => {
            map_bAddModal.addSource('geojson', {
                'type': 'geojson',
                'data': geojson
            });

            // Add styles to the map
            map_bAddModal.addLayer({
                id: 'measure-points',
                type: 'circle',
                source: 'geojson',
                paint: {
                    'circle-radius': 5,
                    'circle-color': '#000'
                },
                filter: ['in', '$type', 'Point']
            });

            map_bAddModal.addLayer({
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

            map_bAddModal.on('click', (e) => {
                const features = map_bAddModal.queryRenderedFeatures(e.point, {
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
                    map_b_sizeAddModal.value = `${distance.toLocaleString()}km`;
                }

                map_bAddModal.getSource('geojson').setData(geojson);
            });
        });

        // for distance
        map_b.on('mousemove', (e) => {
            const features = map_b.queryRenderedFeatures(e.point, {
                layers: ['measure-points']
            });
            // Change the cursor to a pointer when hovering over a point on the map.
            // Otherwise cursor is a crosshair.
            map_b.getCanvas().style.cursor = features.length
                ? 'pointer'
                : 'crosshair';
        });

        // for distance modal
        map_bAddModal.on('mousemove', (e) => {
            const features = map_bAddModal.queryRenderedFeatures(e.point, {
                layers: ['measure-points']
            });
            // Change the cursor to a pointer when hovering over a point on the map.
            // Otherwise cursor is a crosshair.
            map_bAddModal.getCanvas().style.cursor = features.length
                ? 'pointer'
                : 'crosshair';
        });
    }

    map.on('click', add_marker);
    mapAddModal.on('click', add_marker);


    const geocoder = new MapboxGeocoder({
        accessToken: mapboxgl.accessToken, 
        mapboxgl: mapboxgl, 
        marker: false,
        zoom: 18
    });

    map.addControl(geocoder);
    mapAddModal.addControl(geocoder);
});