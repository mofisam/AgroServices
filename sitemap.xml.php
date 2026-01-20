<?php
header('Content-Type: application/xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<urlset
  xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
  http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">

  <!-- Main Pages -->
  <url>
    <loc>https://fandvagroservices.com.ng/</loc>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>

  <url>
    <loc>https://fandvagroservices.com.ng/products/</loc>
    <changefreq>weekly</changefreq>
    <priority>0.9</priority>
  </url>

  <url>
    <loc>https://fandvagroservices.com.ng/services</loc>
    <changefreq>monthly</changefreq>
    <priority>0.8</priority>
  </url>

  <url>
    <loc>https://fandvagroservices.com.ng/about_us</loc>
    <changefreq>monthly</changefreq>
    <priority>0.6</priority>
  </url>

  <url>
    <loc>https://fandvagroservices.com.ng/contact.php</loc>
    <changefreq>monthly</changefreq>
    <priority>0.6</priority>
  </url>

  <url>
    <loc>https://fandvagroservices.com.ng/team.php</loc>
    <changefreq>monthly</changefreq>
    <priority>0.6</priority>
  </url>

  <!-- Programs -->
  <url>
    <loc>https://fandvagroservices.com.ng/farmer_registration/</loc>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
  </url>

  <url>
    <loc>https://fandvagroservices.com.ng/program_details/</loc>
    <changefreq>weekly</changefreq>
    <priority>0.7</priority>
  </url>

  <!-- Accounts -->
  <url>
    <loc>https://fandvagroservices.com.ng/registration/index</loc>
    <changefreq>monthly</changefreq>
    <priority>0.5</priority>
  </url>

  <url>
    <loc>https://fandvagroservices.com.ng/registration/buyer</loc>
    <changefreq>monthly</changefreq>
    <priority>0.5</priority>
  </url>

  <url>
    <loc>https://fandvagroservices.com.ng/registration/seller</loc>
    <changefreq>monthly</changefreq>
    <priority>0.5</priority>
  </url>

  <url>
    <loc>https://fandvagroservices.com.ng/login.php</loc>
    <changefreq>monthly</changefreq>
    <priority>0.3</priority>
  </url>

<?php
require_once __DIR__ . '/config/db.php';

$stmt = $conn->query("SELECT id, updated_at FROM products WHERE status='active'");

while ($row = $stmt->fetch_assoc()):
  $lastmod = date('c', strtotime($row['updated_at']));
?>
  <url>
    <loc>https://fandvagroservices.com.ng/products/view_product.php?id=<?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?></loc>
    <lastmod><?php echo $lastmod; ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.7</priority>
  </url>
<?php endwhile; $conn->close(); ?>

</urlset>