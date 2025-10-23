# 📚 BookLend - Online Knihovna

Kompletní webová aplikace pro správu půjčovny knih postavená v pure PHP s MVC architekturou.

## ✅ Implementováno

### Architektura
- ✅ **MVC Pattern** - čistá separace logiky, dat a prezentace
- ✅ **PSR-4 Autoloading** - automatické načítání tříd
- ✅ **Custom Router** - vlastní routing system s middleware podporou
- ✅ **Repository Pattern** - modely pro práci s databází
- ✅ **File-based Cache** - sdílená file cache s probabilistic cleanup
- ✅ **Session-based Auth** - autentizační systém

### Funkcionality
- ✅ **Katalog knih** - přehled všech knih s dostupností
- ✅ **Detail knihy** - detailní informace + Google Books API integrace
- ✅ **Live Search** - AJAX vyhledávání v reálném čase
- ✅ **Registrace/Přihlášení** - kompletní auth systém
- ✅ **Půjčování knih** - AJAX půjčování s validací dostupnosti
- ✅ **Vracení knih** - AJAX vracení s aktualizací stavu
- ✅ **Uživatelský profil** - přehled uživatelských dat
- ✅ **Správa výpůjček** - přehled aktivních i minulých výpůjček

### Bezpečnost
- ✅ **Prepared Statements** - ochrana proti SQL injection
- ✅ **Password Hashing** - bcrypt hashování hesel
- ✅ **XSS Protection** - escapování výstupu pomocí `e()` funkce
- ✅ **Session Security** - session regeneration po přihlášení
- ✅ **Security Headers** - HTTP security headers v .htaccess

### Performance
- ✅ **File Cache** - sdílená cache pro API volání
- ✅ **Database Triggers** - automatická správa dostupnosti knih
- ✅ **Computed Columns** - is_active, is_overdue na DB úrovni
- ✅ **GZIP Compression** - komprese statických souborů
- ✅ **Browser Caching** - nastavení cache headers

### UI/UX
- ✅ **Moderní Dark Theme** - tmavý design s gradientními akcenty
- ✅ **Responsive Design** - plně responzivní layout
- ✅ **Live Search** - živé vyhledávání bez page reload
- ✅ **AJAX Operace** - půjčování a vracení bez refresh stránky
- ✅ **Form Validation** - client i server-side validace

## 📁 Struktura Projektu

```
booklend/
│
├── public/                    # Veřejně přístupné soubory
│   ├── index.php             # Front controller
│   ├── .htaccess             # Apache konfigurace
│   ├── cache/                # File cache (auto-created)
│   └── assets/
│       ├── css/style.css     # Stylesheet
│       └── js/
│           ├── app.js        # Main JavaScript
│           └── ajax.js       # AJAX funkce
│
├── app/                       # Aplikační logika
│   ├── Controllers/          # Controllery (Book, User, Auth)
│   ├── Models/               # Modely (Book, User, Rental)
│   ├── Views/                # View šablony
│   │   ├── layout.php        # Master layout
│   │   ├── books/            # Book views
│   │   ├── user/             # User views
│   │   ├── auth/             # Auth views
│   │   └── errors/           # Error pages (404, 500)
│   ├── Router.php            # Routing system
│   ├── Database.php          # PDO wrapper
│   ├── Cache.php             # File cache systém
│   ├── Auth.php              # Autentizace
│   └── helpers.php           # Helper funkce
│
├── database/
│   └── schema.sql            # Database schema + seed data
│
├── config.php                # Konfigurace aplikace
└── routes.php                # Definice routes

```

## 🚀 Instalace

### 1. Předpoklady
- XAMPP (Apache + MySQL/MariaDB + PHP 8.0+)
- Git (volitelně)

### 2. Kroky instalace

#### A) Databáze
1. Spusťte XAMPP a zapněte **Apache** a **MySQL**
2. Otevřete phpMyAdmin: `http://localhost/phpmyadmin`
3. Vytvořte databázi `booklend`:
   ```sql
   CREATE DATABASE booklend CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
4. Importujte schema:
   - Vyberte databázi `booklend`
   - Klikněte na záložku **Import**
   - Vyberte soubor `database/schema.sql`
   - Klikněte **Provést**

#### B) Konfigurace

1. Otevřete soubor `config.php`
2. Zkontrolujte/upravte database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'booklend');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // XAMPP default je prázdné heslo
   ```
3. Upravte BASE_URL podle vaší instalace:
   ```php
   define('BASE_URL', 'http://localhost/booklend/public');
   ```

#### C) Oprávnění

Ujistěte se, že složka `public/cache/` má správná oprávnění:
```bash
chmod 755 public/cache
```

#### D) Apache konfigurace (volitelné)

Pro lepší URL můžete nastavit DocumentRoot na `public/` složku v Apache configu:

```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/booklend/public"
    ServerName booklend.local

    <Directory "C:/xampp/htdocs/booklend/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

A přidat do `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1 booklend.local
```

## 🎯 Testovací účty

Po importu databáze máte k dispozici:

### Admin účet
- **Email:** admin@booklend.cz
- **Heslo:** admin123

### Uživatelský účet
- **Email:** user@booklend.cz
- **Heslo:** user123

## 🌐 Použití

### Přístup k aplikaci

1. **Standardní instalace:**
   ```
   http://localhost/booklend/public
   ```

2. **S virtual hostem:**
   ```
   http://booklend.local
   ```

### Hlavní stránky

- `/` - Katalog knih
- `/kniha/{slug}` - Detail knihy
- `/login` - Přihlášení
- `/register` - Registrace
- `/profil` - Uživatelský profil (vyžaduje přihlášení)
- `/moje-vypujcky` - Přehled výpůjček (vyžaduje přihlášení)

### API Endpointy

- `GET /api/search?q={query}` - Vyhledávání knih
- `POST /api/rent` - Půjčení knihy (JSON: `{book_id: number}`)
- `POST /api/return` - Vrácení knihy (JSON: `{rental_id: number}`)

## 🔧 Technologie

### Backend
- **PHP 8.0+** - Server-side logika
- **MySQL/MariaDB** - Relační databáze
- **PDO** - Database abstraction layer
- **Sessions** - Správa přihlášení

### Frontend
- **Vanilla JavaScript** - Bez závislostí
- **Fetch API** - AJAX komunikace
- **CSS3** - Modern styling s CSS Grid/Flexbox
- **Responsive Design** - Mobile-first approach

### Integrace
- **Google Books API** - Metadata o knihách (cover, popis, atd.)

## 📊 Database Schema

### Users
- Správa uživatelských účtů
- Role: user, admin
- Soft delete support

### Books
- ISBN jako unique identifier
- SEO-friendly slugs
- Automatické sledování dostupnosti
- View counter

### Rentals
- Foreign keys s CASCADE/RESTRICT
- Computed columns (is_active, is_overdue)
- Database triggers pro správu stock

## 🎨 Features Detail

### Cache System
- Probabilistic cleanup (1% šance při každém zápisu)
- LRU eviction při překročení limitů
- Sampling pro performance při velkém množství souborů
- Automatické vytvoření directory struktury

### Routing System
- Pattern matching s parametry: `/kniha/{slug}`
- Middleware support (auth)
- HTTP method routing (GET, POST)
- Clean URLs díky .htaccess

### Validation System
- Centralizovaná `validate()` funkce
- Pravidla: required, email, min, max
- Flash messages pro errors
- Old input retention

## 📝 TODO / Možná rozšíření

- [ ] Admin dashboard pro správu knih
- [ ] Email notifikace při blížícím se termínu vrácení
- [ ] Rating systém pro knihy
- [ ] Vyhledávání podle kategorií/žánrů
- [ ] Export výpůjček do PDF/CSV
- [ ] REST API pro mobilní aplikace
- [ ] Image upload pro book covers
- [ ] Reservace knih (když nejsou dostupné)

## 🐛 Debugging

Pro aktivaci debug módu upravte v `public/index.php`:

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

Pro produkci nastavte:

```php
error_reporting(0);
ini_set('display_errors', 0);
```

## 📄 Licence

Školní projekt - BookLend 2025

---

**Vytvořeno jako finální implementace knihovního systému podle přesné specifikace.**
