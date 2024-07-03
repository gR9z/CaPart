(function() {
        const citySelect = document.querySelector('#event_city');
        const placeSelect = document.querySelector('#event_place');

        citySelect.addEventListener('change', function () {
            const cityId = this.value;

            if (!cityId) {
                placeSelect.innerHTML = '<option value="">-- choose a city first --</option>';
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
                } catch (error) {
                    console.error('Error fetching places:', error);
                }
            }

            fetchPlaces(cityId);
        });
})();
