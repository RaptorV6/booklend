-- ═══════════════════════════════════════════════════════════
-- BOOKLEND - DATABASE SCHEMA
-- ═══════════════════════════════════════════════════════════

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS rentals;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ───────────────────────────────────────────────────────────
-- USERS
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
-- BOOKS
-- ───────────────────────────────────────────────────────────
CREATE TABLE books (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(20) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    title VARCHAR(500) NOT NULL,
    author VARCHAR(255) NOT NULL,
    description TEXT NULL,
    thumbnail VARCHAR(500) NULL,
    total_copies INT UNSIGNED DEFAULT 1,
    available_copies INT UNSIGNED DEFAULT 1,
    views_count INT UNSIGNED DEFAULT 0,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    UNIQUE KEY uq_isbn (isbn),
    UNIQUE KEY uq_slug (slug),
    KEY idx_search (title(100), author(100)),
    KEY idx_available (available_copies, deleted_at),
    KEY idx_added (added_at),

    CONSTRAINT chk_stock CHECK (available_copies <= total_copies)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ───────────────────────────────────────────────────────────
-- RENTALS
-- ───────────────────────────────────────────────────────────
CREATE TABLE rentals (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    book_id INT UNSIGNED NOT NULL,
    rented_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    due_at DATETIME NOT NULL,
    returned_at DATETIME NULL,

    is_active TINYINT(1) AS (
        CASE WHEN returned_at IS NULL THEN 1 ELSE 0 END
    ) STORED,

    KEY idx_user_active (user_id, is_active),
    KEY idx_book_active (book_id, is_active),
    KEY idx_due (due_at, user_id),

    CONSTRAINT fk_rentals_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_rentals_book
        FOREIGN KEY (book_id) REFERENCES books(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ───────────────────────────────────────────────────────────
-- TRIGGERS
-- ───────────────────────────────────────────────────────────
DELIMITER $$

CREATE TRIGGER trg_rental_insert
AFTER INSERT ON rentals
FOR EACH ROW
BEGIN
    IF NEW.returned_at IS NULL THEN
        UPDATE books
        SET available_copies = available_copies - 1
        WHERE id = NEW.book_id AND available_copies > 0;

        IF ROW_COUNT() = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No available copies';
        END IF;
    END IF;
END$$

CREATE TRIGGER trg_rental_update
AFTER UPDATE ON rentals
FOR EACH ROW
BEGIN
    IF OLD.returned_at IS NULL AND NEW.returned_at IS NOT NULL THEN
        UPDATE books
        SET available_copies = available_copies + 1
        WHERE id = NEW.book_id;
    END IF;
END$$

DELIMITER ;

-- ───────────────────────────────────────────────────────────
-- SEED DATA
-- ───────────────────────────────────────────────────────────

-- Admin (heslo: admin123)
INSERT INTO users (username, email, password_hash, role) VALUES
('admin', 'admin@booklend.cz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Test user (heslo: user123)
INSERT INTO users (username, email, password_hash, role) VALUES
('testuser', 'user@booklend.cz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Sample books
INSERT INTO books (isbn, slug, title, author, total_copies, available_copies) VALUES
('9788025703673', 'alchymista-paulo-coelho', 'Alchymista', 'Paulo Coelho', 3, 3),
('9780451524935', '1984-george-orwell', '1984', 'George Orwell', 2, 1),
('9780156012188', 'maly-princ-antoine-de-saint-exupery', 'Malý princ', 'Antoine de Saint-Exupéry', 5, 5),
('9780439708180', 'harry-potter-kamen-mudrcuu', 'Harry Potter a Kámen mudrců', 'J.K. Rowling', 4, 4),
('9780547928227', 'hobit-jrr-tolkien', 'Hobit', 'J.R.R. Tolkien', 3, 3);

-- Sample rental
INSERT INTO rentals (user_id, book_id, due_at) VALUES
(2, 2, DATE_ADD(NOW(), INTERVAL 14 DAY));
