<script>
    class LocationService {
        static observers = [];
        static locationObj = {
            lat: null,
            lng: null,
            location: null,
            metaData: null,
        };

        static apiKeyIpstack = null;
        static apiKeyGmaps = null;

        static ipstack(apiKey) {
            this.apiKeyIpstack = apiKey;
            return this;
        }

        static gmaps(apiKey) {
            this.apiKeyGmaps = apiKey;
            return this;
        }

        static async subscribe(callback) {
            this.observers.push(callback);

            async function onError() {
                console.log('Getting location via IPStack');
                if (LocationService.apiKeyIpstack) {
                    try {
                        const response = await fetch(
                            `https://api.ipstack.com/check?access_key=${LocationService.apiKeyIpstack}`,
                        );
                        const data = await response.json();
                        LocationService.locationObj.lat = data.latitude;
                        LocationService.locationObj.lng = data.longitude;
                        LocationService.locationObj.location =
                            data.city + ', ' + data.region_name;
                        LocationService.locationObj.metaData = data;
                        LocationService.notify(LocationService.locationObj);
                    } catch (error) {
                        console.error(
                            'Error fetching location via IPStack:',
                            error,
                        );
                        LocationService.notifyError(error);
                    }
                }
            }

            async function onSuccess(position) {
                const { latitude, longitude } = position.coords;

                LocationService.locationObj.lat = latitude;
                LocationService.locationObj.lng = longitude;

                console.log('Getting fine data from Google');

                if (LocationService.apiKeyGmaps) {
                    try {
                        const response = await fetch(
                            `https://maps.googleapis.com/maps/api/geocode/json?latlng=${latitude},${longitude}&sensor=false&key=${LocationService.apiKeyGmaps}`,
                        );
                        const data = await response.json();

                        if (data.results[0]) {
                            LocationService.locationObj.metaData =
                                data.results[0];
                        } else {
                            onError();
                        }

                        LocationService.notify(LocationService.locationObj);
                    } catch (error) {
                        console.error(
                            'Error fetching location via Google Maps:',
                            error,
                        );
                        LocationService.notifyError(error);
                    }
                }
            }

            console.log('Getting location');
            navigator.geolocation.getCurrentPosition(onSuccess, onError);

            return this;
        }

        static notify(locationObj) {
            this.observers.forEach((observer) => observer(locationObj));
            return this;
        }

        static notifyError(error) {
            this.observers.forEach((observer) => observer({ error }));
            return this;
        }
    }

    const fireLocationEvent = (type, data) => {
        document.dispatchEvent(
            new CustomEvent('location-service', {
                detail: {
                    type: type,
                    data: data.metaData,
                },
            }),
        );
    };

    document.addEventListener('DOMContentLoaded', () =>
        LocationService.ipstack('{{ config('esign.services.ipstack_key') }}')
            .gmaps('{{ config('esign.services.gmaps_key') }}')
            .subscribe(function (location) {
                if (location.error) {
                    fireLocationEvent('error', location.error);
                    console.error('Error:', location.error);
                } else {
                    fireLocationEvent('success', location);
                    console.log('Received location data:', location);
                }
            }),
    );
</script>
