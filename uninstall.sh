#!/bin/bash

LIB_DIR=/usr/local/lib/bb_changelog_gen
BIN_DIR=/usr/local/bin
MAN_DIR_DE=/usr/local/man/de/man1
MAN_DIR_EN=/usr/local/man/en/man1

echo -n "Delete files... "

sudo rm $BIN_DIR/git-generate-changelog 2>/dev/null

if [ -d "$LIB_DIR" ]; then
	sudo rm $LIB_DIR/bb_changelog_gen.php     2>/dev/null
	sudo rm $LIB_DIR/bb_changelog_gen-cli.php 2>/dev/null
	sudo rm -r $LIB_DIR                       2>/dev/null
fi

if [ -d "$MAN_DIR_DE" ]; then
	sudo rm $MAN_DIR_DE/git-generate-changelog.1 2>/dev/null
fi
if [ -d "$MAN_DIR_EN" ]; then
	sudo rm $MAN_DIR_EN/git-generate-changelog.1 2>/dev/null
fi

echo "Ready."
