git-generate-changelog(1) -- BugBuster's GitHub Changelog Generator
===================================================================

## SYNTAX

`git-generate-changelog` [OPTIONS]

## BESCHREIBUNG

Generiert eine Changelog Datei im Markdown Format von deinem Repository auf GitHub, basierend auf Milestones und dessen Issues.

## OPTIONEN

-u [username]

Dein GitHub Username.

-r [repository]

Dein GitHub Repository Name

-t [token]

Optional: GitHub Token. GitHub erlaubt nur 50 Abfragen ohne Authentifizierung. Daher sollte mit dem Parameter -t [40-stelliger-Token] gearbeitet werden.

-l [label]

Optional: Überschreibt den Inhalt der Überschrift, ohne Angabe ist das: "Changelog".

-f [filepath]

Optional: Ersetzt Pfad und Dateiname der Changelog, ohne Angabe erfolgt die Generierung im aktuellem Verzeichnis und bekommt den Dateinamen "CHANGELOG.md"

## BEISPIELE

* Wenn installiert über install.sh:

  $ git generate-changelog -u Mustermann -r MyRepository

* Nutzung ohne Installation

  $ php ./lib/bb_changelog_gen-cli.php  -u Mustermann -r MyRepository

## AUTOR
 Written by Glen Langer

## FEHLER MELDEN
&lt;<https://github.com/BugBuster1701/bb_changelog_gen/issues>&gt;

## SIEHE AUCH
&lt;<https://github.com/BugBuster1701/bb_changelog_gen>&gt;
