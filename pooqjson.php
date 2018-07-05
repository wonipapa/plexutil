#!/usr/bin/php -q
<?php
/* Set internal character encoding to UTF-8 */
mb_internal_encoding("UTF-8");
//Default Time Zone Setting
@date_default_timezone_set('Asia/Seoul');

$ProgramId = $argv[1] ?? exit();
$start_num = $argv[2] ?? 1;
$POOQ_TV_DETAIL = 'https://apis.pooq.co.kr/vod/contents/%s?apikey=E5F3E0D30947AA5440556471321BB6D9&device=pc&partner=pooq&region=kor&targetage=auto&credential=none&pooqzone=none&drm=wm';
$POOQ_TV_EPISODE    = 'https://apis.pooq.co.kr/vod/programs-contents/%s?apikey=E5F3E0D30947AA5440556471321BB6D9&device=pc&partner=pooq&region=kor&targetage=auto&credential=none&pooqzone=none&drm=wm&offset=0&limit=1000&orderby=old';
$options  = array('http' => array('user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.87 Safari/537.36'));
$context  = stream_context_create($options);
try {
    $directors = $producers = $writers = $roles = $episodes = $collections = $genres = $countries = array();
    $episode_json = json_decode(file_get_contents(sprintf($POOQ_TV_EPISODE, $ProgramId), false, $context), true)['list'];
    $tvshow_json = json_decode(file_get_contents(sprintf($POOQ_TV_DETAIL, $episode_json[0]['contentid']), false, $context), true);
    $tvshow = $tvshow_json;
    $title = trim($tvshow['programtitle']);
    $original_title = "";
    $genres[] = $tvshow['genretext'];
    $countries[] = "";
    $year = date_format(date_create($tvshow['releasedate']), "Y");
    $studio = $tvshow['channelname'];
    $originally_available_at = $tvshow['releasedate'];
    $summary = trim($tvshow['programsynopsis']);
    $poster_url = 'http://'.trim($tvshow['programposterimage']);

    foreach($episode_json as $episodeinfo):
        $episodes[] = array(
            'name'                 => $start_num,
            'title'                => trim($episodeinfo['episodetitle']),
            'introduceDescription' => trim(strip_tags($episodeinfo['synopsis'])),
            'broadcastDate'        => str_replace('-', '', $episodeinfo['releasedate'])
        );
        $start_num++;
    endforeach;
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
