<?php include 'includes/header.php'; ?>

<style>
  .service-icon {
    font-size: 2.5rem;
    color: #28a745;
    margin-bottom: 15px;
  }
  .service-card:hover {
    transform: translateY(-5px);
    transition: 0.3s ease;
  }
</style>

<div class="container py-5">
  <div class="text-center mb-5">
    <h1 class="fw-bold text-success">Our Services</h1>
    <p class="lead text-muted">
      At <strong>F and V Agro Services</strong>, we provide end-to-end agricultural solutions that help farms grow smarter, faster, and more profitably.
    </p>
  </div>

  <!-- Services Cards -->
  <div class="row g-4 mb-5">
    <!-- Farm Setup -->
    <div class="col-md-6">
      <div class="card service-card shadow-sm border-0 h-100 p-4 text-center">
        <div class="service-icon">🌿</div>
        <h4 class="fw-bold">Farm Setup</h4>
        <p class="text-muted">
          From land preparation to irrigation and greenhouse design, we offer tailored farm setup services for new and expanding farmers.
          Whether it's a crop farm or livestock, we engineer it for productivity.
        </p>
      </div>
    </div>

    <!-- Advisory Services -->
    <div class="col-md-6">
      <div class="card service-card shadow-sm border-0 h-100 p-4 text-center">
        <div class="service-icon">📊</div>
        <h4 class="fw-bold">Advisory Services</h4>
        <p class="text-muted">
          Our experts provide professional advice on agronomy, soil health, pest control, and sustainable practices.
          We guide your decisions with insights backed by data, research, and real-world field experience.
        </p>
      </div>
    </div>

    <!-- Farm Management -->
    <div class="col-md-6">
      <div class="card service-card shadow-sm border-0 h-100 p-4 text-center">
        <div class="service-icon">🚜</div>
        <h4 class="fw-bold">Farm Management</h4>
        <p class="text-muted">
          Let us manage your farm for you. From labor supervision to harvest scheduling, our end-to-end farm management 
          service ensures optimal yield, minimal waste, and high profitability.
        </p>
      </div>
    </div>

    <!-- Supply of Produce -->
    <div class="col-md-6">
      <div class="card service-card shadow-sm border-0 h-100 p-4 text-center">
        <div class="service-icon">🥬</div>
        <h4 class="fw-bold">Supply of Produce</h4>
        <p class="text-muted">
          We supply fresh and organic agricultural produce directly from our network of farms to your doorstep.
          Bulk orders for retailers, food processors, and exporters? We’ve got you covered — always fresh, always reliable.
        </p>
      </div>
    </div>
  </div>

  <!-- CTA Block -->
  <div class="bg-success text-white text-center rounded p-5">
    <h4 class="fw-bold mb-2">💬 Ready to Grow Your Agribusiness?</h4>
    <p class="mb-3">Let’s work together to unlock the full potential of your land or idea.</p>
    <a href="contact.php" class="btn btn-light btn-lg">📞 Get in Touch</a>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
