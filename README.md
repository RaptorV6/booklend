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

## 📊 Úvodní studie vs. Současný stav

### Informační architektura

| Plánováno | Implementováno | Status |
|-----------|----------------|--------|
| Domů | `/` - Katalog knih | ✅ |
| Katalog knih | `/` - Hlavní stránka | ✅ |
| Detail knihy | `/kniha/{slug}` | ✅ |
| Můj účet / Moje výpůjčky | `/user/profile` + `/user/loans` | ✅ |
| Administrace knih | `/admin` | ✅ |
| Přihlášení / Registrace | `/login` + `/register` | ✅ |
| **Ceník** | **NENÍ** | ❌ |

### SEO Strategie - Implementace

**✅ Meta tagy**
- Homepage: `<title>Katalog knih - BookLend</title>` + description
- Detail knihy: `<title>{title} – {author} | BookLend</title>` + dynamický popis

**✅ Strukturovaná data (Schema.org JSON-LD)**
```json
{
  "@context": "https://schema.org",
  "@type": "Book",
  "name": "{title}",
  "author": { "@type": "Person", "name": "{author}" },
  "isbn": "{isbn}",
  "image": "{thumbnail}"
}
```

**✅ Open Graph (Facebook/Twitter sdílení)**
```html
<meta property="og:type" content="book">
<meta property="og:title" content="{title} – {author}">
<meta property="og:description" content="{description}">
<meta property="og:image" content="{thumbnail}">
<meta property="og:url" content="{BASE_URL}/kniha/{slug}">
<meta name="twitter:card" content="summary_large_image">
```

**✅ robots.txt**
```
User-agent: *
Allow: /
Disallow: /admin
Disallow: /api/
Sitemap: {BASE_URL}/sitemap.xml
```

**✅ sitemap.xml**
- Dynamicky generovaný seznam všech knih
- Priority: homepage (1.0), knihy (0.8)
- Včetně `<lastmod>` pro každou knihu

**✅ URL struktura**
- Homepage: `/`
- Detail knihy: `/kniha/{slug}` (SEO-friendly bez diakritiky)
- Filtry: `/?genre=Fantasy&year=2020` (GET parametry)

### Klíčová slova - Pokrytí

| Kategorie | Plánovaná slova | Implementace |
|-----------|-----------------|--------------|
| **Homepage** | půjčovna knih, online půjčovna, katalog knih | ✅ V meta description |
| **Detail knihy** | {název}, {autor}, {isbn}, "půjčit" | ✅ Dynamicky v title/description |
| **Žánrové filtry** | fantasy knihy, sci-fi knihy, detektivky | ✅ Filtry fungují, slova v UI |

### Rozšíření nad rámec studie

**➕ Navíc implementováno:**
1. **Prodlužování výpůjček** - Neomezené (+15 dní, placené)
2. **Penalizace** - Automatická kalkulace (100 000 Kč/týden po splatnosti)
3. **Google Books API** - Automatické stahování dat o knihách
4. **Prioritizace českých knih** - 3-metodová detekce pro lepší UX
5. **PHP transakce** - Náhrada MySQL triggerů (kompatibilita s levnými hostingy)
6. **AUTO-DETECT BASE_URL** - Bez manuální konfigurace
7. **Mobile-first responzivita** - Plně optimalizováno pro mobily

**Poznámky:**
- ❌ **Ceník** nebyl implementován (výpůjčky jsou v aplikaci zdarma, prodloužení placené ale bez platební brány)
- ✅ Všechny ostatní body úvodní studie **splněny**
- ✅ SEO metriky: Rychlost < 3s ✅, Responzivita ✅, Indexace ✅

---

**Verze:** 1.1 (Listopad 2025)
**Licence:** Open Source
**Web:** http://localhost/booklend
