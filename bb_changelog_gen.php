<?php

namespace BugBuster\Changelog;

class GithubChangelogGenerator
{
    private $token;
    private $fileName = 'CHANGELOG.md';

    private $currentIssues;

    const LABEL_TYPE_BUG        = 'type_bug';
    const LABEL_TYPE_FEATURE    = 'type_feature';
    const LABEL_TYPE_PR         = 'type_pr';

    /* @var array */
    private $issueLabelMapping = [
        self::LABEL_TYPE_BUG => [
            'bug',
            'Bug',
            'defect',
            'Defect'
        ],
        self::LABEL_TYPE_FEATURE => [
            'enhancement',
            'Enhancement',
            'feature',
            'Feature'
        ],
    ];

    public function __construct($token = null, $issueMapping = null)
    {
        if ($issueMapping) {
            $this->issueLabelMapping = $issueMapping;
        }

        $this->token = $token;
    }

    /**
     * Create a changelog from given username and repository
     *
     * @param $user
     * @param $repository
     * @param null $label
     * @param null $savePath
     */
    public function createChangelog($user, $repository, $label = null, $savePath = null)
    {
        $this->currentIssues = null;

        $label    = $label ? $label : "Changelog";  
        $savePath = !$savePath ? getcwd() . '/' . $this->fileName : null;

        $ClosedMilestoneInfos = $this->collectMilestonesClosed($user, $repository);
        $OpenMilestoneInfos   = $this->collectMilestonesOpen($user, $repository);
        /*
        Array
        (
            [1] => Array
            (
                [close_at] => 2014-11-12T22:54:16Z
                [title] => 1.0.0 - Erste stable Version
                [html_url] => https://github.com/.....
            )
        
            [2] => Array
            (
                [close_at] => 2015-08-12T19:20:17Z
                [title] => 1.0.1 - Bugfix Release
                [html_url] => https://github.com/.....
            )
        
        )*/
        $ClosedIssueInfos = $this->collectMilestoneIssues($user, $repository, $ClosedMilestoneInfos);
        $OpenIssueInfos   = $this->collectMilestoneIssues($user, $repository, $OpenMilestoneInfos);
        /*
        Array
        (
            [1] => Array
                (
                    [2] => Array
                        (
                            [issue_title] => Texte in Sprachvariablen wandeln
                            [issue_type] => type_feature
                            [issue_url] => https://github.com/...
                        )
        
                    [1] => Array
                        (
                            [issue_title] => Integration von phpoffice/phpexcel
                            [issue_type] => type_feature
                            [issue_url] => https://github.com/...
                        )
        
                )
        
            [2] => Array
                (
                    [3] => Array
                        (
                            [issue_title] => Update Transifex Language Files
                            [issue_type] => type_feature
                            [issue_url] => https://github.com/...
                        )
        
                )
        
        )
         */
        
        $file = fopen($savePath, 'w');
        fwrite($file, '# ' . $label . "\n\r");
        
        foreach ($OpenMilestoneInfos as $milestonenumber => $arrvalues)
        {
            $issuesByType = $this->orderIssuesByTypeForMilestone($OpenIssueInfos[$milestonenumber]);
            if (0 == count($issuesByType[$this::LABEL_TYPE_FEATURE]) &&
                0 == count($issuesByType[$this::LABEL_TYPE_BUG])
                ) 
            {
            	continue;  //Open milestone without closed issues
            }
            fwrite($file, sprintf('## [%s](%s) (%s-xx-xx)' . "\r\n\r\n",
                                    $arrvalues['title'],
                                    $arrvalues['html_url'],
                                    date('Y')
                                    )
                    );
            $this->writeReleaseIssues($file, $issuesByType);
        }
        
        foreach ($ClosedMilestoneInfos as $milestonenumber => $arrvalues)
        {
            $issuesByType = $this->orderIssuesByTypeForMilestone($ClosedIssueInfos[$milestonenumber]);
            if (0 == count($issuesByType[$this::LABEL_TYPE_FEATURE]) &&
                0 == count($issuesByType[$this::LABEL_TYPE_BUG])
            )
            {
                continue;  //Closed milestone without closed issues
            }
            fwrite($file, sprintf('## [%s](%s) (%s)' . "\r\n\r\n", 
                                    $arrvalues['title'], 
                                    $arrvalues['html_url'], 
                                    date_format(date_create($arrvalues['close_at']),'Y-m-d') 
                                 )
                  );
        	$this->writeReleaseIssues($file, $issuesByType);
        }
    }

    /**
     * Collect Open Milestones With Closed Issues >0
     *
     * @param unknown $user
     * @param unknown $repository
     * @throws \Exception
     * @return array
     */
    private function collectMilestonesClosed($user, $repository)
    {
        $milestones = $this->callGitHubApi(sprintf('repos/%s/%s/milestones', $user, $repository),
                        [
                            'state' => 'closed'
                        ]);
        $data = array();
        if (count($milestones) < 1)
        {
            throw new \Exception('No milestones found for this repository');
        }
        foreach ($milestones as $milestone)
        {
            $html_url = sprintf('https://github.com/%s/%s/issues?q=milestone%s+%s'
                                , $user
                                , $repository
                                , urlencode(':"'. $milestone->title . '"')
                                , urlencode('is:closed')
                                );
            $data[$milestone->number] = ['close_at' => $milestone->closed_at,
                                         'title'    => $milestone->title,
                                         'html_url' => $html_url
                                        ];
        }
        arsort($data); //TODO RSort by closed_at

        echo "Found ".count($data)." closed milestone(s)". PHP_EOL;
        return $data;
    }

    /**
     * Collect Closed Milestones With Closed Issues
     * 
     * @param unknown $user
     * @param unknown $repository
     * @throws \Exception
     * @return multitype:multitype:NULL string
     */
    private function collectMilestonesOpen($user, $repository)
    {
        $milestones = $this->callGitHubApi(sprintf('repos/%s/%s/milestones', $user, $repository),
                                                    [
                                                        'state' => 'open'
                                                    ]);
        $data = array();
        if (count($milestones) < 1)
        {
            return $data;
        }
        foreach ($milestones as $milestone)
        {
            $html_url = sprintf('https://github.com/%s/%s/issues?q=milestone%s+%s'
                                , $user
                                , $repository
                                , urlencode(':"'. $milestone->title . '"')
                                , urlencode('is:closed')
                            );
            $data[$milestone->number] = ['created_at' => $milestone->created_at,
                                         'title'    => $milestone->title,
                                         'html_url' => $html_url
                                        ];
        }
        arsort($data); //TODO RSort by closed_at
        echo "Found ".count($data)." open milestone(s)". PHP_EOL;
        return $data;
    }
    
    /**
     * Collect Milestones Issues For Milestones 
     * 
     * @param unknown $user
     * @param unknown $repository
     * @param unknown $milestones
     * @return array 
     */
    private function collectMilestoneIssues($user, $repository, $milestones)
    {
        $dataMilestone = array();
        foreach ($milestones as $milestonenumber => $arrvalues) 
        {
        	
            echo "Milestone No. " . print_r($milestonenumber,true) . ": ";
            $issues = $this->callGitHubApi(sprintf('repos/%s/%s/issues', $user, $repository),
                        [
                            'state' => 'closed',
                            'milestone' =>$milestonenumber
                        ]);
            $dataIssue = array();
            foreach ($issues as $issue) 
            {
                $type = $this->getTypeFromLabels($issue->labels);
                
            	$dataIssue[$issue->number] = [
                                           'issue_title' => $issue->title,
                                           'issue_type'  => $type,
                                           'issue_url'   => $issue->html_url
                                       	  ];
            }
            $dataMilestone[$milestonenumber] = $dataIssue;
            echo "Found ".count($dataIssue)." closed issue(s)". PHP_EOL;
        }
        return $dataMilestone;
    }
    

    /**
     * Order Issues By Type For Milestone
     * 
     * @param unknown $issues
     * @return array(array(feature),array(bugs))
     */
    private function orderIssuesByTypeForMilestone($issues)
    {
        $feature = array();
        $bugfix = array();
        foreach ($issues as $issues_number => $values) 
        {
        	if ($values['issue_type'] == $this::LABEL_TYPE_FEATURE) 
        	{
        		$feature[] = array('title'=>$values['issue_title'], 'number'=>$issues_number, 'html_url'=>$values['issue_url']);
        	}
        	if ($values['issue_type'] == $this::LABEL_TYPE_BUG)
        	{
        	    $bugfix[] = array('title'=>$values['issue_title'], 'number'=>$issues_number, 'html_url'=>$values['issue_url']);
        	}
        	 
        }
        
        return array($this::LABEL_TYPE_FEATURE=>$feature,
                     $this::LABEL_TYPE_BUG=>$bugfix
                    );
    }
    
    
    /**
     * Collect Tags, not used
     *  
     * @param unknown $user
     * @param unknown $repository
     * @throws \Exception
     * @return multitype:NULL
     * 
     * @see https://api.github.com/repos/user/repository/tags
     */
    private function collectTags($user, $repository)
    {
        $tags = $this->callGitHubApi(sprintf('repos/%s/%s/tags', $user, $repository));
        $data = array();
        if (count($tags) < 1) 
        {
            throw new \Exception('No tags found for this repository');
        }
        foreach ($tags as $tag) 
        {
            $data[] = $tag->name;
        	
        }
        echo "Found ".count($data)." tags". PHP_EOL;
        return $data;
    }
    
    /**
     * Collect Tag Infos, not used
     * @param unknown $user
     * @param unknown $repository
     * @param unknown $tags
     */
    private function collectTagInfos($user, $repository, $tags)
    {
        $tagDate = array();
        foreach ($tags as $tag)
        {
            $tagRefs = $this->callGitHubApi(sprintf('repos/%s/%s/git/refs/tags/%s', $user, $repository, $tag));
            $ApiParameter = substr($tagRefs->object->url, strlen('https://api.github.com/'));
            $tagInfos = $this->callGitHubApi($ApiParameter);
            $tagDate[] = array('tag'=>$tag,
                               'date'=>$tagInfos->tagger->date, 
                               'message'=>$tagInfos->message); 
        }
    }
    
    
    /**
     * Write release issues to file
     *
     * @param $fileStream
     * @param $issues
     */
    private function writeReleaseIssues($fileStream, $issues)
    {
        foreach ($issues as $type => $currentIssues)
        {
            if (0 == count($currentIssues)) 
            {
            	continue;
            }
            switch ($type)
            {
                case $this::LABEL_TYPE_BUG: fwrite($fileStream, '### Fixed bugs' . "\r\n\r\n"); break;
                case $this::LABEL_TYPE_FEATURE: fwrite($fileStream, '### New features' . "\r\n\r\n"); break;
                case $this::LABEL_TYPE_PR: fwrite($fileStream, '### Merged pull requests' . "\r\n\r\n"); break;
            }

            foreach ($currentIssues as $issue) 
            {
                fwrite($fileStream, sprintf('- [\#%s](%s) %s' . "\r\n", $issue['number'], $issue['html_url'], $issue['title']));
            }
            fwrite($fileStream, "\r\n");
        }
    }

    /**
     * Collect all issues from release tags, not used
     *
     * @param $user
     * @param $repository
     * @param null $startDate
     * @return array
     * @throws Exception
     */
    private function collectReleaseIssues($user, $repository, $startDate = null)
    {
        $releases = $this->callGitHubApi(sprintf('repos/%s/%s/releases', $user, $repository));
        $data = [];

        if (count($releases) <= 0) {
            throw new \Exception('No releases found for this repository');
        }

        do
        {
            $currentRelease = current($releases);

            if ($startDate && date_diff(new \DateTime($currentRelease->published_at), new \DateTime($startDate))->days <= 0) {
            //if ($startDate && date_diff(new \DateTime($currentRelease->created_at), new \DateTime($startDate))->days <= 0) {
                continue;
            }

            $lastRelease = next($releases);
            $lastReleaseDate = $lastRelease ? $lastRelease->published_at : null;
            //$lastReleaseDate = $lastRelease ? $lastRelease->created_at : null;
            prev($releases);

            $currentRelease->issues = $this->collectIssues($lastReleaseDate, $user, $repository);
            $data[] = $currentRelease;

        }while(next($releases));

        return $data;
    }

    /**
     * Collect all issues from release date, not used
     *
     * @param $lastReleaseDate
     * @param $user
     * @param $repository
     * @return array
     */
    private function collectIssues($lastReleaseDate, $user, $repository)
    {
        if (!$this->currentIssues) {
            $this->currentIssues = $this->callGitHubApi(sprintf('repos/%s/%s/issues', $user, $repository), [
                'state' => 'closed'
            ]);
        }
        $issues = [];
        foreach ($this->currentIssues as $x => $issue)
        {
            if (new \DateTime($issue->closed_at) > new \DateTime($lastReleaseDate) || $lastReleaseDate == null)
            {
                unset($this->currentIssues[$x]);

                $type = $this->getTypeFromLabels($issue->labels);
                if (!$type && isset($issue->pull_request)) {
                    $type = $this::LABEL_TYPE_PR;
                }

                if ($type) {
                    $events = $this->callGitHubApi(sprintf('repos/%s/%s/issues/%s/events', $user, $repository, $issue->number));
                    $isMerged = false;

                    foreach ($events as $event) {
                        if(($event->event == 'merged' || $event->event == 'referenced') && !empty($event->commit_id)) {
                            $isMerged = true;
                            break;
                        }
                    }

                    if ($isMerged) {
                        $issues[$type][] = $issue;
                    }
                }
            }
        }

        return $issues;
    }

    /**
     * Get the Issue Type from Issue Labels
     *
     * @param $labels
     * @return bool|int|null|string
     */
    private function getTypeFromLabels($labels)
    {
        $type = null;
        foreach ($labels as $label)
        {
            $foundLabel = $this->getTypeFromLabel($label->name);
            if($foundLabel) 
            {
                return $foundLabel;
            }
        }

        return null;
    }

    /**
     * Get Type by label
     *
     * @param $label
     * @param null $haystack
     * @return bool|int|string
     */
    private function getTypeFromLabel($label, $haystack = null)
    {
        $haystack = !$haystack ? $this->issueLabelMapping : $haystack;
        foreach($haystack as $key => $value) {
            $current_key = $key;
            if((is_array($value) && $this->getTypeFromLabel($label, $value) !== false) || (!is_array($value) && strcasecmp($label, $value) === 0)) {
                return $current_key;
            }
        }
        return false;
    }

    /**
     * API call to the github api v3
     *
     * @param $call
     * @param array $params
     * @param int $page
     * @return mixed|null
     */
    private function callGitHubApi($call, $params = [], $page = 1)
    {
        $params = array_merge(
            $params,
            [
                'access_token' => $this->token,
                'page' => $page
            ]
        );

        $options  = [
            'http' => [
                'user_agent' => 'bb_github_changelog_generator'
            ]
        ];

        $url = sprintf('https://api.github.com/%s?%s', $call, http_build_query($params));

        $context  = stream_context_create($options);
        $response = file_get_contents($url, null, $context);
        $response = $response ? json_decode($response) : [];

        if(count(preg_grep('#Link: <(.+?)>; rel="next"#', $http_response_header)) === 1) 
        {
            return array_merge($response, $this->callGitHubApi($call, $params, ++$page));
        }

        return $response;
    }
}
