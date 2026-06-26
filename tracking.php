<?php include 'includes/header.php'; ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<style>
.tracking-section {
    padding: 80px 0;
}
.page-header{
background-color: #7A2A8A;
}
.tracking-search-box {
    max-width: 700px;
    margin: 0 auto 40px auto;
    background: var(--white-color);
    padding: 30px;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}
.tracking-search-box form .form-group {
    display: flex;
    gap: 10px;
}
.tracking-search-box input {
    flex-grow: 1;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 10px;
}
.tracking-search-box button {
    white-space: nowrap;
}
.tracking-results-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    align-items: flex-start;
}
.map-area h2, .info-card-area h2 {
    color: var(--primary-color);
    margin-bottom: 20px;
}
#map {
    height: 450px;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}
.map-note {
    font-style: italic;
    color: #777;
    margin-top: 10px;
    text-align: center;
}
.info-card.glassmorphism {
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(10px);
    padding: 30px;
    border-radius: var(--border-radius);
    border: 1px solid rgba(255, 255, 255, 0.8);
    box-shadow: var(--shadow);
}
#truck-info-details p {
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
}
#truck-info-details strong {
    color: var(--primary-color);
}
#refresh-button {
    margin-top: 20px;
    width: 100%;
    background: var(--primary-color);
    color: var(--white-color);
}
@media (max-width: 992px) {
    .tracking-results-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<section class="page-header">
    <div class="container">
        <h1 data-aos="fade-up">Track Your Shipment</h1>
        <p data-aos="fade-up" data-aos-delay="100">Enter your Truck Number or Shipment ID to see the live location.</p>
    </div>
</section>

<section class="content-section tracking-section">
    <div class="container">
        <div class="tracking-search-box" data-aos="zoom-in">
            <form id="tracking-form">
                <div class="form-group">
                    <input type="text" id="tracking-id" class="form-control" placeholder="e.g., TRK-245 or SHP-1032" required>
                    <button type="submit" class="btn btn-primary">Track Now</button>
                </div>
            </form>
            <div id="tracking-error" class="alert alert-danger" style="display: none; margin-top: 20px;"></div>
        </div>

        <div id="tracking-results" class="tracking-results-grid" style="display: none;">
            <!-- Map Area -->
            <div class="map-area" data-aos="fade-right">
                <h2>Live Location (Simulated)</h2>
                <div id="map"></div>
                <p class="map-note">Note: This is a simulated location for demonstration purposes.</p>
            </div>

            <!-- Truck Info Card -->
            <div class="info-card-area" data-aos="fade-left">
                <div class="info-card glassmorphism">
                    <h2>Shipment Details</h2>
                    <div id="truck-info-details">
                        <!-- Details will be injected here by JavaScript -->
                    </div>
                    <button id="refresh-button" class="btn btn-secondary btn-sm"><i class="fas fa-sync-alt"></i> Refresh</button>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="assets/js/tracking.js"></script>

