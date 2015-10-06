# BugBuster's GitHub Changelog Generator

Generiert eine Changelog im Markdown Format von deinem Repository auf GitHub,
basierend auf Milestones und dessen Issues.


Inspiriert von [ins0/github-changelog-generator][1] (Release basierend) und
[skywinder/github-changelog-generator][2] (Tag basierend)


## Installation

Aufruf der install.sh, kopiert Dateien nach /usr/local/lib und /usr/local/bin.


## Nutzung

``git generate-changelog -u [username] -r [repository]``

Optionale Parameter: ``-t [token] -l [label] -f [filepath]``


## Nutzung ohne Installation

``php ./lib/bb_changelog_gen-cli.php -u [username] -r [repository]``

Optionale Parameter: ``-t [token] -l [label] -f [filepath]``


## Ausgabe Beispiel

Siehe [Changelog][3] von diesem Projekt.


## GitHub Token

GitHub erlaubt nur 50 Abfragen ohne Authentifizierung. Daher sollte mit dem
Parameter ``-t [40-stelliger-Token]`` gearbeitet werden.

Einen Token kann [hier generiert werden][4].


[1]: https://github.com/ins0/github-changelog-generator
[2]: https://github.com/skywinder/github-changelog-generator
[3]: CHANGELOG.md
[4]: https://github.com/settings/tokens/new?description=BugBuster%20Changelog%20Generator%20token
