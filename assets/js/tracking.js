document.addEventListener("DOMContentLoaded", () => {
    const trackingForm = document.getElementById("tracking-form");
    const trackingIdInput = document.getElementById("tracking-id");
    const trackingResults = document.getElementById("tracking-results");
    const trackingError = document.getElementById("tracking-error");
    const truckInfoDetails = document.getElementById("truck-info-details");
    const refreshButton = document.getElementById("refresh-button");

    let map = null;
    let truckMarker = null;
    let currentTrackingId = null;
    let autoRefreshInterval = null;

    // Initialize Leaflet Map
    function initMap(lat, lng) {
        if (map) {
            map.setView([lat, lng], 13);
        } else {
            map = L.map("map").setView([lat, lng], 13);
            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: 
                    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            }).addTo(map);
        }

        if (truckMarker) {
            truckMarker.setLatLng([lat, lng]);
        } else {
            // Using a standard Font Awesome icon for better visibility and no need for an image
            const truckIcon = L.divIcon({
                className: 'custom-div-icon',
                html: "<i class='fas fa-truck fa-2x' style='color: var(--accent-color);'></i>",
                iconSize: [30, 30],
                iconAnchor: [15, 30]
            });
            truckMarker = L.marker([lat, lng], { icon: truckIcon }).addTo(map);
        }
    }

    // Fetch and display tracking data
    async function fetchTrackingData(id) {
        try {
            const response = await fetch(`api/track.php?id=${id}`);
            const data = await response.json();

            if (data.success) {
                trackingError.style.display = "none";
                trackingResults.style.display = "grid";

                // Update info card
                let detailsHtml = "";
                for (const [key, value] of Object.entries(data.truck_info)) {
                    detailsHtml += `<p><strong>${key}:</strong> <span>${value}</span></p>`;
                }
                truckInfoDetails.innerHTML = detailsHtml;

                // Update map
                if (data.location) {
                    initMap(data.location.lat, data.location.lng);
                    truckMarker.bindPopup(`<b>${data.truck_info["Truck Number"]}</b><br>Driver: ${data.truck_info["Driver Name"]}`).openPopup();
                } else {
                    // Handle case where location is not available
                    if (!map) initMap(27.5250, 41.6900); // Default to Ha'il if no location
                }

            } else {
                trackingResults.style.display = "none";
                trackingError.textContent = data.message;
                trackingError.style.display = "block";
                stopAutoRefresh();
            }
        } catch (error) {
            console.error("Error fetching tracking data:", error);
            trackingResults.style.display = "none";
            trackingError.textContent = "An error occurred. Please try again.";
            trackingError.style.display = "block";
            stopAutoRefresh();
        }
    }

    // Start auto-refresh
    function startAutoRefresh() {
        stopAutoRefresh(); // Stop any existing timer
        if (currentTrackingId) {
            autoRefreshInterval = setInterval(() => {
                fetchTrackingData(currentTrackingId);
            }, 30000); // Refresh every 30 seconds
        }
    }

    // Stop auto-refresh
    function stopAutoRefresh() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
            autoRefreshInterval = null;
        }
    }

    // Form submission handler
    trackingForm.addEventListener("submit", (e) => {
        e.preventDefault();
        currentTrackingId = trackingIdInput.value.trim();
        if (currentTrackingId) {
            fetchTrackingData(currentTrackingId);
            startAutoRefresh();
        }
    });

    // Refresh button handler
    refreshButton.addEventListener("click", () => {
        if (currentTrackingId) {
            fetchTrackingData(currentTrackingId);
        }
    });
});
