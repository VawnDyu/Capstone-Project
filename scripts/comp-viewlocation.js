// detect location
navigator.geolocation.getCurrentPosition((pos) => {

    mapboxgl.accessToken = 'pk.eyJ1IjoiamVsbHliZWFucy1zbHkiLCJhIjoiY2t4NmVnYXU5MnJkNjJ1cW92ZDN1b3hndiJ9.FgwIbfJQOkbfbc1OtJHv2Q';
    const mapViewModal = new mapboxgl.Map({
        container: 'map-viewmodal',
        style: 'mapbox://styles/mapbox/satellite-streets-v9',
        center: currPositionView,
        zoom: 18
    });
});

// close modal
let viewModalClose = document.querySelector('#viewModalClose');
viewModalClose.addEventListener('click', () => {
    let viewModal = document.querySelector('.view-modal');
    viewModal.style.display = 'none';
});
