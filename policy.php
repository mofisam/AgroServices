<?php
// Policy Page Meta Variables
$page_title = "F and V Agro Services Policies | Buyer & Seller Guidelines";
$page_description = "Review F and V Agro Services comprehensive marketplace policies for buyers and sellers. Learn about transactions, returns, commissions, and platform rules for agricultural e-commerce in Nigeria.";
$page_keywords = "agro-commerce policies Nigeria, farm marketplace rules, buyer protection policy, seller guidelines, agricultural platform terms, F and V Agro Services terms";
$og_image = "https://www.fandvagroservices.com.ng/assets/images/policy-social-preview.jpg";
$current_url = "https://www.fandvagroservices.com.ng/policy";

session_start();
include 'includes/header.php';
include_once 'includes/tracking.php';
?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebPage",
  "name": "Marketplace Policies",
  "description": "Official terms and conditions for F and V Agro Services platform",
  "lastReviewed": "<?= date('Y-m-d') ?>",
  "primaryImageOfPage": {
    "@type": "ImageObject",
    "url": "https://www.fandvagroservices.com.ng/assets/images/policy-social-preview.jpg"
  },
  "significantLink": [
    "https://www.fandvagroservices.com.ng/policy#buyerPolicy",
    "https://www.fandvagroservices.com.ng/policy#sellerPolicy"
  ]
}
</script>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold text-success mb-3"> F and V Agro Services Policies</h1>
        <p class="lead text-muted">Our commitment to transparency and fair practices in agricultural e-commerce</p>
    </div>

    <div class="row g-4">
        <!-- Quick Links Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Policy Quick Links</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="#buyerPolicy" class="btn btn-outline-primary text-start" data-bs-toggle="collapse" data-bs-target="#buyerPolicy">
                            <i class="bi bi-cart me-2"></i> Buyer Policy
                        </a>
                        <a href="#sellerPolicy" class="btn btn-outline-success text-start" data-bs-toggle="collapse" data-bs-target="#sellerPolicy">
                            <i class="bi bi-shop me-2"></i> Seller Policy
                        </a>
                        <a href="#disclaimer" class="btn btn-outline-secondary text-start" data-bs-toggle="collapse" data-bs-target="#disclaimer">
                            <i class="bi bi-info-circle me-2"></i> General Disclaimer
                        </a>
                    </div>
                    <hr>
                    <div class="alert alert-success">
                        <i class="bi bi-question-circle-fill me-2"></i>
                        <strong>Questions?</strong> Contact our <a href="contact" class="alert-link">support team</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Policy Content -->
        <div class="col-md-8">
            <div class="accordion" id="policyAccordion">

                <!-- Buyer Policy -->
                <div class="accordion-item mb-3 border-0 shadow-sm">
                    <h2 class="accordion-header" id="buyerHeading">
                        <button class="accordion-button bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#buyerPolicy" aria-expanded="true" aria-controls="buyerPolicy">
                            <i class="bi bi-cart me-2 text-primary"></i> Buyer Policy
                        </button>
                    </h2>
                    <div id="buyerPolicy" class="accordion-collapse collapse show" aria-labelledby="buyerHeading" data-bs-parent="#policyAccordion">
                        <div class="accordion-body">
                            <div class="alert alert-primary">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                These policies protect both buyers and sellers on our platform.
                            </div>
                            <ol class="list-group list-group-numbered">
                                <li class="list-group-item border-0">
                                    <strong>Eligibility</strong>
                                    <p class="mb-0">Buyers must be at least 18 years old and legally capable of entering into binding contracts. By using the platform, buyers agree to comply with all applicable laws and the terms set out in this policy.</p>
                                </li>
                                <li class="list-group-item border-0">
                                    <strong>Registration</strong>
                                    <p class="mb-0">To place an order, buyers are required to register on the F and V Agro Services platform by providing accurate personal and contact information.</p>
                                </li>
                                <li class="list-group-item border-0">
                                    <strong>Product Information</strong>
                                    <p class="mb-0">Buyers are encouraged to thoroughly review product listings, including descriptions, pricing, quantity, images, and delivery timelines before making a purchase. F and V Agro Services is not liable for misunderstandings arising from ignored product details.</p>
                                </li>
                                <li class="list-group-item border-0">
                                    <strong>Orders and Payments</strong>
                                    <p class="mb-0">All orders must be placed directly through the platform. Payments must be made using the secure payment channels provided. Orders will not be confirmed until payment is received.</p>
                                </li>
                                <li class="list-group-item border-0">
                                    <strong>Delivery and Logistics</strong>
                                    <p class="mb-0">Delivery terms and fees vary depending on the product type, seller location, and buyer location. Estimated delivery times are communicated at checkout. Buyers must inspect goods upon delivery and report any issues within 48 hours.</p>
                                </li>
                                <li class="list-group-item border-0">
                                    <strong>Returns and Disputes</strong>
                                    <p class="mb-0">Returns or exchanges are subject to the individual seller's return policy. For any complaints or disputes (e.g., wrong items, poor quality), buyers must notify F and V Agro Services within 48 hours of delivery. Our team will mediate between both parties to ensure a fair resolution.</p>
                                </li>
                                <li class="list-group-item border-0">
                                    <strong>Platform Integrity</strong>
                                    <p class="mb-0">Buyers are strictly prohibited from engaging sellers off the platform to avoid transaction fees. Any such activity may result in account suspension or permanent ban.</p>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Seller Policy -->
                <div class="accordion-item mb-3 border-0 shadow-sm">
                    <h2 class="accordion-header" id="sellerHeading">
                        <button class="accordion-button bg-light collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sellerPolicy" aria-expanded="false" aria-controls="sellerPolicy">
                            <i class="bi bi-shop me-2 text-success"></i> Seller Policy
                        </button>
                    </h2>
                    <div id="sellerPolicy" class="accordion-collapse collapse" aria-labelledby="sellerHeading" data-bs-parent="#policyAccordion">
                        <div class="accordion-body">
                            <div class="alert alert-success">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                These guidelines ensure quality and trust in our marketplace.
                            </div>
                            <ol class="list-group list-group-numbered">
                                <li class="list-group-item border-0">
                                    <strong>Eligibility and Registration</strong>
                                    <p class="mb-0">Sellers must register on the F and V Agro Services platform by providing business details, including farm/business name, location, phone number, and valid ID. A ₦3,000 annual registration fee is required to activate a seller account.</p>
                                </li>
                                <li class="list-group-item border-0">
                                    <strong>Product Listings</strong>
                                    <p class="mb-0">Sellers must provide honest, detailed, and accurate descriptions of their products, including prices, units, varieties, grading/quality information, and expected delivery timelines. False or misleading information may result in suspension.</p>
                                </li>
                                <li class="list-group-item border-0">
                                    <strong>Order Fulfillment</strong>
                                    <p class="mb-0">Sellers are expected to fulfill orders promptly and reliably. Products must match the listed descriptions. Late deliveries or delivery failures without valid reasons may attract penalties or removal from the platform.</p>
                                </li>
                                <li class="list-group-item border-0">
                                    <strong>Quality Assurance</strong>
                                    <p class="mb-0">F and V Agro Services reserves the right to verify the quality and source of listed products through inspection, third-party reports, or customer feedback. Sellers with recurring complaints may be suspended or removed.</p>
                                </li>
                                <li class="list-group-item border-0">
                                    <strong>Commission and Fees</strong>
                                    <p class="mb-0">F and V Agro Services charges a 3% commission on every successful sale made through the platform. This is deducted automatically upon payment before disbursing the seller’s balance.</p>
                                </li>
                                <li class="list-group-item border-0">
                                    <strong>Returns and Disputes</strong>
                                    <p class="mb-0">In the event of a complaint by a buyer, the seller must cooperate fully during investigations. If a refund or product return is warranted, the seller must comply based on the outcome of the dispute resolution process.</p>
                                </li>
                                <li class="list-group-item border-0">
                                    <strong>Prohibited Conduct</strong>
                                    <div class="alert alert-warning mt-2">
                                        <ul class="mb-0">
                                            <li>List illegal or substandard products.</li>
                                            <li>Falsify transaction data.</li>
                                            <li>Attempt to conduct transactions outside the platform.</li>
                                            <li>Engage in deceptive or unethical practices.</li>
                                        </ul>
                                        <p class="mb-0 mt-2"><strong>Violations may result in suspension or permanent removal from the platform.</strong></p>
                                    </div>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Disclaimer -->
                <div class="accordion-item border-0 shadow-sm">
                    <h2 class="accordion-header" id="disclaimerHeading">
                        <button class="accordion-button bg-light collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#disclaimer" aria-expanded="false" aria-controls="disclaimer">
                            <i class="bi bi-info-circle me-2 text-secondary"></i> General Disclaimer
                        </button>
                    </h2>
                    <div id="disclaimer" class="accordion-collapse collapse" aria-labelledby="disclaimerHeading" data-bs-parent="#policyAccordion">
                        <div class="accordion-body">
                            <div class="alert alert-secondary">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                Important legal information about our platform services.
                            </div>
                            <div class="card border-warning">
                                <div class="card-body">
                                    <p class="mb-0">
                                    F and V Agro Services acts as an intermediary platform to connect buyers and sellers and is not liable for damages arising from direct buyer-seller arrangements outside the platform. 
                                    We strive to maintain a secure, transparent, and efficient marketplace for agricultural produce and inputs.
                                    </p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <p><strong>Last Updated:</strong> <?= date('F j, Y') ?></p>
                                <p>By using our platform, you agree to these terms and conditions.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>