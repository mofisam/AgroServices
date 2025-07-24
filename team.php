<?php
// Team Page Meta Variables
$page_title = "Our Agricultural Experts | F and V Agro Services Team";
$page_description = "Meet F and V Agro Services team of certified agricultural professionals, consultants, and tech experts driving Nigeria's agro-commerce revolution.";
$page_keywords = "agricultural experts Nigeria, farm consultants, agro-commerce team, sustainable agriculture specialists, F&V Agro professionals, farming technology experts";
$og_image = "https://www.fandvagroservices.com.ng/assets/images/team-social-preview.jpg";
$current_url = "https://www.fandvagroservices.com.ng/team";

include('includes/header.php');
include_once 'includes/tracking.php';

?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "AboutPage",
  "name": "Our Team",
  "description": "Professional team at F&V Agro Services",
  "about": {
    "@type": "ItemList",
    "itemListElement": [
      {
        "@type": "ListItem",
        "position": 1,
        "item": {
          "@type": "Person",
          "name": "Falade Yinka O.",
          "jobTitle": "Founder & Lead Consultant",
          "alumniOf": "Federal University of Agriculture, Abeokuta",
          "description": "World Bank Scholar specializing in Livestock Science and Sustainable Environment"
        }
      },
      {
        "@type": "ListItem",
        "position": 2,
        "item": {
          "@type": "Person",
          "name": "Adewale Damilare Owoade",
          "jobTitle": "Agricultural Extension Specialist",
          "alumniOf": "Federal University of Agriculture, Abeokuta",
          "description": "Commercial poultry expert with 43,000+ broiler production experience"
        }
      }
    ]
  }
}
</script>
<style>
    /* Team Section Styling */
    .team-card {
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .team-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    
    .team-card .card-body {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    
    .team-card .card-text {
        flex-grow: 1;
        margin-bottom: 1rem;
    }
    
    .team-card img {
        height: 250px;
        width: 100%;
        object-fit: cover;
        object-position: top;
    }
    
    .team-social {
        margin-top: auto;
    }
    
    /* Hero Section */
    .hero-image {
        height: 350px;
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        position: relative;
    }
    
    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<!-- Hero Section -->
<section class="hero-image" style="background-image: url('assets/images/team_img.png');">
  <div class="hero-overlay">
    <h1 class="text-white display-4 fw-bold text-center animate__animated animate__fadeInDown">Meet Our Team</h1>
  </div>
</section>

<!-- Team Section -->
<section class="container my-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Meet Our Team</h2>
        <p class="text-muted">Passionate professionals driving agricultural success.</p>
    </div>

    <div class="row g-4">
    <?php
    $team = [
        [
            "id" => "member1",
            "name" => "Falade Yinka O.",
            "title" => "Founder & Lead Consultant",
            "img" => "assets/images/yinka_falade.jpg",
            "bio" => "Falade Yinka is the visionary founder and lead consultant at F and V Agro Services. He holds a Bachelor's degree in Agriculture from the Federal University of Agriculture, Abeokuta (FUNAAB), Ogun State. As a World Bank Scholar, he furthered his education with a Master's degree in Livestock Science and Sustainable Environment from the World Bank Africa Centre of Excellence, FUNAAB.
            <br/><br/>
            Yinka is a dedicated advocate of sustainable agriculture and a hands-on farmer. With years of field experience, he has consulted for numerous farms across Nigeria, particularly in the South West and South East regions, helping to develop profitable, environmentally-conscious agricultural systems.",
            "linkedin" => "#",
            "twitter" => "#"
        ],
        [
            "id" => "member2",
            "name" => "Falade Victory. O",
            "title" => "Co-Founder",
            "img" => "assets/images/img1.jpg",
            "bio" => "His the co-founder of F and V Agro Services and a season business developer expert, she is also a Business negotiator with high knowledge of financial management. She studied accounting from the Federal Polytechnic Nassarawa State. She is currently top Marketing Lead for a tech company in Ibadan, Oyo State.",
            "linkedin" => "#",
            "twitter" => "#"
        ],
        [
            "id" => "member3",
            "name" => "Adewale Damilare Owoade",
            "title" => "Team Member",
            "img" => "assets/images/OWOADE ADEWALE.jpg",
            "bio" => "He is an experienced broiler producer and agricultural extension agent with a proven track record in commercial poultry production. He has successfully raised over 43,000 broiler chickens at my current facility, supplying to top industry players including ZARTECH Farms, Coker Farms, TAGHINI Foods,  Durante Foods, and CHICKEN REPUBLIC. His academic background from FUNAAB (B.Agric and M.Agric) is complemented by practical expertise and a commitment to sustainable, high-quality poultry farming.<br/><br/><i>B.Agric, M.Agric (FUNAAB) | Certified Project Manager (CPM)<br/> Broiler Producer | Agricultural Extension Specialist</i>",
            "linkedin" => "#",
            "twitter" => "#"
        ],
        [
            "id" => "member4",
            "name" => "Samuel Alao",
            "title" => "Tech Lead",
            "img" => "assets/images/sam.jpg",
            "bio" => "Samuel Alao is a Full-Stack Developer with a strong foundation in both frontend and backend technologies, passionate about building dynamic, user-friendly websites and applications. With a B.Tech in Urban and Regional Planning from Ladoke Akintola University of Technology and professional training in Software Engineering from ALX, he bring a unique blend of analytical thinking and technical expertise to every project. <br/><br/> He specialize in helping businesses grow through innovative, scalable tech solutions, ensuring high standards of quality, efficiency, and client satisfaction in all my work. His mission is to transform ideas into powerful digital products that drive real-world impact.",
            "linkedin" => "https://www.linkedin.com/in/samuel-alao/",
            "twitter" => "#"
        ]
    ];

    foreach ($team as $member) {
        // Create short bio (first 20 words)
        $words = str_word_count(strip_tags($member['bio']), 1);
        $shortBio = implode(' ', array_slice($words, 0, 20));
        if (count($words) > 20) $shortBio .= '...';

        echo '
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card team-card shadow-sm h-100" data-bs-toggle="modal" data-bs-target="#'.$member['id'].'Modal">
                <img src="'.$member['img'].'" class="card-img-top" alt="'.$member['name'].'">
                <div class="card-body text-center">
                    <h5 class="card-title">'.$member['name'].'</h5>
                    <p class="text-muted mb-2">'.$member['title'].'</p>
                    <p class="card-text text-muted small">'.$shortBio.'</p>
                    <div class="team-social">
                        <a href="'.$member['linkedin'].'" class="text-primary me-2"><i class="bi bi-linkedin"></i></a>
                        <a href="'.$member['twitter'].'" class="text-info"><i class="bi bi-twitter"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="'.$member['id'].'Modal" tabindex="-1" aria-labelledby="'.$member['id'].'Label" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="'.$member['id'].'Label">'.$member['name'].' - '.$member['title'].'</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="row">
                  <div class="col-md-4 text-center">
                    <img src="'.$member['img'].'" alt="'.$member['name'].'" class="img-fluid rounded mb-3">
                    <div class="mt-3">
                        <a href="'.$member['linkedin'].'" class="btn btn-outline-primary me-2"><i class="bi bi-linkedin"></i> LinkedIn</a>
                        <a href="'.$member['twitter'].'" class="btn btn-outline-info"><i class="bi bi-twitter"></i> Twitter</a>
                    </div>
                  </div>
                  <div class="col-md-8">
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

<!-- Call to Action -->
<section class="bg-success text-white py-5 text-center">
    <div class="container">
        <h3>Need expert advice for your farm?</h3>
        <p>Contact our team today for consultation and support.</p>
        <a href="contact" class="btn btn-light mt-2">Get in Touch</a>
    </div>
</section>

<?php include('includes/footer.php'); ?>