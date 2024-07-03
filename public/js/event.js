(function () {
    const citySelect = document.querySelector('#event_city');
    const placeSelectWrapper = document.querySelector('#place-select-wrapper');
    const placeSelect = document.querySelector('#event_place');

    const placeDetails = document.querySelector('#place-details');
    const placeStreet = document.querySelector('#place-street');
    const placeLongitude = document.querySelector('#place-longitude');
    const placeLatitude = document.querySelector('#place-latitude');

    placeSelectWrapper.style.display = 'none';

    citySelect.addEventListener('change', function () {
        const cityId = this.value;

        if (!cityId) {
            placeSelect.innerHTML = '<option value="">-- choose a city first --</option>';
            placeSelectWrapper.style.display = 'none';
            return;
        }

        async function fetchPlaces(cityId) {
            try {
                const response = await fetch(`/events/places?cityId=${cityId}`);
                const data = await response.json();

                placeSelect.innerHTML = '';

                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = '-- select a place --';
                placeSelect.appendChild(defaultOption);

                data.forEach(place => {
                    const option = document.createElement('option');
                    option.value = place.id;
                    option.textContent = place.name;
                    placeSelect.appendChild(option);
                });

                placeSelectWrapper.style.display = 'block';
            } catch (error) {
                console.error('Error fetching places:', error);
                placeSelectWrapper.style.display = 'none';
            }
        }

        fetchPlaces(cityId);
    });

    placeSelect.addEventListener('change', function () {
        const placeId = this.value;

        if (!placeId) {
            placeDetails.style.display = 'none';
            return;
        }

        async function fetchPlaceDetails(placeId) {
            try {
                const response = await fetch(`/events/place-details?placeId=${placeId}`);
                const data = await response.json();

                placeStreet.textContent = data.street;
                placeLongitude.textContent = data.longitude;
                placeLatitude.textContent = data.latitude;

                placeDetails.style.display = 'block';
            } catch (error) {
                console.error('Error fetching place details:', error);
                placeDetails.style.display = 'none';
            }
        }

        fetchPlaceDetails(placeId);
    });
})();
