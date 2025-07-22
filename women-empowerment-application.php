<?php
// Page Meta Variables
$page_title = "Women in Agriculture Empowerment Program | F and V Agro Services";
$page_description = "Apply for our women empowerment program to get access to resources, training, and market opportunities for your agricultural business.";
$page_keywords = "women farmers Nigeria, agricultural empowerment, farming grants for women, women in agriculture, farming support Nigeria";
$og_image = "https://www.fandvagroservices.com.ng/assets/images/women-farmers-social.jpg";
$current_url = "https://www.fandvagroservices.com.ng/women-empowerment-application";

include 'includes/header.php'; 
?>

<!-- Application Form Section -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-success text-white py-3">
            <h2 class="h4 mb-0">Women in Agriculture Empowerment Program Application</h2>
          </div>
          <div class="card-body p-4 p-md-5">
            <?php if(isset($_GET['success'])): ?>
              <div class="alert alert-success">
                <i class="bi bi-check-circle-fill me-2"></i>
                Thank you for your application! We've received your submission and will contact you within 5 working days.
              </div>
              <div class="text-center mt-4">
                <a href="/" class="btn btn-success">Back to Homepage</a>
              </div>
            <?php else: ?>
              <p class="text-muted mb-4">Please fill out this form completely to apply for our women empowerment program. All fields are required unless marked optional.</p>
              
              <form id="empowermentForm" action="/submit-empowerment-application" method="POST">
                <div class="row mb-3">
                  <div class="col-md-6 mb-3 mb-md-0">
                    <label for="fullName" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="fullName" name="fullName" required>
                  </div>
                  <div class="col-md-6">
                    <label for="phoneNumber" class="form-label">Phone Number <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" required>
                  </div>
                </div>
                
                <div class="row mb-3">
                  <div class="col-md-6 mb-3 mb-md-0">
                    <label for="email" class="form-label">Email Address (optional)</label>
                    <input type="email" class="form-control" id="email" name="email">
                  </div>
                  <div class="col-md-6">
                    <label for="age" class="form-label">Age <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="age" name="age" min="18" max="99" required>
                  </div>
                </div>
                
                <div class="row mb-3">
                  <div class="col-md-6 mb-3 mb-md-0">
                    <label for="state" class="form-label">State of Residence <span class="text-danger">*</span></label>
                    <select class="form-select" id="state" name="state" required>
                      <option value="" selected disabled>Select State</option>
                      <option value="Abia">Abia</option>
                      <option value="Adamawa">Adamawa</option>
                      <!-- Add all Nigerian states -->
                      <option value="Zamfara">Zamfara</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label for="lga" class="form-label">Local Government Area <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="lga" name="lga" required>
                  </div>
                </div>
                
                <div class="mb-3">
                  <label for="address" class="form-label">Full Address <span class="text-danger">*</span></label>
                  <textarea class="form-control" id="address" name="address" rows="2" required></textarea>
                </div>
                
                <div class="mb-3">
                  <label for="farmType" class="form-label">Primary Farming Activity <span class="text-danger">*</span></label>
                  <select class="form-select" id="farmType" name="farmType" required>
                    <option value="" selected disabled>Select Farming Activity</option>
                    <option value="Crop Production">Crop Production</option>
                    <option value="Livestock">Livestock Farming</option>
                    <option value="Poultry">Poultry Farming</option>
                    <option value="Fishery">Fishery/Aquaculture</option>
                    <option value="Agro-processing">Agro-processing</option>
                    <option value="Mixed Farming">Mixed Farming</option>
                  </select>
                </div>
                
                <div class="row mb-3">
                  <div class="col-md-6 mb-3 mb-md-0">
                    <label for="farmSize" class="form-label">Farm Size <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <input type="number" class="form-control" id="farmSize" name="farmSize" step="0.1" required>
                      <select class="form-select" id="sizeUnit" name="sizeUnit" style="max-width: 100px;">
                        <option value="acres">acres</option>
                        <option value="hectares">hectares</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label for="yearsFarming" class="form-label">Years of Farming Experience <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="yearsFarming" name="yearsFarming" min="0" max="50" required>
                  </div>
                </div>
                
                <div class="mb-3">
                  <label for="challenges" class="form-label">Describe Your Main Challenges <span class="text-danger">*</span></label>
                  <textarea class="form-control" id="challenges" name="challenges" rows="3" required></textarea>
                </div>
                
                <div class="mb-4">
                  <label class="form-label">What Support Do You Need? <span class="text-danger">*</span></label>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="training" name="supportNeeded[]" value="Training">
                    <label class="form-check-label" for="training">Training/Education</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="equipment" name="supportNeeded[]" value="Equipment">
                    <label class="form-check-label" for="equipment">Farming Equipment</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="finance" name="supportNeeded[]" value="Financial">
                    <label class="form-check-label" for="finance">Financial Support</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="market" name="supportNeeded[]" value="Market">
                    <label class="form-check-label" for="market">Market Access</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="inputs" name="supportNeeded[]" value="Inputs">
                    <label class="form-check-label" for="inputs">Farming Inputs</label>
                  </div>
                </div>
                
                <div class="mb-4">
                  <label for="additionalInfo" class="form-label">Additional Information (optional)</label>
                  <textarea class="form-control" id="additionalInfo" name="additionalInfo" rows="3"></textarea>
                </div>
                
                <div class="mb-4 form-check">
                  <input type="checkbox" class="form-check-input" id="agreeTerms" name="agreeTerms" required>
                  <label class="form-check-label" for="agreeTerms">I certify that all information provided is accurate and complete <span class="text-danger">*</span></label>
                </div>
                
                <div class="d-grid">
                  <button type="submit" class="btn btn-success btn-lg py-3">
                    <i class="bi bi-send-fill me-2"></i> Submit Application
                  </button>
                </div>
              </form>
            <?php endif; ?>
          </div>
        </div>
        
        <div class="text-center mt-4">
          <p class="text-muted">Need help with your application? Call us at <a href="tel:+2347037997601">+234 703 799 7601</a></p>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
  #empowermentForm .form-control, 
  #empowermentForm .form-select {
    padding: 10px 15px;
    border-radius: 8px;
  }
  
  #empowermentForm label {
    font-weight: 500;
  }
  
  #empowermentForm .form-check-input {
    margin-top: 0.25rem;
  }
</style>

<?php include 'includes/footer.php'; ?>