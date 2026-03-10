# STEW Webshop — Site Configuration Guide

Vollstaendige Konfigurationsanleitung fuer den STEW Webshop (WordPress + WooCommerce + Salient Theme).

---

## 1. SEO-Konfiguration (Yoast SEO)

### 1.1 Permalink-Struktur

WordPress > Einstellungen > Permalinks:

```
Beitragsname: /%postname%/
```

WooCommerce-Permalinks (unter "Optionale Einstellungen"):
- Produktbasis: `/produkt/`
- Kategoriebasis: `/produktkategorie/`
- Tag-Basis: `/produkt-tag/`

### 1.2 Allgemeine SEO-Einstellungen

**Yoast SEO > Allgemein > Funktionen:**
- XML-Sitemaps: Aktiviert
- SEO-Analyse: Aktiviert
- Lesbarkeitsanalyse: Aktiviert
- Erweiterte Einstellungen: Aktiviert
- Breadcrumbs: Aktiviert
- Open Graph: Aktiviert

**Yoast SEO > Darstellung in der Suche:**

Allgemein:
- Titeltrennzeichen: `|`
- Startseiten-Titel: `STEW | Professionelle LED-Beleuchtung Schweiz`
- Startseiten-Meta-Beschreibung: `STEW - Ihr Fachhaendler fuer professionelle LED-Treiber, Netzteile und Beleuchtungsloesungen in der Schweiz. Grosse Auswahl, faire Preise, schneller Versand.`

### 1.3 Meta-Titel-Vorlagen

```
Beitraege:         %%title%% | STEW Blog
Seiten:            %%title%% | STEW
Produkte:          %%title%% | LED-Beleuchtung kaufen | STEW
Produktkategorien: %%term_title%% | LED-Produkte | STEW
Produkttags:       %%term_title%% | STEW
```

### 1.4 Meta-Beschreibungs-Vorlagen

```
Beitraege:         %%excerpt%% — Mehr erfahren auf dem STEW Blog.
Seiten:            %%excerpt%%
Produkte:          %%excerpt%% — Jetzt bei STEW bestellen. Schweizer Fachhandel fuer LED-Beleuchtung.
Produktkategorien: %%term_title%% bei STEW kaufen. Grosse Auswahl an professionellen LED-Produkten. Schweizer Qualitaet, schneller Versand.
```

### 1.5 Breadcrumbs

Yoast SEO > Darstellung in der Suche > Breadcrumbs:

```
Trennzeichen:        >
Startseiten-Ankertext: Startseite
Breadcrumbs aktivieren: Ja
```

PHP-Code im Theme (bereits in den Templates integriert):
```php
<?php
if ( function_exists( 'yoast_breadcrumb' ) ) {
    yoast_breadcrumb( '<nav class="stew-breadcrumbs">', '</nav>' );
}
?>
```

### 1.6 Open Graph & Social Media

Yoast SEO > Social:

- Facebook Open Graph: Aktiviert
- Standard-Bild: STEW-Logo oder Banner-Bild (mind. 1200x630px)
- Twitter Cards: Aktiviert
- Card-Typ: Grosses Bild (summary_large_image)

### 1.7 Sitemap

Automatisch unter: `https://stew.ch/sitemap_index.xml`

Einstellungen:
- Beitraege: Eingeschlossen
- Seiten: Eingeschlossen
- Produkte: Eingeschlossen
- Produktkategorien: Eingeschlossen
- Medien: Ausgeschlossen
- Tags: Ausgeschlossen (wenn wenig Inhalt)

### 1.8 robots.txt

```
User-agent: *
Allow: /
Disallow: /wp-admin/
Disallow: /warenkorb/
Disallow: /kasse/
Disallow: /mein-konto/
Allow: /wp-admin/admin-ajax.php

Sitemap: https://stew.ch/sitemap_index.xml
```

---

## 2. Sicherheit

### 2.1 Wordfence Einstellungen

**Firewall:**
- Web Application Firewall (WAF): Aktiviert
- Modus: "Learning Mode" fuer 1 Woche, dann "Enabled and Protecting"
- Brute-Force-Schutz: Aktiviert
  - Anmeldeversuche sperren nach: 5 Fehlversuchen
  - Sperre nach: 20 fehlgeschlagenen Passwortversuchen
  - Sperrzeit: 60 Minuten
  - Sofort sperren bei ungueltigem Benutzernamen: Ja

**Scan:**
- Scan-Zeitplan: Taeglich
- Scan-Umfang: Standard + erweitert
- E-Mail-Benachrichtigungen bei Problemen: Ja
- Dateien ausserhalb der WordPress-Installation pruefen: Nein

**Login Security:**
- Zwei-Faktor-Authentifizierung (2FA): Aktiviert fuer Administratoren
- reCAPTCHA auf Login-Seite: Aktiviert (v3)

**Empfohlene Konfiguration:**
```
Wordfence > Alle Optionen > General Options:
- Wo sollen E-Mails zugestellt werden: info@stew.ch
- Benachrichtigungen bei:
  - IP gesperrt: Nein (zu viele E-Mails)
  - Neuer Admin-Login: Ja
  - Scan-Ergebnisse: Ja
```

### 2.2 SSL-Konfiguration

SSL ist Pflicht. Ueber den Hosting-Provider (EasyPanel/Lightsail) oder Let's Encrypt:

```
# .htaccess SSL-Weiterleitung (falls nicht automatisch)
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

### 2.3 Dateiberechtigungen

```bash
# WordPress-Verzeichnisse
find /var/www/html -type d -exec chmod 755 {} \;

# WordPress-Dateien
find /var/www/html -type f -exec chmod 644 {} \;

# wp-config.php (besonders schuetzen)
chmod 440 /var/www/html/wp-config.php

# .htaccess
chmod 644 /var/www/html/.htaccess

# wp-content/uploads (Schreibzugriff fuer Medien)
chmod 775 /var/www/html/wp-content/uploads
```

### 2.4 wp-config.php Sicherheitskonstanten

Diese Konstanten in `wp-config.php` hinzufuegen (VOR `/* That's all, stop editing! */`):

```php
/**
 * Sicherheitskonstanten — STEW
 */

// Dateibearbeitung im Admin deaktivieren
define( 'DISALLOW_FILE_EDIT', true );

// Automatische Updates fuer Minor-Versionen
define( 'WP_AUTO_UPDATE_CORE', 'minor' );

// SSL fuer Admin erzwingen
define( 'FORCE_SSL_ADMIN', true );

// Sicherheitsschluessel (von https://api.wordpress.org/secret-key/1.1/salt/ generieren)
// define( 'AUTH_KEY',         'einzigartiger-schluessel-hier' );
// define( 'SECURE_AUTH_KEY',  'einzigartiger-schluessel-hier' );
// define( 'LOGGED_IN_KEY',    'einzigartiger-schluessel-hier' );
// define( 'NONCE_KEY',        'einzigartiger-schluessel-hier' );
// define( 'AUTH_SALT',        'einzigartiger-schluessel-hier' );
// define( 'SECURE_AUTH_SALT', 'einzigartiger-schluessel-hier' );
// define( 'LOGGED_IN_SALT',   'einzigartiger-schluessel-hier' );
// define( 'NONCE_SALT',       'einzigartiger-schluessel-hier' );

// Revisionen begrenzen (Performance)
define( 'WP_POST_REVISIONS', 5 );

// Papierkorb automatisch leeren (Tage)
define( 'EMPTY_TRASH_DAYS', 14 );

// Debug-Modus (nur in Entwicklung aktivieren!)
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );

// Speicherlimit erhoehen
define( 'WP_MEMORY_LIMIT', '256M' );
define( 'WP_MAX_MEMORY_LIMIT', '512M' );

// Cron deaktivieren fuer Server-Cron (empfohlen fuer EasyPanel)
// define( 'DISABLE_WP_CRON', true );
// Server-Cron einrichten: */5 * * * * curl -s https://stew.ch/wp-cron.php > /dev/null 2>&1

// Datenbank-Tabellenpräfix (bei Installation festgelegt)
// $table_prefix = 'stew_';
```

### 2.5 HTTP-Sicherheitsheader

In `.htaccess` oder ueber ein Plugin (z.B. "Headers Security Advanced & HSTS WP"):

```apache
<IfModule mod_headers.c>
    # Strict Transport Security
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"

    # Content Security Policy (anpassen!)
    # Header set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://js.stripe.com https://www.google.com https://www.gstatic.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; frame-src https://js.stripe.com https://www.google.com;"

    # X-Content-Type-Options
    Header set X-Content-Type-Options "nosniff"

    # X-Frame-Options
    Header set X-Frame-Options "SAMEORIGIN"

    # Referrer Policy
    Header set Referrer-Policy "strict-origin-when-cross-origin"

    # Permissions Policy
    Header set Permissions-Policy "camera=(), microphone=(), geolocation=()"
</IfModule>
```

---

## 3. Performance

### 3.1 Redis Object Cache

Fuer EasyPanel/Lightsail mit Docker-basiertem Setup:

**Redis installieren (Docker-Compose Ergaenzung):**
```yaml
redis:
  image: redis:7-alpine
  container_name: stew-redis
  restart: always
  command: redis-server --maxmemory 128mb --maxmemory-policy allkeys-lru
  volumes:
    - redis_data:/data
  networks:
    - stew_network
```

**WordPress-Plugin:** Redis Object Cache (Till Kruess)

**wp-config.php Einstellungen:**
```php
// Redis Object Cache
define( 'WP_REDIS_HOST', 'stew-redis' );  // Docker-Service-Name
define( 'WP_REDIS_PORT', 6379 );
define( 'WP_REDIS_PREFIX', 'stew_' );
define( 'WP_REDIS_DATABASE', 0 );
define( 'WP_REDIS_TIMEOUT', 1 );
define( 'WP_REDIS_READ_TIMEOUT', 1 );
define( 'WP_REDIS_MAXTTL', 86400 );
```

**Aktivierung:**
1. Plugin "Redis Object Cache" installieren und aktivieren
2. Unter Einstellungen > Redis: "Object Cache aktivieren" klicken
3. Status pruefen: Sollte "Connected" anzeigen

### 3.2 Seiten-Caching

**Empfohlenes Plugin:** WP Super Cache oder W3 Total Cache

**WP Super Cache Einstellungen:**
```
Caching: Ein (empfohlen)
Cache-Methode: mod_rewrite (schnellste)
Komprimierung: Aktiviert
304-Header: Aktiviert
Cache fuer bekannte Benutzer: Deaktiviert (wichtig fuer rollenbasierte Preise!)
Cache-Timeout: 3600 Sekunden

Erweitert:
- Seiten mit GET-Parametern nicht cachen
- Mobilgeraete extra cachen: Nein (Responsive Design)
```

**WICHTIG fuer rollenbasierte Preise:**
Da verschiedene Benutzerrollen unterschiedliche Preise sehen, muss das Seiten-Caching fuer eingeloggte Benutzer deaktiviert sein. Redis Object Cache ist fuer diesen Use Case besser geeignet als Seiten-Caching.

```php
// In wp-config.php: Cache fuer eingeloggte Benutzer deaktivieren
define( 'DONOTCACHEPAGE', is_user_logged_in() );
```

### 3.3 Bildoptimierung

**Empfohlenes Plugin:** ShortPixel Image Optimizer oder Imagify

**ShortPixel Einstellungen:**
```
Komprimierungstyp: Verlustbehaftet (Lossy) — bestes Verhaeltnis
WebP-Konvertierung: Aktiviert
AVIF-Konvertierung: Aktiviert (falls vom Server unterstuetzt)
Bestehende Bilder optimieren: Ja (Bulk-Optimierung nach Installation)
Thumbnail-Optimierung: Aktiviert
Originalbild beibehalten: Ja (Backup)
Maximale Bildgroesse: 2560px (groessere Bilder automatisch verkleinern)
EXIF-Daten entfernen: Ja
```

**WordPress Bildgroessen (Einstellungen > Medien):**
```
Vorschaubild:  150 x 150 (Zuschnitt)
Mittel:        450 x 450
Gross:         1024 x 1024
```

**WooCommerce Bildgroessen (WooCommerce > Einstellungen > Produkte):**
```
Produktbild:       800 x 800
Katalogbild:       450 x 450
Vorschau:          150 x 150
Bildverhaeltnis:   1:1 (quadratisch)
```

### 3.4 Lazy Loading

WordPress (ab 5.5) hat natives Lazy Loading integriert.

Zusaetzlich in `functions.php`:
```php
// Native Lazy Loading sicherstellen
add_filter( 'wp_lazy_loading_enabled', '__return_true' );

// Lazy Loading fuer iframes
add_filter( 'wp_iframe_tag_add_loading_attr', '__return_true' );
```

### 3.5 Datenbank-Optimierung

**Empfohlenes Plugin:** WP-Optimize

```
Einstellungen:
- Beitragsrevisionen bereinigen: Aktiviert
- Auto-Entwuerfe bereinigen: Aktiviert
- Papierkorb leeren: Aktiviert
- Spam-Kommentare loeschen: Aktiviert
- Transients bereinigen: Aktiviert
- Datenbanktabellen optimieren: Woechentlich
```

### 3.6 CDN-Ueberlegungen fuer den Schweizer Markt

Fuer einen primaer Schweizer/DACH-Markt:

**Option A: Kein CDN noetig**
- Bei einem AWS Lightsail-Server in `eu-central-1` (Frankfurt) ist die Latenz in die Schweiz bereits sehr gering (~5-10ms)
- Ein CDN bringt fuer statische Inhalte nur marginale Verbesserungen
- Einfachere Konfiguration und weniger Fehlerquellen

**Option B: Cloudflare (kostenlos)**
Falls CDN gewuenscht:
```
Cloudflare Einstellungen:
- SSL/TLS: Full (strict)
- Auto Minify: JavaScript, CSS, HTML
- Brotli: Aktiviert
- Caching Level: Standard
- Browser Cache TTL: 1 Monat
- Always Use HTTPS: Aktiviert
- Page Rules:
  - /wp-admin/* → Cache Level: Bypass
  - /mein-konto/* → Cache Level: Bypass
  - /warenkorb/* → Cache Level: Bypass
  - /kasse/* → Cache Level: Bypass
```

**WICHTIG:** Bei Verwendung von Cloudflare die echte IP-Adresse des Besuchers in WordPress wiederherstellen (Plugin "Cloudflare" oder Server-Konfiguration).

### 3.7 GZIP/Brotli-Komprimierung

```apache
# .htaccess
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE text/javascript
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/json
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE image/svg+xml
    AddOutputFilterByType DEFLATE font/woff
    AddOutputFilterByType DEFLATE font/woff2
</IfModule>
```

### 3.8 Browser-Caching

```apache
# .htaccess
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType image/avif "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/x-icon "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType font/woff "access plus 1 year"
    ExpiresByType font/woff2 "access plus 1 year"
    ExpiresByType application/font-woff "access plus 1 year"
    ExpiresByType application/font-woff2 "access plus 1 year"
</IfModule>
```

---

## 4. E-Mail-Konfiguration

### 4.1 SMTP-Setup

**Empfohlenes Plugin:** WP Mail SMTP oder FluentSMTP

**Konfiguration mit Brevo (ehemals Sendinblue) — empfohlen fuer Schweizer E-Mails:**

```
SMTP-Host:     smtp-relay.brevo.com
SMTP-Port:     587
Verschluesselung: TLS
Authentifizierung: Ja
Benutzername:  [Brevo API-Login]
Passwort:      [Brevo API-Schluessel]

Absender-Name:    STEW GmbH
Absender-E-Mail:  info@stew.ch
Return-Path:      info@stew.ch
```

**Alternative: Amazon SES (fuer Lightsail)**
```
SMTP-Host:     email-smtp.eu-central-1.amazonaws.com
SMTP-Port:     587
Verschluesselung: TLS
Benutzername:  [SES SMTP-Benutzername]
Passwort:      [SES SMTP-Passwort]
```

### 4.2 DNS-Eintraege fuer E-Mail-Zustellung

Beim Domain-Provider folgende DNS-Eintraege setzen:

```
# SPF-Eintrag
TXT  @  v=spf1 include:brevo.com include:amazonses.com ~all

# DKIM (vom SMTP-Provider generiert)
TXT  brevo._domainkey  [DKIM-Schluessel von Brevo]

# DMARC
TXT  _dmarc  v=DMARC1; p=quarantine; rua=mailto:dmarc@stew.ch; pct=100
```

### 4.3 WooCommerce E-Mail-Anpassung

Die E-Mail-Einstellungen sind in `woocommerce-settings.json` definiert.

**Farbschema (passend zum STEW-Design):**
```
Basisfarbe:          #1a1a2e (Dunkelblau)
Hintergrund:         #0f0f1a (Sehr dunkel)
Body-Hintergrund:    #1a1a2e (Dunkelblau)
Textfarbe:           #e0e0e0 (Hellgrau)
Header-Hintergrund:  #0f0f1a (Sehr dunkel)
Header-Textfarbe:    #00d4ff (Cyan/Blau — STEW Akzentfarbe)
Link-Farbe:          #00d4ff (Cyan/Blau)
```

**E-Mail-Fusszeile:**
```
STEW GmbH — Professionelle LED-Beleuchtung
{site_url}
```

### 4.4 E-Mail-Vorlagen ueberschreiben

Um die WooCommerce-E-Mail-Vorlagen anzupassen, Dateien aus:
```
wp-content/plugins/woocommerce/templates/emails/
```
kopieren nach:
```
wp-content/themes/stew-child/woocommerce/emails/
```

Wichtige Vorlagen:
- `email-header.php` — Logo und Header-Bereich
- `email-footer.php` — Fusszeile
- `customer-processing-order.php` — Bestellbestaetigung
- `customer-completed-order.php` — Versandbestaetigung

### 4.5 Test-E-Mails

Nach der SMTP-Konfiguration:
1. WP Mail SMTP > Werkzeuge > E-Mail-Test
2. Test-E-Mail an eigene Adresse senden
3. Pruefen: Zustellung, Absender, SPF/DKIM-Pruefung
4. WooCommerce-Test: Testbestellung aufgeben und E-Mails pruefen

---

## 5. Checkliste vor dem Go-Live

### 5.1 SEO
- [ ] Permalink-Struktur konfiguriert
- [ ] Yoast SEO installiert und konfiguriert
- [ ] Meta-Titel/Beschreibungen fuer wichtige Seiten gesetzt
- [ ] XML-Sitemap generiert und in Google Search Console eingereicht
- [ ] robots.txt korrekt
- [ ] Open Graph / Social Media konfiguriert

### 5.2 Sicherheit
- [ ] SSL-Zertifikat aktiv (HTTPS ueberall)
- [ ] Wordfence installiert und konfiguriert
- [ ] wp-config.php Sicherheitskonstanten gesetzt
- [ ] Dateiberechtigungen korrekt
- [ ] Admin-Benutzername ist NICHT "admin"
- [ ] Starke Passwoerter fuer alle Admin-Konten
- [ ] 2FA fuer Administratoren aktiviert
- [ ] HTTP-Sicherheitsheader konfiguriert

### 5.3 Performance
- [ ] Redis Object Cache aktiv
- [ ] Bildoptimierung (ShortPixel/Imagify) konfiguriert
- [ ] GZIP-Komprimierung aktiv
- [ ] Browser-Caching konfiguriert
- [ ] Lazy Loading aktiv
- [ ] Google PageSpeed Insights: Mobile > 70, Desktop > 85

### 5.4 E-Mail
- [ ] SMTP konfiguriert (nicht PHP mail())
- [ ] SPF/DKIM/DMARC DNS-Eintraege gesetzt
- [ ] Test-E-Mail erfolgreich zugestellt
- [ ] WooCommerce-E-Mails getestet (Bestellung, Versand, etc.)
- [ ] Kontaktformular-E-Mails getestet

### 5.5 WooCommerce
- [ ] Waehrung CHF korrekt konfiguriert
- [ ] Steuersaetze (MwSt. 8.1%) eingerichtet
- [ ] Versandzonen und -kosten konfiguriert
- [ ] Zahlungsmethoden getestet (Stripe, PayPal, Bankueberweisung)
- [ ] Testbestellung durchgefuehrt
- [ ] AGB- und Datenschutz-Seite verlinkt
- [ ] E-Mail-Vorlagen auf Deutsch angepasst

### 5.6 Rechtliches (Schweiz)
- [ ] Impressum vollstaendig
- [ ] Datenschutzerklaerung (inkl. Cookies, Analytics)
- [ ] AGB vorhanden
- [ ] Widerrufsbelehrung (falls anwendbar)
- [ ] Cookie-Banner (z.B. Complianz oder Borlabs Cookie)
- [ ] Preise inkl. MwSt. angezeigt
- [ ] Versandkosten klar kommuniziert
