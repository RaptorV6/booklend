# 📚 BookLend - Online Půjčovna Knih

## Co je BookLend?

BookLend je **webová aplikace**, která funguje jako online knihovna. Představ si to jako klasickou městskou knihovnu, ale celou na internetu. Lidé si můžou **prohlížet katalog knih**, **půjčovat si je** a **vracet je** - všechno z pohodlí domova přes webový prohlížeč.

---

## 🎯 K čemu to je?

### Pro uživatele
- **Najít knihu** - Můžeš procházet katalog knih, filtrovat podle žánru, roku vydání nebo jazyka
- **Půjčit si knihu** - Jedno kliknutí a kniha je tvoje na 30 dní
- **Sledovat výpůjčky** - Vidíš všechny knihy, které máš půjčené a kdy je musíš vrátit
- **Vrátit knihu** - Když dočteš, vrátíš jedním kliknutím

### Pro knihovníky (administrátory)
- **Spravovat katalog** - Přidávat nové knihy, upravovat existující, mazat staré
- **Sledovat inventář** - Systém sám hlídá, kolik knih je dostupných a kolik půjčených
- **Vyhledávat v Google Books** - Automaticky stahovat informace o knihách včetně obrázků obálek

---

## 👥 Kdo může aplikaci používat?

### 1. **Nepřihlášený návštěvník**
- Může prohlížet katalog knih
- Může vyhledávat
- Může si prohlížet detaily jednotlivých knih
- **NEMŮŽE** půjčovat knihy

### 2. **Přihlášený uživatel** (role: "user")
- Všechno co návštěvník +
- **Může půjčovat knihy**
- **Může vracet knihy**
- **Může prodlužovat výpůjčky** (+15 dní, neomezeno, placené)
- Vidí svůj profil
- Má přehled o svých výpůjčkách

### 3. **Administrátor** (role: "admin")
- Všechno co běžný uživatel +
- **Přístup do admin panelu**
- Může přidávat/upravovat/mazat knihy
- Spravuje skladové zásoby (kolik kusů je k dispozici)
- Může vyhledávat v Google Books API

**Výchozí admin účet:**
- Email: `admin@booklend.cz`
- Heslo: `Start321`

---

## 🎬 Jak to funguje v praxi?

### Scénář 1: Registrace nového uživatele

1. **Návštěvník** přijde na web → `http://localhost/booklend`
2. Klikne na tlačítko **"Registrace"** v hlavičce
3. Otevře se mu formulář, kde vyplní:
   - Uživatelské jméno (např. "petr123")
   - Email (např. "petr@email.cz")
   - Heslo (musí mít alespoň 6 znaků)
   - Heslo znovu (pro kontrolu)
4. Klikne "Zaregistrovat se"
5. Systém zkontroluje:
   - ✅ Je email validní? (obsahuje @ a tečku)
   - ✅ Není už email zabraný někým jiným?
   - ✅ Není už username zabraný?
   - ✅ Hesla se shodují?
6. Pokud je vše OK:
   - **Heslo se zašifruje** (pro bezpečnost - nikdo ho neuvidí v čitelné podobě)
   - Vytvoří se nový záznam v databázi
   - Uživatel je přesměrován na přihlášení
7. Teď se může přihlásit a půjčovat knihy!

### Scénář 2: Půjčení knihy

1. **Přihlášený uživatel** si prohlíží katalog
2. Najde knihu, která ho zajímá (např. "1984" od George Orwella)
3. Klikne na ni → otevře se **detail knihy**
4. Vidí tam:
   - Velký obrázek obálky
   - Popis knihy
   - Autor, rok vydání, žánr
   - **Důležité: "Dostupné: 2 z 5 kusů"** (znamená, že z celkových 5 kusů jsou 2 volné)
5. Pod tím je zelené tlačítko **"Půjčit knihu"**
6. Klikne na něj → zobrazí se **vyskakovací okno s podmínkami půjčení:**
   ```
   Opravdu chcete půjčit tuto knihu?

   📅 Podmínky půjčení:
   • Výpůjční doba: 30 dní
   • Prodloužení: kdykoliv o 15 dní (placené)
   • Penalizace: 100 000 Kč za každý týden zpoždění

   [Zrušit] [Potvrdit]
   ```
7. Potvrdí
8. **Co se teď stane v pozadí:**
   - Systém vytvoří záznam ve výpůjčkách: "Petr si půjčil knihu 1984 dne 14.11.2025, vrátit do 14.12.2025"
   - **Automaticky sníží počet dostupných kusů** z 2 na 1 (díky databázovému triggeru)
   - Zobrazí se zelená notifikace: "Kniha byla úspěšně půjčena!"
   - Tlačítko se změní na "Již vypůjčeno" (disable)
9. Teď když jiný uživatel navštíví tuto knihu, uvidí: "Dostupné: 1 z 5 kusů"

### Scénář 3: Vrácení knihy

1. Uživatel jde do sekce **"Moje výpůjčky"**
2. Vidí seznam všech knih, co má půjčené:
   ```
   ┌──────────────────────────────────────────┐
   │ 1984                                     │
   │ George Orwell                            │
   │ Půjčeno: 14.11.2025                      │
   │ Vrátit do: 14.12.2025                    │
   │ Status: 🔵 Aktivní                       │
   │ [🔄 Prodloužit (+15 dní)] [Vrátit knihu] │
   └──────────────────────────────────────────┘
   ```
3. Klikne na "Vrátit knihu"
4. Potvrdí vrácení
5. **Co se stane:**
   - Zaznamená se datum vrácení: "Vráceno 20.11.2025"
   - **Automaticky se zvýší počet dostupných kusů** z 1 na 2
   - Status se změní na 🟢 Vráceno
   - Tlačítko "Vrátit knihu" zmizí
6. Teď může knihu půjčit někdo jiný!

### Scénář 3B: Prodloužení výpůjčky

1. **Přihlášený uživatel** jde do sekce **"Moje výpůjčky"**
2. U aktivní výpůjčky vidí tlačítko **"🔄 Prodloužit (+15 dní)"**
   ```
   ┌──────────────────────────────────────────┐
   │ 1984                                     │
   │ George Orwell                            │
   │ Půjčeno: 14.11.2025                      │
   │ Vrátit do: 14.12.2025                    │
   │ Status: 🔵 Aktivní                       │
   │ Prodlouženo: 0×                          │
   │ [🔄 Prodloužit (+15 dní)] [Vrátit knihu] │
   └──────────────────────────────────────────┘
   ```
3. Klikne na **"🔄 Prodloužit (+15 dní)"**
4. Zobrazí se potvrzovací dialog:
   ```
   Prodloužit výpůjčku o 15 dní?
   (Bude účtován poplatek)

   Aktuální splatnost: 14.12.2025
   Nová splatnost: 29.12.2025

   [Zrušit] [Potvrdit]
   ```
5. Potvrdí
6. **Co se stane v pozadí:**
   - Systém uloží původní datum splatnosti (pokud je to první prodloužení)
   - Datum `due_at` se prodlouží o 15 dní
   - Zvýší se `extension_count` o 1
   - Uloží se `extended_at` (aktuální timestamp)
   - Zobrazí se zelená notifikace: "Výpůjčka byla prodloužena do 29.12.2025"
7. Teď vidí aktualizovanou kartu:
   ```
   ┌──────────────────────────────────────────┐
   │ 1984                                     │
   │ George Orwell                            │
   │ Půjčeno: 14.11.2025                      │
   │ Vrátit do: 29.12.2025                    │
   │ Status: 🔵 Aktivní                       │
   │ 🔄 Prodlouženo: 1× (poslední 14.12.2025)│
   │ [🔄 Prodloužit (+15 dní)] [Vrátit knihu] │
   └──────────────────────────────────────────┘
   ```
8. Může prodlužovat **neomezeně** - každé kliknutí přidá dalších 15 dní!

### Scénář 3C: Vrácení knihy po splatnosti s penále

1. **Uživatel** si půjčil knihu 20.10.2025, měl ji vrátit 19.11.2025
2. Dnes je 10.12.2025 - kniha je **21 dní po splatnosti** (3 týdny)
3. Jde do sekce **"Moje výpůjčky"** a vidí:
   ```
   ┌──────────────────────────────────────────┐
   │ Duna                                     │
   │ Frank Herbert                            │
   │ Půjčeno: 20.10.2025                      │
   │ Vrátit do: 19.11.2025                    │
   │ Status: 🔴 Po splatnosti (21 dní)        │
   │ ⚠️ PENÁLE: 300 000 Kč                    │
   │    (3 týdny × 100 000 Kč)                │
   │ [Vrátit knihu]                           │
   └──────────────────────────────────────────┘
   ```
4. **Jak se počítá penále:**
   - 1. týden po splatnosti (19.11 - 26.11): 100 000 Kč
   - 2. týden po splatnosti (27.11 - 3.12): 100 000 Kč
   - 3. týden po splatnosti (4.12 - 10.12): 100 000 Kč
   - **Celkem: 300 000 Kč**
   - Každý **započatý** týden = plná částka (i 1 den = 100k)
5. Klikne na "Vrátit knihu" a potvrdí
6. **Co se stane:**
   - Kniha je vrácena (`returned_at` = aktuální datum)
   - Počet dostupných kusů se zvýší
   - Penále `fine_amount` = 300 000 Kč zůstává zaznamenáno
   - Status: 🟢 Vráceno (penále k úhradě)

### Scénář 4: Admin přidává novou knihu

1. **Admin** se přihlásí
2. Jde do sekce **"/admin"**
3. Klikne na **"Přidat knihu"**
4. Otevře se formulář s vyhledáváním
5. Zadá např. "Harry Potter Kámen mudrců"
6. Systém se zeptá **Google Books API**: "Hej Google, máš info o knize Harry Potter?"
7. Google odpoví s daty:
   ```
   Název: Harry Potter a Kámen mudrců
   Autor: J.K. Rowling
   ISBN: 978-80-00-02590-8
   Rok: 2000
   Žánr: Fantasy
   Jazyk: cs
   Obrázek: https://books.google.com/books/content?id=xyz...
   Popis: Harry Potter žil u Dursleyových...
   ```
8. Admin vidí **náhled** těchto dat
9. Může upravit:
   - Počet kusů celkem (např. 5)
   - Počet dostupných kusů (např. 5)
10. Klikne "Přidat do katalogu"
11. **Co se stane:**
    - Stáhne se obrázek obálky z Google
    - Vytvoří se SEO-friendly URL: `/kniha/harry-potter-a-kamen-mudrcU` (bez diakritiky, pomlčky místo mezer)
    - Všechna data se uloží do databáze
    - Kniha se objeví v katalogu pro všechny uživatele!

---

## 🔍 Jak funguje vyhledávání a SEO

### Co je SEO a proč je důležité?

**SEO** (Search Engine Optimization) = "Optimalizace pro vyhledávače"

Představ si, že máš obchod s knihami. SEO je jako **velký svítící nápis, který říká Googlu**: "Hej, jsem tady! A prodávám knihy o fantasy, sci-fi a detektivkách!"

Když to uděláš dobře, tak když někdo na Googlu hledá "půjčit si 1984 online", tvoje stránka se objeví ve výsledcích.

### Jak je SEO implementováno v BookLend?

#### 1. **Přátelské URL adresy**

**Špatně:** `http://localhost/booklend/book.php?id=123`
**Dobře:** `http://localhost/booklend/kniha/1984`

Proč je to lepší?
- Google i lidé hned vidí, o čem stránka je
- Je to zapamatovatelné
- Vypadá to profesionálně

**Jak to funguje:**
- Název knihy "1984" se automaticky změní na "slug": `1984`
- Složitější název "Pán prstenů: Společenstvo Prstenu" → `pan-prstenu-spolecenstvo-prstenu`
- Odstraní se diakritika (háčky a čárky)
- Mezery se nahradí pomlčkami
- Všechno malými písmeny

#### 2. **Meta tagy** - Říkají Googlu co na stránce je

Pro každou knihu se generují "neviditelné značky" v HTML hlavičce:

```html
<title>1984 - George Orwell | BookLend</title>
<meta name="description" content="Půjčte si knihu 1984 od autora George Orwell. Dystopický román o totalitním režimu. Fantasy, rok vydání 1949.">
```

Když někdo hledá na Googlu, vidí tohle:

```
🔍 Google výsledek:
┌────────────────────────────────────────┐
│ 1984 - George Orwell | BookLend        │ ← title tag
│ localhost/booklend/kniha/1984          │ ← URL
│ Půjčte si knihu 1984 od autora...     │ ← description
└────────────────────────────────────────┘
```

#### 3. **Open Graph tagy** - Pro Facebook a sociální sítě

Když někdo sdílí odkaz na knihu na Facebooku, zobrazí se hezká kartička s obrázkem:

```
┌─────────────────────────────────────┐
│  ┌─────────────┐                    │
│  │  Obrázek    │  1984              │
│  │  obálky     │  George Orwell     │
│  │             │                     │
│  └─────────────┘  Dystopický román  │
│                   o totalitním...    │
└─────────────────────────────────────┘
```

Tohle zajišťují Open Graph meta tagy.

#### 4. **Structured Data (JSON-LD)** - Google to miluje!

Tohle je "tajná zpráva" pro Google, která říká: "Hele, tady je KNIHA, ne článek nebo recept!"

```json
{
  "@type": "Book",
  "name": "1984",
  "author": "George Orwell",
  "isbn": "978-80-257-0706-3",
  "genre": "Fantasy"
}
```

Díky tomu Google ví, že na stránce je kniha, a může ji zobrazit ve speciálních výsledcích (třeba s hvězdičkami hodnocení).

#### 5. **Sitemap.xml** - Mapa pro Google

Soubor `sitemap.xml` je jako **telefonní seznam** všech stránek na webu:

```xml
<urlset>
  <url>
    <loc>http://localhost/booklend/</loc>
    <priority>1.0</priority> ← Tohle je hlavní stránka!
  </url>
  <url>
    <loc>http://localhost/booklend/kniha/1984</loc>
    <lastmod>2025-11-13</lastmod> ← Naposledy změněno
    <priority>0.8</priority> ← Důležitá stránka
  </url>
  ...všechny knihy...
</urlset>
```

Google si stáhne tenhle soubor a ví, které stránky má indexovat (zařadit do vyhledávání).

#### 6. **Robots.txt** - Pravidla pro roboty

```
User-agent: *
Allow: /              ← Indexuj všechno
Disallow: /admin      ← KROMĚ admin panelu (nechceme ho na Googlu!)
Disallow: /api/       ← A API endpointů

Sitemap: http://localhost/booklend/sitemap.xml  ← A tady je mapa!
```

#### 7. **Klíčová slova** - Kde se používají?

Klíčová slova jsou slova, která lidé píšou do Googlu. V BookLend jsou umístěna:

**A) V názvech knih:**
- "1984", "Harry Potter", "Pán prstenů"

**B) V popisech:**
- "dystopický román o totalitním režimu"
- "fantasy příběh o hobitovi"

**C) V žánrech:**
- Fantasy, Sci-Fi, Detektivka, Romantika

**D) V URL:**
- `/kniha/harry-potter-a-kamen-mudrcU`

**E) V meta description:**
- "Půjčte si knihu 1984 online zdarma. Dystopický román George Orwell."

Když někdo hledá "půjčit fantasy knihu online", Google najde BookLend, protože:
- Slovo "fantasy" je v žánrech
- Slovo "knihu" je všude
- Systém je o půjčování (v popisu)

---

## 🗄️ Jak funguje databáze (datový model)

Databáze je jako **velká tabulková kalkulace** (Excel), kde se ukládají všechna data. BookLend má 3 hlavní tabulky:

### Tabulka 1: **users** (uživatelé)

Představ si to jako **seznam členů knihovny**:

| id | username | email | heslo (zašifrované) | role | registrován |
|----|----------|-------|---------------------|------|-------------|
| 1 | admin | admin@booklend.cz | $2y$10$zf2... | admin | 2025-01-01 |
| 2 | petr123 | petr@email.cz | $2y$10$abc... | user | 2025-11-14 |
| 3 | jana | jana@seznam.cz | $2y$10$xyz... | user | 2025-11-15 |

**Co se tady ukládá:**
- **id** - Unikátní číslo každého uživatele (jako rodné číslo)
- **username** - Přezdívka uživatele
- **email** - Pro přihlášení
- **password_hash** - Zašifrované heslo (nikdo ho nevidí, ani admin!)
- **role** - Je to běžný uživatel nebo administrátor?
- **registered_at** - Kdy se zaregistroval

### Tabulka 2: **books** (knihy)

**Katalog všech knih** v knihovně:

| id | isbn | název | autor | žánr | celkem | dostupné | obrázek |
|----|------|-------|-------|------|--------|----------|---------|
| 1 | 978-80... | 1984 | George Orwell | Fantasy | 5 | 2 | https://... |
| 2 | 978-80... | Harry Potter | J.K. Rowling | Fantasy | 3 | 3 | https://... |
| 3 | 978-80... | Hobit | J.R.R. Tolkien | Fantasy | 4 | 1 | https://... |

**Co se tady ukládá:**
- **id** - Unikátní číslo knihy
- **isbn** - Mezinárodní číslo knihy (jako EAN čárový kód)
- **title** - Název
- **author** - Autor
- **genre** - Žánr (Fantasy, Sci-Fi, Detektivka...)
- **total_copies** - Kolik kusů knihy knihovna celkem má
- **available_copies** - Kolik kusů je právě dostupných k půjčení
- **thumbnail** - URL obrázku obálky (z Google Books)
- **description** - Popis děje
- **published_year** - Rok vydání
- **language** - Jazyk (cs, en, de...)
- **views_count** - Kolikrát si knihu někdo prohlédl
- **slug** - SEO-friendly URL část (např. "harry-potter-a-kamen-mudrcU")

### Tabulka 3: **rentals** (výpůjčky)

**Evidence všech půjček** - kdo si co půjčil a kdy:

| id | user_id | book_id | půjčeno | vrátit do | vráceno |
|----|---------|---------|---------|-----------|---------|
| 1 | 2 | 1 | 2025-11-14 | 2025-11-28 | NULL |
| 2 | 3 | 1 | 2025-11-10 | 2025-11-24 | 2025-11-20 |
| 3 | 2 | 3 | 2025-11-01 | 2025-11-15 | 2025-11-14 |

**Co se tady ukládá:**
- **id** - Číslo výpůjčky
- **user_id** - KDO si půjčil (odkaz na tabulku users)
- **book_id** - CO si půjčil (odkaz na tabulku books)
- **rented_at** - Kdy si půjčil
- **due_at** - Kdy to má vrátit (standardně +30 dní, lze prodloužit o 15 dní)
- **returned_at** - Kdy to vrátil (NULL = ještě nemá vráceno)
- **original_due_at** - Původní datum splatnosti před prvním prodloužením
- **extension_count** - Kolikrát byla výpůjčka prodloužena (neomezeno)
- **extended_at** - Datum posledního prodloužení
- **fine_amount** - Výše penále v Kč (100 000 Kč za každý započatý týden po splatnosti)
- **fine_paid** - Zda bylo penále zaplaceno (0 = ne, 1 = ano)

### Jak spolu tabulky souvisí?

Představ si to jako **propojené tabulky v Excelu**:

```
┌─────────────┐         ┌─────────────┐         ┌─────────────┐
│   USERS     │         │   RENTALS   │         │    BOOKS    │
│             │         │             │         │             │
│ 2: petr123  │◄────────│ user_id: 2  │────────►│ 1: 1984     │
│             │         │ book_id: 1  │         │             │
│             │         │ vráceno: -  │         │ dostupné: 2 │
└─────────────┘         └─────────────┘         └─────────────┘
```

**Překlad do lidštiny:**
"Petr (id=2) si půjčil knihu 1984 (id=1) a ještě ji nevrátil. Proto je dostupných jen 2 kusy místo 3."

### Co jsou ty "triggery"?

Trigger je jako **automatický robot v databázi**, který reaguje na události.

**Příklad:**
Když někdo půjčí knihu (vytvoří se záznam v tabulce `rentals`), trigger automaticky:
1. Podívá se, jakou knihu si půjčil (book_id)
2. Najde tu knihu v tabulce `books`
3. Sníží `available_copies` o 1

**Proč je to geniální?**
Nikdo nemůže zapomenout snížit počet dostupných knih - systém to udělá SAM. Ani programátor nemůže udělat chybu.

---

## 🌐 Google Books API - Co to je a jak funguje?

### Co je API?

**API** (Application Programming Interface) = "Rozhraní pro aplikace"

Představ si to jako **automat na kávu**:
- Ty zmáčkneš tlačítko "Cappuccino" (pošleš požadavek)
- Automat ti udělá cappuccino (vrátí data)
- Nemusíš vědět, jak automat funguje uvnitř

### Co je Google Books API?

Google Books je obrovská **databáze všech knih na světě**. Google nabízí API, které znamená:

"Hej, ostatní aplikace! Můžete se mě ZEPTAT na informace o knihách a já vám je POŠLU."

### Jak se to používá v BookLend?

#### Scénář: Admin hledá knihu

1. **Admin** v admin panelu zadá do vyhledávání: "Harry Potter"

2. **BookLend aplikace** pošle požadavek na Google:
   ```
   GET https://www.googleapis.com/books/v1/volumes?q=Harry+Potter&country=CZ
   ```

   Co to znamená v lidštině:
   "Hej Google, dej mi seznam knih, kde je v názvu 'Harry Potter', preferuj české vydání"

3. **Google** odpoví (JSON formát):
   ```json
   {
     "items": [
       {
         "volumeInfo": {
           "title": "Harry Potter a Kámen mudrců",
           "authors": ["J.K. Rowling"],
           "publishedDate": "2000",
           "description": "Harry Potter žil u Dursleyových...",
           "language": "cs",
           "imageLinks": {
             "thumbnail": "https://books.google.com/books/content?id=xyz..."
           },
           "industryIdentifiers": [
             {"type": "ISBN_13", "identifier": "9788000025908"}
           ]
         }
       }
     ]
   }
   ```

4. **BookLend** si z toho vybere důležité informace:
   - Název: "Harry Potter a Kámen mudrců"
   - Autor: "J.K. Rowling"
   - Rok: 2000
   - ISBN: 978-80-00-02590-8
   - Obrázek obálky: https://books.google.com/...
   - Popis: "Harry Potter žil..."
   - Jazyk: "cs"

5. **BookLend** ještě:
   - Automaticky detekuje žánr z klíčových slov v popisu
   - Stáhne obrázek obálky ve vysokém rozlišení
   - Zkontroluje, jestli už kniha s tímto ISBN není v databázi

6. **Admin** vidí náhled a může knihu přidat do katalogu jedním kliknutím!

### Proč používat Google Books API?

**Bez API:**
- Admin by musel RUČNĚ opisovat název, autora, rok, popis...
- Musel by hledat obrázek obálky na internetu a stahovat ho
- Zabere to 5-10 minut na jednu knihu
- Můžou se stát překlepy

**S API:**
- Admin zadá jen ISBN nebo název
- Za 2 vteřiny má všechna data včetně obrázku
- Žádné překlepy
- Data jsou aktuální a správná (přímo od Googlu)

### Kdy se API volá?

1. **Admin vyhledává knihu** → API call
2. **Admin zadá ISBN** → API call (ověření)
3. **Cache expiruje** → Nový API call (jednou za 30 dní max)

### Cache (vyrovnávací paměť)

Aby se Google Books API nezatěžoval zbytečně, používá se **cache**.

**Jak to funguje:**
1. První dotaz: "Dej info o knize ISBN 978-80-257-0706-3"
   - Zavolá Google API
   - Uloží si odpověď do cache (soubor na disku)
   - Platnost: 30 dní

2. Druhý dotaz (za 5 minut) na stejné ISBN:
   - Neptat se Google!
   - Vrátit data z cache (rychlejší!)

3. Za 31 dní:
   - Cache vypršela
   - Zavolat Google znovu (možná se změnila cena nebo popis)

**Proč je cache důležitá?**
- Google má **denní limit volání** (např. 1000 požadavků/den)
- Bez cache bychom limit přesáhli
- S cache: rychlejší odpovědi, šetříme Google i sebe

---

## 📂 Struktura souborů - Co dělá každý soubor?

Představ si aplikaci jako **firma s odděleními**:

```
booklend/  (celá firma)
│
├── config.php
│   → "Ředitelství" - Tady jsou všechna základní nastavení
│     (heslo do databáze, Google API klíč, název aplikace...)
│
├── routes.php
│   → "Recepce" - Když někdo přijde na URL, recepce ho pošle na správné oddělení
│     Příklad: URL "/kniha/1984" → Pošli ho do knihovny (BookController)
│
├── app/  (aplikační logika - srdce firmy)
│   │
│   ├── Controllers/
│   │   → "Manažeři oddělení" - Každý manažer řídí jednu část aplikace
│   │   │
│   │   ├── BookController.php
│   │   │   → Manažer knihovny
│   │   │     - Ukazuje katalog knih
│   │   │     - Zobrazuje detail knihy
│   │   │     - Zpracovává půjčování a vracení
│   │   │
│   │   ├── AuthController.php
│   │   │   → Manažer zabezpečení
│   │   │     - Přihlašování uživatelů
│   │   │     - Registrace nových členů
│   │   │     - Odhlašování
│   │   │
│   │   ├── UserController.php
│   │   │   → Manažer uživatelských účtů
│   │   │     - Zobrazuje profil
│   │   │     - Seznam výpůjček uživatele
│   │   │
│   │   └── AdminController.php
│   │       → Hlavní šéf
│   │         - Správa katalogu (přidávání/mazání knih)
│   │         - Vyhledávání v Google Books API
│   │         - Editace knih
│   │
│   ├── Models/
│   │   → "Skladníci" - Komunikují s databází, obstarávají data
│   │   │
│   │   ├── Book.php
│   │   │   → Pracuje s knihami v databázi
│   │   │     - Načítá seznam knih
│   │   │     - Vyhledává knihy
│   │   │     - Filtruje podle žánru/roku
│   │   │     - Volá Google Books API
│   │   │
│   │   ├── User.php
│   │   │   → Pracuje s uživateli
│   │   │     - Vytváří nové účty
│   │   │     - Ověřuje hesla při přihlášení
│   │   │     - Načítá profily
│   │   │
│   │   └── Rental.php
│   │       → Pracuje s výpůjčkami
│   │         - Vytváří novou výpůjčku
│   │         - Vrací knihu
│   │         - Zobrazuje seznam výpůjček
│   │
│   ├── Views/
│   │   → "Grafici" - Připravují, jak stránky budou VYPADAT
│   │   │   (HTML šablony)
│   │   │
│   │   ├── books/
│   │   │   ├── catalog.php
│   │   │   │   → Katalog knih (mřížka s knihami, filtry)
│   │   │   │
│   │   │   └── detail.php
│   │   │       → Detail jedné knihy (velký obrázek, popis, tlačítko půjčit)
│   │   │
│   │   ├── user/
│   │   │   ├── profile.php
│   │   │   │   → Profil uživatele (jméno, email, kdy se registroval)
│   │   │   │
│   │   │   └── loans.php
│   │   │       → Seznam výpůjček (co má půjčené, kdy vrátit)
│   │   │
│   │   ├── auth/
│   │   │   ├── login.php
│   │   │   │   → Přihlašovací formulář
│   │   │   │
│   │   │   └── register.php
│   │   │       → Registrační formulář
│   │   │
│   │   └── admin/
│   │       └── dashboard.php
│   │           → Admin panel (tabulka knih, tlačítka přidat/upravit/smazat)
│   │
│   ├── Auth.php
│   │   → "Vrátný" - Kontroluje, kdo je přihlášený a kdo má práva
│   │
│   ├── Database.php
│   │   → "IT podpora" - Stará se o spojení s databází
│   │
│   ├── Router.php
│   │   → "GPS navigace" - Směruje požadavky na správná místa
│   │
│   ├── Cache.php
│   │   → "Archiv" - Ukládá často používaná data pro rychlejší přístup
│   │
│   └── helpers.php
│       → "Údržbář" - Malé pomocné funkce (ověřování emailu,
│         přesměrování, ochrana proti hackerům...)
│
├── public/
│   → "Výloha obchodu" - Jediná část, kterou vidí návštěvníci
│   │
│   ├── index.php
│   │   → Hlavní vstupní brána - Tady všechno začíná
│   │
│   ├── assets/
│   │   │
│   │   ├── css/
│   │   │   → Styly - Barvy, fonty, rozložení (jak stránky vypadají)
│   │   │   │
│   │   │   ├── style.css
│   │   │   │   → Hlavní styly pro celou aplikaci
│   │   │   │
│   │   │   ├── responsive.css
│   │   │   │   → Styly pro mobily a tablety
│   │   │   │
│   │   │   └── admin.css
│   │   │       → Styly specifické pro admin panel
│   │   │
│   │   └── js/
│   │       → JavaScript - Interaktivita (co se stane když klikneš na tlačítko)
│   │       │
│   │       ├── app.js
│   │       │   → Hlavní JS aplikace (search, filtry...)
│   │       │
│   │       ├── ajax.js
│   │       │   → Půjčování/vracení BEZ refreshe stránky
│   │       │
│   │       ├── pagination.js
│   │       │   → Nekonečné scrollování (načítání dalších knih)
│   │       │
│   │       ├── toast.js
│   │       │   → Zelené/červené notifikace ("Kniha půjčena!")
│   │       │
│   │       └── admin.js
│   │           → Admin funkce (vyhledávání v Google Books...)
│   │
│   ├── cache/
│   │   → Dočasné uložiště (API odpovědi, obrázky...)
│   │
│   ├── sitemap.php
│   │   → Generuje seznam všech stránek pro Google
│   │
│   └── robots.php
│       → Říká Google, co může indexovat
│
└── database/
    └── schema.sql
        → Plán databáze - Co všechno se ukládá a jak
          (jako nákres domu před stavbou)
```

---

## 🔐 Jak funguje zabezpečení?

Bezpečnost je KRITICKÁ, aby hackeři nemohli:
- Ukrást hesla uživatelů
- Smazat knihy z databáze
- Vydávat se za jiné uživatele

### 1. Hashování hesel

**Špatný způsob (NIKDY):**
```
Databáze:
petr123 | heslo: mujpes123  ← Viditelné všem!
```

**Správný způsob (BookLend):**
```
Databáze:
petr123 | heslo: $2y$10$zf2Dn6ejbY5UUvimTFZUguSoCA.VbW...  ← Nečitelné!
```

**Jak to funguje:**
1. Uživatel zadá heslo: `mujpes123`
2. Systém ho "promele" šifrovacím algoritmem (bcrypt)
3. Výsledek: `$2y$10$zf2Dn6ejbY5UUvimTFZUguSoCA.VbW...`
4. Tohle se uloží do databáze

**Při přihlášení:**
1. Uživatel zadá heslo: `mujpes123`
2. Systém ho promele stejným algoritmem
3. Porovná výsledek s databází
4. Pokud se shoduje → Správné heslo!

**Proč je to bezpečné:**
- Z hashe NELZE získat původní heslo zpátky (je to jednosměrné)
- Ani admin nevidí hesla uživatelů!
- Kdyby hacker ukradl databázi, hesla jsou nepoužitelná

### 2. Ochrana proti SQL injection

**Co je SQL injection?**
Hacker se pokusí "propašovat" svůj kód do databázového dotazu.

**Špatný příklad (zranitelný kód):**
```php
$email = $_POST['email'];  // Hacker zadá: admin@email.cz' OR '1'='1
$sql = "SELECT * FROM users WHERE email = '$email'";
// Výsledek: SELECT * FROM users WHERE email = 'admin@email.cz' OR '1'='1'
// → Přihlásí se jako první uživatel (admin!) bez hesla!
```

**BookLend používá "prepared statements":**
```php
$sql = "SELECT * FROM users WHERE email = ?";
$params = [$_POST['email']];
$db->query($sql, $params);
```

Systém AUTOMATICKY:
- Escapuje speciální znaky
- Validuje vstup
- Hacker nemůže vložit vlastní SQL kód

### 3. Ochrana proti XSS (Cross-Site Scripting)

**Co je XSS?**
Hacker se pokusí vložit JavaScript kód, který se spustí v prohlížeči jiných uživatelů.

**Špatný příklad:**
```php
// Uživatel zadá jméno: <script>alert('Hacked!')</script>
echo "Ahoj, " . $_POST['username'];
// → Spustí se JavaScript!
```

**BookLend používá htmlspecialchars():**
```php
echo "Ahoj, " . htmlspecialchars($_POST['username']);
// → Zobrazí se: Ahoj, &lt;script&gt;alert('Hacked!')&lt;/script&gt;
// (jako text, ne jako kód!)
```

### 4. Kontrola přístupu

**Middleware** = "Vrátný před dveřmi"

Když někdo chce jít na `/admin`:
1. Middleware zkontroluje: "Je přihlášený?"
   - NE → Pošli ho na `/login`
   - ANO → Pokračuj...
2. Middleware zkontroluje: "Je to admin?"
   - NE → Pošli ho na hlavní stránku
   - ANO → Pouštím dál!

**Bez middleware:**
Každý by mohl napsat `/admin` do prohlížeče a smazat všechny knihy!

**S middleware:**
Systém automaticky kontroluje práva PŘED zobrazením stránky.

---

## 🎨 Jak vypadá aplikace?

### Pro běžného uživatele

#### 1. Hlavní stránka (Katalog)
```
┌─────────────────────────────────────────────────────────┐
│ [Logo BookLend]    🔍 Hledat...    [Přihlásit] [Registrace] │
└─────────────────────────────────────────────────────────┘

┌─────────────┐  Filtry:  ☑ Fantasy  ☑ Sci-Fi  ☐ Detektivka
│             │           Rok: ☑ 2020  ☑ 2021  ☐ 2019
│   Filtry    │           Seřadit: [Nejnovější ▼]
│             │
│  [Použít]   │  ┌────────┐  ┌────────┐  ┌────────┐  ┌────────┐
└─────────────┘  │ 📖     │  │ 📖     │  │ 📖     │  │ 📖     │
                  │ 1984   │  │ Hobit  │  │ Duna   │  │ Metro  │
                  │ George │  │ J.R.R. │  │ Frank  │  │ Dmitry │
                  │ Orwell │  │ Tolkien│  │ Herbert│  │ Glukh. │
                  │        │  │        │  │        │  │        │
                  │🟢 3/5  │  │🟢 2/4  │  │🔴 0/2  │  │🟢 1/3  │
                  └────────┘  └────────┘  └────────┘  └────────┘

                  ... při scrollování dolů se načítají další knihy ...
```

#### 2. Detail knihy
```
┌───────────────────────────────────────────────────────┐
│                                                       │
│  ┌──────────────┐     1984                           │
│  │              │     George Orwell                   │
│  │   Obrázek    │                                     │
│  │   obálky     │     📚 Žánr: Fantasy                │
│  │   knihy      │     📅 Rok vydání: 1949             │
│  │   (HD)       │     🌍 Jazyk: cs                    │
│  │              │     📖 ISBN: 978-80-257-0706-3      │
│  └──────────────┘                                     │
│                       🟢 Dostupné: 2 z 5 kusů         │
│                                                       │
│  📝 Popis:                                            │
│  Winston Smith žije v dystopickém světě, kde         │
│  totalitní režim Strany sleduje každý jeho krok...   │
│                                                       │
│  [✅ Půjčit knihu]                                    │
│                                                       │
└───────────────────────────────────────────────────────┘
```

#### 3. Moje výpůjčky
```
┌───────────────────────────────────────────────────────┐
│  Moje výpůjčky                                        │
├───────────────────────────────────────────────────────┤
│                                                       │
│  📖 1984 - George Orwell                              │
│  Půjčeno: 14.11.2025  →  Vrátit do: 14.12.2025       │
│  Status: 🔵 Aktivní  |  Prodlouženo: 0×              │
│  [🔄 Prodloužit (+15 dní)] [Vrátit knihu]            │
│                                                       │
├───────────────────────────────────────────────────────┤
│                                                       │
│  📖 Hobit - J.R.R. Tolkien                            │
│  Půjčeno: 01.11.2025  →  Vráceno: 14.11.2025         │
│  Status: 🟢 Vráceno                                   │
│                                                       │
├───────────────────────────────────────────────────────┤
│                                                       │
│  📖 Duna - Frank Herbert                              │
│  Půjčeno: 20.10.2025  →  Vrátit do: 03.11.2025       │
│  Status: 🔴 Po splatnosti (6 týdnů)                  │
│  ⚠️ Penále: 600 000 Kč (6 týdnů × 100 000 Kč)       │
│  [Vrátit knihu]                                      │
│                                                       │
└───────────────────────────────────────────────────────┘
```

### Pro administrátora

#### Admin panel
```
┌───────────────────────────────────────────────────────┐
│  Admin panel  |  [+ Přidat knihu]  [Odhlásit se]     │
├───────────────────────────────────────────────────────┤
│                                                       │
│  Seřadit: [Název ▼]   Zobrazit: [20 na stránku ▼]    │
│                                                       │
│  ┌─────────────────────────────────────────────────┐ │
│  │ ID │ Název    │ Autor      │ Skladem │ Akce    │ │
│  ├────┼──────────┼────────────┼─────────┼─────────┤ │
│  │ 1  │ 1984     │ G. Orwell  │ 2/5     │ ✏️ ❌   │ │
│  │ 2  │ Hobit    │ J. Tolkien │ 2/4     │ ✏️ ❌   │ │
│  │ 3  │ Duna     │ F. Herbert │ 0/2     │ ✏️ ❌   │ │
│  └─────────────────────────────────────────────────┘ │
│                                                       │
│  ◄ Předchozí  [1] 2 3 4  Další ►                     │
│                                                       │
└───────────────────────────────────────────────────────┘
```

#### Přidání knihy přes Google Books
```
┌───────────────────────────────────────────────────────┐
│  Přidat knihu                              [✕ Zavřít] │
├───────────────────────────────────────────────────────┤
│                                                       │
│  🔍 [Harry Potter Kámen    ]  [Vyhledat v Google]    │
│                                                       │
│  Výsledky z Google Books:                             │
│  ┌─────────────────────────────────────────────────┐ │
│  │ ✅ Harry Potter a Kámen mudrců                   │ │
│  │    J.K. Rowling, 2000                            │ │
│  │    ISBN: 978-80-00-02590-8                       │ │
│  │    Žánr: Fantasy  |  Jazyk: cs                   │ │
│  │                                                   │ │
│  │    [📖 Náhled]  [➕ Přidat do katalogu]          │ │
│  └─────────────────────────────────────────────────┘ │
│                                                       │
│  Počet kusů celkem:     [5]                           │
│  Počet dostupných kusů: [5]                           │
│                                                       │
│  [✅ Potvrdit a přidat]                               │
│                                                       │
└───────────────────────────────────────────────────────┘
```

---

## 💡 Shrnutí klíčových funkcí

### Co dělá aplikaci DOBROU:

✅ **Automatizace**
- Počet dostupných kusů se upravuje SAMY (databázové triggery)
- Administrátor nemusí ručně opisovat data o knihách (Google Books API)
- Cache šetří čas a limity API

✅ **Flexibilní výpůjční systém**
- Standardní výpůjční doba: **30 dní**
- **Neomezený počet prodloužení** po 15 dnech (placená služba)
- Automatická kalkulace penále: **100 000 Kč za každý týden** po splatnosti
- Transparentní zobrazení podmínek před půjčením (vyskakovací okno)
- Sledování počtu prodloužení a historie změn splatnosti
- Možnost prodloužit i po splatnosti (po uhrazení penále)

✅ **Bezpečnost**
- Hesla jsou zašifrovaná (bcrypt)
- Ochrana proti SQL injection (prepared statements)
- Ochrana proti XSS (htmlspecialchars)
- Kontrola přístupu (middleware)

✅ **SEO optimalizace**
- Přátelské URL (`/kniha/harry-potter`)
- Meta tagy pro Google
- Sitemap pro indexaci
- Open Graph pro sociální sítě
- Structured data (JSON-LD)

✅ **Uživatelský zážitek**
- Responzivní design (funguje na mobilu i počítači)
- Rychlé vyhledávání (live search)
- Nekonečné scrollování (infinite scroll)
- Notifikace o akcích (toast messages)
- Filtry a řazení

✅ **Administrace**
- Snadné přidávání knih (Google Books API)
- Přehledná tabulka
- Editace a mazání
- Správa skladů

---

## 🚀 Jak to spustit?

### Co budeš potřebovat:

1. **XAMPP** (nebo jiný lokální server)
   - Obsahuje Apache (webový server) a MySQL (databázi)
   - Stáhni z: https://www.apachefriends.org/

2. **Web browser** (Chrome, Firefox...)

3. **Základní znalost ovládání počítače**

### Postup:

#### Krok 1: Nainstaluj XAMPP
- Stáhni a nainstaluj XAMPP
- Spusť "XAMPP Control Panel"
- Klikni "Start" u **Apache** a **MySQL**

#### Krok 2: Zkopíruj projektu
- Otevři složku `C:\xampp\htdocs\`
- Zkopíruj tam celou složku `booklend`

#### Krok 3: Vytvoř databázi
1. Otevři prohlížeč
2. Jdi na: `http://localhost/phpmyadmin`
3. Klikni "New" (vlevo nahoře)
4. Jméno databáze: `booklend`
5. Encoding: `utf8mb4_unicode_ci`
6. Klikni "Create"
7. Klikni na databázi `booklend` (vlevo)
8. Záložka "Import"
9. Vyber soubor `database/schema.sql` z projektu
10. Klikni "Go"

#### Krok 4: Nastav konfiguraci
- Otevři soubor `config.php` v textovém editoru
- Zkontroluj řádky:
  ```php
  define('DB_HOST', 'localhost');     ← Ponech
  define('DB_NAME', 'booklend');      ← Ponech
  define('DB_USER', 'root');          ← Ponech
  define('DB_PASS', '');              ← Ponech prázdné (XAMPP default)

  define('BASE_URL', 'http://localhost/booklend');  ← Zkontroluj
  ```

#### Krok 5: Otevři aplikaci
- V prohlížeči jdi na: `http://localhost/booklend`
- Měl bys vidět katalog knih!

#### Krok 6: Přihlaš se jako admin
- Klikni "Přihlásit se"
- Email: `admin@booklend.cz`
- Heslo: `Start321`
- Teď máš přístup do admin panelu na: `http://localhost/booklend/admin`

---

## 🎓 Pro koho je tahle aplikace?

### Pro studenty
- Naučíš se, jak funguje webová aplikace od A do Z
- Pochopíš koncepty jako MVC, API, databáze, autentizace
- Můžeš to použít jako základ pro svůj projekt

### Pro začínající vývojáře
- Real-world projekt s best practices
- Dobře dokumentovaný kód
- Vidíš, jak se věci dělají "správně"

### Pro knihovny
- Můžeš to upravit a použít ve skutečné knihovně
- Přidat platební bránu pro poplatky
- Přidat notifikace emailem

### Pro nadšence
- Projekt je open source
- Můžeš přidávat vlastní funkce
- Experimentovat a učit se

---

## ❓ Časté otázky

### Musím umět programovat?
Abys aplikaci POUŽÍVAL - ne.
Abys jí UPRAVOVAL - ano, základy PHP, HTML, SQL.

### Je to zdarma?
Ano, projekt je open source.

### Můžu to použít pro svůj projekt?
Ano, klidně! Můžeš to upravit podle svých potřeb.

### Co když najdu chybu?
Super! Napiš issue na GitHubu nebo to opravu sám a pošli pull request.

### Jak přidám novou funkci?
Záleží na funkci - ale dokumentace ti ukáže, kde co najdeš.

### Potřebuju platit za Google Books API?
Google má free tier (1000 požadavků/den), což stačí pro malé projekty.

---

## 📚 Závěr

**BookLend** je kompletní webová aplikace, která ukazuje, jak funguje moderní web:

- 🗄 **Databáze** ukládá data
- 🔐 **Autentizace** chrání uživatelské účty
- 🌐 **API** komunikuje s vnějším světem (Google Books)
- 🎨 **Frontend** zobrazuje krásné rozhraní
- 🔍 **SEO** pomáhá lidem najít web na Googlu
- ⚡ **Cache** zrychluje načítání
- 🔒 **Bezpečnost** chrání před hackery

Je to jako malá firma, kde každá část má svou roli a všechno spolupracuje, aby uživatel měl skvělý zážitek.

Teď už víš, jak to celé funguje - od kliknutí na tlačítko až po uložení dat do databáze!

---

**Vytvořil:** Claude & váš tým
**Verze:** 1.0
**Licence:** Open Source
**Web:** http://localhost/booklend
