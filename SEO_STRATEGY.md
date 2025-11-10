# SEO strategie a roadmapa pro BookLend

Tento dokument shrnuje analýzu, strategii a konkrétní kroky pro optimalizaci webu BookLend pro vyhledávače (SEO).

## 1. Analýza současného stavu

### Co funguje dobře:
- **SEO-friendly URL:** Aplikace již používá přívětivé URL ve formátu `/kniha/{slug}`, což je dobrý základ.
- **Dostupnost dat:** V detailu knihy jsou k dispozici všechna potřebná data (název, autor, popis, ISBN, obálka) pro generování pokročilých SEO tagů.
- **Základní meta tagy:** Globální `layout.php` obsahuje základní `<title>` a `<meta name="description">`.

### Co je potřeba vylepšit:
- **Meta tagy:** Titulek a popis nejsou dostatečně specifické pro detail knihy.
- **Strukturovaná data:** Chybí jakákoli forma strukturovaných dat (např. Schema.org), která pomáhá vyhledávačům lépe pochopit obsah.
- **Sociální sítě:** Chybí Open Graph a Twitter Card tagy pro lepší sdílení na sociálních sítích.
- **Indexace:** Chybí soubory `robots.txt` a `sitemap.xml` pro efektivní řízení indexace.
- **Duplicita:** Chybí `canonical` URL, které by mohly předejít problémům s duplicitním obsahem.

---

## 2. SEO strategie

### 2.1 Meta tagy
- **Title:** `<title>{title} – {author} | Půjčovna knih</title>`
- **Description:** `<meta name="description" content="Vypůjčte si knihu {title} od {author}. {shortDesc}">`
- **Implementace:** Dynamicky generovat na stránce detailu knihy.

### 2.2 Strukturovaná data (schema.org – Book)
- **JSON-LD:**
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
- **Implementace:** Vložit do `<head>` sekce na stránce detailu knihy.

### 2.3 Open Graph & Twitter Cards
- **Meta tagy:**
  ```html
  <meta property="og:type" content="book">
  <meta property="og:title" content="{title} – {author}">
  <meta property="og:description" content="{shortDesc}">
  <meta property="og:image" content="{coverUrl}">
  <meta property="og:url" content="https://example.cz/kniha/{slug}">
  <meta name="twitter:card" content="summary_large_image">
  ```
- **Implementace:** Vložit do `<head>` sekce na stránce detailu knihy.

### 2.4 Sitemap & robots.txt
- **robots.txt:**
  ```
  User-agent: *
  Allow: /
  Sitemap: https://example.cz/sitemap.xml
  ```
- **sitemap.xml (statická ukázka):**
  ```xml
  <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>https://example.cz/</loc></url>
    <url><loc>https://example.cz/kniha/slug-knihy-1</loc></url>
  </urlset>
  ```
- **Implementace:** Vytvořit soubory v `public/` adresáři.

### 2.5 URL a interní prolinkování
- **URL:** Doporučeno sjednotit na `/knihy/{slug}`.
- **Prolinkování:** V budoucnu propojit katalog → detail → recenze → podobné tituly.

---

## 3. SEO Roadmapa (Implementační kroky)

### Fáze 1 – Technický základ (tato implementace)
1.  **[✔] Vytvoření `SEO_STRATEGY.md`**
2.  **[ ] Úprava `app/Views/layout.php`:** Přidání bloků pro vkládání specifických SEO tagů.
3.  **[ ] Úprava `app/Views/books/detail.php`:** Implementace dynamických meta tagů, JSON-LD a Open Graph.
4.  **[ ] Vytvoření `public/robots.txt`**.
5.  **[ ] Vytvoření statického `public/sitemap.xml`**.
6.  **[ ] (Volitelné) Úprava `routes.php`** pro sjednocení URL na `/knihy/{slug}`.

### Fáze 2 – Obsah a další rozvoj (budoucí kroky)
-   Vytvoření žánrových a autorských stránek.
-   Založení blogu.
-   Implementace uživatelských recenzí.
-   Dynamické generování `sitemap.xml`.
