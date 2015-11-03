#!/bin/bash

LIB_DIR=/usr/local/lib/bb_changelog_gen
BIN_DIR=/usr/local/bin
MAN_DIR_DE=/usr/local/man/de/man1
MAN_DIR_EN=/usr/local/man/en/man1

if [ ! -d "$LIB_DIR" ]; then
	sudo mkdir $LIB_DIR
fi

if [ ! -d "$MAN_DIR_DE" ]; then
	sudo mkdir -p $MAN_DIR_DE
fi
if [ ! -d "$MAN_DIR_EN" ]; then
	sudo mkdir -p $MAN_DIR_EN
fi

declare -A msg

#############
##
## English
##
#############
msg[START_COPY]="Copy files ... "
msg[GEN_MAN_DB]="Generate the internal database of 'man' ... "
msg[CALL_GEN_CL]="Call: git generate-changelog"
msg[MANUAL]="Manual: man git-generate-changelog"
msg[OK]="OK"

#############
##
## German
##
#############
if [[ ${LANG:0:2} == de ]]; then
	msg[START_COPY]="Kopiere Dateien ... "
	msg[GEN_MAN_DB]="Generiere man's interne Datenbank ... "
	msg[CALL_GEN_CL]="Aufruf: git generate-changelog"
	msg[MANUAL]="Handbuch: man git-generate-changelog"
	msg[OK]="OK"
fi

echo -n "${msg[START_COPY]}"

sudo cp lib/bb_changelog_gen.php     $LIB_DIR/
sudo cp lib/bb_changelog_gen-cli.php $LIB_DIR/
sudo cp bin/git-generate-changelog   $BIN_DIR/
# ruby-ronn
# ronn man/de/man1/git-generate-changelog.md
# ronn man/en/man1/git-generate-changelog.md
sudo cp man/de/man1/git-generate-changelog.1 $MAN_DIR_DE/
sudo cp man/en/man1/git-generate-changelog.1 $MAN_DIR_EN/

sudo chmod 755 $LIB_DIR
sudo chmod 644 $LIB_DIR/bb_changelog_gen.php
sudo chmod 755 $LIB_DIR/bb_changelog_gen-cli.php
sudo chmod 755 $BIN_DIR/git-generate-changelog
sudo chmod 644 $MAN_DIR_DE/git-generate-changelog.1
sudo chmod 644 $MAN_DIR_EN/git-generate-changelog.1

echo "${msg[OK]}"

echo -n "${msg[GEN_MAN_DB]}"
sudo mandb >/dev/null
echo "${msg[OK]}"

echo
echo "${msg[CALL_GEN_CL]}"
echo "${msg[MANUAL]}"
echo

 