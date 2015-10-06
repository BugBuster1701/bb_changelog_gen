#!/bin/bash

LIB_DIR=/usr/local/lib/bb_changelog_gen 
BIN_DIR=/usr/local/bin

if [ ! -d "$LIB_DIR" ]; then
	sudo mkdir $LIB_DIR
fi

sudo cp lib/bb_changelog_gen.php     $LIB_DIR/
sudo cp lib/bb_changelog_gen-cli.php $LIB_DIR/
sudo cp bin/git-generate-changelog   $BIN_DIR

sudo chmod 755 $LIB_DIR
sudo chmod 644 $LIB_DIR/bb_changelog_gen.php
sudo chmod 755 $LIB_DIR/bb_changelog_gen-cli.php
sudo chmod 755 $BIN_DIR/git-generate-changelog

echo "Aufruf: git generate-changelog"
