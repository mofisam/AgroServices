<?php include 'includes/header.php'; ?>

<style>
  .hero-section {
    background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('assets/images/farm-hero.jpg');
    background-size: cover;
    background-position: center;
    color: white;
    padding: 50px 0;
    margin-bottom: 60px;
  }
  
  .service-icon {
    font-size: 3rem;
    color: #28a745;
    margin-bottom: 20px;
    transition: transform 0.3s ease;
  }
  
  .service-card {
    border: none;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    height: 100%;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  }
  
  .service-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(40, 167, 69, 0.2);
  }
  
  .service-card:hover .service-icon {
    transform: scale(1.1);
  }
  
  .service-card-body {
    padding: 30px;
  }
  
  .cta-section {
    background: linear-gradient(135deg, #28a745 0%, #218838 100%);
    border-radius: 12px;
    overflow: hidden;
    position: relative;
    z-index: 1;
  }
  
  .cta-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('assets/images/pattern.png');
    opacity: 0.1;
    z-index: -1;
  }
  
  .section-title {
    position: relative;
    display: inline-block;
    margin-bottom: 30px;
  }
  
  .section-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: #28a745;
  }
</style>

<!-- Hero Section -->
<section class="hero-section text-center">
  <div class="container">
    <h1 class="display-4 fw-bold mb-4">Agricultural Excellence Through Innovation</h1>
    <p class="lead mb-5">Comprehensive farming solutions designed to maximize your yield and profitability</p>
    <a href="#services" class="btn btn-success btn-lg px-4 py-2">Explore Our Services</a>
  </div>
</section>

<!-- Services Section -->
<div class="container py-5" id="services">
  <div class="text-center mb-5">
    <h2 class="fw-bold section-title">Our Premium Services</h2>
    <p class="lead text-muted w-75 mx-auto">
      At <strong class="text-success">F and V Agro Services</strong>, we combine traditional farming wisdom with modern technology to deliver exceptional results for farms of all sizes.
    </p>
  </div>

  <!-- Services Cards -->
  <div class="row g-4 mb-5">
    <!-- Farm Setup -->
    <div class="col-lg-3 col-md-6">
      <div class="card service-card h-100">
        <div class="service-card-body text-center">
          <div class="service-icon">🌱</div>
          <h4 class="fw-bold mb-3">Farm Setup</h4>
          <p class="text-muted">
            Complete farm establishment from land preparation to infrastructure development, tailored to your specific agricultural needs.
          </p>
          <ul class="text-start text-muted ps-3 mt-3">
            <li>Land preparation</li>
            <li>Irrigation systems</li>
            <li>Greenhouse design</li>
            <li>Infrastructure planning</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Advisory Services -->
    <div class="col-lg-3 col-md-6">
      <div class="card service-card h-100">
        <div class="service-card-body text-center">
          <div class="service-icon">📈</div>
          <h4 class="fw-bold mb-3">Advisory Services</h4>
          <p class="text-muted">
            Data-driven agricultural consulting to optimize every aspect of your farming operations.
          </p>
          <ul class="text-start text-muted ps-3 mt-3">
            <li>Crop selection guidance</li>
            <li>Soil health analysis</li>
            <li>Pest management</li>
            <li>Sustainable practices</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Farm Management -->
    <div class="col-lg-3 col-md-6">
      <div class="card service-card h-100">
        <div class="service-card-body text-center">
          <div class="service-icon">🤝</div>
          <h4 class="fw-bold mb-3">Farm Management</h4>
          <p class="text-muted">
            Comprehensive management solutions to ensure your farm operates at peak efficiency.
          </p>
          <ul class="text-start text-muted ps-3 mt-3">
            <li>Daily operations</li>
            <li>Labor supervision</li>
            <li>Harvest scheduling</li>
            <li>Quality control</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Supply of Produce -->
    <div class="col-lg-3 col-md-6">
      <div class="card service-card h-100">
        <div class="service-card-body text-center">
          <div class="service-icon">🚚</div>
          <h4 class="fw-bold mb-3">Produce Supply</h4>
          <p class="text-muted">
            Reliable supply chain solutions connecting farm-fresh produce to markets.
          </p>
          <ul class="text-start text-muted ps-3 mt-3">
            <li>Fresh organic produce</li>
            <li>Bulk order fulfillment</li>
            <li>Direct farm-to-business</li>
            <li>Export quality standards</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- CTA Section -->
  <div class="cta-section text-white text-center p-5 my-5">
    <div class="row align-items-center">
      <div class="col-lg-8 text-lg-start">
        <h3 class="fw-bold mb-3">Ready to Transform Your Agricultural Operations?</h3>
        <p class="mb-lg-0">Our team of experts is ready to help you achieve exceptional results.</p>
      </div>
      <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
        <a href="contact.php" class="btn btn-light btn-lg px-4 py-2 rounded-pill fw-bold">Get Started Today</a>
      </div>
    </div>
  </div>
  
  <!-- Why Choose Us Section -->
  <div class="row align-items-center my-5 py-4">
    <div class="col-lg-6 mb-4 mb-lg-0">
      <img src="assets/images/team2.jpg" alt="Our Farm Team" class="img-fluid rounded shadow">
    </div>
    <div class="col-lg-6">
      <h2 class="fw-bold mb-4">Why Choose F and V Agro Services?</h2>
      <div class="d-flex mb-3">
        <div class="me-4 text-success">
          <i class="fas fa-check-circle fa-2x"></i>
        </div>
        <div>
          <h5 class="fw-bold">Decades of Combined Experience</h5>
          <p class="text-muted">Our team brings together agricultural experts with years of practical field experience.</p>
        </div>
      </div>
      <div class="d-flex mb-3">
        <div class="me-4 text-success">
          <i class="fas fa-leaf fa-2x"></i>
        </div>
        <div>
          <h5 class="fw-bold">Sustainable Practices</h5>
          <p class="text-muted">We prioritize methods that protect the environment while maximizing productivity.</p>
        </div>
      </div>
      <div class="d-flex">
        <div class="me-4 text-success">
          <i class="fas fa-chart-line fa-2x"></i>
        </div>
        <div>
          <h5 class="fw-bold">Proven Results</h5>
          <p class="text-muted">Our clients consistently achieve higher yields and better profitability.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>