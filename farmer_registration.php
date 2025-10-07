<?php
// Page Meta Variables
$page_title = "Smallholder Farmer Registration | F and V Agro Services";
$page_description = "Apply for free training and grant opportunities to support your agribusiness. Women in agriculture are especially encouraged to apply.";
$page_keywords = "farmer registration Nigeria, agricultural training, agribusiness grant, smallholder farmers, women in agriculture";
$og_image = "https://www.fandvagroservices.com.ng/assets/images/logo.jpg";
$current_url = "https://www.fandvagroservices.com.ng/farmer_registration";

include 'includes/header.php'; 
include_once 'includes/tracking.php';

// Display error message if exists
if (isset($_GET['error'])) {
  $errorMessages = [
      'required_fields' => 'Please fill all required fields.',
      'database' => 'There was an error processing your application. Please try again.',
      'duplicate_phone' => 'This phone number has already been used for registration. Please contact support if you need to update your application.',
      'duplicate_email' => 'This email address has already been used for registration. Please contact support if you need to update your application.'
  ];
  
  if (array_key_exists($_GET['error'], $errorMessages)) {
      echo '<div class="alert alert-danger text-center">'.$errorMessages[$_GET['error']].'</div>';
  }
}
?>

<!-- Farmer Registration Form Section -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="card border-0 shadow-sm">
          <div class="card-h/eader bg-success text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h2 class="h4 mb-1">Smallholder Farmer Registration Form</h2>
                <p class="mb-0">For Free Training & Grant Opportunity (Women Encouraged to Apply)</p>
              </div>
            </div>
          </div>
          
          <div class="card-body p-4 p-md-5">
            <?php if(isset($_GET['success'])): ?>
              <div class="alert alert-success">
                <i class="bi bi-check-circle-fill me-2"></i>
                Thank you for your application! We've received your submission and will contact you within 5 working days.
              </div>
              <div class="text-center mt-4">
                <a href="index" class="btn btn-success">Back to Homepage</a>
              </div>
            <?php else: ?>
              <div class="alert alert-info mb-4">
                <i class="bi bi-info-circle-fill me-2"></i>
                <strong>Purpose:</strong> To support smallholder farmers—especially women—in sustainable agribusiness, value chain development, processing & packaging. The best 3 applicants will receive a grant to support their agro-enterprise.
              </div>
              
              <form id="farmerRegistrationForm" action="submit_farmer_registration" method="POST">
                <!-- SECTION A: Personal Information -->
                <div class="mb-5">
                  <div class="d-flex align-items-center mb-4">
                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                      <span class="fs-5">A</span>
                    </div>
                    <h3 class="ms-3 mb-0">Personal Information</h3>
                  </div>
                  
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label for="fullName" class="form-label">Full Name <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="fullName" name="fullName" required>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label">Gender <span class="text-danger">*</span></label>
                      <div class="d-flex gap-4">
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="gender" id="male" value="Male" required>
                          <label class="form-check-label" for="male">Male</label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="gender" id="female" value="Female">
                          <label class="form-check-label" for="female">Female</label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="gender" id="noSay" value="Prefer not to say">
                          <label class="form-check-label" for="noSay">Prefer not to say</label>
                        </div>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <label for="dob" class="form-label">Date of Birth (DD/MM/YYYY) <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="dob" name="dob" placeholder="DD/MM/YYYY" required>
                    </div>
                    
                    <div class="col-md-6">
                      <label for="phone" class="form-label">Phone Number (WhatsApp preferred) <span class="text-danger">*</span></label>
                      <input type="tel" class="form-control" id="phone" name="phone" required>
                    </div>
                    
                    <div class="col-md-6">
                      <label for="email" class="form-label">Email Address (if available)</label>
                      <input type="email" class="form-control" id="email" name="email">
                    </div>
                    
                    <div class="col-md-6">
                      <label for="address" class="form-label">Residential Address <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="address" name="address" required>
                    </div>
                    
                    <div class="col-md-6">
                      <label for="state" class="form-label">State of Residence <span class="text-danger">*</span></label>
                      <select class="form-select" id="state" name="state" required>
                        <option value="" selected disabled>Select State</option>
                        <?php
                        // Nigerian states array
                        $states = [
                          "Abia", "Adamawa", "Akwa Ibom", "Anambra", "Bauchi", "Bayelsa", 
                          "Benue", "Borno", "Cross River", "Delta", "Ebonyi", "Edo", 
                          "Ekiti", "Enugu", "FCT", "Gombe", "Imo", "Jigawa", 
                          "Kaduna", "Kano", "Katsina", "Kebbi", "Kogi", "Kwara", 
                          "Lagos", "Nasarawa", "Niger", "Ogun", "Ondo", "Osun", 
                          "Oyo", "Plateau", "Rivers", "Sokoto", "Taraba", "Yobe", "Zamfara"
                        ];
                        
                        foreach ($states as $state) {
                          echo "<option value=\"$state\">$state</option>";
                        }
                        ?>
                      </select>
                    </div>
                    
                    <div class="col-md-6">
                      <label for="lga" class="form-label">Local Government Area (LGA) <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="lga" name="lga" required>
                    </div>
                    
                    <div class="col-md-6">
                      <label for="nationality" class="form-label">Nationality <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="nationality" name="nationality" value="Nigeria" required>
                    </div>
                  </div>
                </div>
                
                <!-- SECTION B: Farming / Agribusiness Profile -->
                <div class="mb-5">
                  <div class="d-flex align-items-center mb-4">
                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                      <span class="fs-5">B</span>
                    </div>
                    <h3 class="ms-3 mb-0">Farming / Agribusiness Profile</h3>
                  </div>
                  
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label">Are you a woman in agriculture? <span class="text-danger">*</span></label>
                      <div class="d-flex gap-4">
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="womanInAgri" id="womanYes" value="Yes" required>
                          <label class="form-check-label" for="womanYes">Yes</label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="womanInAgri" id="womanNo" value="No">
                          <label class="form-check-label" for="womanNo">No</label>
                        </div>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label">Are you a youth (18–35 years)? <span class="text-danger">*</span></label>
                      <div class="d-flex gap-4">
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="youth" id="youthYes" value="Yes" required>
                          <label class="form-check-label" for="youthYes">Yes</label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="youth" id="youthNo" value="No">
                          <label class="form-check-label" for="youthNo">No</label>
                        </div>
                      </div>
                    </div>
                    
                    <div class="col-12">
                      <label class="form-label">Are you currently involved in any of the following? (Select all that apply) <span class="text-danger">*</span></label>
                      <div class="row g-3">
                        <div class="col-md-4">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="activities[]" id="cropFarming" value="Crop Farming">
                            <label class="form-check-label" for="cropFarming">Crop Farming</label>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="activities[]" id="livestock" value="Livestock Farming">
                            <label class="form-check-label" for="livestock">Livestock Farming</label>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="activities[]" id="aquaculture" value="Aquaculture">
                            <label class="form-check-label" for="aquaculture">Aquaculture</label>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="activities[]" id="processing" value="Agro-processing">
                            <label class="form-check-label" for="processing">Agro-processing</label>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="activities[]" id="inputSales" value="Agro-input sales">
                            <label class="form-check-label" for="inputSales">Agro-input sales</label>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="activities[]" id="trainingConsult" value="Agricultural training/consulting">
                            <label class="form-check-label" for="trainingConsult">Agricultural training/consulting</label>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="activities[]" id="otherActivity" value="Others" onchange="toggleOtherField(this, 'otherActivityText')">
                            <label class="form-check-label" for="otherActivity">Others</label>
                          </div>
                        </div>
                        <div class="col-md-8" id="otherActivityField" style="display: none;">
                          <input type="text" class="form-control" id="otherActivityText" name="otherActivityText" placeholder="Please specify">
                        </div>
                      </div>
                    </div>
                    
                    <div class="col-12">
                      <label for="businessDescription" class="form-label">Briefly describe your farming/agro business (max 200 words) <span class="text-danger">*</span></label>
                      <textarea class="form-control" id="businessDescription" name="businessDescription" rows="3" maxlength="1000" required></textarea>
                      <div class="form-text">Describe what you do, your products/services, scale of operation, etc.</div>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label">How long have you been in this business? <span class="text-danger">*</span></label>
                      <select class="form-select" name="yearsInBusiness" required>
                        <option value="" selected disabled>Select duration</option>
                        <option value="Less than 1 year">Less than 1 year</option>
                        <option value="1–3 years">1–3 years</option>
                        <option value="4–7 years">4–7 years</option>
                        <option value="Above 7 years">Above 7 years</option>
                      </select>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label">Do you farm individually or as part of a cooperative/group? <span class="text-danger">*</span></label>
                      <div class="d-flex gap-4">
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="farmingType" id="individually" value="Individually" required onchange="toggleCoopField()">
                          <label class="form-check-label" for="individually">Individually</label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="farmingType" id="cooperative" value="Cooperative/group" onchange="toggleCoopField()">
                          <label class="form-check-label" for="cooperative">Cooperative/group</label>
                        </div>
                      </div>
                      <div id="coopNameField" style="display: none;" class="mt-2">
                        <input type="text" class="form-control" id="coopName" name="coopName" placeholder="Name of Cooperative/Group">
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- SECTION C: Training & Grant Interest -->
                <div class="mb-5">
                  <div class="d-flex align-items-center mb-4">
                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                      <span class="fs-5">C</span>
                    </div>
                    <h3 class="ms-3 mb-0">Training & Grant Interest</h3>
                  </div>
                  
                  <div class="row g-3">
                    <div class="col-12">
                      <label class="form-label">Which of the following trainings interest you the most? (Select all that apply) <span class="text-danger">*</span></label>
                      <div class="row g-3">
                        <div class="col-md-6">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="trainings[]" id="sustainableAgri" value="Sustainable Agribusiness">
                            <label class="form-check-label" for="sustainableAgri">Sustainable Agribusiness</label>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="trainings[]" id="valueChain" value="Agricultural Value Chain">
                            <label class="form-check-label" for="valueChain">Agricultural Value Chain</label>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="trainings[]" id="processingPackaging" value="Processing and Packaging">
                            <label class="form-check-label" for="processingPackaging">Processing and Packaging</label>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="trainings[]" id="businessPlanning" value="Farm Business Planning & Record Keeping">
                            <label class="form-check-label" for="businessPlanning">Farm Business Planning & Record Keeping</label>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <div class="col-12">
                      <label class="form-label">What major challenge(s) do you face in your agribusiness? <span class="text-danger">*</span></label>
                      <div class="row g-3">
                        <div class="col-md-6">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="challenges[]" id="financeChallenge" value="Access to finance">
                            <label class="form-check-label" for="financeChallenge">Access to finance</label>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="challenges[]" id="marketChallenge" value="Access to market">
                            <label class="form-check-label" for="marketChallenge">Access to market</label>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="challenges[]" id="productivityChallenge" value="Low productivity">
                            <label class="form-check-label" for="productivityChallenge">Low productivity</label>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="challenges[]" id="packagingChallenge" value="Poor packaging/processing">
                            <label class="form-check-label" for="packagingChallenge">Poor packaging/processing</label>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="challenges[]" id="trainingChallenge" value="Limited training">
                            <label class="form-check-label" for="trainingChallenge">Limited training</label>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="challenges[]" id="otherChallenge" value="Others" onchange="toggleOtherField(this, 'otherChallengeText')">
                            <label class="form-check-label" for="otherChallenge">Others</label>
                          </div>
                        </div>
                        <div class="col-md-12" id="otherChallengeField" style="display: none;">
                          <input type="text" class="form-control" id="otherChallengeText" name="otherChallengeText" placeholder="Please explain briefly">
                        </div>
                      </div>
                    </div>
                    
                    <div class="col-12">
                      <label for="trainingBenefit" class="form-label">If selected, how will the training and/or grant help your business? (max 150 words) <span class="text-danger">*</span></label>
                      <textarea class="form-control" id="trainingBenefit" name="trainingBenefit" rows="3" maxlength="750" required></textarea>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label">Do you have access to a smartphone or internet-enabled device? <span class="text-danger">*</span></label>
                      <div class="d-flex gap-4">
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="hasSmartphone" id="smartphoneYes" value="Yes" required>
                          <label class="form-check-label" for="smartphoneYes">Yes</label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="hasSmartphone" id="smartphoneNo" value="No">
                          <label class="form-check-label" for="smartphoneNo">No</label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- SECTION D: Business Idea for Grant Competition -->
                <div class="mb-5">
                  <div class="d-flex align-items-center mb-4">
                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                      <span class="fs-5">D</span>
                    </div>
                    <div>
                      <h3 class="ms-3 mb-1">Business Idea for Grant Competition</h3>
                      <p class="ms-3 mb-0 text-muted">(This section is optional but required for those interested in the grant competition)</p>
                    </div>
                  </div>
                  
                  <div class="row g-3">
                    <div class="col-12">
                      <label for="businessIdea" class="form-label">What is your agro-enterprise idea or proposal? (max 300 words)</label>
                      <textarea class="form-control" id="businessIdea" name="businessIdea" rows="4" maxlength="1500"></textarea>
                      <div class="form-text">Describe your business idea, its uniqueness, potential impact, and how it solves a problem</div>
                    </div>
                    
                    <div class="col-md-6">
                      <label for="fundingAmount" class="form-label">How much funding do you think your idea will need to take off or scale? (in Naira)</label>
                      <div class="input-group">
                        <span class="input-group-text">₦</span>
                        <input type="number" class="form-control" id="fundingAmount" name="fundingAmount" min="0" step="1000">
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label">Have you received any grant/support before?</label>
                      <div class="d-flex gap-4">
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="receivedGrant" id="grantYes" value="Yes" onchange="toggleGrantField()">
                          <label class="form-check-label" for="grantYes">Yes</label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="receivedGrant" id="grantNo" value="No" onchange="toggleGrantField()">
                          <label class="form-check-label" for="grantNo">No</label>
                        </div>
                      </div>
                      <div id="grantDetailsField" style="display: none;" class="mt-2">
                        <input type="text" class="form-control" id="grantDetails" name="grantDetails" placeholder="State the source and year">
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- SECTION E: Declaration -->
                <div class="mb-4">
                  <div class="d-flex align-items-center mb-4">
                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                      <span class="fs-5">E</span>
                    </div>
                    <h3 class="ms-3 mb-0">Declaration</h3>
                  </div>
                  
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label">Do you agree to participate fully in the training if selected? <span class="text-danger">*</span></label>
                      <div class="d-flex gap-4">
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="agreeTraining" id="agreeTrainingYes" value="Yes" required>
                          <label class="form-check-label" for="agreeTrainingYes">Yes</label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="agreeTraining" id="agreeTrainingNo" value="No">
                          <label class="form-check-label" for="agreeTrainingNo">No</label>
                        </div>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label">Do you agree that the information provided above is true and correct to the best of your knowledge? <span class="text-danger">*</span></label>
                      <div class="d-flex gap-4">
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="agreeInfo" id="agreeInfoYes" value="Yes" required>
                          <label class="form-check-label" for="agreeInfoYes">Yes</label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="agreeInfo" id="agreeInfoNo" value="No">
                          <label class="form-check-label" for="agreeInfoNo">No</label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="d-grid mt-4">
                  <button type="submit" class="btn btn-success btn-lg py-3">
                    <i class="bi bi-send-fill me-2"></i> Submit Application
                  </button>
                </div>
              </form>
            <?php endif; ?>
          </div>
        </div>
        
        <div class="text-center mt-4">
          <p class="text-muted">Need help with your application? Call us at <a href="tel:+2347037997601">+234 703 799 7601</a> or email <a href="mailto:info@fandvagroservices.com.ng">info@fandvagroservices.com.ng</a></p>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
  #farmerRegistrationForm .form-control, 
  #farmerRegistrationForm .form-select {
    padding: 10px 15px;
    border-radius: 8px;
  }
  
  #farmerRegistrationForm label {
    font-weight: 500;
  }
  
  #farmerRegistrationForm .form-check-input {
    margin-top: 0.25rem;
  }
  
  #farmerRegistrationForm .section-header {
    border-bottom: 2px solid #198754;
    padding-bottom: 10px;
    margin-bottom: 20px;
  }
  
  .word-count {
    font-size: 0.8rem;
    color: #6c757d;
    text-align: right;
  }
</style>

<script>
  // Toggle other activity field
  function toggleOtherField(checkbox, fieldId) {
    const otherField = document.getElementById(fieldId.replace('Text', 'Field'));
    if (checkbox.checked) {
      otherField.style.display = 'block';
    } else {
      otherField.style.display = 'none';
      document.getElementById(fieldId).value = '';
    }
  }
  
  // Toggle cooperative name field
  function toggleCoopField() {
    const coopField = document.getElementById('coopNameField');
    if (document.getElementById('cooperative').checked) {
      coopField.style.display = 'block';
      document.getElementById('coopName').required = true;
    } else {
      coopField.style.display = 'none';
      document.getElementById('coopName').required = false;
      document.getElementById('coopName').value = '';
    }
  }
  
  // Toggle grant details field
  function toggleGrantField() {
    const grantField = document.getElementById('grantDetailsField');
    if (document.getElementById('grantYes').checked) {
      grantField.style.display = 'block';
      document.getElementById('grantDetails').required = true;
    } else {
      grantField.style.display = 'none';
      document.getElementById('grantDetails').required = false;
      document.getElementById('grantDetails').value = '';
    }
  }
  
  // Word count for textareas
  document.addEventListener('DOMContentLoaded', function() {
    const textareas = ['businessDescription', 'trainingBenefit', 'businessIdea'];
    
    textareas.forEach(id => {
      const textarea = document.getElementById(id);
      if (textarea) {
        // Create word count element
        const wordCount = document.createElement('div');
        wordCount.className = 'word-count';
        textarea.parentNode.appendChild(wordCount);
        
        // Update word count on input
        textarea.addEventListener('input', function() {
          const maxWords = id === 'businessIdea' ? 300 : (id === 'businessDescription' ? 200 : 150);
          const words = this.value.trim() ? this.value.trim().split(/\s+/).length : 0;
          wordCount.textContent = `${words} / ${maxWords} words`;
          
          if (words > maxWords) {
            wordCount.style.color = '#dc3545';
          } else {
            wordCount.style.color = '#6c757d';
          }
        });
      }
    });
  });

  // Check for duplicate registration before form submission
document.getElementById('farmerRegistrationForm').addEventListener('submit', function(e) {
    const phone = document.getElementById('phone').value;
    const email = document.getElementById('email').value;
    
    // First check phone number
    fetch('includes/check_farmer_phone?phone=' + encodeURIComponent(phone))
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                alert('This phone number has already been registered. Please contact support if you need to update your application.');
                e.preventDefault();
                return;
            }
            
            // If email is provided, check it too
            if (email) {
                return fetch('includes/check_farmer_email?email=' + encodeURIComponent(email))
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            alert('This email address has already been registered. Please contact support if you need to update your application.');
                            e.preventDefault();
                        }
                    });
            }
        })
        .catch(error => {
            console.error('Error checking registration:', error);
        });
});
</script>

<?php include 'includes/footer.php'; ?>