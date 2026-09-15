import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

function initMerchantMap() {
    const container = document.getElementById('merchant-map');
    if (!container) return;

    const latInput = document.getElementById('merchant-latitude');
    const lngInput = document.getElementById('merchant-longitude');
    const searchInput = document.getElementById('merchant-search-place');
    const searchBtn = document.getElementById('merchant-search-btn');
    const geocodeBtn = document.getElementById('merchant-geocode-address');

    const readCoords = () => {
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);
        return Number.isFinite(lat) && Number.isFinite(lng) ? [lat, lng] : null;
    };

    const saved = readCoords();
    const center = saved ?? [-6.1754, 106.8272];

    const map = L.map(container, { scrollWheelZoom: false }).setView(center, saved ? 16 : 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    const icon = L.divIcon({
        className: 'merchant-marker',
        html: '<div style="width:28px;height:28px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);background:#0ea5e9;border:3px solid #fff;box-shadow:0 4px 12px rgba(2,32,71,.35);"></div>',
        iconSize: [28, 28],
        iconAnchor: [14, 26],
    });

    let marker = null;
    const setPin = (lat, lng) => {
        latInput.value = lat.toFixed(7);
        lngInput.value = lng.toFixed(7);
        if (marker) {
            marker.setLatLng([lat, lng]);
            return;
        }
        marker = L.marker([lat, lng], { icon, draggable: true }).addTo(map);
        marker.on('dragend', () => {
            const pos = marker.getLatLng();
            setPin(pos.lat, pos.lng);
        });
    };

    if (saved) {
        setPin(saved[0], saved[1]);
    }

    map.on('click', (e) => setPin(e.latlng.lat, e.latlng.lng));

    const geocode = async (q) => {
        if (!q.trim()) return false;
        try {
            const res = await fetch(
                'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&countrycodes=id&q=' + encodeURIComponent(q),
                { headers: { 'Accept': 'application/json' } }
            );
            const data = await res.json();
            if (Array.isArray(data) && data[0]) {
                const lat = parseFloat(data[0].lat);
                const lng = parseFloat(data[0].lon);
                map.setView([lat, lng], 16);
                setPin(lat, lng);
                return true;
            }
        } catch (e) {
            // fallthrough
        }
        return false;
    };

    const flagSearchError = () => {
        searchInput.classList.add('ring-2', 'ring-red-400');
        setTimeout(() => searchInput.classList.remove('ring-2', 'ring-red-400'), 1500);
    };

    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchBtn.click();
        }
    });
    searchBtn.addEventListener('click', async () => {
        if (!(await geocode(searchInput.value))) flagSearchError();
    });
    geocodeBtn.addEventListener('click', async () => {
        const address = document.querySelector('input[name="address"]')?.value?.trim() || '';
        const city = document.querySelector('input[name="city"]')?.value?.trim() || '';
        const q = [address, city].filter(Boolean).join(', ');
        if (!(await geocode(q))) flagSearchError();
    });
}

document.addEventListener('DOMContentLoaded', initMerchantMap);