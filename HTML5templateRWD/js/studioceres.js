/**
 * Studio Ceres — distance from Earth center (km), WGS84 ellipsoid, h=0.
 * Falls back to a rounded nominal radius if geolocation is unavailable.
 */

const EARTH_RADIUS_M = 6378137;
const EARTH_FLAT = 1 / 298.257223563;

const getDistanceFromEarthCenterKm = (latitudeDeg, longitudeDeg) => {
	const phi = (latitudeDeg * Math.PI) / 180;
	const lambda = (longitudeDeg * Math.PI) / 180;
	const sinPhi = Math.sin(phi);
	const cosPhi = Math.cos(phi);
	const e2 = 2 * EARTH_FLAT - EARTH_FLAT * EARTH_FLAT;
	const n = EARTH_RADIUS_M / Math.sqrt(1 - e2 * sinPhi * sinPhi);
	const x = n * cosPhi * Math.cos(lambda);
	const y = n * cosPhi * Math.sin(lambda);
	const z = n * (1 - e2) * sinPhi;
	return Math.sqrt(x * x + y * y + z * z) / 1000;
};

const formatKm = (value) => {
	const rounded = Math.round(value);
	return rounded.toLocaleString(undefined, { maximumFractionDigits: 0 });
};

const setDistanceText = (km) => {
	const el = document.getElementById("distance-km");
	if (!el) {
		return;
	}
	el.textContent = formatKm(km);
};

const handleGeoSuccess = (position) => {
	const { latitude, longitude } = position.coords;
	const km = getDistanceFromEarthCenterKm(latitude, longitude);
	setDistanceText(km);
};

const handleGeoError = () => {
	setDistanceText(6371);
};

const handleDomContentLoaded = () => {
	setDistanceText(6371);

	if (!navigator.geolocation) {
		return;
	}

	navigator.geolocation.getCurrentPosition(handleGeoSuccess, handleGeoError, {
		enableHighAccuracy: false,
		timeout: 15000,
		maximumAge: 300000,
	});
};

document.addEventListener("DOMContentLoaded", handleDomContentLoaded);
