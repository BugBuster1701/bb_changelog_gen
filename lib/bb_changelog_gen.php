<?php

/**
 * Github Changelog Generator bb_changelog_gen
 * 
 * @author  ins0        Marco Rieger (Parts of github-changelog-generator)
 * @author  BugBuster   Glen Langer
 *
 */

namespace BugBuster\Changelog;

class GithubChangelogGenerator
{
    private $token;
    private $fileName = 'CHANGELOG.md';

    const LABEL_TYPE_BUG         = 'type_bug';
    const LABEL_TYPE_FEATURE     = 'type_feature';
    const LABEL_TYPE_PR          = 'type_pr';
    const LABEL_TYPE_EXCLUDE     = 'type_exclude';
    const LABEL_TYPE_DOC         = 'type_doc';
    const LABEL_TYPE_MAINTENANCE = 'type_maintenance';

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
                    self::LABEL_TYPE_DOC => [
                        'doc',
                        'Doc',
                        'documentation',
                        'Documentation'
                    ],
                    self::LABEL_TYPE_MAINTENANCE => [
                        'maintenance',
                        'Maintenance',
                        'service',
                        'Service'
                    ],
                ];
    
    /* @var array */
    private $issueExcludeLabelMapping = [
    	self::LABEL_TYPE_EXCLUDE => [
            'duplicate',
            'question',
            'invalid',
            'wontfix',
            'Duplicate',
            'Question',
            'Invalid',
            'Wontfix'
        ]
        
    ];

    public function __construct($token = null, $issueMapping = null)
    {
        if ($issueMapping) 
        {
            $this->issueLabelMapping = $issueMapping;
        }

        $this->token = $token;
    }

    /**
     * Create a changelog from given username and repository
     *
     * @param string $user
     * @param string $repository
     * @param string $label
     * @param string $savePath
     */
    public function createChangelog($user, $repository, $label = null, $savePath = null)
    {
        $label    = $label ? $label : "Changelog";  
        $savePath = !$savePath ? getcwd() . '/' . $this->fileName : $savePath;
        
        $OpenMilestoneInfos   = $this->collectMilestonesOpen($user, $repository);
        $ClosedMilestoneInfos = $this->collectMilestonesClosed($user, $repository);

        $OpenIssueInfos   = $this->collectMilestoneIssues($user, $repository, $OpenMilestoneInfos);
        $ClosedIssueInfos = $this->collectMilestoneIssues($user, $repository, $ClosedMilestoneInfos);

        $file = fopen($savePath, 'w');
        fwrite($file, '# ' . $label . PHP_EOL . PHP_EOL);
        
        foreach ($OpenMilestoneInfos as $milestonenumber => $arrvalues)
        {
            $issuesByType = $this->orderIssuesByTypeForMilestone($OpenIssueInfos[$milestonenumber]);
            if (0 == count($issuesByType[$this::LABEL_TYPE_FEATURE]) &&
                0 == count($issuesByType[$this::LABEL_TYPE_BUG])     &&
                0 == count($issuesByType[$this::LABEL_TYPE_DOC])     &&
                0 == count($issuesByType[$this::LABEL_TYPE_MAINTENANCE])
                ) 
            {
            	continue;  //Open milestone without closed issues
            }
            fwrite($file, sprintf('## [%s](%s) (%s-xx-xx)' . PHP_EOL . PHP_EOL,
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
                0 == count($issuesByType[$this::LABEL_TYPE_BUG])     &&
                0 == count($issuesByType[$this::LABEL_TYPE_DOC])     &&
                0 == count($issuesByType[$this::LABEL_TYPE_MAINTENANCE])
            )
            {
                continue;  //Closed milestone without closed issues
            }
            fwrite($file, sprintf('## [%s](%s) (%s)' . PHP_EOL . PHP_EOL, 
                                    $arrvalues['title'], 
                                    $arrvalues['html_url'], 
                                    date('Y-m-d', strtotime($arrvalues['close_at']))
                                 )
                  );
        	$this->writeReleaseIssues($file, $issuesByType);
        }
        
        fwrite($file, PHP_EOL . PHP_EOL . '<sub>*This changelog was automatically generated by [bb_changelog_gen](https://github.com/BugBuster1701/bb_changelog_gen)*</sub>' . PHP_EOL);
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
            echo "No closed milestones found for this repository" . PHP_EOL;
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
            $data[$milestone->number] = ['close_at' => $milestone->closed_at,
                                         'title'    => $milestone->title,
                                         'html_url' => $html_url
                                        ];
        }
        
        arsort($data); 
        
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
            echo "No open milestones found for this repository" . PHP_EOL;
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
                                         'title'      => $milestone->title,
                                         'html_url'   => $html_url
                                        ];
        }
        
        arsort($data); 
        
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
        $feature       = array();
        $bugfix        = array();
        $documentation = array();
        $maintenance   = array();
        
        foreach ($issues as $issues_number => $values) 
        {
            switch ($values['issue_type']) 
            {
            	case $this::LABEL_TYPE_BUG :
            	    $bugfix[] = array('title'=>$values['issue_title'], 'number'=>$issues_number, 'html_url'=>$values['issue_url']);
            	    break;
        	    case $this::LABEL_TYPE_FEATURE :
        	        $feature[] = array('title'=>$values['issue_title'], 'number'=>$issues_number, 'html_url'=>$values['issue_url']);
        	        break;
    	        case $this::LABEL_TYPE_DOC :
    	            $documentation[] = array('title'=>$values['issue_title'], 'number'=>$issues_number, 'html_url'=>$values['issue_url']);
    	            break;
	            case $this::LABEL_TYPE_MAINTENANCE :
	                $maintenance[] = array('title'=>$values['issue_title'], 'number'=>$issues_number, 'html_url'=>$values['issue_url']);
	                break;
            }
        }
        
        return array($this::LABEL_TYPE_FEATURE=>$feature,
                     $this::LABEL_TYPE_BUG=>$bugfix,
                     $this::LABEL_TYPE_DOC=>$documentation,
                     $this::LABEL_TYPE_MAINTENANCE=>$maintenance
                    );
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
                case $this::LABEL_TYPE_BUG : 
                    fwrite($fileStream, '### Fixed bugs' . PHP_EOL . PHP_EOL); 
                    break;
                case $this::LABEL_TYPE_FEATURE : 
                    fwrite($fileStream, '### New features' . PHP_EOL . PHP_EOL); 
                    break;
                case $this::LABEL_TYPE_DOC : 
                    fwrite($fileStream, '### Documentation improvements' . PHP_EOL . PHP_EOL); 
                    break;
                case $this::LABEL_TYPE_MAINTENANCE :
                    fwrite($fileStream, '### Maintenance case' . PHP_EOL . PHP_EOL);
                    break;
                case $this::LABEL_TYPE_PR : 
                    fwrite($fileStream, '### Merged pull requests' . PHP_EOL . PHP_EOL); 
                    break;
            }

            foreach ($currentIssues as $issue) 
            {
                // replace " __" with " \_\_"
                // then in markdown editors ist " __" = bold
                $issue['title'] = preg_replace('/ __/',' \_\_',$issue['title']);
                // replace " _" or " *" with " \_"
                // then in markdown editors ist " _" or " *" = italic                
                $issue['title'] = preg_replace('/ _/' ,' \_'  ,$issue['title']);
                $issue['title'] = preg_replace('/ \*/' ,' \\*'  ,$issue['title']);
                fwrite($fileStream, sprintf('- [\#%s](%s) %s' . PHP_EOL, 
                                            $issue['number'], 
                                            $issue['html_url'], 
                                            $issue['title']));
            }
            fwrite($fileStream, PHP_EOL);
        }
    }

    /**
     * Get the Issue Type from Issue Labels
     *
     * @param $labels
     * @return null|string
     */
    private function getTypeFromLabels($labels)
    {
        $foundLabel   = false;
        $excludeLabel = false;
        
        foreach ($labels as $label)
        {
            if (!$foundLabel) 
            {
            	$foundLabel   = $this->getTypeFromLabel($label->name);
            }
            if (!$excludeLabel) 
            {
            	$excludeLabel = $this->getTypeFromLabel($label->name, $this->issueExcludeLabelMapping);
            }
        }

        if($foundLabel && !$excludeLabel)
        {
            return $foundLabel;
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
        foreach($haystack as $key => $value) 
        {
            $current_key = $key;
            if ( (is_array($value) && $this->getTypeFromLabel($label, $value) !== false) || 
                 (!is_array($value) && strcasecmp($label, $value) === 0)
               ) 
            {
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
                'page' => $page
            ]
        );

        $options  = [
                    'http' => [
                        'method' => 'GET',
                        'header' => [
                                'User-Agent: PHP',
                                'Content-type: application/x-www-form-urlencoded',
                                'Authorization: Bearer '.$this->token,
                                'X-GitHub-Api-Version: 2022-11-28'
                        ]
                    ]
                ];

        $url = sprintf('https://api.github.com/%s?%s', $call, http_build_query($params));

        $context  = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        if ($response === false) 
        {
        	echo 'HTTP 404 Not Found. '. PHP_EOL .'Are username and repository correctly specified?'. PHP_EOL . PHP_EOL;
        	exit(404);
        }
        $response = $response ? json_decode($response) : [];

        if(count(preg_grep('#Link: <(.+?)>; rel="next"#', $http_response_header)) === 1) 
        {
            return array_merge($response, $this->callGitHubApi($call, $params, ++$page));
        }

        return $response;
    }
}
