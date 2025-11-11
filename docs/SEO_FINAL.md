# SEO Strategie - BookLend
> Školní projekt - online půjčovna knih

---

## 1. SEO Strategie

### 1.1 Meta tagy

**Homepage (katalog):**
```html
<title>Katalog knih - BookLend</title>
<meta name="description" content="Online půjčovna knih. Procházejte katalog a vypůjčte si knihy.">
```

**Detail knihy:**
```html
<title>{title} – {author} | BookLend</title>
<meta name="description" content="Vypůjčte si knihu {title} od {author}. {popis}">
```

---

### 1.2 Strukturovaná data (Schema.org)

**Detail knihy - JSON-LD:**
```json
{
  "@context": "https://schema.org",
  "@type": "Book",
  "name": "{title}",
  "author": { "@type": "Person", "name": "{author}" },
  "isbn": "{isbn}",
  "image": "{coverUrl}"
}
```

**Účel:** Google zobrazí knihu jako rich result.

---

### 1.3 Open Graph (sdílení)

**Detail knihy:**
```html
<meta property="og:type" content="book">
<meta property="og:title" content="{title} – {author}">
<meta property="og:description" content="{popis}">
<meta property="og:image" content="{coverUrl}">
<meta property="og:url" content="https://booklend.cz/kniha/{slug}">
<meta name="twitter:card" content="summary_large_image">
```

**Účel:** Náhled při sdílení na Facebooku, Twitteru.

---

### 1.4 Sitemap & robots.txt

**robots.txt:**
```txt
User-agent: *
Allow: /

Disallow: /admin
Disallow: /api/

Sitemap: https://booklend.cz/sitemap.xml
```

**sitemap.xml:**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>https://booklend.cz/</loc>
    <priority>1.0</priority>
  </url>

  <url>
    <loc>https://booklend.cz/kniha/{slug}</loc>
    <priority>0.8</priority>
  </url>
</urlset>
```

---

### 1.5 URL struktura

| Stránka | URL | Příklad |
|---------|-----|---------|
| Homepage | `/` | https://booklend.cz/ |
| Detail knihy | `/kniha/{slug}` | https://booklend.cz/kniha/hobit-jrr-tolkien |
| Filtr žánr | `/?zanr=Fantasy` | https://booklend.cz/?zanr=Fantasy |
| Filtr rok | `/?rok=2020` | https://booklend.cz/?rok=2020 |

**Poznámka:** Žánr a rok jsou GET parametry, ne samostatné stránky.

---

## 2. Klíčová slova

### 2.1 Homepage (katalog)

**Klíčová slova:**
- půjčovna knih
- online půjčovna
- katalog knih
- půjčit knihu
- vypůjčit knihu
- knihy online
- online knihy
- půjčování knih

---

### 2.2 Detail knihy

**Klíčová slova (dynamická):**
- {název knihy}
- {autor}
- {název knihy} {autor}
- {název knihy} půjčit
- {název knihy} online
- {isbn}

**Příklad pro "Hobit":**
- Hobit
- J.R.R. Tolkien
- Hobit J.R.R. Tolkien
- Hobit půjčit
- Hobit online
- 9780547928227

---

### 2.3 Žánrové filtry

**Klíčová slova:**
- fantasy knihy
- sci-fi knihy
- historické romány
- detektivky
- romány
- fantasy online
- sci-fi půjčovna

**Poznámka:** Tyto slova cílí na filtrované výsledky `/?zanr=Fantasy`.

---

## 3. Měření

**Nástroje:**
- Google Search Console (indexace)
- Lighthouse (Chrome DevTools - výkon)

**Základní metriky:**
- Indexované stránky: homepage + všechny knihy
- Rychlost načtení: < 3s
- Mobilní přívětivost: ANO

---

**Verze:** 1.0
**Datum:** 2025-01-10
