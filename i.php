<?php 
include 'includes/header.php'; 
?>

<!-- ✅ Hero Section -->
<section class="bg-success text-white text-center py-5" style="background: url('assets/images/contact_us.jpg') center/cover no-repeat;">
  <div class="container">
    <h1 class="display-4 fw-bold">Empowering Agriculture. Feeding the Future.</h1>
    <p class="lead mt-3">Integrated Agribusiness & E-commerce platform for farmers, buyers, and innovators.</p>
    <a href="/products/index.php" class="btn btn-light btn-lg mt-4">🌱 Explore Marketplace</a>
  </div>
</section>

<!-- ✅ About Section -->
<section class="py-5 bg-white">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-6">
        <img src="assets/images/product.jpg" class="img-fluid rounded shadow" alt="Agribusiness">
      </div>
      <div class="col-md-6">
        <h2 class="mb-3">About AgriLink Hub</h2>
        <p>AgriLink Hub is a next-generation agribusiness platform focused on solving food security challenges. 
           We support farm setup, offer advisory services, manage farms with data-driven solutions, and enable 
           seamless buying and selling of agricultural products and equipment.</p>
        <p>We serve farmers, buyers, food processors, and agricultural entrepreneurs across Nigeria and Africa, making agriculture accessible and profitable for everyone.</p>
        <a href="/about.php" class="btn btn-success mt-3">Learn More</a>
      </div>
    </div>
  </div>
</section>

<!-- ✅ Services Section -->
<section class="py-5 bg-light text-center">
  <div class="container">
    <h2 class="mb-5">Our Core Services</h2>
    <div class="row g-4">
      
      <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
          <img src="assets/images/Farm Setup.png" class="card-img-top rounded" alt="Farm Setup">
          <div class="card-body">
            <h5 class="card-title mt-3">Farm Setup</h5>
            <p class="card-text">Comprehensive farm establishment services for sustainable agriculture.</p>
          </div>
        </div>
      </div>
      
      <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
          <img src="assets/images/Advisory Services.png" class="card-img-top rounded" alt="Advisory Services">
          <div class="card-body">
            <h5 class="card-title mt-3">Advisory Services</h5>
            <p class="card-text">Professional consulting for farmers, agro-dealers, and agri-entrepreneurs.</p>
          </div>
        </div>
      </div>
      
      <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
          <img src="assets/images/Farm Management.png" class="card-img-top rounded" alt="Farm Management">
          <div class="card-body">
            <h5 class="card-title mt-3">Farm Management</h5>
            <p class="card-text">Smart monitoring, reporting, and data-driven farm management solutions.</p>
          </div>
        </div>
      </div>
      
      <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
          <img src="assets/images/Marketplace.png" class="card-img-top rounded" alt="Marketplace">
          <div class="card-body">
            <h5 class="card-title mt-3">Marketplace</h5>
            <p class="card-text">Sell and buy agricultural goods, equipment, and produce easily and securely.</p>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>


<!-- ✅ Categories Section -->
<section class="py-5 bg-white">
  <div class="container">
    <h2 class="text-center mb-5">Explore Categories</h2>
    <div class="row g-4">
      <?php
      $categories = [
        'Cash Crops' => 'Cashew, Cocoa, Bitter Kola',
        'Vegetables & Fruits' => 'Tomatoes, Leafy Greens, Citrus',
        'Grains & Tubers' => 'Rice, Maize, Cassava, Yam',
        'Animal Produce' => 'Catfish, Poultry, Cattle, Pigs',
        'Farm Equipment' => 'Tractors, Planters, Irrigation Kits',
        'Processing Tools' => 'Grinders, Threshers, Oil Extractors',
        'Kitchenware' => 'Knives, Cooking Pots, Storage',
        'Farm Wear' => 'Boots, Gloves, Overalls'
      ];
      foreach ($categories as $title => $desc): ?>
        <div class="col-md-3">
          <div class="card h-100 text-center border-0">
            <div class="card-body">
              <h5 class="card-title"><?= $title ?></h5>
              <p class="text-muted small"><?= $desc ?></p>
              <a href="/products/index.php" class="btn btn-sm btn-outline-success">Browse</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ✅ Call to Action -->
<section class="py-5 bg-success text-white text-center">
  <div class="container">
    <h3 class="mb-3">Join us in revolutionizing African agriculture 🌍</h3>
    <a href="/register.php" class="btn btn-outline-light btn-lg">Get Started</a>
  </div>
</section>

<?php 
include 'includes/footer.php'; 
?>