-- ============================================================
-- News Site Database Schema
-- Database: news_db | Charset: utf8mb4 | Collation: utf8mb4_unicode_ci
--
-- Run this file once to set up the database:
--   mysql -u root -p < database/schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS news_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE news_db;

-- ============================================================
-- Table: admins
-- ============================================================
CREATE TABLE IF NOT EXISTS admins (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    username      VARCHAR(60)     NOT NULL,
    email         VARCHAR(180)    NOT NULL,
    password_hash VARCHAR(255)    NOT NULL,
    created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admins_username (username),
    UNIQUE KEY uq_admins_email    (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: categories
-- status: 1 = active, 0 = inactive
-- ============================================================
CREATE TABLE IF NOT EXISTS categories (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name       VARCHAR(100) NOT NULL,
    slug       VARCHAR(120) NOT NULL,
    status     TINYINT(1)   NOT NULL DEFAULT 1,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categories_slug (slug),
    KEY idx_categories_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: news
-- ============================================================
CREATE TABLE IF NOT EXISTS news (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id  INT UNSIGNED          DEFAULT NULL,
    title        VARCHAR(300) NOT NULL,
    slug         VARCHAR(320) NOT NULL,
    summary      TEXT,
    body         LONGTEXT     NOT NULL,
    image        VARCHAR(255)          DEFAULT NULL,
    author       VARCHAR(120) NOT NULL DEFAULT 'Admin',
    status       ENUM('draft','published') NOT NULL DEFAULT 'draft',
    is_featured  TINYINT(1)   NOT NULL DEFAULT 0,
    is_breaking  TINYINT(1)   NOT NULL DEFAULT 0,
    views        INT UNSIGNED NOT NULL DEFAULT 0,
    published_at DATETIME              DEFAULT NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_news_slug        (slug),
    KEY idx_news_category_id       (category_id),
    KEY idx_news_status            (status),
    KEY idx_news_is_featured       (is_featured),
    KEY idx_news_is_breaking       (is_breaking),
    KEY idx_news_published_at      (published_at),
    CONSTRAINT fk_news_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: papers (Newspaper Editions)
-- ============================================================
CREATE TABLE IF NOT EXISTS papers (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title        VARCHAR(255) NOT NULL,
    edition_date DATE         NOT NULL,
    pdf_path     VARCHAR(255) NOT NULL,
    thumbnail    VARCHAR(255)          DEFAULT NULL,
    status       TINYINT(1)   NOT NULL DEFAULT 1,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_papers_edition_date (edition_date),
    KEY idx_papers_status       (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: contacts (contact form submissions)
-- ============================================================
CREATE TABLE IF NOT EXISTS contacts (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name       VARCHAR(120) NOT NULL,
    email      VARCHAR(180) NOT NULL,
    subject    VARCHAR(255)          DEFAULT NULL,
    message    TEXT         NOT NULL,
    is_read    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_contacts_is_read    (is_read),
    KEY idx_contacts_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: site_content (editable CMS content blocks)
-- ============================================================
CREATE TABLE IF NOT EXISTS site_content (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    page_key   VARCHAR(100) NOT NULL,
    title      VARCHAR(255)          DEFAULT NULL,
    content    LONGTEXT     NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_site_content_page_key (page_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Seed: Default categories
-- ============================================================
INSERT IGNORE INTO categories (name, slug) VALUES
    ('Politics',      'politics'),
    ('Sports',        'sports'),
    ('Business',      'business'),
    ('Technology',    'technology'),
    ('Entertainment', 'entertainment'),
    ('World',         'world');

-- ============================================================
-- Seed: Default admin account
-- Username : admin
-- Password : admin123
-- ============================================================
INSERT IGNORE INTO admins (username, email, password_hash) VALUES
    (
        'admin',
        'admin@dailynews.com',
        '$2y$12$jXXOQisJ92dYkLzwXF9bWOavuGZe265vymz0Ei0R7hsHzL8csUJim'
    );

-- ============================================================
-- Seed: site_content blocks
-- ============================================================
INSERT IGNORE INTO site_content (page_key, title, content) VALUES
    ('about',
     'About Daily News',
     '<p>Welcome to <strong>Daily News</strong> — your most trusted source for accurate, timely, and in-depth news coverage. Founded in 2020, we are committed to delivering fact-based journalism that informs and empowers our readers every day.</p><p>Our team of experienced journalists covers politics, business, sports, technology, entertainment, and world affairs. We hold ourselves to the highest editorial standards, ensuring every story is verified before publication.</p><p>Daily News reaches over 500,000 readers across the country and continues to grow as a leading digital news platform. Thank you for being part of our community.</p>'),
    ('contact_info',
     'Contact Information',
     '<p><strong>Daily News Media House</strong><br>123 Press Avenue, Colombo 03<br>Sri Lanka</p><p>📧 news@dailynews.com<br>📞 +94 11 234 5678<br>🕐 Mon–Sat: 8:00 AM – 6:00 PM</p>');

-- ============================================================
-- Seed: 10 sample news articles
-- ============================================================
INSERT IGNORE INTO news (category_id, title, slug, summary, body, status, is_featured, is_breaking, published_at) VALUES
(1, 'Government Announces Major Infrastructure Investment Plan',
 'government-announces-major-infrastructure-investment-plan',
 'The government has unveiled an ambitious multi-billion infrastructure investment plan aimed at modernising transport, energy, and digital connectivity across the country.',
 '<p>The government today announced a comprehensive infrastructure investment plan worth over $5 billion, targeting critical sectors including road networks, renewable energy, and high-speed broadband connectivity.</p><p>Prime Minister revealed the initiative at a press conference in the capital, stating that the plan would create over 200,000 jobs over the next five years. The investment is expected to be funded through a combination of government bonds, foreign direct investment, and public-private partnerships.</p><p>"This is the largest infrastructure programme in our nation\'s history," the Prime Minister declared. "It will lay the foundation for a modern, competitive economy that benefits every citizen."</p><p>Opposition leaders have called for more transparency on financing details, while economists have largely welcomed the announcement as a necessary step toward long-term economic development.</p>',
 'published', 1, 1, '2026-06-06 08:00:00'),

(2, 'National Cricket Team Wins Championship Against Australia',
 'national-cricket-team-wins-championship-against-australia',
 'In a thrilling final match, the national cricket team defeated Australia by 34 runs to claim the international championship title.',
 '<p>The national cricket team produced a stunning performance to defeat Australia by 34 runs in the final of the international championship, sending fans across the country into celebrations.</p><p>Batting first, the home side posted a commanding total of 312/6, powered by an outstanding century from opener Chamara Silva who scored 127 off 115 balls. The middle order also contributed with useful partnerships.</p><p>Australia\'s chase never quite got going as the pace attack dismissed openers early. Despite a fighting half-century from their captain, the visitors were bowled out for 278 in the 49th over.</p><p>The winning team captain dedicated the victory to the fans: "This is for the people who have supported us through every high and low. We promised we\'d bring the trophy home, and we delivered."</p>',
 'published', 1, 1, '2026-06-05 18:30:00'),

(4, 'Tech Giant Opens Regional Headquarters in Colombo',
 'tech-giant-opens-regional-headquarters-in-colombo',
 'A leading global technology corporation has officially opened its South Asian regional headquarters in Colombo, bringing 3,000 high-tech jobs to the country.',
 '<p>A leading global technology corporation officially inaugurated its South Asian regional headquarters in Colombo on Friday, marking one of the largest foreign direct investments in the country\'s technology sector.</p><p>The state-of-the-art facility spans 150,000 square feet across a newly constructed tower in the Colombo Port City. Initially employing 3,000 professionals, the office is expected to grow to 8,000 employees within three years.</p><p>The CEO expressed confidence in the country\'s talent pool: "Sri Lanka has a world-class engineering workforce. This headquarters will serve as our innovation hub for the entire South Asian region."</p><p>The government facilitated the investment through a dedicated tech enterprise zone with favourable tax conditions for the first decade of operations.</p>',
 'published', 1, 0, '2026-06-04 10:00:00'),

(3, 'Stock Market Reaches All-Time High Amid Economic Growth',
 'stock-market-reaches-all-time-high-amid-economic-growth',
 'The Colombo Stock Exchange hit a record high this week as investor confidence surged on the back of strong quarterly GDP growth figures.',
 '<p>The Colombo Stock Exchange (CSE) reached an all-time high on Thursday, with the All Share Price Index (ASPI) crossing the 15,000-point mark for the first time in its history.</p><p>The milestone follows the release of first-quarter GDP data showing the economy grew at 6.8%, significantly above analyst expectations of 4.5%. Key sectors driving growth included tourism, exports, and the IT industry.</p><p>Market analysts attributed the surge to renewed investor confidence and a stable exchange rate policy. Foreign investors net-bought Rs. 2.3 billion worth of equities during the week.</p><p>"The fundamentals are finally lining up," said a senior economist at a leading investment bank. "Reduced inflation, a stabilised currency, and strong growth data are creating ideal conditions for market appreciation."</p>',
 'published', 0, 0, '2026-06-03 12:00:00'),

(5, 'International Film Festival Returns to Capital City',
 'international-film-festival-returns-to-capital-city',
 'The 15th International Film Festival will be held in Colombo next month, featuring over 200 films from 60 countries across 12 screening venues.',
 '<p>The International Film Festival announced its return to Colombo next month, promising the most ambitious programme in the event\'s 15-year history. Over 200 films from 60 countries will be screened across 12 venues throughout the city.</p><p>The festival lineup includes world premieres, award-winning features from Cannes and Venice, and a special retrospective honouring South Asian cinema.</p><p>Festival director noted that ticket sales have already surpassed last year\'s totals. "There\'s incredible appetite for diverse, quality cinema," she said.</p><p>Opening night will feature a red-carpet premiere attended by international directors and actors. The 10-day event runs from July 1–10 and offers free screenings on weekends at public parks.</p>',
 'published', 0, 0, '2026-06-02 14:00:00'),

(6, 'Climate Summit Reaches Historic Agreement on Emissions',
 'climate-summit-reaches-historic-agreement-on-emissions',
 'World leaders signed a landmark climate agreement committing 140 nations to achieving net-zero carbon emissions by 2045, five years ahead of previous targets.',
 '<p>In a landmark moment for global climate action, 140 nations signed a new international agreement committing to achieve net-zero carbon emissions by 2045 — five years earlier than previous accords.</p><p>The agreement, reached after intense two-week negotiations in Geneva, includes binding commitments for major economies to phase out coal power by 2035 and significantly boost financing for developing nations.</p><p>Climate scientists have cautiously welcomed the deal. "This is meaningful progress," said the lead author of the latest IPCC report. "But implementation will be critical to its success."</p><p>Environmental groups described the summit as a turning point, while calling for even faster action on emissions reductions.</p>',
 'published', 0, 0, '2026-06-01 16:00:00'),

(1, 'New Hospital Complex to Serve Northern Province',
 'new-hospital-complex-to-serve-northern-province',
 'A modern 500-bed hospital complex has been inaugurated in Jaffna, bringing advanced medical care including cancer treatment and cardiac surgery to the Northern Province.',
 '<p>A state-of-the-art 500-bed hospital complex was officially inaugurated in Jaffna on Saturday, dramatically expanding healthcare access for residents of the Northern Province.</p><p>The facility includes specialised units for oncology, cardiac surgery, neurology, and neonatal care — services that previously required patients to travel to Colombo.</p><p>The Health Minister called it a milestone in equitable healthcare development: "No Sri Lankan should have to travel 400 kilometres to receive the care they need."</p><p>The hospital will employ 850 medical and support staff, with recruitment drives targeting specialists from both local institutions and the diaspora.</p>',
 'published', 0, 0, '2026-05-31 09:00:00'),

(2, 'Local Football Club Qualifies for Asian Cup',
 'local-football-club-qualifies-for-asian-cup',
 'Colombo City FC has made history by qualifying for the AFC Asian Cup group stage, the first Sri Lankan club to reach this milestone.',
 '<p>Colombo City FC made history on Wednesday night by becoming the first Sri Lankan club to qualify for the AFC Asian Cup group stage, following a dramatic 2-1 victory in the qualifying playoff.</p><p>The decisive match was settled in the 88th minute when striker Nuwan Perera converted a penalty to send the home crowd at Sugathadasa Stadium into raptures.</p><p>Head coach expressed pride: "These players have worked incredibly hard for this moment. To be the first Sri Lankan club at this level of Asian football is something we will all cherish forever."</p><p>Sponsorship interest has reportedly surged following the historic qualification, with several international brands in talks with the club.</p>',
 'published', 0, 0, '2026-05-30 20:00:00'),

(3, 'Central Bank Announces Interest Rate Decision',
 'central-bank-announces-interest-rate-decision',
 'The Central Bank has held interest rates steady at 8.5%, citing improving inflation trends while maintaining a cautious outlook on global economic uncertainties.',
 '<p>The Central Bank\'s Monetary Policy Board voted unanimously to hold the benchmark interest rate at 8.5%, citing encouraging progress on inflation while noting continued uncertainty in global financial markets.</p><p>Headline inflation fell to 4.2% in May, down from a peak of 69% during the economic crisis, representing a remarkable recovery.</p><p>"The disinflation trend is firmly established," the Governor said. "However, we remain watchful of external risks including oil price volatility."</p><p>Commercial banks responded by adjusting lending rates marginally downward, which should gradually ease borrowing costs for businesses and homeowners.</p>',
 'published', 0, 0, '2026-05-28 11:00:00'),

(5, 'Streaming Platform Launches Regional Original Series',
 'streaming-platform-launches-regional-original-series',
 'A major international streaming platform has launched its first Sri Lankan original series, a political thriller shot entirely on location across the island.',
 '<p>A major international streaming platform officially premiered its first Sri Lankan original series today — a political thriller titled "The Island Accord," shot over eight months entirely on location across Sri Lanka.</p><p>The 10-episode series follows a fictional diplomat navigating political intrigue set against Sri Lanka\'s dramatic landscapes, employing over 300 local crew members.</p><p>Early viewer responses have been overwhelmingly positive, with the series trending in 15 countries within hours of release.</p><p>The streaming platform\'s content chief said negotiations are already underway for a second season: "The response exceeded our expectations. We are committed to telling more stories from this remarkable island."</p>',
 'published', 0, 0, '2026-05-25 09:00:00');
