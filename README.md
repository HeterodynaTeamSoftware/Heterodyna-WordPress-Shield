# Heterodyna WordPress Shield 🛡️

**Open-source WordPress hardening and technical SEO plugin / Otwarta wtyczka do zabezpieczeń i technicznego SEO WordPress.**

## Polski

Wtyczka oferuje konfigurację wybranych nagłówków HTTP (HSTS, CSP, Permissions-Policy, Referrer-Policy, nosniff, X-Frame-Options), prostą kontrolę XML-RPC, dostępności tras REST users/oEmbed, ograniczenie prostego skanowania `?author=`, usunięcie części metadanych WordPress oraz tagi meta description, Open Graph i Twitter Cards.

**Ostrożnie:** HSTS, CSP i blokowanie tras REST są domyślnie wyłączone przy nowej instalacji. Włączenie HSTS wymaga poprawnego HTTPS również na wszystkich subdomenach, jeśli używasz `includeSubDomains`. CSP może blokować zasoby. Domyślna proponowana CSP jest kompatybilnościowa i **nie** zapewnia rygorystycznej ochrony XSS. Ukrywanie informacji o WordPressie nie jest pełną ochroną przed skanerami. Nagłówki ustawiane przez PHP nie zastępują konfiguracji serwera/proxy; mogą być nadpisane.

**Instalacja:** pobierz paczkę `heterodyna-wordpress-shield-v1.0.0.zip` z GitHub Releases, w WordPress wybierz Wtyczki → Dodaj nową → Wyślij wtyczkę, następnie aktywuj i przejrzyj Ustawienia → Heterodyna WordPress Shield. Nie włączaj jednocześnie dawnej Heterodyna SEO Tarcza (duplikowane nagłówki i metadane). Ustawienia dawnej wtyczki są zachowane przez zgodny klucz opcji, w tym wcześniejsze wartości HSTS/CSP — **sprawdź je po aktualizacji**.

Wtyczka nie konfiguruje DMARC, SPF/DKIM ani serwera WWW. Nie gwarantuje wyniku SEO ani odporności na ataki. Jeśli używasz innego pluginu SEO, wyłącz dublujące się tagi.

## English

The plugin adds configurable HTTP headers (HSTS, CSP, Permissions-Policy and basic security headers), disables XML-RPC methods, can remove selected users/oEmbed REST endpoints, discourages simple `?author=` enumeration, removes some WordPress discovery tags, and outputs basic meta description/Open Graph/Twitter Card tags.

**Safety:** HSTS, CSP and REST-route blocking are disabled by default on fresh installs. Verify HTTPS and subdomains before enabling HSTS. Test CSP on a staging site: the sample policy prioritizes compatibility and includes `unsafe-inline`/`unsafe-eval`; it is not a strict XSS defense. Obscuring WordPress metadata is not a firewall, and PHP headers do not replace server/reverse-proxy configuration. Existing settings from the old plugin are preserved and may override these defaults; review them after upgrading.

**Install:** upload `heterodyna-wordpress-shield-v1.0.0.zip` via Plugins → Add New → Upload Plugin, activate, then review Settings → Heterodyna WordPress Shield. Disable the previous Heterodyna SEO Tarcza plugin to avoid duplicated headers/meta tags. Other SEO plugins may also duplicate metadata.

License: **GPL-2.0-or-later**. Author: Heterodyna Team Software.

## Development / Rozwój

Source entrypoint: `heterodyna-wordpress-shield/heterodyna-wordpress-shield.php`. No license key or activation server is needed. This package contains no external runtime dependencies. Tested: PHP syntax lint. WordPress integration/end-to-end compatibility is **not yet verified**. See `SECURITY.md` and `CHANGELOG.md`.

## Support the project / Wsparcie projektu 💙

**English:** If this project is useful to you, you can voluntarily support its development. Thank you for every contribution! Donations help fund further development, testing and maintenance. No payment is required to use any feature.

**Polski:** Jeśli projekt jest dla Ciebie przydatny, możesz dobrowolnie wesprzeć jego rozwój. Dziękujemy za każdą wpłatę! Wsparcie pomaga w dalszym rozwoju, testowaniu i utrzymaniu projektu. Żadna funkcja nie wymaga wpłaty.

☕ **Revolut:** https://revolut.me/adrian00qj

**Payment title / Tytuł płatności / Назначение платежа:** `Heterodyna WordPress Shield`
