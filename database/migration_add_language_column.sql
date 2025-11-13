-- Migration: Add language column to books table
-- Date: 2025-11-13

-- Add language column (ISO 639-1 language codes: cs, en, de, etc.)
ALTER TABLE books
ADD COLUMN language VARCHAR(10) DEFAULT 'cs' AFTER genre;

-- Add index for better filter performance
CREATE INDEX idx_language ON books(language);

-- Set existing books to Czech language
UPDATE books SET language = 'cs' WHERE language IS NULL OR language = '';
