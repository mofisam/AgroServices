<?php
// About Us Page Meta Variables
$page_title = "About F and V Agro Services Limited | Nigeria's Trusted Agricultural Consultancy & Platform";
$page_description = "Discover how F and V Agro Services Limited is solving Nigeria's food crisis through sustainable farm setup, professional farm management, and digital market access.";
$page_keywords = "about F and V Agro, agricultural consultancy Nigeria, farm setup services, farm management Nigeria, agribusiness consulting, food security solutions, digital agricultural platform";
$og_image = "https://www.fandvagroservices.com.ng/assets/images/logo.jpg";
$current_url = "https://www.fandvagroservices.com.ng/about_us";

// Include your header file
include 'includes/header.php';
include_once 'includes/tracking.php';
?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "AboutPage",
  "name": "About F and V Agro Services Limited",
  "description": "Nigeria's trusted agricultural consulting firm and agribusiness platform solving food crisis through sustainable farm setup, management and digital market access",
  "publisher": {
    "@type": "Organization",
    "name": "F and V Agro Services Limited",
    "founder": {
      "@type": "Person",
      "name": "Founder Name",
      "description": "Agricultural digital transformation expert"
    },
    "foundingDate": "2024",
    "numberOfEmployees": "4",
    "address": {
      "@type": "PostalAddress",
      "addressCountry": "Nigeria"
    }
  }
}
</script>

<style>
/* Modern UI/UX Styles */
:root {
  --primary-green: #28a745;
  --primary-green-dark: #218838;
  --primary-green-light: #d4edda;
  --secondary-color: #6c757d;
  --dark-bg: #343a40;
  --light-bg: #f8f9fa;
  --transition-smooth: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
}

/* Hero Section */
.about-hero {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 4rem 0;
  margin-bottom: 3rem;
  border-radius: 0 0 50px 50px;
  position: relative;
  overflow: hidden;
}

.about-hero::before {
  content: "🌾";
  position: absolute;
  font-size: 15rem;
  opacity: 0.1;
  right: -2rem;
  bottom: -2rem;
  transform: rotate(-15deg);
}

.about-hero h1 {
  font-size: 3rem;
  font-weight: 800;
  background: linear-gradient(120deg, #28a745, #20c997);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 1rem;
  animation: fadeInUp 0.8s ease;
}

.about-hero .lead {
  font-size: 1.3rem;
  color: #495057;
  max-width: 800px;
  margin: 0 auto;
  animation: fadeInUp 1s ease;
}

/* Enhanced Highlight Card */
.about-highlight {
  background: white;
  border-radius: 20px;
  padding: 2.5rem;
  margin-bottom: 4rem;
  box-shadow: 0 20px 40px rgba(0,0,0,0.05);
  border: none;
  position: relative;
  overflow: hidden;
  transition: var(--transition-smooth);
}

.about-highlight:hover {
  transform: translateY(-5px);
  box-shadow: 0 30px 60px rgba(40, 167, 69, 0.15);
}

.about-highlight::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 6px;
  height: 100%;
  background: linear-gradient(180deg, #28a745, #20c997);
  border-radius: 3px;
}

.about-highlight h4 {
  font-size: 1.8rem;
  color: #28a745;
  margin-bottom: 1.2rem;
  font-weight: 700;
}

.about-highlight p {
  font-size: 1.1rem;
  line-height: 1.8;
  color: #495057;
  margin-bottom: 0;
}

/* Modern Card Designs */
.modern-card {
  background: white;
  border-radius: 20px;
  padding: 2rem;
  height: 100%;
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
  transition: var(--transition-smooth);
  border: 1px solid rgba(0,0,0,0.03);
  position: relative;
  overflow: hidden;
}

.modern-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 30px 60px rgba(40, 167, 69, 0.12);
  border-color: rgba(40, 167, 69, 0.2);
}

.modern-card::after {
  content: "";
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 4px;
  background: linear-gradient(90deg, #28a745, #20c997);
  transform: scaleX(0);
  transition: var(--transition-smooth);
}

.modern-card:hover::after {
  transform: scaleX(1);
}

.modern-card .card-icon {
  font-size: 3rem;
  margin-bottom: 1.5rem;
  display: inline-block;
  background: var(--primary-green-light);
  width: 80px;
  height: 80px;
  line-height: 80px;
  text-align: center;
  border-radius: 50%;
  transition: var(--transition-smooth);
}

.modern-card:hover .card-icon {
  background: var(--primary-green);
  color: white;
  transform: rotateY(180deg);
}

/* Specialty List Styling */
.specialty-list {
  list-style: none;
  padding: 0;
}

.specialty-list li {
  padding: 12px 0 12px 35px;
  position: relative;
  font-size: 1.1rem;
  border-bottom: 1px dashed #e9ecef;
  transition: var(--transition-smooth);
}

.specialty-list li:hover {
  transform: translateX(5px);
  color: var(--primary-green);
}

.specialty-list li:last-child {
  border-bottom: none;
}

.specialty-list li:before {
  content: "🌾";
  position: absolute;
  left: 0;
  font-size: 1.2rem;
  animation: bounce 2s infinite;
}

/* Stats Cards */
.stat-card {
  background: white;
  border-radius: 20px;
  padding: 2rem 1rem;
  text-align: center;
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
  transition: var(--transition-smooth);
  border: 1px solid rgba(0,0,0,0.03);
}

.stat-card:hover {
  transform: translateY(-5px) scale(1.02);
  box-shadow: 0 20px 40px rgba(40, 167, 69, 0.15);
  border-color: var(--primary-green);
}

.stat-number {
  font-size: 3rem;
  font-weight: 800;
  color: var(--primary-green);
  margin-bottom: 0.5rem;
  line-height: 1;
  background: linear-gradient(135deg, #28a745, #20c997);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.stat-label {
  font-size: 1rem;
  color: #6c757d;
  font-weight: 500;
  margin: 0;
}

/* Image Styling */
.about-image-wrapper {
  position: relative;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.about-image-wrapper img {
  transition: var(--transition-smooth);
  width: 100%;
  height: auto;
}

.about-image-wrapper:hover img {
  transform: scale(1.05);
}

.about-image-wrapper::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, rgba(40, 167, 69, 0.2), rgba(32, 201, 151, 0.2));
  z-index: 1;
  opacity: 0;
  transition: var(--transition-smooth);
}

.about-image-wrapper:hover::before {
  opacity: 1;
}

/* Section Titles */
.section-title {
  text-align: center;
  margin-bottom: 3rem;
  position: relative;
}

.section-title h2 {
  font-size: 2.5rem;
  font-weight: 800;
  color: #212529;
  margin-bottom: 1rem;
  position: relative;
  display: inline-block;
}

.section-title h2::after {
  content: "";
  position: absolute;
  bottom: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: 80px;
  height: 4px;
  background: linear-gradient(90deg, #28a745, #20c997);
  border-radius: 2px;
}

.section-title p {
  font-size: 1.2rem;
  color: #6c757d;
  max-width: 700px;
  margin: 0 auto;
}

/* CTA Section */
.cta-modern {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  border-radius: 30px;
  padding: 4rem;
  margin: 4rem 0;
  position: relative;
  overflow: hidden;
}

.cta-modern::before {
  content: "🌾🌽🥬";
  position: absolute;
  font-size: 8rem;
  opacity: 0.1;
  right: -1rem;
  bottom: -2rem;
  transform: rotate(-10deg);
  white-space: nowrap;
}

.cta-modern h3 {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
  color: white;
}

.cta-modern p {
  font-size: 1.2rem;
  margin-bottom: 2rem;
  color: rgba(255,255,255,0.9);
}

.cta-button {
  background: white;
  color: #28a745;
  border: none;
  padding: 1rem 3rem;
  font-size: 1.2rem;
  font-weight: 600;
  border-radius: 50px;
  transition: var(--transition-smooth);
  box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.cta-button:hover {
  transform: translateY(-3px);
  box-shadow: 0 15px 30px rgba(0,0,0,0.2);
  background: #f8f9fa;
  color: #218838;
}

/* Animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-5px);
  }
}

.fade-in-up {
  animation: fadeInUp 0.8s ease forwards;
}

/* Spacing & Layout */
.content-section {
  margin-bottom: 5rem;
}

/* Responsive Design */
@media (max-width: 768px) {
  .about-hero h1 {
    font-size: 2.2rem;
  }
  
  .about-hero .lead {
    font-size: 1.1rem;
  }
  
  .section-title h2 {
    font-size: 2rem;
  }
  
  .cta-modern {
    padding: 3rem 1.5rem;
  }
  
  .cta-modern h3 {
    font-size: 1.8rem;
  }
  
  .stat-number {
    font-size: 2.5rem;
  }
}
</style>

<!-- Hero Section -->
<div class="about-hero">
  <div class="container text-center">
    <h1 class="display-3 fw-bold">About F and V Agro Services Limited</h1>
    <p class="lead mx-auto">Solving the food crisis through sustainable farm setup for investors, farm management and digital market access.</p>
    <div class="mt-4">
      <span class="badge bg-success me-2 px-3 py-2">🌱 Sustainable Farming</span>
      <span class="badge bg-success me-2 px-3 py-2">📊 Expert Consulting</span>
      <span class="badge bg-success px-3 py-2">🌍 Market Access</span>
    </div>
  </div>
</div>

<div class="container">
  <!-- Commitment Section -->
  <div class="content-section">
    <div class="about-highlight fade-in-up">
      <h4>🌾 Our Commitment to Food Security</h4>
      <p>At F and V Agro Services Limited, we are committed to addressing the growing challenge of food scarcity by establishing sustainable crop and livestock farms for investors while connecting local producers to national markets. We believe that access to nutritious food is a fundamental right—not a luxury. Through innovative digital solutions, strong community engagement, and strategic partnerships, we are enabling farmers to produce more efficiently and ensuring agricultural products reach the markets where they are most needed. Our approach combines modern agricultural practices, technology, and market connectivity to create a more resilient and sustainable food system across Nigeria and beyond.</p>
    </div>
  </div>

  <!-- Who We Are Section -->
  <div class="content-section">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div class="about-image-wrapper">
          <img src="assets/images/ads.jpg" alt="F and V Agro Services Limited - Agricultural Consulting and Farm Management" class="img-fluid">
        </div>
      </div>
      <div class="col-lg-6">
        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 mb-3">🌱 Who We Are</span>
        <h2 class="display-5 fw-bold mb-4">Building a Sustainable Agricultural Future</h2>
        <p class="lead mb-4">F and V Agro Services Limited is a Nigerian-based agricultural consulting firm and agribusiness platform dedicated to supporting farmers, empowering buyers, and digitalizing agricultural trade.</p>
        
        <h5 class="fw-bold mb-3">We specialize in:</h5>
        <ul class="specialty-list mb-4">
          <li>Farm setup and management for crops and livestock</li>
          <li>Agribusiness consulting and advisory services</li>
          <li>Connecting buyers and sellers of agricultural produce</li>
          <li>Facilitating access to agricultural inputs, machinery, and services</li>
        </ul>
        
        <div class="d-flex gap-3">
          <div class="bg-light rounded-3 p-3 text-center flex-fill">
            <div class="fw-bold text-success h3 mb-0">2024</div>
            <small class="text-muted">Founded</small>
          </div>
          <div class="bg-light rounded-3 p-3 text-center flex-fill">
            <div class="fw-bold text-success h3 mb-0">4+</div>
            <small class="text-muted">States</small>
          </div>
          <div class="bg-light rounded-3 p-3 text-center flex-fill">
            <div class="fw-bold text-success h3 mb-0">24/7</div>
            <small class="text-muted">Support</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Mission & Vision Cards -->
  <div class="content-section">
    <div class="row g-4">
      <div class="col-md-6">
        <div class="modern-card h-100">
          <div class="card-icon">🎯</div>
          <h3 class="fw-bold mb-3">Our Mission</h3>
          <p class="text-muted mb-0">To revolutionize the food system through sustainable agricultural consultancy and innovative agribusiness solutions, helping farmers increase their productivity, access better markets, and enabling communities to enjoy reliable access to quality food.</p>
        </div>
      </div>
      <div class="col-md-6">
        <div class="modern-card h-100">
          <div class="card-icon">🚀</div>
          <h3 class="fw-bold mb-3">Our Vision</h3>
          <p class="text-muted mb-0">To become a leading agricultural consultancy and agribusiness platform in Africa, driving food security, reducing food wastage, and transforming agricultural trade through digital innovation.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- What We Stand For Section -->
  <div class="content-section">
    <div class="section-title">
      <h2>What We Stand For</h2>
      <p>Our core commitments drive everything we do</p>
    </div>
    
    <div class="row g-4">
      <div class="col-md-6 col-lg-3">
        <div class="modern-card text-center h-100">
          <div class="card-icon mx-auto">🏭</div>
          <h5 class="fw-bold mb-3">Sustainable Farm Setup</h5>
          <p class="text-muted mb-0">Establishing sustainable farms for investors, ensuring long-term productivity and profitability.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="modern-card text-center h-100">
          <div class="card-icon mx-auto">👨‍🌾</div>
          <h5 class="fw-bold mb-3">Farmer Empowerment</h5>
          <p class="text-muted mb-0">Empowering farmers with knowledge, resources, and continuous support for growth.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="modern-card text-center h-100">
          <div class="card-icon mx-auto">🔄</div>
          <h5 class="fw-bold mb-3">Direct Market Access</h5>
          <p class="text-muted mb-0">Connecting producers directly to markets, eliminating middlemen and maximizing value.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="modern-card text-center h-100">
          <div class="card-icon mx-auto">💡</div>
          <h5 class="fw-bold mb-3">Technology & Innovation</h5>
          <p class="text-muted mb-0">Driving food security through cutting-edge technology and innovative solutions.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Impact Stats Section -->
  <div class="content-section">
    <div class="section-title">
      <h2>Our Impact So Far</h2>
      <p>Making a difference in Nigerian agriculture</p>
    </div>
    
    <div class="row g-4">
      <div class="col-md-3 col-6">
        <div class="stat-card">
          <div class="stat-number">1,200+</div>
          <p class="stat-label">Happy Customers</p>
          <small class="text-success">⭐ 4.9/5 rating</small>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="stat-card">
          <div class="stat-number">600+</div>
          <p class="stat-label">Active Farmers</p>
          <small class="text-success">🌱 Growing network</small>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="stat-card">
          <div class="stat-number">200+</div>
          <p class="stat-label">Products Listed</p>
          <small class="text-success">📦 Weekly updates</small>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="stat-card">
          <div class="stat-number">98%</div>
          <p class="stat-label">Satisfaction Rate</p>
          <small class="text-success">🏆 Industry leader</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Team CTA -->
  <div class="text-center mb-5">
    <div class="modern-card p-5 bg-light">
      <h2 class="fw-bold mb-3">👥 Meet the Team Behind the Innovation</h2>
      <p class="text-muted mb-4 fs-5">Our passionate team of agricultural experts and technology innovators are working tirelessly to transform Nigeria's agricultural landscape.</p>
      <a href="team" class="btn btn-success btn-lg px-5 py-3 rounded-pill">
        🌟 Meet Our Experts
        <span class="ms-2">→</span>
      </a>
    </div>
  </div>

  <!-- Modern CTA Section -->
  <div class="cta-modern text-center text-white">
    <h3>💬 Ready to Partner With Us?</h3>
    <p class="fs-5">Let's transform agriculture together and build a food-secure future.</p>
    <div class="d-flex justify-content-center gap-3 flex-wrap">
      <a href="contact" class="cta-button">📞 Contact Us Today</a>
      <a href="#" class="btn btn-outline-light btn-lg px-5 py-3 rounded-pill">📋 Schedule Consultation</a>
    </div>
  </div>
</div>

<!-- Floating Contact Button (Optional Enhancement) -->
<div class="position-fixed bottom-0 end-0 p-4" style="z-index: 99;">
  <a href="contact" class="btn btn-success rounded-pill shadow-lg px-4 py-3">
    💬 Let's Talk
    <span class="ms-2">→</span>
  </a>
</div>

<?php include 'includes/footer.php'; ?>