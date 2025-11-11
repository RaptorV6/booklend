# Analýza databázové struktury - BookLend

## 1. Současné schéma

### Tabulka: `users`
```sql
id, username, email, password_hash, role, is_active, last_login_at, registered_at, deleted_at
```

**Analýza sloupců:**
- ✅ **is_active** - POUŽÍVÁ SE v [AuthController.php:51](app/Controllers/AuthController.php#L51) pro deaktivaci účtu
- ✅ **last_login_at** - POUŽÍVÁ SE v `updateLastLogin()` pro sledování aktivity
- ✅ **deleted_at** - POUŽÍVÁ SE jako soft delete (účet se nemazá, jen označí)
- ✅ **role** - POUŽÍVÁ SE pro rozlišení admin/user

**Závěr:** Všechny sloupce jsou využity, nic nemazat.

---

### Tabulka: `books`
```sql
id, isbn, slug, title, author, description, thumbnail,
total_copies, available_copies, views_count,
added_at, updated_at, deleted_at
```

**❌ CHYBÍ:**
- **genre** - kód ho používá v [Book.php:65](app/Models/Book.php#L65) pro filtrování
- **published_year** - kód ho používá v [Book.php:71](app/Models/Book.php#L71) pro filtrování

**Analýza sloupců:**
- ✅ **views_count** - POUŽÍVÁ SE v [BookController.php:81](app/Controllers/BookController.php#L81) pro statistiky
- ✅ **deleted_at** - POUŽÍVÁ SE jako soft delete (kniha se nemazá, jen skryje)
- ✅ **slug** - POUŽÍVÁ SE pro SEO URL `/kniha/{slug}`

**Závěr:** Přidat `genre` a `published_year` → viz [migration_add_genre_and_year.sql](migration_add_genre_and_year.sql)

---

### Tabulka: `rentals`
```sql
id, user_id, book_id, rented_at, due_at, returned_at, is_active (computed)
```

**Analýza sloupců:**
- ✅ **is_active** - COMPUTED column `CASE WHEN returned_at IS NULL THEN 1 ELSE 0 END`
- ✅ Používá se v [Rental.php:30](app/Models/Rental.php#L30) pro dotaz na aktivní výpůjčky
- ✅ Optimalizace: INDEX `idx_user_active (user_id, is_active)` pro rychlé dotazy

**Závěr:** Chytrá implementace, computed column šetří místo a automaticky se aktualizuje.

---

## 2. Důvody pro computed columns

### `rentals.is_active` jako COMPUTED
```sql
is_active TINYINT(1) AS (
    CASE WHEN returned_at IS NULL THEN 1 ELSE 0 END
) STORED
```

**Výhody:**
- ✅ Automatická aktualizace - při `UPDATE returned_at` se samo změní
- ✅ Indexovatelné (STORED) - rychlé dotazy `WHERE is_active = 1`
- ✅ Úspora kódu - nemusíš ručně počítat v PHP

**Nevýhody:**
- ❌ Nelze explicitně nastavit hodnotu (je počítaná)
- ❌ Starší MySQL (< 5.7) to nepodporují

---

## 3. Vazební tabulky - kdy použít?

### Současný stav: `books.genre` jako VARCHAR

**Výhoda:**
- Rychlé dotazy `WHERE genre = 'Fantasy'`
- Jednoduchá implementace

**Nevýhoda:**
- Kniha může mít jen 1 žánr
- Překlepy: "Sci-fi", "SciFi", "Science Fiction" = 3 různé žánry

---

### Budoucí možnost: vazební tabulka (M:N)

```sql
CREATE TABLE genres (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL
);

CREATE TABLE book_genres (
    book_id INT UNSIGNED NOT NULL,
    genre_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (book_id, genre_id),
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE CASCADE
);
```

**Výhody:**
- Kniha může mít více žánrů: Hobit = Fantasy + Adventure + Young Adult
- Konzistence: žánr je jen jednou definovaný (id=1 → Fantasy)
- SEO: `/zanr/fantasy` může mít vlastní popis, meta tagy

**Nevýhody:**
- Složitější dotazy (JOIN přes book_genres)
- Musíš přepsat všechny filtrovací dotazy
- Administrace: admin musí přiřazovat žánry z dropdownu

**Kdy implementovat:**
- Když budeš chtít knihu s více žánry
- Když budeš mít > 50 žánrů (pak má smysl je normalizovat)
- Když budeš chtít žánrové landing pages s unikátním obsahem

**Teď to není potřeba** - jednoduchá implementace s VARCHAR stačí.

---

## 4. Optimalizace indexů

### Současné indexy v `books`:
```sql
UNIQUE KEY uq_isbn (isbn)
UNIQUE KEY uq_slug (slug)
KEY idx_search (title(100), author(100))
KEY idx_available (available_copies, deleted_at)
KEY idx_added (added_at)
```

### Po migraci přibyly:
```sql
KEY idx_genre (genre, deleted_at)
KEY idx_year (published_year, deleted_at)
KEY idx_genre_year (genre, published_year, deleted_at)
```

**Proč `idx_genre_year`?**
- Když filtruješ `WHERE genre = 'Fantasy' AND published_year = 2020 AND deleted_at IS NULL`
- MySQL použije kombinovaný index místo 2 samostatných
- Rychlejší dotaz (1 lookup místo 2)

**Pozor:**
- Příliš mnoho indexů = pomalejší INSERT/UPDATE
- Udržuj jen indexy, které skutečně používáš

---

## 5. Doporučení

### Co zachovat:
- ✅ Computed column `is_active` v rentals
- ✅ Soft delete (`deleted_at`) pro users a books
- ✅ Triggery pro automatickou aktualizaci `available_copies`

### Co přidat:
- ✅ `genre` a `published_year` do books (viz migrace)
- ✅ Indexy pro rychlé filtrování

### Co NEPOTŘEBUJEŠ (zatím):
- ❌ Vazební tabulka genres + book_genres
- ❌ Tabulka reviews (nemáš hodnocení)
- ❌ Tabulka authors (autor je jen VARCHAR, stačí)

---

**Závěr:**
Databáze je dobře navržená s chytrými optimalizacemi (computed columns, soft delete, triggery).
Stačí přidat 2 chybějící sloupce a máš solidní základ pro SEO a filtrování.
