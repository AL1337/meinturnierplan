=== MeinTurnierplan ===
Contributors: meinturnierplan, ramzesimus
Tags: tournament, sports, table, matches, standings
Requires at least: 6.3
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Zeigen Sie Turniertabellen und Spiellisten mit benutzerdefinierten Beitragstypen an, unterstützt Gutenberg-Blöcke, Widgets und Shortcodes.

== Beschreibung ==

MeinTurnierplan ermöglicht es Ihnen, Turniertabellen und Spielpläne von meinturnierplan.de auf Ihrer WordPress-Website anzuzeigen. Perfekt für Sportvereine, Ligen und Turnierveranstalter, die Tabellenstände, Rankings und Spielpläne auf ihrer WordPress-Website präsentieren möchten.

== Externe Dienste ==

**MeinTurnierplan.de Dienst**

Dieses Plugin verwendet [MeinTurnierplan.de](https://www.meinturnierplan.de/) sowohl für die Anzeige von Turnierinhalten als auch für den Abruf von Turnierkonfigurationsdaten.

**Was es macht:**

1. **Frontend-Anzeige (Öffentlich):**
   - Zeigt Turniertabellen und Spielpläne für Besucher der Website über iframe-Einbettungen an
   - **Verwendete Endpunkte:**
     * `https://www.meinturnierplan.de/displayTable.php` (für Turniertabellen)
     * `https://www.meinturnierplan.de/displayMatches.php` (für Spielpläne)
   - **Wann:** Wenn ein Besucher eine Seite mit Turnierinhalten lädt (Shortcode, Block oder Widget)

2. **Admin-Konfiguration (Nur Admin-Bereich):**
   - Stellt Turnierstrukturdaten über JSON-API bereit, um Administratoren bei der Konfiguration von Anzeigen zu helfen
   - **Verwendeter Endpunkt:**
     * `https://www.meinturnierplan.de/json/json.php` (Turnierstrukturdaten)
   - **Wann:** Nur im WordPress-Admin-Bereich wenn:
     * Administrator eine Turnier-ID in den Einstellungen eingibt
     * Administrator auf "Gruppen aktualisieren" oder ähnliche Aktualisierungsschaltflächen klickt
     * Admin-Vorschau geladen oder aktualisiert wird
   - **Was abgerufen wird:**
     * Turniergruppen
     * Teamlisten und Namen
     * Turnieroptionen (Feld anzeigen, Gruppe anzeigen, Schiedrichter zeigen uvm.)
   - **Zweck:**
     * Automatisches Ausfüllen von Gruppenauswahl-Dropdowns in der Admin-Oberfläche
     * Bestimmen, welche Funktionen für das Turnier verfügbar sind
     * Bessere Admin-Benutzererfahrung mit automatischer Konfiguration bieten
   - **Daten zwischengespeichert:** Abgerufene Daten werden 15 Minuten lang zwischengespeichert, um API-Aufrufe zu minimieren
   - **NICHT im Frontend verwendet:** JSON-API wird nur vom WordPress-Admin-Bereich kontaktiert, niemals von öffentlich zugänglichen Seiten

* **Gesendete Daten:** Nur Turnier-ID (keine persönlichen Daten, keine Benutzerinformationen)
* [Datenschutzrichtlinie](https://www.meinturnierplan.de/legal.php?t=privacy&v=2019-04-20&l=de)
* [Nutzungsbedingungen](https://www.meinturnierplan.de/legal.php?t=tou&v=2019-04-20&l=de)

**Was die eingebetteten Widgets sammeln:**

* **KEINE Tracking-Skripte** - Die eingebetteten Widgets verwenden weder Google Analytics noch andere Analysedienste
* **KEINE Cookies** - Die Widgets setzen keine Cookies in den Browsern der Benutzer
* **KEINE Drittanbieter-Ressourcen** - Die Widgets laden nur CSS-Styling von meinturnierplan.de (keine Google Fonts, AdSense oder andere externe Dienste)
* **Kommunikation:** Die Widgets verwenden nur JavaScript, um iframe-Dimensionen an Ihre Seite für die korrekte Anzeigegröße zu senden (über postMessage-API)

**Welche Daten möglicherweise gesammelt werden:**

Wenn Benutzer eingebettete Turnierinhalte anzeigen, protokolliert der Webserver von meinturnierplan.de möglicherweise automatisch:

* IP-Adressen (Standard-Webserver-Protokolle)
* Browsertyp und Version (aus User-Agent-Header)
* Referrer-URL (Ihre Website, auf der das Widget eingebettet ist)
* Zugriffszeitstempel

Dies ist eine Standard-Webserver-Protokollierung und beinhaltet keine Cookies, Tracking-Skripte oder dauerhafte Benutzeridentifikation.

== Datenschutzhinweis ==

**Dieses Plugin selbst:**

* Verfolgt keine Benutzer
* Sammelt keine persönlichen Daten
* Verwendet keine Cookies oder localStorage
* Sendet keine persönlichen oder sensiblen Daten an einen Server

**Datenübertragung:**
Die einzigen Daten, die von diesem Plugin gesendet werden, sind die Turnier-ID an meinturnierplan.de, wenn Sie explizit Turnierinhalte (über Shortcode, Block oder Widget) hinzufügen, um diese auf Ihren Seiten anzuzeigen.

**Verhalten eingebetteter Widgets:**
Die eingebetteten Widgets von meinturnierplan.de:

* Verwenden KEINE Tracking-Skripte (kein Google Analytics in Widgets)
* Setzen KEINE Cookies
* Laden KEINE Drittanbieterdienste (keine Google Fonts, AdSense usw.)
* Kommunizieren nur iframe-Dimensionen zurück an Ihre Seite für die korrekte Anzeige

**Standard-Webserver-Protokollierung:**
Wie bei jeder Web-Ressource können die Server von meinturnierplan.de beim Bereitstellen der eingebetteten Inhalte Standard-HTTP-Anfragedaten (IP-Adresse, Browsertyp, Referrer, Zeitstempel) protokollieren. Dies ist gängige Praxis für alle Webserver und beinhaltet kein Benutzer-Tracking oder Cookies.

**Keine Einwilligung erforderlich:**
Da die eingebetteten Widgets keine Cookies, Tracking-Skripte oder dauerhafte Benutzeridentifikation verwenden, ist keine zusätzliche Cookie-Einwilligung erforderlich, außer der Standard-Webserver-Protokollierungsoffenlegung in Ihrer Datenschutzrichtlinie.

= Verfügbare Sprachen =

Das Plugin ist in den folgenden Sprachen verfügbar:

* Englisch (English)
* Deutsch (German)
* Spanisch (Español)
* Französisch (Français)
* Italienisch (Italiano)
* Polnisch (Polski)

= Hauptfunktionen =

**Zwei benutzerdefinierte Beitragstypen:**

* **Turniertabellen** - Anzeige von Ständen, Rankings und Statistiken
* **Spiellisten** - Anzeige geplanter Spiele und Ergebnisse

**Mehrere Anzeigemethoden:**

* **Gutenberg-Blöcke** - Native Block-Editor-Unterstützung für Tabellen und Spiele
* **Shortcodes** - `[mtrn-table]` und `[mtrn-matches]` mit umfangreichen Anpassungsoptionen
* **Widgets** - Legacy-Widget-Unterstützung für beide Inhaltstypen

**Umfangreiche Anpassung:**

* Steuerung von Farben, Schriftarten, Rahmen und Abständen
* Sichtbarkeit bestimmter Spalten umschalten (Siege, Niederlagen, Logos usw.)
* Styling-Optionen anpassen (Farben, Schriftarten, Abstände)
* Echtzeit-Vorschau während der Bearbeitung im Admin-Bereich

**Zusätzliche Funktionen:**

* Externe Integration mit Turnierverwaltungssystemen über IDs
* Responsive Design - Mobile-freundliches Styling mit automatischen Anpassungen
* AJAX-gestützte Live-Vorschau im Admin-Bereich
* Automatische Anzeige auf einzelnen benutzerdefinierten Beitragstyp-Seiten

= Verwendung =

Nach der Aktivierung navigieren Sie zu **Turniertabellen** oder **Spiele** im Admin-Menü, um Ihren ersten Inhalt zu erstellen. Sie können Ihren Inhalt dann anzeigen mit:

1. **Gutenberg-Blöcke** - Fügen Sie den Turniertabellen- oder Spiele-Block zu einem Beitrag oder einer Seite hinzu
2. **Shortcodes** - Verwenden Sie `[mtrn-table id="123"]` oder `[mtrn-matches id="456"]`
3. **Widgets** - Fügen Sie das Turniertabellen- oder Spiele-Widget zu einem Widget-Bereich hinzu
4. **Automatische Anzeige** - Besuchen Sie einzelne Turniertabellen- oder Spiellisten-Seiten direkt

= Shortcode-Beispiele =

**Turniertabelle:**

* `[mtrn-table id="externe-id"]`
* `[mtrn-table post_id="123"]`
* `[mtrn-table id="externe-id" lang="de" group="A"]`

**Spiele:**

* `[mtrn-matches id="externe-id"]`
* `[mtrn-matches post_id="456"]`
* `[mtrn-matches id="externe-id" lang="de" group="A"]`

= Links =

* [GitHub Repository](https://github.com/AL1337/meinturnierplan)
* [Plugin-Website](https://www.meinturnierplan.de)

== Installation ==

= Automatische Installation =

1. Melden Sie sich in Ihrem WordPress-Admin-Panel an
2. Navigieren Sie zu **Plugins > Installieren**
3. Suchen Sie nach "MeinTurnierplan"
4. Klicken Sie auf "Jetzt installieren" und dann auf "Aktivieren"

= Manuelle Installation =

1. Laden Sie die Plugin-Zip-Datei herunter
2. Melden Sie sich in Ihrem WordPress-Admin-Panel an
3. Navigieren Sie zu **Plugins > Installieren > Plugin hochladen**
4. Wählen Sie die heruntergeladene Zip-Datei aus und klicken Sie auf "Jetzt installieren"
5. Aktivieren Sie das Plugin über das Menü **Plugins** in WordPress

= Nach der Aktivierung =

1. Navigieren Sie zu **Turniertabellen** oder **Turnier-Spiellisten** im Admin-Menü
2. Klicken Sie auf **Neue Turniertabelle hinzufügen** oder **Neue Turnier-Spielliste hinzufügen**, um Ihre erste Turniertabelle oder Spielliste zu erstellen
3. Konfigurieren Sie Einstellungen und Styling-Optionen
4. Verwenden Sie den Vorschaubereich, um Änderungen in Echtzeit zu sehen
5. Veröffentlichen und anzeigen mit Blöcken, Shortcodes oder Widgets

== Häufig gestellte Fragen ==

= Wie zeige ich eine Turniertabelle an? =

Sie haben mehrere Optionen:
1. Verwenden Sie den Gutenberg-Block: Fügen Sie den "Turniertabelle"-Block zu einem Beitrag oder einer Seite hinzu
2. Verwenden Sie den Shortcode: `[mtrn-table post_id="123"]` (ersetzen Sie 123 durch die Beitrags-ID Ihrer Tabelle)
3. Verwenden Sie das Widget: Gehen Sie zu Design > Widgets und fügen Sie das "Turniertabelle"-Widget hinzu
4. Besuchen Sie die Einzelbeitragsseite direkt - Inhalt wird automatisch angezeigt

= Wie passe ich das Aussehen an? =

Jede Turniertabelle und Spielliste hat umfangreiche Anpassungsoptionen im Admin-Bereich:
* Farben konfigurieren (Text, Hintergrund, Rahmen, Hover-Zustände)
* Schriftgrößen für Überschriften und Inhalt anpassen
* Abstände steuern (Padding, Margins)
* Sichtbarkeit bestimmter Spalten oder Informationen umschalten
* Live-Vorschau verwenden, um Änderungen sofort zu sehen

= Was sind die Shortcode-Attribute? =

**Turniertabelle Shortcode-Attribute:**

Allgemein:
* `id` - Externe Turnier-ID
* `post_id` - WordPress Beitrags-ID
* `lang` - Sprachcode (en, de, usw.)
* `group` - Nach Gruppenname filtern
* `width` - Tabellenbreite überschreiben
* `height` - Tabellenhöhe überschreiben

Styling:
* `s-size` - Schriftgröße (Standard: 9)
* `s-sizeheader` - Überschrift Schriftgröße (Standard: 10)
* `s-color` - Textfarbe (Hex ohne #)
* `s-maincolor` - Haupt-/Akzentfarbe (Standard: 173f75)
* `s-padding` - Tabellen-Padding (Standard: 2)
* `s-innerpadding` - Inneres Zellen-Padding (Standard: 5)
* `s-bgcolor` - Hintergrundfarbe mit Deckkraft (8-stelliger Hex)
* `s-logosize` - Logo-Größe (Standard: 20)
* `s-bcolor` - Rahmenfarbe (Standard: bbbbbb)
* `s-bsizeh` - Horizontale Rahmengröße (Standard: 1)
* `s-bsizev` - Vertikale Rahmengröße (Standard: 1)

Anzeigeoptionen:
* `sw` - Siege/Niederlagen/Unentschieden unterdrücken (1 zum Ausblenden)
* `sl` - Logos unterdrücken (1 zum Ausblenden)
* `sn` - Anzahl der Spiele unterdrücken (1 zum Ausblenden)
* `bm` - Projektor-/Präsentationsmodus (1 zum Aktivieren)
* `nav` - Gruppennavigation aktivieren (1 zum Aktivieren)

**Spiele Shortcode-Attribute:**

Allgemein:
* `id` - Externe Turnier-ID
* `post_id` - WordPress Beitrags-ID
* `lang` - Sprachcode
* `group` - Nach Gruppe filtern
* `gamenumbers` - Kommagetrennte Liste von Spielnummern

Anzeigeoptionen:
* `si` - Icons anzeigen (1 zum Anzeigen)
* `sf` - Flaggen anzeigen (1 zum Anzeigen)
* `st` - Zeiten anzeigen (1 zum Anzeigen)
* `sg` - Gruppen anzeigen (1 zum Anzeigen)
* `sr` - Runden anzeigen (1 zum Anzeigen)
* `se` - Zusatzinformationen anzeigen (1 zum Anzeigen)
* `sp` - Teilnehmer anzeigen (1 zum Anzeigen)
* `sh` - Überschriften anzeigen (1 zum Anzeigen)
* `bm` - Projektor-/Präsentationsmodus (1 zum Aktivieren)

== Screenshots ==

1. Eine neue Turniertabelle hinzufügen.
2. Eine neue Turnier-Spielliste hinzufügen.
3. Vorschau der Turniertabelle im Backend.
4. Vorschau der Turnier-Spielliste im Backend.

== Änderungsprotokoll ==

= 1.0.4 =
* Verbesserung - Deutsche Plugin-Beschreibung hinzugefügt

= 1.0.3 =
* Fehlerbehebung - Korrigierte Plugin-Version

= 1.0.2 =
* Lokalisierung - Aktualisierung der spanischen, französischen, polnischen und italienischen Übersetzungen

= 1.0.1 =
* Lokalisierung - Deutsche, spanische, französische, polnische und italienische Übersetzungen hinzugefügt

= 1.0.0 =
* Erste Veröffentlichung

== Upgrade-Hinweis ==

= 1.0.3 =
* Fehlerbehebung - Korrigierte Plugin-Version

= 1.0.2 =
* Aktualisierung der spanischen, französischen, polnischen und italienischen Übersetzungen

= 1.0.1 =
* Übersetzungen für mehrere Sprachen hinzugefügt

= 1.0.0 =
Erste Veröffentlichung von MeinTurnierplan. Zeigen Sie Turniertabellen und Spielpläne von meinturnierplan.de auf Ihrer WordPress-Website an.

== Entwicklung ==

Das Plugin folgt WordPress-Codierungsstandards und Best Practices:

* **Sicherheit** - Ordnungsgemäße Bereinigung, Validierung und Nonce-Überprüfung
* **Internationalisierung** - Vollständige i18n-Unterstützung mit Textdomäne `meinturnierplan`
* **Modernes WordPress** - Unterstützung für Gutenberg-Blöcke und REST-API
* **Saubere Architektur** - Trennung der Belange mit dedizierten Klassen für jede Funktion
* **Objektorientiert** - Klassenbasierte Struktur mit Singleton-Muster
* **AJAX-Integration** - Echtzeit-Vorschaufunktionalität

== Support ==

Für Probleme, Feature-Anfragen und Beiträge besuchen Sie bitte:
[GitHub Repository](https://github.com/AL1337/meinturnierplan)
