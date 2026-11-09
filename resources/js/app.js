// The new-idea form: shrinks photos on the phone before upload, and turns the
// phone's location into a place name with OpenStreetMap.
const MAX_SIDE = 1200;

function shrink(file) {
    return new Promise((resolve, reject) => {
        const image = new Image();
        image.onload = () => {
            const scale = Math.min(1, MAX_SIDE / Math.max(image.width, image.height));
            const canvas = document.createElement('canvas');
            canvas.width = Math.round(image.width * scale);
            canvas.height = Math.round(image.height * scale);
            canvas.getContext('2d').drawImage(image, 0, 0, canvas.width, canvas.height);
            canvas.toBlob((blob) => (blob ? resolve(blob) : reject(new Error('Could not read the photo'))), 'image/jpeg', 0.85);
            URL.revokeObjectURL(image.src);
        };
        image.onerror = reject;
        image.src = URL.createObjectURL(file);
    });
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('ideaForm', ($wire) => ({
        preview: null,
        shrinking: false,
        locating: false,
        locationNote: '',

        async pick(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.shrinking = true;
            try {
                const blob = await shrink(file);
                this.preview = URL.createObjectURL(blob);
                const photo = new File([blob], 'spot.jpg', { type: 'image/jpeg' });
                await new Promise((resolve, reject) => $wire.upload('photo', photo, resolve, reject));
            } catch {
                this.preview = null;
            } finally {
                this.shrinking = false;
            }
        },

        locate() {
            if (!navigator.geolocation) {
                this.locationNote = 'This browser can’t share its location.';
                return;
            }
            this.locating = true;
            navigator.geolocation.getCurrentPosition(async ({ coords }) => {
                $wire.latitude = Number(coords.latitude.toFixed(6));
                $wire.longitude = Number(coords.longitude.toFixed(6));
                try {
                    const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&zoom=16&lat=${coords.latitude}&lon=${coords.longitude}`;
                    const place = await (await fetch(url, { headers: { 'Accept-Language': 'en' } })).json();
                    const a = place.address ?? {};
                    $wire.place = place.name || a.tourism || a.attraction || a.neighbourhood || a.suburb || a.village || a.town || a.city || '';
                    this.locationNote = 'Location saved. Edit the name if it’s not quite right.';
                } catch {
                    this.locationNote = 'Location saved, but no place name came back. Type it in.';
                }
                this.locating = false;
            }, () => {
                this.locating = false;
                this.locationNote = 'Couldn’t get your location.';
            }, { enableHighAccuracy: true, timeout: 10000 });
        },
    }));
});
