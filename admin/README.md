# Admin Scripts

Utility scripts for managing the BookLend application.

## Available Scripts

### `update-books-metadata.php`
Updates book metadata (covers, descriptions) from APIs (Open Library + Google Books).

**Usage:**
```bash
php update-books-metadata.php
# or visit: http://localhost/booklend/admin/update-books-metadata.php
```

### `clear-cache.php`
Clears the application cache.

**Usage:**
```bash
php clear-cache.php
# or visit: http://localhost/booklend/admin/clear-cache.php
```

### `update-to-czech-isbns.php`
Updates selected books to Czech ISBNs with verified covers.

**Usage:**
```bash
php update-to-czech-isbns.php
# or visit: http://localhost/booklend/admin/update-to-czech-isbns.php
```

## Best Practices

1. Always clear cache after updating ISBNs
2. Run metadata update after adding new books
3. These scripts are for development/admin use only - not for production URLs
