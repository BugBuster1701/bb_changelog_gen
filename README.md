# BugBuster's GitHub Changelog Generator

[![Downloads](https://img.shields.io/github/downloads/BugBuster1701/bb_changelog_gen/latest/total.svg)](https://github.com/BugBuster1701/bb_changelog_gen/archive/master.zip)
[![Release](https://img.shields.io/github/release/BugBuster1701/bb_changelog_gen.svg)](https://github.com/BugBuster1701/bb_changelog_gen)
[![Tag](https://img.shields.io/github/tag/BugBuster1701/bb_changelog_gen.svg)](https://github.com/BugBuster1701/bb_changelog_gen)
[![License](https://img.shields.io/badge/license-LGPL--3.0%2B-green.svg)](https://github.com/BugBuster1701/bb_changelog_gen)
[![HuBoard badge](http://img.shields.io/badge/Hu-Board-7965cc.svg)](https://huboard.com/BugBuster1701/bb_changelog_gen)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/6c03efb6-f697-493d-97ca-44fc18491fa0/small.png)](https://insight.sensiolabs.com/projects/6c03efb6-f697-493d-97ca-44fc18491fa0)

Generiert eine Changelog Datei im Markdown Format von deinem Repository auf GitHub,
basierend auf Milestones und dessen Issues.


Inspiriert von [ins0/github-changelog-generator][1] (Release basierend) und
[skywinder/github-changelog-generator][2] (Tag basierend)


## Installation

Download von [GitHub][5] und entpacken.

Aufruf der install.sh, diese kopiert Dateien nach ``/usr/local/[lib|bin|man]``.


## Nutzung

``git generate-changelog -u [username] -r [repository]``

Optionale Parameter: ``-t [token] -l [label] -f [filepath]``


## Nutzung ohne Aufruf von install.sh

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
[5]: https://github.com/BugBuster1701/bb_changelog_gen/archive/master.zip
