#!/usr/bin/php -q
<?php
/* Set internal character encoding to UTF-8 */
mb_internal_encoding("UTF-8");
//Default Time Zone Setting
@date_default_timezone_set('Asia/Seoul');

$ProgramId = $argv[1] ?? exit();
$start_num = $argv[2] ?? 1;
$DAUM_TV_DETAIL     = "http://movie.daum.net/tv/main?tvProgramId=%s";
$DAUM_TV_EPISODE    = "http://movie.daum.net/tv/episode?tvProgramId=%s&order=old";
$DAUM_TV_CAST       = "http://movie.daum.net/tv/crew?tvProgramId=%s";
$DAUM_TV_SERIES     = "http://movie.daum.net/tv/series_list.json?tvProgramId=%s&programIds=%s";
$options  = array('http' => array('user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.87 Safari/537.36'));
$context  = stream_context_create($options);
try {
    $directors = $producers = $writers = $roles = $episodes = $collections = $genres = $countries = array();

    $tvshow_json = json_decode(file_get_contents(sprintf($DAUM_TV_SERIES, $ProgramId, $ProgramId), false, $context), true);
    $tvshow = $tvshow_json['programList'][0];
    $title = trim($tvshow['name']);
    $original_title = $tvshow['nameOrg'];
    $genres[] = $tvshow['genre'];
    $countries[] = $tvshow['countries'];
    $year = $tvshow['year'];
    $studio = $tvshow['channels'][0]['name'];
    $originally_available_at = date_format(date_create($tvshow['channels'][0]['startDate']), "Y-m-d");
    $summary = trim($tvshow['introduceDescription']);
    $poster_url = $tvshow['mainImageUrl'];
    $directors = $producers = $writers = $roles = $episodes = $collections = array();
    foreach($tvshow['crews'] as $crew_info):
        if (in_array($crew_info['type'], array('감독', '연출'))) :
            $directors[] = array('name' => $crew_info['name'], 'photo' => $crew_info['mainImageUrl']);
        endif;
        if (in_array($crew_info['type'], array('제작'))) :
            $producers[] = array('name' => $crew_info['name'], 'photo' => $crew_info['mainImageUrl']);
        endif;
        if (in_array($crew_info['type'], array('극본', '각본'))) :
            $writers[] = array('name' => $crew_info['name'], 'photo' => $crew_info['mainImageUrl']);
        endif;
    endforeach;
    foreach($tvshow['castings'] as $role_info):
       $roles[] = array('name' => $role_info['name'], 'role' => $role_info['type'], 'photo' => $role_info['mainImageUrl']);
    endforeach;
    $tvepisode = file_get_contents(sprintf($DAUM_TV_EPISODE, $ProgramId), false, $context);
    $pattern = '/oreView\.init\(\d+, (.*?[\s\S]*}])\);/';
    preg_match($pattern, $tvepisode, $matches);
    if($matches):
        $episode_json = json_decode($matches[1], true);
        foreach($episode_json as $episodeinfo):
            $episodes[] = array(
                'name' => $start_num,
                'title' => trim($episodeinfo['title']),
                'introduceDescription' =>  trim(strip_tags($episodeinfo['introduceDescription'])),
                'broadcastDate' => $episodeinfo['channels'][0]['broadcastDate'] ? $episodeinfo['channels'][0]['broadcastDate'] : $episodeinfo['channels'][1]['broadcastDate']
            );
            $start_num++;
        endforeach;
    endif;
    $json_array = array(
         'title' => $title,
         'original_title' => $original_title,
         'summary' => $summary,
         'year' => $year,
         'originally_available_at' =>  $originally_available_at,
         'countries' => $countries,
         'studio' => $studio,
         'poster' => $poster_url,
         'directors' => $directors,
         'producers' => $producers,
         'writers' => $writers,
         'episodes' => $episodes,
         'roles' => $roles,
         'genres' => $genres,
         'collections' => $collections
        );
    $json_file = $title.'.json';
    $fp = fopen($json_file, 'w+');
    fwrite($fp, json_encode($json_array, JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE));
    fclose($fp);
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
?>
