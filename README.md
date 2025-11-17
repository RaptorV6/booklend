# 📚 BookLend - Online Půjčovna Knih

Webová aplikace pro půjčování knih s automatickou správou inventáře, Google Books API integrací a plnou responzivitou.

## 🎯 Hlavní funkce

**Pro uživatele:**
- Procházení a vyhledávání knih (filtry podle žánru, roku, jazyka)
- Půjčování na 30 dní + neomezené prodlužování (+15 dní, placené)
- Sledování výpůjček a automatická kalkulace penále (100 000 Kč/týden po splatnosti)

**Pro administrátory:**
- Správa katalogu (přidávání/editace/mazání knih)
- Google Books API integrace (automatické stahování dat + obrázků obálek)
- Inteligentní prioritizace českých knih (3-metodová detekce)

**Výchozí admin:**
- Email: `admin@booklend.cz`
- Heslo: `Start321`

---

## ⚙️ Technické řešení

### PHP Transakce místo MySQL Triggerů
Aplikace používá **PHP transakce** pro správu inventáře místo MySQL triggerů → **funguje i na levných hostinzích bez TRIGGER privilege**.

**Při půjčení knihy:**
```php
BEGIN TRANSACTION
SELECT * FROM books WHERE id = X FOR UPDATE  // Zamkne řádek
// Zkontroluje dostupnost
INSERT INTO rentals (...)
UPDATE books SET available_copies = available_copies - 1
COMMIT  // Vše najednou nebo nic
```

**Výhody:**
- Atomicita (vše nebo nic)
- `SELECT...FOR UPDATE` prevence race conditions
- Funguje na všech hostinzích (InfinityFree, ...)

### Google Books API - Prioritizace českých knih

**Problém:** API vrací anglické knihy jako první i pro české dotazy.

**Řešení:** 3-metodová detekce českých knih:
1. **České znaky** v názvu (ě, š, č, ř, ž, ý, á, í, é, ů, ú, ň, ť, ď)
2. **Pattern**: `/harry\s+potter\s+a\s+[písmeno]/` → detekuje "Harry Potter a relikvie smrti"
3. **Fráze**: "kámen mudrců", "tajemná komnata", "relikvie smrti", atd.

**Výsledek:** České knihy automaticky nahoře ve výsledcích.

**Proč ne `langRestrict=cs`?**
- API označuje anglické knihy O češtině jako `lang: "cs"`
- České překlady mají často `lang: "en"`
- Příliš restriktivní

### Databázový model

**users** (uživatelé)
- `id`, `username`, `email`, `password_hash` (bcrypt), `role` (admin/user)

**books** (knihy)
- `id`, `isbn`, `title`, `author`, `genre`, `published_year`, `language`
- `total_copies`, `available_copies` (automaticky upravováno transakcemi)
- `thumbnail` (URL obrázku z Google Books), `slug` (SEO URL)

**rentals** (výpůjčky)
- `id`, `user_id`, `book_id`, `rented_at`, `due_at`, `returned_at`
- `original_due_at`, `extension_count`, `extended_at` (prodloužení)
- `fine_amount`, `fine_paid` (penále)

---

## 🚀 Instalace

### Localhost (XAMPP)

1. **Nainstaluj XAMPP** → Spusť Apache + MySQL

2. **Zkopíruj projekt** do `C:\xampp\htdocs\booklend`

3. **Vytvoř databázi:**
   - Jdi na `http://localhost/phpmyadmin`
   - Vytvoř databázi `booklend` (utf8mb4_unicode_ci)
   - Importuj `database/schema.sql`

4. **Otevři aplikaci:** `http://localhost/booklend`

### Hosting bez TRIGGER privilege

1. Importuj `database/schema-no-triggers.sql` místo `schema.sql`
2. PHP transakce zajistí správnou funkcionalitu
3. `BASE_URL` se detekuje automaticky (není třeba nastavovat)

---

## 📂 Struktura projektu

```
booklend/
├── app/
│   ├── Controllers/       # BookController, AuthController, AdminController, UserController
│   ├── Models/           # Book, User, Rental (databázové operace)
│   ├── Views/            # HTML šablony (books/, user/, auth/, admin/)
│   ├── Database.php      # PDO wrapper + transakce
│   ├── Auth.php          # Autentizace a autorizace
│   └── helpers.php       # Pomocné funkce
├── public/
│   ├── assets/
│   │   ├── css/         # style.css, responsive.css, admin.css
│   │   └── js/          # app.js, ajax.js, admin.js, toast.js
│   └── index.php        # Entry point
├── database/
│   ├── schema.sql                # S triggery (localhost)
│   └── schema-no-triggers.sql   # Bez triggerů (hosting)
├── config.php            # AUTO-DETECT BASE_URL, DB přístupy
└── routes.php           # URL routing
```

---

## 🔒 Bezpečnost

- **Hesla:** bcrypt hashing
- **SQL injection:** Prepared statements
- **XSS:** htmlspecialchars()
- **Autorizace:** Middleware kontrola rolí

---

## ✨ Klíčové vlastnosti

✅ **Hosting-ready** - Funguje i bez MySQL TRIGGER privilege
✅ **AUTO-DETECT BASE_URL** - Automatická detekce localhost/produkce
✅ **Inteligentní API** - Prioritizace českých knih (3 metody)
✅ **Responzivní** - Mobile-first design (adaptivní logo, optimalizované karty)
✅ **Bezpečné** - Bcrypt, prepared statements, XSS protection
✅ **SEO** - Přátelské URL, meta tagy, sitemap, Open Graph

---

**Verze:** 1.1 (Listopad 2025)
**Licence:** Open Source
**Web:** http://localhost/booklend
