-- Migration: Fix soft delete slug constraint issue
-- Date: 2025-11-13
-- Issue: UNIQUE constraint on slug prevents re-adding books with same title after soft delete

-- Drop UNIQUE constraint on slug
-- (we rely on application-level duplicate checking which respects soft deletes)
ALTER TABLE books DROP INDEX uq_slug;

-- Add regular index for performance (slug is still used for URLs and searches)
CREATE INDEX idx_slug ON books(slug);
