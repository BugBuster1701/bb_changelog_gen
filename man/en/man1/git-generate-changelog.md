git-generate-changelog(1) -- BugBuster's GitHub Changelog Generator
===================================================================

## SYNOPSIS

`git-generate-changelog` [OPTIONS]

## DESCRIPTION

Generates a changelog file in markdown format from a repository on GitHub, based on milestones and its issues.

## OPTIONS

-u [username]

Your GitHub username.

-r [repository]

Your GitHub repository name

-t [token]

Optional: GitHub tokens. GitHub allowed only 50 queries without authentication. Therefore, you should work with the parameter -t [40-digit-token].

-l [label]

Optional: Overrides the content of the heading. Default is: "Changelog".

-f [filepath]

Optional: Replaces the path and filename of the changelog. Without this parameter, the generation is carried out in the current directory and has the file name "CHANGELOG.md"

## EXAMPLES

* When installed via install.sh:

  $ git generate-changelog -u Doe -r MyRepository

* Using without installation

  $ php ./lib/bb_changelog_gen-cli.php  -u Doe -r MyRepository

## AUTHOR
 Written by Glen Langer

## REPORTING BUGS
&lt;<https://github.com/BugBuster1701/bb_changelog_gen/issues>&gt;

## SEE ALSO
&lt;<https://github.com/BugBuster1701/bb_changelog_gen>&gt;
