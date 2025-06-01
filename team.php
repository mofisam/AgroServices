<?php
$page_title = "About Us - F and V Agro Services";
$page_description = "Learn more about F and V Agro Services – our mission, values, and team.";
$page_keywords = "Agro Services, About, Farming, Agriculture";
include 'includes/header.php';
?>


<!-- Hero Section -->
<section class="hero-image position-relative" style="background: url('assets/images/team_img.png') center/cover no-repeat; height: 350px;">
  <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex align-items-center justify-content-center">
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
            "bio" => "Falade Yinka is the visionary founder and lead consultant at F and V Agro Services. He holds a Bachelor’s degree in Agriculture from the Federal University of Agriculture, Abeokuta (FUNAAB), Ogun State. As a World Bank Scholar, he furthered his education with a Master’s degree in Livestock Science and Sustainable Environment from the World Bank Africa Centre of Excellence, FUNAAB.
            <br/>
            Yinka is a dedicated advocate of sustainable agriculture and a hands-on farmer. With years of field experience, he has consulted for numerous farms across Nigeria, particularly in the South West and South East regions, helping to develop profitable, environmentally-conscious agricultural systems.",
            "linkedin" => "#",
            "twitter" => "#"
        ],
        [
            "id" => "member2",
            "name" => "Falade Victory. O",
            "title" => "co-founder",
            "img" => "assets/images/img1.jpg",
            "bio" => "Is the co-founder of F and V Agro Services and a season business developer expert, she is also a Business negotiator with high knowledge of financial management. She studied accounting from the Federal Polytechnic Nassarawa State. She is currently top Marketing Lead for a tech company in Ibadan, Oyo State.",
            "linkedin" => "#",
            "twitter" => "#"
        ],
        [
            "id" => "member3",
            "name" => "Pauls Deborah I",
            "title" => "Team Member",
            "img" => "assets/images/img1.jpg",
            "bio" => "",
            "linkedin" => "#",
            "twitter" => "#"
        ],
        [
            "id" => "member4",
            "name" => "Samuel Alao",
            "title" => "Tech Lead",
            "img" => "assets/images/img1.jpg",
            "bio" => "Samuel Alao is a Full-Stack Developer with a strong foundation in both frontend and backend technologies, passionate about building dynamic, user-friendly websites and applications. With a B.Tech in Urban and Regional Planning from Ladoke Akintola University of Technology and professional training in Software Engineering from ALX, he bring a unique blend of analytical thinking and technical expertise to every project. <br/> He specialize in helping businesses grow through innovative, scalable tech solutions, ensuring high standards of quality, efficiency, and client satisfaction in all my work. His mission is to transform ideas into powerful digital products that drive real-world impact.",
            "linkedin" => "#",
            "twitter" => "#"
        ]
    ];

    foreach ($team as $member) {
        // Short bio (max 12 words)
        $shortBio = implode(' ', array_slice(explode(' ', $member['bio']), 0, 12)) . '...';

        echo '
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card team-card shadow-sm h-100" style="cursor:pointer" data-bs-toggle="modal" data-bs-target="#'.$member['id'].'Modal">
                <img src="'.$member['img'].'" class="card-img-top" alt="'.$member['name'].'">
                <div class="card-body text-center">
                    <h5 class="card-title">'.$member['name'].'</h5>
                    <p class="text-muted mb-1">'.$member['title'].'</p>
                    <p class="small text-muted">'.$shortBio.'</p>
                    <div>
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
                    <div>
                        <a href="'.$member['linkedin'].'" class="text-primary me-2"><i class="bi bi-linkedin fs-4"></i></a>
                        <a href="'.$member['twitter'].'" class="text-info"><i class="bi bi-twitter fs-4"></i></a>
                    </div>
                  </div>
                  <div class="col-md-8">
                    <p>'.$member['bio'].'</p>
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
        <a href="contact.php" class="btn btn-light mt-2">Get in Touch</a>
    </div>
</section>

<?php include('includes/footer.php'); ?>