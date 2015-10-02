<?php

namespace BugBuster\Changelog;

require_once __DIR__ .'/bb_changelog_gen.php';

$options = getopt("u:r:t:l:f:");

$token        = isset($options['t']) ? $options['t'] : null;
$user         = isset($options['u']) ? $options['u'] : null;
$repository   = isset($options['r']) ? $options['r'] : null;
$label        = isset($options['l']) ? $options['l'] : null;
$saveFilePath = isset($options['f']) ? $options['f'] : null;

if (!$user || !$repository)
{
    die('option -u [username] -r [repository] are required');
}

$generator = new GithubChangelogGenerator($token);
$generator->createChangelog($user, $repository, $label, $saveFilePath);
