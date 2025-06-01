<!-- ✅ Enhanced Footer -->
<footer class="footer bg-dark text-white pt-5 pb-4">
  <div class="container">
    <div class="row g-4">
      <!-- Company Info -->
      <div class="col-lg-4 mb-4 mb-lg-0">
        <div class="d-flex align-items-center mb-3">
          <img src="assets/images/logo.jpg" alt="F and V Agro Services" height="40" class="me-2">
          <h5 class="mb-0 text-white">F and V Agro Services</h5>
        </div>
        <p class="mb-3">Empowering farmers. Connecting markets. Driving food security through technology and innovation.</p>
        
        <!-- Social Media -->
        <div class="social-icons">
          <a href="https://www.facebook.com/profile.php?id=100083032495869" class="text-white me-2" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
          <!--<a href="#" class="text-white me-2" aria-label="Twitter"><i class="bi bi-x"></i></a>-->
          <a href="https://www.instagram.com/falade_yinka?igsh=YTZxbmEyd3Q2NmR5" class="text-white me-2" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          <a href="https://www.linkedin.com/company/f-and-v-services/" class="text-white" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="col-md-4 col-lg-2 mb-4 mb-md-0">
        <h6 class="text-uppercase fw-bold mb-3">Quick Links</h6>
        <ul class="list-unstyled">
          <li class="mb-2"><a href="products/index.php" class="text-white-50 text-decoration-none hover-white">Marketplace</a></li>
          <li class="mb-2"><a href="services.php" class="text-white-50 text-decoration-none hover-white">Services</a></li>
          <li class="mb-2"><a href="blog.php" class="text-white-50 text-decoration-none hover-white">Blog</a></li>
          <li class="mb-2"><a href="about.php" class="text-white-50 text-decoration-none hover-white">About Us</a></li>
          <li><a href="contact.php" class="text-white-50 text-decoration-none hover-white">Contact</a></li>
        </ul>
      </div>

      <!-- Resources -->
      <div class="col-md-4 col-lg-2 mb-4 mb-md-0">
        <h6 class="text-uppercase fw-bold mb-3">Resources</h6>
        <ul class="list-unstyled">
          <li class="mb-2"><a href="faq.php" class="text-white-50 text-decoration-none hover-white">FAQs</a></li>
          <li class="mb-2"><a href="team.php" class="text-white-50 text-decoration-none hover-white">Meet Our Team</a></li>
          <li class="mb-2"><a href="pricing.php" class="text-white-50 text-decoration-none hover-white">Pricing</a></li>
          <li><a href="policy.php" class="text-white-50 text-decoration-none hover-white">Privacy Policy</a></li>
        </ul>
      </div>

      <!-- Contact Info -->
      <div class="col-md-4 col-lg-4">
        <h6 class="text-uppercase fw-bold mb-3">Contact Us</h6>
        <ul class="list-unstyled text-white-50">
          <li class="mb-2 d-flex align-items-center">
            <i class="bi bi-envelope me-2"></i>
            <span>info@fandvagroservices.com</span>
          </li>
          <li class="mb-2 d-flex align-items-center">
            <i class="bi bi-telephone me-2"></i>
            <span>+234 703 799 7601</span>
          </li>
          <li class="d-flex align-items-start">
            <i class="bi bi-geo-alt me-2 mt-1"></i>
            <span> Ibadan, Nigeria</span>
          </li>
        </ul>
        
      </div>
    </div>

    <!-- Copyright -->
    <div class="border-top border-secondary mt-4 pt-3 text-center text-white-50">
      <div class="d-flex flex-wrap justify-content-center">
        <span class="me-2">&copy; <?= date('Y') ?> F and V Agro Services.</span>
        <span>All rights reserved.</span>
      </div>
    </div>
  </div>
</footer>

<!-- Back to Top Button -->
<a href="#" class="btn btn-success btn-back-to-top position-fixed bottom-0 end-0 m-3 rounded-circle shadow" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
  <i class="bi bi-arrow-up"></i>
</a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Back to top button functionality
  const backToTopButton = document.querySelector('.btn-back-to-top');
  window.addEventListener('scroll', () => {
    if (window.pageYOffset > 300) {
      backToTopButton.style.display = 'flex';
    } else {
      backToTopButton.style.display = 'none';
    }
  });
</script>

<style>
  /* Custom styles for the footer */
  .hover-white:hover {
    color: white !important;
    transition: color 0.2s ease;
  }
  
  .social-icons a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background-color: rgba(255,255,255,0.1);
    transition: background-color 0.2s ease;
  }
  
  .social-icons a:hover {
    background-color: rgba(255,255,255,0.2);
    color: white !important;
  }
  
  .btn-back-to-top {
    display: none;
    z-index: 99;
  }
</style>
</body>
