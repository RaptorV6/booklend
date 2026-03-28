# BookLend – Online Půjčovna Knih

**Skupina č. 09 | Školní rok 2025/2026 | KIT – ČZU Praha**

---

## Obsah

1. [Úvod](#úvod)
2. [Cíle](#cíle)
3. [Datový model](#datový-model)
4. [Use Case](#use-case)
5. [Použité technologie](#použité-technologie)
   - [Frameworky](#frameworky)
   - [Bezpečnost](#bezpečnost)
   - [Zdroje](#zdroje-použitých-technologií)
6. [Příklady implementace](#příklady-implementace)
7. [Testování](#testování)
8. [Monitoring](#monitoring)
9. [Tým, kompetence, strávená doba](#tým-kompetence-strávená-doba)
10. [Závěr](#závěr)
11. [Zdroje](#zdroje)

---

## Úvod

BookLend je webová aplikace pro online správu půjčovny knih. Projekt byl vytvořen jako semestrální práce v rámci předmětu na Katedře informačních technologií ČZU Praha, skupina **č. 09**, školní rok **2025/2026**.

Aplikace umožňuje uživatelům procházet katalog knih, půjčovat si tituly na dobu 30 dní, prodlužovat výpůjčky a sledovat historii půjčení. Administrátor spravuje celý katalog včetně automatického doplňování metadat z Google Books API.

**Živá ukázka:** https://ete89e.pef.czu.cz/zs2526/09/booklend/public/

**Výchozí administrátorský účet:**
- Email: `admin@booklend.cz`
- Heslo: `Start321`

---

## Cíle

### Hlavní cíle
- Vytvořit funkční webovou půjčovnu knih dostupnou přes prohlížeč bez instalace
- Implementovat správu uživatelských účtů s registrací a přihlašováním
- Zajistit správu inventáře knih (dostupné/vypůjčené kopie) bez rizika race conditions
- Umožnit administrátorovi snadnou správu katalogu s automatickým doplňováním dat

### Vedlejší cíle
- Kompatibilita se sdílenými hostingy (bez potřeby MySQL TRIGGER privilege)
- Responzivní design fungující na mobilních zařízeních
- SEO optimalizace (meta tagy, sitemap, strukturovaná data)

### Co bylo splněno

| Cíl | Stav |
|-----|------|
| Katalog knih s filtrováním a vyhledáváním | ✅ |
| Registrace a přihlášení uživatelů | ✅ |
| Půjčování a vracení knih | ✅ |
| Prodlužování výpůjček (+15 dní) | ✅ |
| Automatická kalkulace penále | ✅ |
| Administrátorský panel | ✅ |
| Google Books API integrace | ✅ |
| Responzivní design (mobile-first) | ✅ |
| SEO (sitemap, meta, Open Graph) | ✅ |
| Ceník / platební brána | ❌ (mimo rozsah) |

---

## Datový model

Aplikace používá relační databázi MySQL se třemi tabulkami.

### Schéma

```
┌─────────────────────────────────────┐
│               users                 │
├─────────────────────────────────────┤
│ id              INT UNSIGNED PK AI  │
│ username        VARCHAR(100) UNIQUE │
│ email           VARCHAR(255) UNIQUE │
│ password_hash   VARCHAR(255)        │
│ role            ENUM('user','admin')│
│ is_active       TINYINT(1)          │
│ last_login_at   TIMESTAMP NULL      │
│ registered_at   TIMESTAMP           │
│ deleted_at      TIMESTAMP NULL      │
└─────────────────────────────────────┘
           │
           │ 1:N
           ▼
┌─────────────────────────────────────┐
│              rentals                │
├─────────────────────────────────────┤
│ id              INT UNSIGNED PK AI  │◄──┐
│ user_id         INT UNSIGNED FK     │   │
│ book_id         INT UNSIGNED FK     │   │
│ rented_at       TIMESTAMP           │   │
│ due_at          DATETIME            │   │
│ returned_at     DATETIME NULL       │   │
│ original_due_at DATETIME NULL       │   │
│ extension_count INT UNSIGNED        │   │
│ extended_at     DATETIME NULL       │   │
│ fine_amount     DECIMAL(10,2)       │   │
│ fine_paid       TINYINT(1)          │   │
│ is_active       TINYINT(1) STORED   │   │
└─────────────────────────────────────┘   │
                                          │ 1:N
┌─────────────────────────────────────┐   │
│               books                 │   │
├─────────────────────────────────────┤   │
│ id              INT UNSIGNED PK AI  │───┘
│ isbn            VARCHAR(20)         │
│ slug            VARCHAR(255)        │
│ title           VARCHAR(500)        │
│ author          VARCHAR(255)        │
│ genre           VARCHAR(50)         │
│ language        VARCHAR(10)         │
│ description     TEXT NULL           │
│ published_year  INT(4) UNSIGNED     │
│ thumbnail       VARCHAR(500) NULL   │
│ total_copies    INT UNSIGNED        │
│ available_copies INT UNSIGNED       │
│ views_count     INT UNSIGNED        │
│ added_at        TIMESTAMP           │
│ updated_at      TIMESTAMP           │
│ deleted_at      TIMESTAMP NULL      │
└─────────────────────────────────────┘
```

### Klíčové vlastnosti

- **Soft delete** — záznamy se nemažou fyzicky, pouze se nastaví `deleted_at`
- **Computed column** — `rentals.is_active` se vypočítává automaticky z `returned_at`
- **Constraint** — `CHECK (available_copies <= total_copies)` zabraňuje nekonzistenci stavu
- **Cizí klíče** — `rentals.user_id → users.id` (CASCADE), `rentals.book_id → books.id` (RESTRICT)

### Správa inventáře: PHP transakce místo triggerů

Aplikace záměrně **nepoužívá MySQL triggery** pro správu `available_copies`. Důvod: sdílené hostingy neposkytují uživateli oprávnění `TRIGGER`. Místo toho jsou použity PHP PDO transakce:

```sql
BEGIN
  SELECT * FROM books WHERE id = ? FOR UPDATE   -- zamkne řádek
  -- PHP ověří available_copies > 0
  INSERT INTO rentals (...)
  UPDATE books SET available_copies = available_copies - 1
COMMIT
```

`SELECT ... FOR UPDATE` zabraňuje race conditions při souběžném půjčení stejné knihy.

---

## Use Case

### Aktéři
- **Návštěvník** – nepřihlášený uživatel
- **Uživatel** – přihlášený čtenář
- **Administrátor** – správce systému

### Diagram

```
Návštěvník
├── Prohlížet katalog knih
├── Filtrovat (žánr, rok, jazyk)
├── Vyhledávat knihy
├── Zobrazit detail knihy
└── Přihlásit / Registrovat se

Uživatel (vše výše +)
├── Půjčit knihu (30 dní)
├── Vrátit knihu
├── Prodloužit výpůjčku (+15 dní)
├── Zobrazit své výpůjčky
└── Zobrazit profil a historii

Administrátor (vše výše +)
├── Přidat knihu (ručně nebo z Google Books API)
├── Editovat knihu
├── Smazat knihu
├── Změnit počet kopií
└── Zobrazit administrátorský přehled
```

### Hlavní scénáře

**UC-01: Půjčení knihy**
1. Přihlášený uživatel otevře detail knihy
2. Klikne na „Půjčit"
3. Systém ověří dostupnost (transakce s `FOR UPDATE`)
4. Vytvoří záznam v `rentals`, sníží `available_copies`
5. Uživateli se zobrazí potvrzení s datem splatnosti

**UC-02: Prodloužení výpůjčky**
1. Uživatel otevře „Moje výpůjčky"
2. Klikne na „Prodloužit" u aktivní výpůjčky
3. Systém přidá 15 dní k `due_at`, inkrementuje `extension_count`

**UC-03: Přidání knihy adminem**
1. Admin zadá název nebo ISBN do vyhledávacího pole v panelu
2. Systém dotáže Google Books API
3. Admin vybere správný výsledek
4. Data (název, autor, žánr, obálka) se automaticky vyplní
5. Admin potvrdí uložení

---

## Použité technologie

| Technologie | Verze | Účel |
|-------------|-------|------|
| PHP | 8.2 | Backend, business logika |
| MySQL | 8.0 | Relační databáze |
| Apache | 2.4 | Webový server |
| HTML5 | – | Struktura stránek |
| CSS3 | – | Stylování, responzivita |
| JavaScript (Vanilla) | ES6+ | Interaktivita, AJAX |
| Google Books API | v1 | Metadata knih, obálky |

### Architektura aplikace

Aplikace používá vlastní **MVC architekturu bez externího frameworku**:

```
booklend/
├── app/
│   ├── Controllers/    # BookController, AuthController, AdminController, UserController
│   ├── Models/         # Book, User, Rental
│   ├── Views/          # PHP šablony (books/, user/, auth/, admin/, errors/)
│   ├── Database.php    # PDO wrapper
│   ├── Auth.php        # Autentizace a autorizace
│   ├── Cache.php       # File-based cache
│   └── helpers.php     # Pomocné funkce
├── public/
│   ├── assets/css/     # style.css, responsive.css, admin.css, ...
│   ├── assets/js/      # app.js, ajax.js, toast.js, ...
│   └── index.php       # Front controller (jediný vstupní bod)
├── database/
│   ├── schema.sql               # Schéma s triggery (localhost)
│   └── schema-no-triggers.sql  # Schéma bez triggerů (hosting)
├── config.php          # Konfigurace DB, cache, BASE_URL
└── routes.php          # URL routing
```

**Tok požadavku:**
```
HTTP požadavek → public/index.php → Router → Middleware → Controller → Model → View → HTTP odpověď
```

### Editace obsahu webu (PHP administrace)

Obsah webu spravuje administrátor přes vestavěný admin panel na `/admin`. Jde o plnohodnotný CRUD systém implementovaný v PHP.

| Akce | Endpoint | Popis |
|------|----------|-------|
| Zobrazit katalog | `GET /admin` | Přehled všech knih, vyhledávání, statistiky |
| Přidat knihu | `POST /api/admin/create` | Ruční zadání nebo import z Google Books API |
| Upravit knihu | `POST /api/admin/update` | Editace všech polí (název, autor, žánr, rok, jazyk, popis, obálka) |
| Změnit počet kopií | `POST /api/admin/update-stock` | Úprava `total_copies` a `available_copies` |
| Smazat knihu | `POST /api/admin/delete` | Soft delete – nastaví `deleted_at`, kniha zmizí z katalogu |

**Google Books API** — admin zadá název nebo ISBN a systém automaticky doplní metadata. Výsledky jsou cachovány 30 dní v `public/cache/`.

### Responzivita

Aplikace používá **mobile-first přístup** — styly jsou nejprve navrženy pro malé obrazovky a postupně rozšiřovány pro větší.

| Breakpoint | Zařízení | Změny layoutu |
|-----------|----------|---------------|
| `< 480px` | Malý mobil | 1 sloupec karet, kompaktní navbar |
| `480px–768px` | Mobil / tablet | 2 sloupce karet, hamburger menu |
| `768px–1024px` | Tablet | 3 sloupce karet |
| `> 1024px` | Desktop | 4–6 sloupců karet, plný navbar |

### SEO, sitemap a robots.txt

**sitemap.xml** — dynamicky generovaný soubor `public/sitemap.php`, přesměrovaný přes `.htaccess`:
```
RewriteRule ^sitemap\.xml$ sitemap.php [L]
```

**robots.txt** — generovaný přes `public/robots.php`, blokuje `/admin` a `/api/`:
```
User-agent: *
Allow: /
Disallow: /admin
Disallow: /api/
Sitemap: https://ete89e.pef.czu.cz/zs2526/09/booklend/public/sitemap.xml
```

**Meta tagy a strukturovaná data** — každá stránka obsahuje Open Graph a Schema.org JSON-LD:
```html
<meta property="og:type" content="book">
<meta property="og:title" content="Eragon – Christopher Paolini">
<meta property="og:image" content="https://books.google.com/...">

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Book",
  "name": "Eragon",
  "author": { "@type": "Person", "name": "Christopher Paolini" },
  "isbn": "9788025303603"
}
</script>
```

---

### Frameworky

Aplikace záměrně **nepoužívá žádný PHP framework** (Laravel, Symfony apod.). Důvody:
- Výuková hodnota – pochopení principů MVC od základu
- Minimální závislosti – žádný `vendor/` adresář, snadné nasazení
- Kompatibilita – funguje na jakémkoli sdíleném hostingu s PHP 8+

**Vlastní implementace klíčových komponent:**

- **Router** (`app/Router.php`) – mapování URL na controllery pomocí regex patternů, podpora parametrů (`/kniha/{slug}`)
- **Database** (`app/Database.php`) – PDO wrapper s podporou transakcí, prepared statements
- **Auth** (`app/Auth.php`) – session-based autentizace, kontrola rolí
- **Cache** (`app/Cache.php`) – file-based cache pro výsledky Google Books API (TTL 30 dní)

**JavaScript:**
- Žádný framework (React, Vue apod.)
- Vanilla JS ES6+ pro AJAX volání, toast notifikace, modální okna

---

### Bezpečnost

| Hrozba | Opatření |
|--------|----------|
| SQL Injection | PDO prepared statements pro všechny DB dotazy |
| XSS (Cross-Site Scripting) | `htmlspecialchars()` (`e()` helper) na všech výstupech |
| CSRF | Session-based validace formulářů |
| Slabá hesla | bcrypt hashing (`password_hash()`, cost 10) |
| Neoprávněný přístup | Middleware kontrola role před každou chráněnou akcí |
| Race conditions | `SELECT ... FOR UPDATE` v transakcích při půjčení |
| Directory traversal | `Options -Indexes` v `.htaccess` |

```php
// Prepared statement – ochrana proti SQL injection
$user = $this->db->fetch(
    "SELECT * FROM users WHERE email = ? AND is_active = 1",
    [$email]
);

// XSS ochrana – výstupní sanitizace
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Autorizace – middleware v routeru
if ($route['middleware'] === 'admin') {
    if (!Auth::isAdmin()) {
        header('Location: ' . BASE_URL . '/');
        exit;
    }
}
```

---

### Zdroje použitých technologií

- [PHP 8.2 – oficiální dokumentace](https://www.php.net/docs.php)
- [MySQL 8.0 – referenční příručka](https://dev.mysql.com/doc/refman/8.0/en/)
- [Apache HTTP Server – dokumentace](https://httpd.apache.org/docs/2.4/)
- [Google Books API – dokumentace](https://developers.google.com/books/docs/v1/using)
- [MDN Web Docs – JavaScript ES6+](https://developer.mozilla.org/en-US/docs/Web/JavaScript)

---

## Příklady implementace

### 1. Půjčení knihy s transakcí (ochrana před race conditions)

```php
// app/Models/Rental.php
public function rentBook(int $userId, int $bookId): array {
    try {
        $this->db->beginTransaction();

        // Zamknout řádek – zabrání souběžnému půjčení téže knihy
        $book = $this->db->fetch(
            "SELECT * FROM books WHERE id = ? AND deleted_at IS NULL FOR UPDATE",
            [$bookId]
        );

        if (!$book) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Kniha nenalezena'];
        }

        if ($book['available_copies'] < 1) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Kniha není dostupná'];
        }

        // Zkontrolovat, zda uživatel nemá tuto knihu již půjčenou
        $existing = $this->db->fetch(
            "SELECT id FROM rentals
             WHERE user_id = ? AND book_id = ? AND returned_at IS NULL",
            [$userId, $bookId]
        );

        if ($existing) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Tuto knihu již máte půjčenou'];
        }

        $dueAt = date('Y-m-d H:i:s', strtotime('+30 days'));

        // Vložit výpůjčku
        $this->db->execute(
            "INSERT INTO rentals (user_id, book_id, due_at) VALUES (?, ?, ?)",
            [$userId, $bookId, $dueAt]
        );

        // Snížit počet dostupných kopií
        $this->db->execute(
            "UPDATE books SET available_copies = available_copies - 1 WHERE id = ?",
            [$bookId]
        );

        $this->db->commit();
        return ['success' => true, 'due_at' => $dueAt];

    } catch (\Exception $e) {
        $this->db->rollback();
        return ['success' => false, 'message' => 'Chyba serveru'];
    }
}
```

### 2. Prioritizace českých knih z Google Books API

```php
// app/Controllers/AdminController.php
private function isCzechBook(array $book): bool {
    $title = $book['title'] ?? '';

    // Metoda 1: České znaky v názvu (ě, š, č, ř, ž, ý, á, í, é, ů, ú, ň, ť, ď)
    if (preg_match('/[ěščřžýáíéůúňťď]/iu', $title)) {
        return true;
    }

    // Metoda 2: Detekce českých překladů Harry Pottera
    if (preg_match('/harry\s+potter\s+a\s+[a-záčďéěíňóřšťúůýž]/iu', $title)) {
        return true;
    }

    // Metoda 3: Známé české fráze
    $czechPhrases = ['kámen mudrců', 'tajemná komnata', 'relikvie smrti'];
    foreach ($czechPhrases as $phrase) {
        if (mb_stripos($title, $phrase) !== false) return true;
    }

    return false;
}

private function sortBooksByLanguage(array $books): array {
    // České knihy na začátek výsledků
    usort($books, fn($a, $b) => $this->isCzechBook($b) - $this->isCzechBook($a));
    return $books;
}
```

### 3. Vlastní router s URL parametry

```php
// app/Router.php
public function dispatch(string $requestMethod, string $uri): void {
    $uri = strtok($uri, '?'); // Odstraní query string

    // Odstraní base path pro subdirectory instalaci
    $basePath = parse_url(BASE_URL, PHP_URL_PATH);
    if ($basePath && strpos($uri, $basePath) === 0) {
        $uri = substr($uri, strlen($basePath));
    }
    $uri = rtrim($uri, '/') ?: '/';

    foreach ($this->routes as $route) {
        if ($route['method'] !== $requestMethod) continue;

        // Převod {slug} → regex capture group: /kniha/{slug} → #^/kniha/([^/]+)$#
        $pattern = preg_replace('/\{([a-z]+)\}/', '([^/]+)', $route['path']);
        $pattern = "#^{$pattern}$#";

        if (preg_match($pattern, $uri, $matches)) {
            array_shift($matches); // Odstranit celý match, ponechat capture groups
            // Spuštění middleware + controlleru...
        }
    }

    http_response_code(404);
    require __DIR__ . '/Views/errors/404.php';
}
```

---

## Testování

Testování bylo prováděno manuálně (uživatelské testování) v prohlížeči Chrome a Firefox.

| # | Scénář | Postup | Očekávaný výsledek | Výsledek |
|---|--------|--------|--------------------|----------|
| T-01 | Registrace nového uživatele | Vyplnit formulář s platným emailem a heslem (min. 6 znaků) | Účet vytvořen, přesměrování na katalog | ✅ OK |
| T-02 | Přihlášení s chybným heslem | Zadat správný email, špatné heslo | Zobrazení chybové hlášky, nezalogování | ✅ OK |
| T-03 | Půjčení dostupné knihy | Přihlásit se, otevřít detail knihy, kliknout Půjčit | Výpůjčka vytvořena, available_copies -1 | ✅ OK |
| T-04 | Půjčení nedostupné knihy | Pokusit se půjčit knihu s available_copies = 0 | Tlačítko deaktivováno, hlášení „Nedostupná" | ✅ OK |
| T-05 | Půjčení téže knihy dvakrát | Půjčit knihu, kterou uživatel již má | Chybová hláška „Tuto knihu již máte půjčenou" | ✅ OK |
| T-06 | Prodloužení výpůjčky | V sekci Výpůjčky kliknout Prodloužit | due_at +15 dní, extension_count +1 | ✅ OK |
| T-07 | Vrácení knihy | Kliknout Vrátit u aktivní výpůjčky | returned_at nastaven, available_copies +1 | ✅ OK |
| T-08 | Přidání knihy adminem | Vyhledat knihu přes Google Books API, uložit | Kniha přidána do katalogu s obálkou | ✅ OK |
| T-09 | Filtrování katalogu | Vybrat žánr „Fantasy", rok „2000" | Zobrazeny pouze odpovídající knihy | ✅ OK |
| T-10 | Přístup na /admin bez role | Přihlásit jako běžný uživatel, navštívit /admin | Přesměrování na katalog | ✅ OK |
| T-11 | Responzivita | Otevřít na mobilním zařízení (375px) | Hamburger menu, karty v jednom sloupci | ✅ OK |
| T-12 | SQL injection pokus | Zadat `' OR '1'='1` do vyhledávání | Dotaz nevrátí neautorizovaná data | ✅ OK |

---

## Monitoring

Aplikace neobsahuje automatický monitoring (není v rozsahu školního projektu). Dohled probíhá manuálně:

### Dostupné nástroje

- **phpMyAdmin / Adminer** – přímý přístup k databázi, kontrola tabulek a počtů záznamů
- **Apache error log** – `/var/log/apache2/error.log` na školním serveru
- **PHP error log** – chyby jsou logovány přes `error_log()`, výstup na obrazovku je vypnut (`display_errors = 0`)

### Klíčové dotazy pro ruční monitoring

| Metrika | SQL dotaz |
|---------|-----------|
| Aktivní výpůjčky | `SELECT COUNT(*) FROM rentals WHERE returned_at IS NULL` |
| Knihy po splatnosti | `SELECT * FROM rentals WHERE due_at < NOW() AND returned_at IS NULL` |
| Celkové penále | `SELECT SUM(fine_amount) FROM rentals WHERE fine_paid = 0` |
| Registrovaní uživatelé | `SELECT COUNT(*) FROM users WHERE is_active = 1` |

### Cache

Výsledky Google Books API jsou ukládány do souborů v `public/cache/` s TTL 30 dní, aby se minimalizoval počet externích API volání.

---

## Tým, kompetence, strávená doba

| Člen | Role | Kompetence | Strávená doba |
|------|------|------------|---------------|
| Denis Vaň | Vedoucí vývojář (full-stack) | PHP backend, MVC architektura, databázový návrh, JS frontend, CSS design | ~40 hod |
| Jiří Pavlis | UI/UX designér | Návrh uživatelského rozhraní, wireframy, grafická konzistence, testování designu na mobilních zařízeních | ~12 hod |
| Jakub Cink | Frontend designér, tester | Styly stránek, responzivní layout, uživatelské testování, zpětná vazba k UX | ~10 hod |
| Michal Havelka | Projektový manažer, finanční plán | Řízení projektu, časový harmonogram, rozdělení úkolů, kalkulace nákladů na provoz a vývoj | ~8 hod |
| Miroslav Krček | Analytik, dokumentace | Analýza požadavků, use case diagram, psaní dokumentace, závěrečné review | ~8 hod |

**Celkem:** ~78 hodin

### Rozpočet a náklady projektu

| Položka | Popis | Náklad |
|---------|-------|--------|
| Hosting | Školní server KIT ČZU (zdarma pro studenty) | 0 Kč |
| Doména | Subdoména školního serveru | 0 Kč |
| Google Books API | Free tier (1 000 req/den) | 0 Kč |
| Vývojové nástroje | VS Code, XAMPP, WinSCP (open-source / zdarma) | 0 Kč |
| Práce vývojářů | 78 hodin × 0 Kč (školní projekt) | 0 Kč |
| **Celkem** | | **0 Kč** |

> Při komerčním nasazení: VPS hosting (~200–500 Kč/měsíc), případně placená úroveň Google Books API při překročení denního limitu.

### Rozdělení práce

| Oblast | Zodpovídá |
|--------|-----------|
| PHP backend, MVC, databáze, API | Denis Vaň |
| UI/UX návrh, grafika | Jiří Pavlis, Jakub Cink |
| Responzivní CSS, frontend styly | Denis Vaň, Jiří Pavlis, Jakub Cink |
| Projektový plán, harmonogram | Michal Havelka |
| Finanční plán, kalkulace nákladů | Michal Havelka |
| Analýza požadavků, use case | Miroslav Krček |
| Dokumentace (README) | Miroslav Krček, Denis Vaň |
| Nasazení na server, DevOps | Denis Vaň |
| Uživatelské testování | Jakub Cink, Miroslav Krček |

---

## Závěr

### Úspěšnost splnění cílů

Projekt BookLend splnil všechny hlavní cíle stanovené na začátku semestru. Aplikace je funkční, nasazená na školním serveru a pokrývá celý životní cyklus výpůjčky — od registrace uživatele, přes půjčení knihy, prodloužení, až po vrácení a výpočet penále.

Nad rámec původního zadání bylo implementováno:
- Integrace Google Books API s prioritizací českých knih
- PHP transakce jako náhrada MySQL triggerů (kompatibilita se sdílenými hostingy)
- File-based cache pro API výsledky
- SEO (sitemap.xml, robots.txt, Open Graph, Schema.org JSON-LD)
- Mobile-first responzivní design

### Co nebylo implementováno

- **Platební brána** – prodloužení je zaznamenáno jako placené, ale skutečná integrace (GoPay, Stripe) nebyla součástí zadání
- **Email notifikace** – upozornění na blížící se splatnost nebylo implementováno
- **Automatické vymáhání penále** – penále je vypočítáno, ale jeho vymáhání je manuální

### Další příležitosti

- Implementace platební brány (GoPay API)
- Email notifikace při blížící se splatnosti (PHPMailer + cron)
- REST API pro mobilní aplikaci
- Hodnocení a recenze knih uživateli
- Rezervační systém pro nedostupné knihy

---

## Zdroje

- [PHP Dokumentace – PDO](https://www.php.net/manual/en/book.pdo.php)
- [PHP Dokumentace – password_hash()](https://www.php.net/manual/en/function.password-hash.php)
- [Google Books API – dokumentace](https://developers.google.com/books/docs/v1/using)
- [MDN Web Docs – Fetch API](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API)
- [OWASP – SQL Injection Prevention](https://cheatsheetseries.owasp.org/cheatsheets/SQL_Injection_Prevention_Cheat_Sheet.html)
- [OWASP – XSS Prevention](https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html)
- [Schema.org – Book](https://schema.org/Book)
- [Apache mod_rewrite dokumentace](https://httpd.apache.org/docs/2.4/mod/mod_rewrite.html)
