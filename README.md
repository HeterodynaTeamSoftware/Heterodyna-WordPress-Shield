# Heterodyna WordPress Shield 🛡️

**Heterodyna WordPress Shield** to otwarta wtyczka do WordPressa, która pomaga uporządkować podstawowe zabezpieczenia strony i kilka elementów technicznego SEO. Nie próbuje zastępować firewalla, konfiguracji serwera ani pełnego pakietu SEO. Jej zadaniem jest zebranie w jednym miejscu ustawień, które często i tak trzeba konfigurować osobno.

ENGLISH

Heterodyna WordPress Shield is an open-source WordPress plugin for basic website hardening and technical SEO. It is not meant to replace a firewall, server configuration or a full SEO suite. The idea is to keep several commonly used security and metadata settings in one place.

## Polski

Wtyczka pozwala ustawić najważniejsze nagłówki HTTP związane z bezpieczeństwem, w tym HSTS, Content Security Policy, Permissions-Policy, Referrer-Policy, `X-Content-Type-Options` i `X-Frame-Options`.

Może też ograniczyć część typowych metod rozpoznawania instalacji WordPressa. Dotyczy to między innymi XML-RPC, wybranych tras REST API oraz prostych prób wykrywania nazw użytkowników przez adresy takie jak `?author=1`, `?author=2` itd. Nie oznacza to wyłączania autorów wpisów — chodzi tylko o utrudnienie botom automatycznego odgadywania nazw kont.

Wtyczka potrafi również usunąć część zbędnych informacji generowanych przez WordPress oraz dodać podstawowe meta description, Open Graph i Twitter Cards. Jeżeli korzystasz już z osobnej wtyczki SEO, warto wyłączyć dublujące się opcje, żeby nie generować tych samych metadanych dwa razy.

Niektóre ustawienia wymagają ostrożności. HSTS powinno być włączane tylko wtedy, gdy strona działa poprawnie po HTTPS. Jeśli używasz `includeSubDomains`, HTTPS musi działać również na subdomenach. CSP również warto najpierw sprawdzić na stronie testowej, bo zbyt restrykcyjna polityka może zablokować skrypty, style, fonty albo inne zasoby.

Przykładowa polityka CSP dostępna we wtyczce została przygotowana z myślą o kompatybilności, dlatego nie należy traktować jej jako pełnej ochrony przed XSS. Podobnie ukrywanie informacji o WordPressie utrudnia proste skanowanie, ale nie sprawia, że instalacja staje się niewidoczna dla bardziej zaawansowanych narzędzi.

Nagłówki wysyłane przez PHP mogą też zostać zmienione albo nadpisane przez serwer WWW, reverse proxy, CDN lub inną wtyczkę. Jeżeli masz możliwość ustawienia ich bezpośrednio w nginx, Apache albo Cloudflare, konfiguracja po stronie serwera zwykle ma pierwszeństwo.

### Instalacja

Pobierz `heterodyna-wordpress-shield-v1.0.0.zip` z GitHub Releases, a następnie w WordPress przejdź do:

**Wtyczki → Dodaj nową → Wyślij wtyczkę**

Po aktywacji otwórz:

**Ustawienia → Heterodyna WordPress Shield**

Jeżeli wcześniej korzystałeś z **Heterodyna SEO Tarcza**, wyłącz starą wtyczkę, żeby nie dublować nagłówków i metadanych. Ustawienia starej wersji są zachowywane przez zgodny klucz opcji, dlatego po aktualizacji warto je przejrzeć — szczególnie HSTS i CSP.

Wtyczka nie konfiguruje SPF, DKIM, DMARC ani samego serwera WWW. Nie gwarantuje konkretnego wyniku SEO i nie należy traktować jej jako pełnego systemu ochrony przed atakami.

---

## English

**Heterodyna WordPress Shield** is an open-source WordPress plugin for basic website hardening and technical SEO. It is not meant to replace a firewall, server configuration or a full SEO suite. The idea is to keep several commonly used security and metadata settings in one place.

The plugin can configure common HTTP security headers such as HSTS, Content Security Policy, Permissions-Policy, Referrer-Policy, `X-Content-Type-Options` and `X-Frame-Options`.

It can also reduce some of the simpler ways bots identify information about a WordPress installation. This includes XML-RPC controls, selected REST API endpoints and basic username enumeration attempts using URLs such as `?author=1`, `?author=2` and similar requests. This does not disable post authors — it only makes automated discovery of account names harder.

The plugin can remove some unnecessary WordPress metadata and provide basic meta description, Open Graph and Twitter Card tags. If you already use another SEO plugin, you should disable overlapping options to avoid generating duplicate metadata.

Some settings need to be enabled carefully. HSTS should only be used when HTTPS is working correctly. If `includeSubDomains` is enabled, HTTPS must also work on every affected subdomain. CSP should be tested before enabling it on a production site because an incorrect policy can block scripts, styles, fonts or other resources.

The sample CSP included with the plugin is intentionally compatibility-focused and should not be treated as a strict XSS defense. Hiding WordPress metadata may reduce simple automated scanning, but it does not make a WordPress installation invisible to more advanced scanners.

Headers set by PHP may also be changed or overridden by a web server, reverse proxy, CDN or another plugin. Where possible, server-level configuration should still be treated as the primary source of security headers.

### Installation

Download `heterodyna-wordpress-shield-v1.0.0.zip` from GitHub Releases and install it through:

**Plugins → Add New → Upload Plugin**

After activation, open:

**Settings → Heterodyna WordPress Shield**

If you previously used **Heterodyna SEO Tarcza**, disable the old plugin to avoid duplicate headers and metadata. Existing settings are preserved through the compatible option key, so review them after upgrading, especially HSTS and CSP values.

The plugin does not configure SPF, DKIM, DMARC or the web server itself. It does not guarantee SEO results and should not be treated as a complete security system.

---

## Development / Rozwój

Główny plik wtyczki znajduje się tutaj:

`heterodyna-wordpress-shield/heterodyna-wordpress-shield.php`

Wtyczka nie wymaga klucza licencyjnego, serwera aktywacji ani zewnętrznych zależności do uruchomienia.

Kod przeszedł kontrolę składni PHP. Pełne testy integracyjne z WordPressem i zgodność ze wszystkimi konfiguracjami serwerów nie zostały jeszcze zakończone.

Więcej informacji znajduje się w:

- `SECURITY.md`
- `CHANGELOG.md`

License: **GPL-2.0-or-later**

Author: **Heterodyna Team Software**  
https://heterodynateamsoftware.pl

### English

The main plugin file is located here:

`heterodyna-wordpress-shield/heterodyna-wordpress-shield.php`

The plugin does not require a license key, an activation server, or any external runtime dependencies.

The code has passed a PHP syntax check. Full WordPress integration testing and compatibility testing across different server configurations have not yet been completed.

More information is available in:

- `SECURITY.md`
- `CHANGELOG.md`

License: **GPL-2.0-or-later**

Author: **Heterodyna Team Software**  
https://heterodynateamsoftware.pl

## Support the project / Wsparcie projektu 💙

If the project is useful to you, you can support its further development voluntarily. Donations help with testing, maintenance and future releases. Every feature remains available without payment.

Jeśli projekt jest dla Ciebie przydatny, możesz dobrowolnie wesprzeć jego dalszy rozwój. Wsparcie pomaga finansować testy, utrzymanie i kolejne wersje. Wszystkie funkcje pozostają dostępne bez żadnych opłat.

☕ **Revolut:** https://revolut.me/adrian00qj

**Payment title / Tytuł płatności:** `Heterodyna WordPress Shield`
