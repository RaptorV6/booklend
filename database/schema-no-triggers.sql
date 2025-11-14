-- ═══════════════════════════════════════════════════════════
-- BOOKLEND - DATABASE SCHEMA (NO TRIGGERS VERSION)
-- Updated: 2025-11-14
-- For hosting providers without TRIGGER privileges
-- ═══════════════════════════════════════════════════════════
--
-- NOTE: This schema does NOT include MySQL triggers.
-- Inventory management (available_copies) is handled by PHP code
-- in app/Models/Rental.php using database transactions.
--
-- ═══════════════════════════════════════════════════════════

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS rentals;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ───────────────────────────────────────────────────────────
-- USERS TABLE
-- Stores user accounts with authentication and role management
-- ───────────────────────────────────────────────────────────
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    is_active TINYINT(1) DEFAULT 1,
    last_login_at TIMESTAMP NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    UNIQUE KEY uq_username (username),
    UNIQUE KEY uq_email (email),
    KEY idx_active (is_active, deleted_at),
    KEY idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ───────────────────────────────────────────────────────────
-- BOOKS TABLE
-- Book catalog with inventory management and metadata
-- ───────────────────────────────────────────────────────────
CREATE TABLE books (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(20) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    title VARCHAR(500) NOT NULL,
    author VARCHAR(255) NOT NULL,
    genre VARCHAR(50) DEFAULT 'Ostatní',
    language VARCHAR(10) DEFAULT 'cs',
    description TEXT NULL,
    published_year INT(4) UNSIGNED NULL,
    thumbnail VARCHAR(500) NULL,
    total_copies INT UNSIGNED DEFAULT 1,
    available_copies INT UNSIGNED DEFAULT 1,
    views_count INT UNSIGNED DEFAULT 0,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    KEY idx_isbn (isbn),
    KEY idx_slug (slug),
    KEY idx_search (title(100), author(100)),
    KEY idx_available (available_copies, deleted_at),
    KEY idx_added (added_at),
    KEY idx_genre (genre, deleted_at),
    KEY idx_year (published_year, deleted_at),
    KEY idx_language (language),
    KEY idx_genre_year (genre, published_year, deleted_at),

    CONSTRAINT chk_stock CHECK (available_copies <= total_copies)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ───────────────────────────────────────────────────────────
-- RENTALS TABLE
-- Tracks book lending transactions and history
-- ───────────────────────────────────────────────────────────
CREATE TABLE rentals (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    book_id INT UNSIGNED NOT NULL,
    rented_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    due_at DATETIME NOT NULL,
    returned_at DATETIME NULL,

    -- Extension tracking (unlimited extensions allowed)
    original_due_at DATETIME NULL COMMENT 'Původní splatnost před prvním prodloužením',
    extension_count INT UNSIGNED DEFAULT 0 COMMENT 'Počet prodloužení (neomezeno)',
    extended_at DATETIME NULL COMMENT 'Datum posledního prodloužení',

    -- Fine management (100,000 CZK per week overdue)
    fine_amount DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Výše penále v Kč',
    fine_paid TINYINT(1) DEFAULT 0 COMMENT 'Zda bylo penále zaplaceno (0=ne, 1=ano)',

    -- Computed column: automatically calculates if rental is active
    is_active TINYINT(1) AS (
        CASE WHEN returned_at IS NULL THEN 1 ELSE 0 END
    ) STORED,

    KEY idx_user_active (user_id, is_active),
    KEY idx_book_active (book_id, is_active),
    KEY idx_due (due_at, user_id),
    KEY idx_fine (fine_amount, fine_paid),
    KEY idx_extension (extension_count),

    CONSTRAINT fk_rentals_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_rentals_book
        FOREIGN KEY (book_id) REFERENCES books(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ───────────────────────────────────────────────────────────
-- NO TRIGGERS NEEDED
-- ───────────────────────────────────────────────────────────
-- Inventory management is handled by PHP code in:
-- - app/Models/Rental.php::create()     (decreases available_copies)
-- - app/Models/Rental.php::returnBook() (increases available_copies)
--
-- Both methods use database transactions with row-level locking
-- (SELECT ... FOR UPDATE) to prevent race conditions.
-- ───────────────────────────────────────────────────────────

-- ───────────────────────────────────────────────────────────
-- SEED DATA
-- Admin user for initial setup
-- ───────────────────────────────────────────────────────────

-- Admin user (username: admin, password: Start321)
INSERT INTO users (username, email, password_hash, role) VALUES
('admin', 'admin@booklend.cz', '$2y$10$zf2Dn6ejbY5UUvimTFZUguSoCA.VbWJUA0meCNlhiWfukbStGN/Gm', 'admin');
