<?php
// Team Page Meta Variables - OPTIMIZED
$page_title = "Our Agricultural Experts & Team | F and V Agro Services Nigeria";
$page_description = "Meet F and V Agro Services' team of certified agricultural professionals in Nigeria: Falade Yinka O. (Founder), Falade Victory O. (Co-Founder), Adewale Damilare Owoade, and Samuel Alao (Tech Lead). Expert farm consultants driving Nigeria's agro-commerce revolution.";
$page_keywords = "Falade Yinka, Falade Yinka O., Falade Victory, Adewale Damilare Owoade, Samuel Alao, agricultural experts Nigeria, farm consultants Nigeria, agro-commerce team, F&V Agro professionals, farming technology experts Nigeria, agricultural consultants Abeokuta, poultry farming experts, livestock specialists Nigeria";
$og_image = "https://www.fandvagroservices.com.ng/assets/images/logo.jpg";
$current_url = "https://www.fandvagroservices.com.ng/team.php";
$canonical_url = $current_url;

include('includes/header.php');
include_once 'includes/tracking.php';

?>
<!-- Enhanced Schema Markup -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "AboutPage",
  "name": "F and V Agro Services Team - Agricultural Experts in Nigeria",
  "description": "Meet the professional agricultural team at F&V Agro Services Nigeria",
  "url": "<?php echo $current_url; ?>",
  "mainEntity": {
    "@type": "ItemList",
    "numberOfItems": 4,
    "itemListElement": [
      {
        "@type": "ListItem",
        "position": 1,
        "item": {
          "@type": "Person",
          "name": "Falade Yinka O.",
          "alternateName": "Falade Yinka",
          "jobTitle": "Founder & Lead Agricultural Consultant",
          "alumniOf": "Federal University of Agriculture, Abeokuta",
          "description": "World Bank Scholar specializing in Livestock Science and Sustainable Environment.",
          "worksFor": {
            "@type": "Organization",
            "name": "F and V Agro Services"
          },
          "url": "<?php echo $current_url; ?>#falade-yinka"
        }
      },
      {
        "@type": "ListItem",
        "position": 2,
        "item": {
          "@type": "Person",
          "name": "Falade Victory O.",
          "alternateName": "Falade Victory",
          "jobTitle": "Co-Founder & Business Developer",
          "alumniOf": "Federal Polytechnic Nassarawa",
          "description": "Business development expert and financial management specialist.",
          "worksFor": {
            "@type": "Organization",
            "name": "F and V Agro Services"
          },
          "url": "<?php echo $current_url; ?>#falade-victory"
        }
      },
      {
        "@type": "ListItem",
        "position": 3,
        "item": {
          "@type": "Person",
          "name": "Adewale Damilare Owoade",
          "alternateName": "Damilare Owoade",
          "jobTitle": "Agricultural Extension Specialist & Poultry Expert",
          "alumniOf": "Federal University of Agriculture, Abeokuta",
          "description": "Commercial poultry expert with 43,000+ broiler production experience.",
          "worksFor": {
            "@type": "Organization",
            "name": "F and V Agro Services"
          },
          "url": "<?php echo $current_url; ?>#adewale-owoade"
        }
      },
      {
        "@type": "ListItem",
        "position": 4,
        "item": {
          "@type": "Person",
          "name": "Samuel Alao",
          "alternateName": "Alao Samuel",
          "jobTitle": "Tech Lead & Full-Stack Developer",
          "alumniOf": "Ladoke Akintola University of Technology",
          "description": "Full-Stack Developer specializing in agricultural technology solutions.",
          "worksFor": {
            "@type": "Organization",
            "name": "F and V Agro Services"
          },
          "url": "<?php echo $current_url; ?>#samuel-alao"
        }
      }
    ]
  }
}
</script>

<style>
/* ====== MODERN DESIGN VARIABLES ====== */
:root {
    --primary-green: #198754;
    --dark-green: #146c43;
    --light-green: #d1e7dd;
    --accent-gold: #ffc107;
    --dark-text: #2d3748;
    --light-text: #718096;
    --white: #ffffff;
    --section-bg: #f8f9fa;
    --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    --hover-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
    --gradient-primary: linear-gradient(135deg, #198754 0%, #146c43 100%);
}

/* ====== ENHANCED TEAM DESIGN ====== */
.team-hero {
    position: relative;
    height: 60vh;
    min-height: 500px;
    background: linear-gradient(rgba(25, 135, 84, 0.85), rgba(20, 108, 67, 0.9)),
                url('assets/images/team_img.png') center/cover fixed;
    display: flex;
    align-items: center;
    overflow: hidden;
}

.team-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
}

.hero-content {
    position: relative;
    z-index: 2;
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
    padding: 2rem;
}

.hero-badge {
    display: inline-block;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: var(--white);

    border-radius: 50px;
    margin-bottom: 0.5rem;
    font-weight: 500;
    letter-spacing: 1px;
}

/* ====== ENHANCED TEAM CARDS ====== */
.team-card-modern {
    background: var(--white);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: var(--card-shadow);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    height: 100%;
    position: relative;
    border: none;
}

.team-card-modern:hover {
    transform: translateY(-15px);
    box-shadow: var(--hover-shadow);
}

.team-card-modern:hover .member-image {
    transform: scale(1.05);
}

.member-image-container {
    height: 280px;
    overflow: hidden;
    position: relative;
}

.member-image {
    height: 100%;
    width: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
    filter: grayscale(20%);
}

.member-image-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, transparent 70%, rgba(0,0,0,0.7) 100%);
    z-index: 1;
}

.member-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: var(--gradient-primary);
    color: white;
    padding: 1.5rem;
    transform: translateY(100%);
    transition: transform 0.3s ease;
    z-index: 2;
}

.team-card-modern:hover .member-overlay {
    transform: translateY(0);
}

.member-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    background: var(--accent-gold);
    color: var(--dark-text);
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 600;
    z-index: 3;
    box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
}

.member-content {
    padding: 2rem;
    position: relative;
    z-index: 2;
}

.member-name {
    color: var(--dark-text);
    font-weight: 700;
    margin-bottom: 0.5rem;
    font-size: 1.25rem;
}

.member-title {
    color: var(--primary-green);
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.member-title i {
    color: var(--accent-gold);
}

.member-expertise {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 1rem;
}

.expertise-tag {
    background: var(--light-green);
    color: var(--dark-green);
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 500;
}

.member-social {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(0,0,0,0.1);
}

.social-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    text-decoration: none;
    transition: all 0.3s ease;
}

.social-icon.linkedin {
    background: #0077b5;
}

.social-icon.twitter {
    background: #1da1f2;
}

.social-icon:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

/* ====== ENHANCED MODAL DESIGN ====== */
.modal-team {
    --bs-modal-border-radius: 25px;
    --bs-modal-box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
}

.modal-team .modal-content {
    border: none;
    border-radius: 25px;
    overflow: hidden;
}

.modal-header-gradient {
    background: var(--gradient-primary);
    color: white;
    border: none;
    padding: 2rem;
}

.modal-team .btn-close {
    filter: brightness(0) invert(1);
    opacity: 0.8;
}

.modal-team .btn-close:hover {
    opacity: 1;
}

.modal-team .modal-body {
    padding: 0;
}

.member-modal-content {
    display: flex;
    min-height: 500px;
}

.modal-image-side {
    flex: 0 0 35%;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 2rem;
}

.modal-image-side img {
    width: 200px;
    height: 200px;
    border-radius: 50%;
    object-fit: cover;
    border: 5px solid var(--white);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.modal-info-side {
    flex: 0 0 65%;
    padding: 3rem;
}

.expertise-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-top: 1.5rem;
}

.expertise-item {
    background: var(--light-green);
    padding: 1rem;
    border-radius: 10px;
    border-left: 4px solid var(--primary-green);
}

/* ====== STATS SECTION ====== */
.stats-section {
    background: var(--section-bg);
    padding: 5rem 0;
    margin: 5rem 0;
}

.stat-card {
    background: var(--white);
    padding: 2.5rem;
    border-radius: 20px;
    text-align: center;
    box-shadow: var(--card-shadow);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-10px);
}

.stat-number {
    font-size: 3rem;
    font-weight: 800;
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 1rem;
}

.stat-label {
    color: var(--light-text);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 0.9rem;
}

/* ====== EXPERTISE SECTION ====== */
.expertise-section {
    padding: 5rem 0;
}

.expertise-grid-large {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.expertise-card {
    background: var(--white);
    padding: 2.5rem;
    border-radius: 20px;
    box-shadow: var(--card-shadow);
    border-top: 5px solid var(--primary-green);
    transition: all 0.3s ease;
}

.expertise-card:hover {
    border-top-color: var(--accent-gold);
    transform: translateY(-5px);
}

.expertise-icon {
    width: 60px;
    height: 60px;
    background: var(--light-green);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
    color: var(--primary-green);
    font-size: 1.5rem;
}

/* ====== CTA SECTION ====== */
.cta-section-team {
    background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
    color: white;
    padding: 5rem 0;
    position: relative;
    overflow: hidden;
}

.cta-section-team::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
}

.cta-content {
    position: relative;
    z-index: 2;
    text-align: center;
    max-width: 800px;
    margin: 0 auto;
}

.cta-badge {
    display: inline-block;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 0.5rem 1.5rem;
    border-radius: 50px;
    margin-bottom: 1.5rem;
    font-weight: 600;
    backdrop-filter: blur(10px);
}

.cta-button {
    background: white;
    color: var(--primary-green);
    padding: 1rem 2.5rem;
    border-radius: 50px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.cta-button:hover {
    background: transparent;
    color: white;
    border-color: white;
    transform: translateY(-3px);
}

/* ====== RESPONSIVE DESIGN ====== */
@media (max-width: 768px) {
    .team-hero {
        height: 70vh;
        min-height: 400px;
    }
    
    .member-modal-content {
        flex-direction: column;
    }
    
    .modal-image-side,
    .modal-info-side {
        flex: 1;
    }
    
    .expertise-grid {
        grid-template-columns: 1fr;
    }
    
    .expertise-grid-large {
        grid-template-columns: 1fr;
    }
}

/* ====== ANIMATIONS ====== */
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.floating {
    animation: float 3s ease-in-out infinite;
}
</style>

<!-- Enhanced Hero Section -->
<section class="team-hero">
    <div class="hero-content">
        <div class="hero-badge floating">
            <i class="bi bi-people-fill me-2"></i>Expert Agricultural Team
        </div>
        <h1 class="display-4 fw-bold text-white mb-4">Meet Our Agricultural<br>Visionaries</h1>
        <p class="lead text-white mb-4 opacity-90">A team of passionate professionals combining decades of farming experience with cutting-edge technology to revolutionize agriculture in Nigeria.</p>
        <div class="d-flex gap-3 justify-content-center">
            <span class="text-white"><i class="bi bi-check-circle-fill text-warning me-1"></i> Certified Experts</span>
            <span class="text-white"><i class="bi bi-check-circle-fill text-warning me-1"></i> Field Experience</span>
            <span class="text-white"><i class="bi bi-check-circle-fill text-warning me-1"></i> Technology-Driven</span>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="container my-5 py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold display-5 mb-3">Our Agricultural Experts</h2>
        <p class="lead text-muted mb-5">Meet the passionate professionals driving innovation and excellence in Nigerian agriculture</p>
    </div>

    <div class="row g-4">
    <?php
    $team = [
        [
            "id" => "member1",
            "slug" => "falade-yinka",
            "name" => "Falade Yinka O.",
            "alternate_names" => "Falade Yinka, Yinka Falade",
            "title" => "Founder & Lead Agricultural Consultant",
            "img" => "assets/images/yinka_falade.jpg",
            "img_alt" => "Falade Yinka O. - Founder and Lead Agricultural Consultant at F and V Agro Services Nigeria",
            "badge" => "World Bank Scholar",
            "expertise" => ["Livestock Science", "Sustainable Agriculture", "Farm Consultation", "Project Management"],
            "bio" => "Falade Yinka is the visionary founder and lead consultant at F and V Agro Services. He holds a Bachelor's degree in Agriculture from the Federal University of Agriculture, Abeokuta (FUNAAB), Ogun State. As a World Bank Scholar, he furthered his education with a Master's degree in Livestock Science and Sustainable Environment from the World Bank Africa Centre of Excellence, FUNAAB.
            
            Yinka is a dedicated advocate of sustainable agriculture and a hands-on farmer. With years of field experience, he has consulted for numerous farms across Nigeria, particularly in the South West and South East regions, helping to develop profitable, environmentally-conscious agricultural systems.
            
            <div class='expertise-grid mt-4'>
                <div class='expertise-item'>
                    <strong>Education:</strong><br>
                    B.Agric, M.Agric (FUNAAB)<br>
                    World Bank Scholar
                </div>
                <div class='expertise-item'>
                    <strong>Specialization:</strong><br>
                    Livestock Science<br>
                    Sustainable Environment
                </div>
                <div class='expertise-item'>
                    <strong>Experience:</strong><br>
                    8+ Years<br>
                    Farm Consultation
                </div>
                <div class='expertise-item'>
                    <strong>Regions:</strong><br>
                    South West &<br>
                    South East Nigeria
                </div>
            </div>",
            "linkedin" => "#",
            "twitter" => "#"
        ],
        [
            "id" => "member2",
            "slug" => "falade-victory",
            "name" => "Falade Victory O.",
            "alternate_names" => "Falade Victory, Victory Falade",
            "title" => "Co-Founder & Business Developer",
            "img" => "assets/images/img1.jpg",
            "img_alt" => "Falade Victory O. - Co-Founder and Business Development Expert at F and V Agro Services",
            "badge" => "Marketing Lead",
            "expertise" => ["Business Development", "Financial Management", "Marketing Strategy", "Business Negotiation"],
            "bio" => "She is the co-founder of F and V Agro Services and a seasoned business development expert, she is also a Business negotiator with high knowledge of financial management. She studied accounting from the Federal Polytechnic Nassarawa State. She is currently top Marketing Lead for a tech company in Ibadan, Oyo State.
            
            <div class='expertise-grid mt-4'>
                <div class='expertise-item'>
                    <strong>Education:</strong><br>
                    Accounting Diploma<br>
                    Federal Polytechnic
                </div>
                <div class='expertise-item'>
                    <strong>Specialization:</strong><br>
                    Business Development<br>
                    Financial Management
                </div>
                <div class='expertise-item'>
                    <strong>Current Role:</strong><br>
                    Marketing Lead<br>
                    Tech Company, Ibadan
                </div>
                <div class='expertise-item'>
                    <strong>Expertise:</strong><br>
                    Business Negotiation<br>
                    Strategy Development
                </div>
            </div>",
            "linkedin" => "#",
            "twitter" => "#"
        ],
        [
            "id" => "member3",
            "slug" => "adewale-owoade",
            "name" => "Adewale Damilare O.",
            "alternate_names" => "Damilare Owoade, Owoade Adewale",
            "title" => "Agricultural Extension Specialist & Poultry Expert",
            "img" => "assets/images/OWOADE ADEWALE.jpg",
            "img_alt" => "Adewale Damilare Owoade - Agricultural Extension Specialist and Poultry Expert at F and V Agro Services",
            "badge" => "Certified PM",
            "expertise" => ["Poultry Production", "Agricultural Extension", "Broiler Farming", "Supply Chain"],
            "bio" => "He is an experienced broiler producer and agricultural extension agent with a proven track record in commercial poultry production. He has successfully raised over 43,000 broiler chickens at my current facility, supplying to top industry players including ZARTECH Farms, Coker Farms, TAGHINI Foods,  Durante Foods, and CHICKEN REPUBLIC. His academic background from FUNAAB (B.Agric and M.Agric) is complemented by practical expertise and a commitment to sustainable, high-quality poultry farming.
            
            <div class='expertise-grid mt-4'>
                <div class='expertise-item'>
                    <strong>Education:</strong><br>
                    B.Agric, M.Agric<br>
                    (FUNAAB)
                </div>
                <div class='expertise-item'>
                    <strong>Certification:</strong><br>
                    Certified Project<br>
                    Manager (CPM)
                </div>
                <div class='expertise-item'>
                    <strong>Production:</strong><br>
                    43,000+<br>
                    Broilers Raised
                </div>
                <div class='expertise-item'>
                    <strong>Clients:</strong><br>
                    ZARTECH, Coker<br>
                    TAGHINI, Chicken Republic
                </div>
            </div>",
            "linkedin" => "#",
            "twitter" => "#"
        ],
        [
            "id" => "member4",
            "slug" => "samuel-alao",
            "name" => "Samuel Alao M",
            "alternate_names" => "Alao Samuel, Samuel Alao Mofifoluwa",
            "title" => "Tech Lead & Full-Stack Developer",
            "img" => "assets/images/sam.jpg",
            "img_alt" => "Samuel Alao - Tech Lead and Full-Stack Developer at F and V Agro Services",
            "badge" => "ALX Certified",
            "expertise" => ["Full-Stack Development", "Agricultural Tech", "Digital Transformation", "E-commerce"],
            "bio" => "Samuel Alao is a Full-Stack Developer with a strong foundation in both frontend and backend technologies, passionate about building dynamic, user-friendly websites and applications. With a B.Tech in Urban and Regional Planning from Ladoke Akintola University of Technology and professional training in Software Engineering from ALX, he bring a unique blend of analytical thinking and technical expertise to every project.
            
            He specialize in helping businesses grow through innovative, scalable tech solutions, ensuring high standards of quality, efficiency, and client satisfaction in all my work. His mission is to transform ideas into powerful digital products that drive real-world impact.
            
            <div class='expertise-grid mt-4'>
                <div class='expertise-item'>
                    <strong>Education:</strong><br>
                    B.Tech (LAUTECH)<br>
                    ALX Software Engineering
                </div>
                <div class='expertise-item'>
                    <strong>Specialization:</strong><br>
                    Full-Stack<br>
                    Development
                </div>
                <div class='expertise-item'>
                    <strong>Focus:</strong><br>
                    Agricultural<br>
                    Technology
                </div>
                <div class='expertise-item'>
                    <strong>Mission:</strong><br>
                    Digital Transformation<br>
                    for Agriculture
                </div>
            </div>",
            "linkedin" => "https://www.linkedin.com/in/samuel-alao/",
            "twitter" => "#"
        ]
    ];

    foreach ($team as $member) {
        echo '
        <div class="col-lg-3 col-md-6">
            <div class="team-card-modern" data-bs-toggle="modal" data-bs-target="#'.$member['id'].'Modal">
                <div class="member-image-container">
                    <img src="'.$member['img'].'" class="member-image" alt="'.$member['img_alt'].'" loading="lazy">
                    <div class="member-badge">'.$member['badge'].'</div>
                    <div class="member-overlay">
                        <h3 class="member-name text-white mb-2">'.$member['name'].'</h3>
                        <p class="text-white mb-0 opacity-90">'.$member['title'].'</p>
                    </div>
                </div>
                <div class="member-content">
                    <h3 class="member-name">'.$member['name'].'</h3>
                    <div class="member-title">
                        <i class="bi bi-award"></i>
                        '.$member['title'].'
                    </div>
                    <div class="member-expertise">';
        
        foreach ($member['expertise'] as $skill) {
            echo '<span class="expertise-tag">'.$skill.'</span>';
        }
        
        echo '
                    </div>
                    <div class="member-social">
                        <a href="'.$member['linkedin'].'" class="social-icon linkedin" aria-label="Connect with '.$member['name'].' on LinkedIn">
                            <i class="bi bi-linkedin"></i>
                        </a>
                        <a href="'.$member['twitter'].'" class="social-icon twitter" aria-label="Follow '.$member['name'].' on Twitter">
                            <i class="bi bi-twitter"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Modal -->
        <div class="modal fade modal-team" id="'.$member['id'].'Modal" tabindex="-1" aria-labelledby="'.$member['id'].'Label" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
              <div class="modal-header modal-header-gradient">
                <div>
                    <h2 class="modal-title h3 mb-2" id="'.$member['id'].'Label">'.$member['name'].'</h2>
                    <p class="text-white mb-0 opacity-90">'.$member['title'].'</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="member-modal-content">
                  <div class="modal-image-side">
                    <img src="'.$member['img'].'" alt="'.$member['img_alt'].'">
                    <div class="mt-4 text-center">
                        <div class="badge bg-warning text-dark fs-6 mb-3">'.$member['badge'].'</div>
                        <div class="d-flex gap-3 justify-content-center">
                            <a href="'.$member['linkedin'].'" class="social-icon linkedin">
                                <i class="bi bi-linkedin"></i>
                            </a>
                            <a href="'.$member['twitter'].'" class="social-icon twitter">
                                <i class="bi bi-twitter"></i>
                            </a>
                        </div>
                    </div>
                  </div>
                  <div class="modal-info-side">
                    <div class="member-bio">'.$member['bio'].'</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>';
    }
    ?>
    </div>
</section>

<!-- Expertise Section -->
<section class="expertise-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold display-5 mb-3">Our Areas of Expertise</h2>
            <p class="lead text-muted">Comprehensive agricultural solutions powered by expertise</p>
        </div>
        
        <div class="expertise-grid-large">
            <div class="expertise-card">
                <div class="expertise-icon">
                    <i class="bi bi-tree"></i>
                </div>
                <h3 class="h4 mb-3">Livestock & Poultry</h3>
                <p class="text-muted">Expert guidance in livestock management, poultry production, and sustainable animal farming practices.</p>
                <ul class="list-unstyled mt-3">
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Broiler Production Management</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Livestock Health & Nutrition</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Sustainable Farming Systems</li>
                </ul>
            </div>
            
            <div class="expertise-card">
                <div class="expertise-icon">
                    <i class="bi bi-graph-up"></i>
                </div>
                <h3 class="h4 mb-3">Business Development</h3>
                <p class="text-muted">Strategic planning, financial management, and business growth strategies for agricultural enterprises.</p>
                <ul class="list-unstyled mt-3">
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Farm Business Planning</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Financial Management</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Market Strategy Development</li>
                </ul>
            </div>
            
            <div class="expertise-card">
                <div class="expertise-icon">
                    <i class="bi bi-cpu"></i>
                </div>
                <h3 class="h4 mb-3">Agricultural Technology</h3>
                <p class="text-muted">Cutting-edge tech solutions for modern farming, digital transformation, and e-commerce platforms.</p>
                <ul class="list-unstyled mt-3">
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Farm Management Software</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> E-commerce Solutions</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Digital Marketing for Agriculture</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Enhanced CTA Section -->
<section class="cta-section-team">
    <div class="container">
        <div class="cta-content">
            <div class="cta-badge mb-4">
                <i class="bi bi-chat-dots-fill me-2"></i>Let's Grow Together
            </div>
            <h2 class="display-5 fw-bold text-white mb-4">Ready to Transform Your Agricultural Business?</h2>
            <p class="lead text-white mb-5 opacity-90">Connect with our team of experts for personalized consultation, technical support, and innovative solutions tailored to your farming needs.</p>
            <div class="d-flex flex-wrap gap-3 justify-content-center">
                <a href="contact.php" class="cta-button">
                    <i class="bi bi-calendar-check me-2"></i> Schedule Consultation
                </a>
                <a href="tel:+2348031234567" class="cta-button" style="background: transparent; border: 2px solid white; color: white;">
                    <i class="bi bi-telephone me-2"></i> Call Our Experts
                </a>
            </div>
            <p class="text-white mt-4 opacity-75">
                <i class="bi bi-lightning-charge-fill me-2"></i>
                Average response time: 2 hours
            </p>
        </div>
    </div>
</section>

<?php include('includes/footer.php'); ?>