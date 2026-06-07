<?php
/**
 * seed.php
 *
 * Populates the news_db with realistic sample data:
 *  - Downloads category-appropriate sample images from picsum.photos
 *    and saves them under assets/images/seed/.
 *  - Inserts 15 news articles spanning every category, with featured
 *    and breaking flags.
 *  - Inserts About + Contact Info content blocks.
 *
 * Run from the command line:
 *     php database/seed.php
 *
 * Re-running is safe: the script wipes news and site_content tables
 * first, but preserves categories, the admin user, and submitted
 * contact-form messages.
 */

declare(strict_types=1);

require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/helpers.php';

// CLI-only safety: refuse to run when invoked from the web.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script may only be run from the command line.\n");
}

$projectRoot = dirname(__DIR__);
$seedDir     = $projectRoot . '/assets/images/seed';
$webImageDir = 'assets/images/seed';

if (!is_dir($seedDir) && !mkdir($seedDir, 0755, true) && !is_dir($seedDir)) {
    fwrite(STDERR, "Cannot create seed image directory: {$seedDir}\n");
    exit(1);
}

echo "==> Seeding news_db\n";

// ----------------------------------------------------------------
// Helpers
// ----------------------------------------------------------------

function out(string $msg): void {
    echo $msg . PHP_EOL;
}

/**
 * Download a remote image to disk via cURL.
 * Returns the destination filename on success, or null on failure.
 */
function downloadImage(string $url, string $destPath): ?string {
    if (file_exists($destPath) && filesize($destPath) > 1024) {
        return basename($destPath); // Already downloaded; reuse.
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_USERAGENT      => 'DailyNewsSeed/1.0',
    ]);
    $data = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($data === false || $code !== 200 || strlen($data) < 1024) {
        fwrite(STDERR, "  ! Failed to download {$url} (HTTP {$code}, err={$err})\n");
        return null;
    }

    if (file_put_contents($destPath, $data) === false) {
        fwrite(STDERR, "  ! Failed to write {$destPath}\n");
        return null;
    }

    return basename($destPath);
}

/** Picsum URL for stable per-seed images. */
function picsumUrl(string $seed, int $width = 1200, int $height = 675): string {
    return "https://picsum.photos/seed/" . rawurlencode($seed) . "/{$width}/{$height}";
}

// ----------------------------------------------------------------
// Wipe transactional tables (keep categories, admins, contacts).
// ----------------------------------------------------------------

out('-> Truncating news, site_content');
try {
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    $pdo->exec('TRUNCATE TABLE news');
    $pdo->exec('TRUNCATE TABLE site_content');
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
} catch (PDOException $e) {
    fwrite(STDERR, "Failed to truncate tables: {$e->getMessage()}\n");
    exit(1);
}

// ----------------------------------------------------------------
// Resolve category slug -> id.
// ----------------------------------------------------------------

$catRows = $pdo->query('SELECT id, slug FROM categories')->fetchAll();
$catId   = [];
foreach ($catRows as $row) {
    $catId[$row['slug']] = (int) $row['id'];
}

if (empty($catId)) {
    fwrite(STDERR, "No categories found — run schema.sql first.\n");
    exit(1);
}

// ----------------------------------------------------------------
// Article seed data.
// ----------------------------------------------------------------

$articles = [
    [
        'category'    => 'politics',
        'title'       => 'Government Announces Major Infrastructure Investment Plan',
        'summary'     => 'The government has unveiled a landmark infrastructure investment plan worth billions, targeting roads, bridges, railways, and digital connectivity across all provinces.',
        'body'        => '<p>The government today announced one of the most ambitious infrastructure investment plans in the country\'s history, pledging substantial funding to upgrade and expand critical national infrastructure over the next five years.</p><p>The plan, officially titled the "National Connectivity and Growth Initiative," allocates funding across four primary sectors: road network rehabilitation covering over 12,000 kilometres; railway modernisation; digital infrastructure expansion to every district; and three new international-standard bridges.</p><p>Finance Minister Dr. Priya Rathnayake stated the investment would be funded through a combination of government revenue, low-interest development loans, and public-private partnership arrangements with stringent procurement oversight.</p><p>Opposition parties acknowledged the scale but called for detailed project timelines and accountability mechanisms. Economic analysts broadly welcomed the initiative.</p>',
        'author'      => 'Admin',
        'is_featured' => 1, 'is_breaking' => 1, 'views' => 4820,
        'published'   => '2026-06-01 08:00:00',
    ],
    [
        'category'    => 'sports',
        'title'       => 'National Cricket Team Wins Championship Against Australia',
        'summary'     => 'In a thrilling final-over finish at Melbourne, the national cricket team clinched the championship title against Australia, sending millions of fans into jubilation.',
        'body'        => '<p>The national cricket team produced a stunning performance to defeat Australia in the final of the Tri-Nation Championship Series at the Melbourne Cricket Ground. Chasing 287, the team reached the winning run off the very last ball.</p><p>Opener Sanath Mendis crafted a masterful 112 off 98 balls including 14 boundaries. His 156-run partnership with Roshan Fernando proved decisive. Even when both fell, lower-order batsman Kasun Perera scrambled two off the final delivery to seal it.</p><p>Captain Dinesh Abeysekara dedicated the victory to the nation\'s fans. A public reception is planned at the national stadium next week.</p>',
        'author'      => 'Sports Desk',
        'is_featured' => 1, 'is_breaking' => 1, 'views' => 7341,
        'published'   => '2026-06-02 09:30:00',
    ],
    [
        'category'    => 'technology',
        'title'       => 'Tech Giant Opens Regional Headquarters in Colombo',
        'summary'     => 'A leading global technology corporation has officially opened its South Asian regional headquarters in Colombo, marking a pivotal moment for the country\'s technology sector.',
        'body'        => '<p>Global technology leader NovaTech Corporation formally inaugurated its South Asian regional headquarters in Colombo today, in a ceremony attended by the Minister of Technology and senior officials.</p><p>The 40,000 sq ft facility in the Colombo Port City zone will support operations across the region. NovaTech currently employs 800 staff locally and committed to growing this to 3,500 within three years.</p><p>The HQ will serve as the hub for cloud computing, AI, and cybersecurity divisions across South and Southeast Asia. The company also announced partnerships with three local universities to establish a research centre with $5 million initial investment.</p>',
        'author'      => 'Technology Desk',
        'is_featured' => 1, 'is_breaking' => 0, 'views' => 3102,
        'published'   => '2026-06-02 11:00:00',
    ],
    [
        'category'    => 'business',
        'title'       => 'Stock Market Reaches All-Time High Amid Economic Growth',
        'summary'     => 'The Colombo Stock Exchange closed at an all-time high on Wednesday, driven by strong corporate earnings, positive macroeconomic data, and sustained foreign investor interest.',
        'body'        => '<p>The CSE\'s All Share Price Index closed at a record 14,823.47 points, surpassing the previous all-time high. The benchmark gained 312 points or 2.15 percent on turnover exceeding 8.5 billion rupees.</p><p>The rally was underpinned by a stronger-than-expected GDP growth figure of 5.8 percent annualised in Q1 2026 — the fourth consecutive quarter of accelerating growth. Banking, consumer goods, and manufacturing led the gains.</p><p>Foreign investors recorded a net inflow of 1.2 billion rupees. Analysts attributed this to improved sovereign credit rating, stabilising inflation, and confidence in the reform programme.</p>',
        'author'      => 'Business Desk',
        'is_featured' => 1, 'is_breaking' => 0, 'views' => 2254,
        'published'   => '2026-06-03 08:45:00',
    ],
    [
        'category'    => 'entertainment',
        'title'       => 'International Film Festival Returns to Capital City',
        'summary'     => 'The prestigious Colombo International Film Festival is back after a two-year hiatus, showcasing over 80 films from 45 countries.',
        'body'        => '<p>The Colombo International Film Festival opened with a spectacular red-carpet premiere at the Nelum Pokuna Theatre, marking the festival\'s return after a two-year absence. This year\'s edition features 80+ films from 45 countries selected from 3,200+ entries.</p><p>The opening night film was "The River Remembers" by Nirosha Perera, which received a standing ovation and has attracted international distribution interest. The festival features dedicated programmes for South Asian Cinema, post-war documentary, and a new short film competition for filmmakers under 30.</p><p>The festival runs through June 15 at venues across Colombo, with free weekend screenings at Liberty Plaza.</p>',
        'author'      => 'Entertainment Desk',
        'is_featured' => 0, 'is_breaking' => 0, 'views' => 1876,
        'published'   => '2026-06-03 10:15:00',
    ],
    [
        'category'    => 'world',
        'title'       => 'Climate Summit Reaches Historic Agreement on Emissions',
        'summary'     => 'World leaders at the Geneva Climate Summit have signed a landmark accord committing to net-zero emissions by 2045, with binding targets and a $500 billion fund.',
        'body'        => '<p>Representatives from 172 nations signed the Geneva Accord on Climate Action — widely described as the most significant climate agreement since the Paris Agreement. The accord commits signatories to net-zero by 2045, five years earlier than previously agreed.</p><p>Central to the agreement is a $500 billion Global Climate Transition Fund, capitalised primarily by high-income nations over ten years to finance renewable energy and adaptation in developing nations.</p><p>The accord also introduces mandatory carbon pricing for all major economies and brings aviation and maritime sectors within binding targets for the first time. Environmental organisations broadly welcomed the agreement.</p>',
        'author'      => 'World Desk',
        'is_featured' => 0, 'is_breaking' => 1, 'views' => 3489,
        'published'   => '2026-06-03 14:00:00',
    ],
    [
        'category'    => 'politics',
        'title'       => 'New Hospital Complex to Serve Northern Province',
        'summary'     => 'Construction has commenced on a 600-bed multi-specialty hospital complex in Jaffna, set to be the most advanced medical facility in the Northern Province.',
        'body'        => '<p>The Minister of Health broke ground today for the Jaffna National Medical Complex, a 600-bed multi-specialty hospital — the single largest investment in healthcare infrastructure in the Northern Province in over four decades. Total project cost: 18.5 billion rupees.</p><p>The complex will provide tertiary-level care including cardiac surgery, oncology, neurology, neonatology, and organ transplants — specialties currently unavailable in the north. It will also house a 200-seat medical education facility in partnership with the University of Jaffna.</p><p>Local civil society and medical professionals welcomed the announcement as long overdue.</p>',
        'author'      => 'Admin',
        'is_featured' => 0, 'is_breaking' => 0, 'views' => 1543,
        'published'   => '2026-06-04 08:00:00',
    ],
    [
        'category'    => 'sports',
        'title'       => 'Local Football Club Qualifies for Asian Cup',
        'summary'     => 'Colombo FC has made history by becoming the first Sri Lankan club to qualify for the AFC Asian Cup club competition.',
        'body'        => '<p>Colombo FC became the first Sri Lankan football club to qualify for the AFC Asian Cup club competition, securing qualification with a 3-1 victory over Club Valencia at Sugathadasa Stadium in front of 25,000 spectators.</p><p>Brazilian forward Diego Andrade netted a hat-trick. Coach Rajan Gunaratne praised the squad\'s commitment, calling it the proudest night of his career. The aggregate score across the two-legged playoff was 4-2.</p><p>The Football Federation pledged to increase investment in youth development. The AFC Asian Cup group stage draw takes place in Kuala Lumpur next month.</p>',
        'author'      => 'Sports Desk',
        'is_featured' => 0, 'is_breaking' => 0, 'views' => 2987,
        'published'   => '2026-06-04 11:30:00',
    ],
    [
        'category'    => 'business',
        'title'       => 'Central Bank Announces Interest Rate Decision',
        'summary'     => 'The Central Bank\'s Monetary Policy Board has voted to hold the key policy rate at 8.5 percent, citing balanced risks to inflation and growth.',
        'body'        => '<p>The Central Bank held the Standing Lending Facility Rate at 8.50 percent and the Standing Deposit Facility Rate at 7.50 percent for the third consecutive meeting. The decision was in line with market expectations.</p><p>Headline inflation remains within the 4-6 percent target range and underlying price pressures have moderated. The bank revised its 2026 GDP growth projection upward to 5.4 percent, citing stronger performance in manufacturing, services, and tourism.</p><p>Commercial bank lending rates have declined by 150 basis points over the past year, and private sector credit growth has been recovering gradually.</p>',
        'author'      => 'Business Desk',
        'is_featured' => 0, 'is_breaking' => 0, 'views' => 1298,
        'published'   => '2026-06-05 09:00:00',
    ],
    [
        'category'    => 'entertainment',
        'title'       => 'Streaming Platform Launches Regional Original Series',
        'summary'     => 'StreamMax has officially launched its first Sri Lankan original drama series "Kolamba Diaries," a six-episode production exploring urban life.',
        'body'        => '<p>StreamMax debuted all six episodes of "Kolamba Diaries" to subscribers across 60 countries simultaneously. The series follows four families in a Colombo apartment building over a single monsoon season.</p><p>Created by screenwriter Chamari De Silva, the series was filmed entirely on location in Colombo. Director Isuru Bandara made his television directorial debut. Lead performances from Dilani Jayawardena and Rohan Samarawickrama have drawn particular praise.</p><p>StreamMax stated this is the first of at least four planned Sri Lankan original productions over the next two years, representing $12 million in local content investment.</p>',
        'author'      => 'Entertainment Desk',
        'is_featured' => 0, 'is_breaking' => 0, 'views' => 2105,
        'published'   => '2026-06-05 14:30:00',
    ],
    [
        'category'    => 'technology',
        'title'       => 'AI Research Lab Opens at University of Moratuwa',
        'summary'     => 'A new AI research lab focused on language models and computer vision has opened at the University of Moratuwa with international partnerships.',
        'body'        => '<p>The University of Moratuwa unveiled its new Centre for Applied AI Research, a 12,000 sq ft facility funded jointly by the Ministry of Higher Education and three industry partners. The centre will focus on large language models for Sinhala and Tamil, computer vision for agriculture, and AI ethics frameworks.</p><p>Initial research grants total 450 million rupees over three years. The lab will accommodate 60 graduate researchers and partner with universities in Singapore, India, and the UK.</p><p>The Vice-Chancellor stated the lab represents a strategic investment in sovereign AI capability.</p>',
        'author'      => 'Technology Desk',
        'is_featured' => 0, 'is_breaking' => 0, 'views' => 1432,
        'published'   => '2026-06-06 10:00:00',
    ],
    [
        'category'    => 'world',
        'title'       => 'Regional Trade Bloc Adds Three New Member States',
        'summary'     => 'The South Asian Economic Cooperation Forum has formally admitted three new member states at its summit in Kathmandu.',
        'body'        => '<p>The South Asian Economic Cooperation Forum (SAECF) formally admitted three new member states at its summit in Kathmandu, expanding the bloc to 11 members. The new members gain reduced tariffs on 80% of traded goods over a five-year phase-in period.</p><p>The expansion was endorsed unanimously after two years of negotiations. Combined GDP of the expanded bloc now exceeds $4.2 trillion. Member states also agreed on a common framework for digital trade and cross-border payments.</p><p>Analysts called the expansion a meaningful step towards deeper regional integration, though significant implementation challenges remain.</p>',
        'author'      => 'World Desk',
        'is_featured' => 0, 'is_breaking' => 0, 'views' => 987,
        'published'   => '2026-06-06 12:30:00',
    ],
    [
        'category'    => 'politics',
        'title'       => 'Parliament Passes Landmark Data Protection Bill',
        'summary'     => 'After lengthy debate, Parliament has passed the Data Protection and Privacy Bill, introducing comprehensive obligations for businesses handling personal data.',
        'body'        => '<p>Parliament passed the Data Protection and Privacy Bill 142-31, introducing the country\'s first comprehensive data protection framework. The legislation establishes a Data Protection Authority with powers to issue fines of up to 4% of global turnover for serious breaches.</p><p>Key provisions include mandatory breach notification within 72 hours, the right to data portability, and explicit consent requirements for processing sensitive personal data. Implementation will be phased over 18 months.</p><p>Civil liberties groups welcomed the law while flagging concerns about the breadth of national security exemptions.</p>',
        'author'      => 'Admin',
        'is_featured' => 0, 'is_breaking' => 0, 'views' => 1789,
        'published'   => '2026-06-06 14:00:00',
    ],
    [
        'category'    => 'sports',
        'title'       => 'Marathon Runner Sets New National Record',
        'summary'     => 'Long-distance runner Nilanthi Wickramasinghe has broken the national marathon record at the Tokyo International Marathon.',
        'body'        => '<p>Nilanthi Wickramasinghe smashed the 22-year-old national marathon record at the Tokyo International Marathon, clocking 2:23:47 — bettering the previous mark by over four minutes. She finished 11th overall in a high-quality elite field.</p><p>Wickramasinghe, 28, trains under the national athletics programme and has been a consistent presence at major international events over the past three years. Her performance qualifies her for the upcoming Asian Games.</p><p>Athletics officials hailed the run as a watershed moment for distance running in the country.</p>',
        'author'      => 'Sports Desk',
        'is_featured' => 0, 'is_breaking' => 0, 'views' => 2156,
        'published'   => '2026-06-06 16:00:00',
    ],
    [
        'category'    => 'business',
        'title'       => 'Renewable Energy Sector Attracts Record Investment',
        'summary'     => 'Foreign and domestic investors have committed $1.8 billion to renewable energy projects this year, the highest annual total on record.',
        'body'        => '<p>The Board of Investment confirmed that committed investment in renewable energy projects this year has reached $1.8 billion across 23 approved projects — the highest annual total on record. Solar accounts for $1.1 billion, wind for $480 million, and biomass for the balance.</p><p>The pipeline includes three utility-scale solar parks exceeding 100 MW each, two offshore wind feasibility studies, and a regional manufacturing facility for solar panels. The Ministry of Power expects renewables to account for 70% of new generation capacity over the next five years.</p><p>Sector analysts welcomed the figures while urging streamlined permitting to convert commitments into completed projects.</p>',
        'author'      => 'Business Desk',
        'is_featured' => 0, 'is_breaking' => 0, 'views' => 1342,
        'published'   => '2026-06-06 17:30:00',
    ],
];

// ----------------------------------------------------------------
// Download images + insert articles.
// ----------------------------------------------------------------

out('-> Downloading article images + inserting articles');

$insertNews = $pdo->prepare(
    'INSERT INTO news
       (category_id, title, slug, summary, body, image, author, status,
        is_featured, is_breaking, views, published_at)
     VALUES
       (:cat, :title, :slug, :summary, :body, :image, :author, "published",
        :feat, :brk, :views, :pub)'
);

$ok = 0;
foreach ($articles as $i => $a) {
    $slug = createSlug($a['title']);
    if (!isset($catId[$a['category']])) {
        fwrite(STDERR, "  ! Unknown category {$a['category']} — skipping article\n");
        continue;
    }

    $filename = $slug . '.jpg';
    $destPath = $seedDir . '/' . $filename;
    $saved    = downloadImage(picsumUrl($slug), $destPath);
    $image    = $saved ? ($webImageDir . '/' . $saved) : null;

    $insertNews->execute([
        ':cat'     => $catId[$a['category']],
        ':title'   => $a['title'],
        ':slug'    => $slug,
        ':summary' => $a['summary'],
        ':body'    => $a['body'],
        ':image'   => $image,
        ':author'  => $a['author'],
        ':feat'    => $a['is_featured'],
        ':brk'     => $a['is_breaking'],
        ':views'   => $a['views'],
        ':pub'     => $a['published'],
    ]);

    $marker = $image ? '✓' : '×';
    out("  [{$marker}] " . ($i + 1) . '/' . count($articles) . ' ' . $a['title']);
    $ok++;
}
out("   Inserted {$ok} articles");

// ----------------------------------------------------------------
// Today's news — make a few articles appear "today" for the home page.
// ----------------------------------------------------------------

$pdo->exec("UPDATE news
            SET published_at = NOW() - INTERVAL FLOOR(RAND() * 12) HOUR
            WHERE id IN (SELECT id FROM (SELECT id FROM news ORDER BY id DESC LIMIT 4) t)");

// ----------------------------------------------------------------
// Site content blocks (About + Contact Info).
// ----------------------------------------------------------------

out('-> Inserting site content (about, contact_info)');

$aboutHtml = <<<HTML
<h2>Our Mission</h2>
<p>Daily News is committed to delivering accurate, impartial, and timely journalism to readers across the country and around the world. We believe an informed public is the cornerstone of a healthy democracy.</p>

<h2>Our Values</h2>
<ul>
  <li><strong>Accuracy:</strong> We verify every fact before publishing.</li>
  <li><strong>Independence:</strong> Editorial decisions are free from commercial or political influence.</li>
  <li><strong>Transparency:</strong> We are open about our sources and methods.</li>
  <li><strong>Fairness:</strong> We give a voice to all sides of every story.</li>
</ul>

<h2>Our Team</h2>
<p>Our newsroom is staffed by experienced journalists, editors, photographers, and digital producers dedicated to bringing you the stories that matter most — from breaking news to long-form features that go beyond the headlines.</p>
HTML;

$contactHtml = <<<HTML
<p><strong>Daily News Editorial Office</strong><br>
No. 45, Lake Drive, Colombo 03, Sri Lanka</p>

<p><strong>Telephone:</strong> <a href="tel:+94112345678">+94 11 234 5678</a><br>
<strong>Editorial:</strong> <a href="mailto:editor@dailynews.com">editor@dailynews.com</a><br>
<strong>Advertising:</strong> <a href="mailto:ads@dailynews.com">ads@dailynews.com</a><br>
<strong>General Enquiries:</strong> <a href="mailto:info@dailynews.com">info@dailynews.com</a></p>

<p><strong>Office Hours:</strong><br>
Monday – Friday, 8:00 AM – 6:00 PM (IST)</p>
HTML;

$insertContent = $pdo->prepare(
    'INSERT INTO site_content (page_key, title, content) VALUES (:k, :t, :c)'
);
$insertContent->execute([':k' => 'about',        ':t' => 'About Daily News',   ':c' => $aboutHtml]);
$insertContent->execute([':k' => 'contact_info', ':t' => 'Contact Information',':c' => $contactHtml]);

// ----------------------------------------------------------------
// Summary
// ----------------------------------------------------------------

$newsCount  = $pdo->query('SELECT COUNT(*) FROM news')->fetchColumn();
$ctntCount  = $pdo->query('SELECT COUNT(*) FROM site_content')->fetchColumn();

out('');
out('==> Seed complete');
out("   news:         {$newsCount} articles");
out("   site_content: {$ctntCount} blocks");
out('   admin login:  admin / admin123');
