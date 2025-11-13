-- Migration: Fix soft delete ISBN constraint issue
-- Date: 2025-11-13
-- Issue: UNIQUE constraint on ISBN prevents re-adding books with same ISBN after soft delete

-- Drop UNIQUE constraint on ISBN
-- (we rely on application-level duplicate checking with existsByIsbn() which respects soft deletes)
ALTER TABLE books DROP INDEX uq_isbn;

-- Add regular index for performance (ISBN is still searchable, just not unique)
CREATE INDEX idx_isbn ON books(isbn);
