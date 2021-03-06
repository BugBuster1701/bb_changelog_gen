<?php

namespace BugBuster\Changelog;

require_once __DIR__ .'/bb_changelog_gen.php';

$envToken = getenv('GITHUB_CHANGELOG_GENERATOR_TOKEN');
$options  = getopt("u:r:t:l:f:");

$token        = isset($options['t']) ? $options['t'] : null;
$user         = isset($options['u']) ? $options['u'] : null;
$repository   = isset($options['r']) ? $options['r'] : null;
$label        = isset($options['l']) ? $options['l'] : null;
$saveFilePath = isset($options['f']) ? $options['f'] : null;

if (!$user || !$repository)
{
    die('Parameter -u [username] -r [repository] are required'. PHP_EOL 
       .'Optional: -t [token] -l [label] -f [filepath]'. PHP_EOL . PHP_EOL);
    
}

if ( null === $token && false !== $envToken) 
{
	$token = $envToken;
}

$generator = new GithubChangelogGenerator($token);
$generator->createChangelog($user, $repository, $label, $saveFilePath);
