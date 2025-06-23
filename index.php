<?php 
include 'includes/header.php'; 
?>

<!-- Add this modal at the top of your body content -->
<?php if (!isset($_SESSION['user_id'])): ?>
<div class="modal fade" id="registrationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Join Our Agro Community</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-4">
                <img src="assets/images/logo.jpg" alt="Description of image" width="40">
                </div>
                <h4 class="mb-3">Get Started as a Buyer or Seller</h4>
                <p class="text-muted mb-4">Register now to access all features of our agricultural platform</p>
                
                <div class="d-grid gap-3">
                    <a href="/registration/buyer" class="btn btn-success py-3">
                        <i class="bi bi-cart-check me-2"></i> Register as Buyer
                    </a>
                    <a href="/registration/seller" class="btn btn-warning py-3 text-white">
                        <i class="bi bi-shop me-2"></i> Register as Seller
                    </a>
                    <a href="/login" class="btn btn-outline-secondary">
                        Already have an account? Sign In
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show modal after 5 seconds for non-logged-in users
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        var modal = new bootstrap.Modal(document.getElementById('registrationModal'));
        modal.show();
    }, 5000); // 5000 milliseconds = 5 seconds
});
</script>
<?php endif; ?>


<!-- ✅ Hero Section with Video Background -->
<section class="hero-section text-white text-center py-5 position-relative">
  <div class="video-background">
    <video autoplay muted loop>
      <source src="<?= BASE_URL ?>/assets/Video/homebg.mp4" type="video/mp4">
    </video>
    <div class="overlay"></div>
  </div>
  <div class="container position-relative">
    <h1 class="display-4 fw-bold">Empowering Agriculture. Feeding the Future.</h1>
    <p class="lead mt-3">Integrated Agribusiness & E-commerce platform for farmers, buyers, and innovators.</p>
    <div class="d-flex justify-content-center gap-3 mt-4">
      <a href="products/index" class="btn btn-light btn-lg">🌱 Explore Marketplace</a>
      <a href="#video-tour" class="btn btn-outline-light btn-lg">▶ Watch Intro</a>
    </div>
  </div>
</section>

<!-- ✅ Stats Counter -->
<section class="py-4 bg-success text-white">
  <div class="container">
    <div class="row text-center">
      <div class="col-md-3">
        <div class="counter">
          <h3 class="count" data-target="0">0</h3>
          <p>Farmers Empowered</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="counter">
          <h3 class="count" data-target="0">0</h3>
          <p>Agribusinesses Supported</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="counter">
          <h3 class="count" data-target="0">0</h3>
          <p>Million ₦ Processed</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="counter">
          <h3 class="count" data-target="0">0</h3>
          <p>States Covered</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ✅ Legacy Section -->
<section class="py-5 bg-dark text-white">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-6">
        <span class="badge bg-success mb-3">TRANSGENERATIONAL WEALTH</span>
        <h2 class="mb-4">Building Agricultural Legacies That Last</h2>
        <div class="d-flex mb-3">
          <div class="me-4">
            <i class="bi bi-check-circle-fill text-success fs-4"></i>
          </div>
          <div>
            <p>Agriculture rewards patience and strategic investment - not get-rich-quick schemes</p>
          </div>
        </div>
        <div class="d-flex mb-3">
          <div class="me-4">
            <i class="bi bi-check-circle-fill text-success fs-4"></i>
          </div>
          <div>
            <p>Professional management transforms farms into generational assets</p>
          </div>
        </div>
        <div class="d-flex">
          <div class="me-4">
            <i class="bi bi-check-circle-fill text-success fs-4"></i>
          </div>
          <div>
            <p>Our clients achieve 3-5× returns through structured farm management</p>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card border-0 bg-dark-light">
          <div class="card-body p-4">
            <h4 class="mb-3">Building Agricultural Legacies That Last</h4>
            <div class="timeline">
              <div class="timeline-item">
                <div class="timeline-badge bg-success"></div>
                <div class="timeline-content">
                  <h6>Laying the Foundation</h6>
                  <p>We start with strategic infrastructure and operational planning — no shortcuts, just solid ground for long-term success.</p>
                </div>
              </div>
              <div class="timeline-item">
                <div class="timeline-badge bg-warning"></div>
                <div class="timeline-content">
                  <h6>Smart Growth</h6>
                  <p>With professional management in place, farms begin to scale efficiently, increasing output and profitability.</p>
                </div>
              </div>
              <div class="timeline-item">
                <div class="timeline-badge bg-primary"></div>
                <div class="timeline-content">
                  <h6>Generational Impact</h6>
                  <p>Farms become lasting assets, delivering 3–5× returns and positioning families for long-term wealth and food security.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ✅ Video Tour Section -->
<section id="video-tour" class="py-5 bg-light">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-6">
        <div class="ratio ratio-16x9">
        <video controls>
          <source src="<?= BASE_URL ?>/assets/Video/intro.mp4" type="video/mp4">
          Your browser does not support the video tag.
        </video>  
        </div>
      </div>
      <div class="col-md-6">
        <h2 class="mb-3">Connecting Farmers to Buyers Nationwide</h2>
        <p>At F and V Agro Services, we empower farmers by connecting them directly with buyers online — no middlemen, no delays.</p>
        <ul class="list-check">
          <li>🚛 Faster and more reliable transactions</li>
          <li>📈 Better prices and more profit for farmers</li>
          <li>🤝 A trusted marketplace for agro-inputs and produce</li>
          <li>💡 Simple tools to list and sell with ease</li>
        </ul>
        <p>Join thousands of farmers already growing their income through our digital platform.</p>
        <a href="registration" class="btn btn-success mt-3">Register today</a>
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
          <img src="assets/images/product.jpg" class="card-img-top rounded" alt="Marketplace">
          <div class="card-body">
            <h5 class="card-title mt-3">Marketplace</h5>
            <p class="card-text">Sell and buy agricultural goods, equipment, and produce easily and securely.</p>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ✅ Testimonials Section -->
<section class="py-5 bg-white">
  <div class="container">
    <h2 class="text-center mb-5">What Our Clients Say</h2>
    <div class="row">
      <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex mb-3">
              <img src="assets/images/img1.jpg" class="rounded-circle me-3" width="60" height="60" alt="Client">
              <div>
                <h5 class="mb-0">Maxlife Integrated Farm</h5>
                <p class="text-muted small">Mushroom farm, Nigeria</p>
              </div>
            </div>
            <p>F and V Agro Services delivered an exceptional setup of our mushroom farm. Nearly a year later, we are still harvesting fresh mushrooms consistently. They also established several acres of maize and cassava farmland for us within just one month—a task that would have typically taken over two months. The yields from both crops were impressive. Their efficiency and expertise truly exceeded our expectations.</p>
            <div class="rating">
              <i class="bi bi-star-fill text-warning"></i>
              <i class="bi bi-star-fill text-warning"></i>
              <i class="bi bi-star-fill text-warning"></i>
              <i class="bi bi-star-fill text-warning"></i>
              <i class="bi bi-star-fill text-warning"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex mb-3">
              <img src="assets/images/img1.jpg" class="rounded-circle me-3" width="60" height="60" alt="Client">
              <div>
                <h5 class="mb-0">Skymedew Farm</h5>
                <p class="text-muted small"> Nigeria</p>
              </div>
            </div>
            <p>F and V Agro Services helped us acquire multiple acres of farmland and handled all the necessary documentation seamlessly. They prepared the land and planted cocoa, plantain, maize, and cassava—all without requiring our physical presence. Their professionalism, transparency, and reliability make them the most dependable agricultural service provider we’ve worked with.</p>
            <div class="rating">
              <i class="bi bi-star-fill text-warning"></i>
              <i class="bi bi-star-fill text-warning"></i>
              <i class="bi bi-star-fill text-warning"></i>
              <i class="bi bi-star-fill text-warning"></i>
              <i class="bi bi-star-fill text-warning"></i>
            </div>
          </div>
        </div>
      </div>
      <!-- Add 2 more testimonial cards -->
    </div>
  </div>
</section>

<!-- ✅ Blog Highlights Section -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0">Latest Agricultural Insights</h2>
      <a href="/blog" class="btn btn-sm btn-outline-success">View All Articles</a>
    </div>
    <div class="row">
      <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
          <img src="assets/images/maize.jpg" class="card-img-top" alt="Blog Post">
          <div class="card-body">
            <span class="badge bg-success mb-2">Farm Management</span>
            <h5 class="card-title">5 Irrigation Techniques That Boost Yield by 30%</h5>
            <p class="card-text">Learn water management strategies that maximize crop production while conserving resources.</p>
          </div>
          <div class="card-footer bg-transparent border-0">
            <a href="#" class="btn btn-sm btn-success">Read More</a>
          </div>
        </div>
      </div>
      <!-- Add 2 more blog cards -->
    </div>
  </div>
</section>

<!-- Investment CTA Section -->
<section class="py-5 bg-success text-white position-relative overflow-hidden">
  <div class="container position-relative">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <h2 class="display-5 fw-bold mb-3">Invest in Nigeria's Agricultural Future</h2>
        <p class="lead mb-4">Get  annual returns through our managed farm investment programs with complete transparency.</p>
        <div class="d-flex flex-wrap gap-3">
          <a href="https://wa.me/2347037997601" class="btn btn-light btn-lg px-4">
            🌱 Start Investing Today
          </a>
        </div>
      </div>
      <div class="col-lg-4 mt-4 mt-lg-0">
        <div class="card border-0 bg-success-light text-center py-4">
          <div class="card-body">
            <i class="bi bi-graph-up-arrow display-4 text-warning mb-3"></i>
            <h5>Proven Track Record</h5>
            <p class="mb-0">Average % annual returns since 2025</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Background elements 
  <div class="position-absolute top-0 end-0 opacity-10">
    <svg width="300" height="300" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
      <path fill="#FFFFFF" d="M45,-45C58.1,-29.6,68.6,-14.8,68.5,0.1C68.4,15,57.7,30.1,44.6,43.8C31.4,57.5,15.7,69.9,0.7,69.2C-14.3,68.5,-28.6,54.7,-42.3,41C-56,27.3,-69.2,13.6,-70.4,-1.2C-71.6,-16.1,-60.8,-32.1,-47.1,-47.5C-33.5,-62.8,-16.7,-77.4,-0.3,-77.1C16.2,-76.8,32.3,-61.5,45,-45Z" transform="translate(100 100)" />
    </svg>
  </div> -->
</section>

<style>
  
</style>

<?php if (!isset($_SESSION['user_id'])): ?>
<!-- ✅ Dual Registration Section -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="text-center mb-5">
      <h2>Join F and V Agro Services Hub Today</h2>
      <p class="lead">Choose your registration path to get started</p>
    </div>
    
    <div class="row g-4 justify-content-center">
      <!-- Buyer Registration Card -->
      <div class="col-md-5">
        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
          <div class="card-body text-center p-4">
            <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-block mb-4">
              <i class="bi bi-cart-check-fill text-success fs-1"></i>
            </div>
            <h3>Buyer Registration</h3>
            <p class="mb-4">Join as a buyer to access quality farm products at competitive prices from verified sellers.</p>
            <ul class="text-start mb-4 ps-4">
              <li>Browse thousands of agricultural products</li>
              <li> </li>
              <li>Access verified suppliers</li>
              <li>Secure payment options</li>
            </ul>
            <a href="registration/buyer" class="btn btn-success btn-lg px-4 py-3 stretched-link">
              Register as Buyer <i class="bi bi-arrow-right ms-2"></i>
            </a>
          </div>
        </div>
      </div>
      
      <div class="col-md-1 d-flex align-items-center justify-content-center">
        <div class="vr d-none d-md-flex" style="height: 200px;"></div>
        <span class="d-md-none py-3 text-muted">OR</span>
      </div>
      
      <!-- Seller Registration Card -->
      <div class="col-md-5">
        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
          <div class="card-body text-center p-4">
            <div class="bg-warning bg-opacity-10 p-3 rounded-circle d-inline-block mb-4">
              <i class="bi bi-shop text-warning fs-1"></i>
            </div>
            <h3>Seller Registration</h3>
            <p class="mb-4">Join as a seller to reach thousands of buyers and grow your agricultural business.</p>
            <ul class="text-start mb-4 ps-4">
              <li>Access thousands of potential buyers</li>
              <li>Low commission rates</li>
              <li>Seller protection policies</li>
              <li>Business growth tools</li>
            </ul>
            <a href="registration/seller" class="btn btn-warning btn-lg px-4 py-3 stretched-link text-white">
              Register as Seller <i class="bi bi-arrow-right ms-2"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
    
    <div class="text-center mt-5">
      <p class="text-muted">Already have an account? <a href="/login" class="text-success fw-bold">Sign In</a></p>
    </div>
  </div>
</section>
<?php endif; ?>

<?php 
include 'includes/footer.php'; 
?>

<style>
  .hero-section {
    min-height: 80vh;
    display: flex;
    align-items: center;
  }
  .video-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: -1;
  }
  .video-background video {
    min-width: 100%;
    min-height: 100%;
    object-fit: cover;
  }
  .overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
  }
  .counter h3 {
    font-size: 2.5rem;
    font-weight: 700;
  }
  .timeline {
    position: relative;
    padding-left: 30px;
  }
  .timeline-item {
    position: relative;
    padding-bottom: 20px;
  }
  .timeline-badge {
    position: absolute;
    left: -35px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;
  }
  .list-check {
    list-style: none;
    padding-left: 0;
  }
  .list-check li {
    position: relative;
    padding-left: 30px;
    margin-bottom: 10px;
  }
  .list-check li:before {
    
    font-family: "Bootstrap Icons";
    position: absolute;
    left: 0;
    color: #28a745;
  }
  .hover-shadow:hover {
    box-shadow: 0 1rem 3rem rgba(0,0,0,.1)!important;
    transform: translateY(-5px);
  }
  .transition-all {
    transition: all 0.3s ease;
  }
  .stretched-link::after {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 1;
    content: "";
  }
  .bg-success-light {
    background-color: rgba(255,255,255,0.15);
    backdrop-filter: blur(5px);
  }
</style>

<script>
  // Counter animation
  const counters = document.querySelectorAll('.count');
  const speed = 200;
  
  counters.forEach(counter => {
    const target = +counter.getAttribute('data-target');
    const count = +counter.innerText;
    const increment = target / speed;
    
    if(count < target) {
      counter.innerText = Math.ceil(count + increment);
      setTimeout(updateCount, 1);
    } else {
      counter.innerText = target;
    }
    
    function updateCount() {
      const count = +counter.innerText;
      if(count < target) {
        counter.innerText = Math.ceil(count + increment);
        setTimeout(updateCount, 1);
      } else {
        counter.innerText = target;
      }
    }
  });
</script>