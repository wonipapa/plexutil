#!/usr/bin/php -q
<?php
/* Set internal character encoding to UTF-8 */
mb_internal_encoding("UTF-8");
//Default Time Zone Setting
@date_default_timezone_set('Asia/Seoul');
$id = $argv[1] ?? exit();
$PLEX_DIR = '/var/lib/plexmediaserver/Library/Application Support/Plex Media Server';
$PLEX_META_DIR = $PLEX_DIR."/"."Metadata";
$PLEX_MEDIA_DIR = $PLEX_DIR."/"."Media/localhost";
$PLEX_DB_DIR = $PLEX_DIR."/"."Plug-in Support/Databases";
$DB = $PLEX_DB_DIR."/"."com.plexapp.plugins.library.db";
try {
    $db = new PDO('sqlite:'.$DB);
    // Set errormode to exceptions
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //ID를 이용해서 정보 가져오기
    $query = "SELECT title, original_title, metadata_type, guid, studio, rating, summary, year, originally_available_at, user_thumb_url,user_art_url FROM metadata_items WHERE id=:id";
    $sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->bindParam(':id', $id, PDO::PARAM_STR);
    $sth->execute();
    $info =  $sth->fetch(PDO::FETCH_ASSOC);
    $sha = sha1($info['guid']);
    $title = trim($info['title']);
    $file_title = preg_replace('/[!\-,#@$%^&*()\\/:;?<>|\.\[\]\{\}_=+`~]+/', '', $title);
    $original_title = trim($info['original_title']);
    $rating = sprintf("%.1f", $info['rating']);
    $summary = trim($info['summary']);
    $year = trim($info['year']);
    $originally_available_at = date('Y-m-d', strtotime($info['originally_available_at']));
    $studio = trim($info['studio']);

    $sha = sha1($info['guid']);
    if($info['metadata_type'] == 1): // MOVIE
        $movie_dirname = $year ? $file_title." (".$year.")" : $file_title;
        $movie_filename = $movie_dirname."/".$file_title;
        if(!is_dir($movie_dirname)) :
            mkdir($movie_dirname);
        endif;
        $genres = $collections = $directors = $writers = $roles = $producers = $countries = $movie_json = array();
        //추가정보 가져오기
        $querym = "SELECT t1.tag, t2.text, t1.tag_type, t1.user_thumb_url FROM tags t1 LEFT JOIN taggings t2 ON t1.id=t2.tag_id WHERE t2.metadata_item_id=:id ORDER BY t1.tag_type, t2.`index`";
        $msth = $db->prepare($querym, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $msth->bindParam(':id', $id, PDO::PARAM_STR);
        $msth->execute();
        while($movie_info = $msth->fetch(PDO::FETCH_ASSOC)):
            switch($movie_info['tag_type']):
                case "1":
                    $genres[] = $movie_info['tag'];
                    break;
                case "2":
                    $collections[] = $movie_info['tag'];
                    break;
                case "4":
                    $directors[] = array('name' => $movie_info['tag'], 'photo' => $movie_info['user_thumb_url']);
                    break;
                case "5":
                    $writers[] = array('name' => $movie_info['tag'], 'photo' => $movie_info['user_thumb_url']);
                    break;
                case "6":
                    $roles[] = array('name' => $movie_info['tag'], 'role' => $movie_info['text'], 'photo' => $movie_info['user_thumb_url']);
                    break;
                case "7":
                    $producers[] = array('name' => $movie_info['tag'], 'photo' => $movie_info['user_thumb_url']);
                    break;
                case "8":
                    $countries[] = $movie_info['tag'];
                    break;                    
            endswitch;
        endwhile;
        //포스터, 아트 이미지 가져오기
        $PLEX_POSTER_DIR = $PLEX_META_DIR."/Movies/".$sha[0]."/".substr($sha,1).".bundle/Contents";
        if($info['user_thumb_url']):
            $postername = $movie_filename;
            $posterinfo = array($postername, $PLEX_POSTER_DIR, $info['user_thumb_url'], 1);
            getposter($posterinfo);
        endif;
        if($info['user_art_url']):
            $artname = $movie_filename;
            $artinfo = array($artname, $PLEX_POSTER_DIR, $info['user_art_url'], 1);
            getposter($artinfo);
        endif;
        //JSON 정보
        $movie_json = array(
            'title' => $title,
            'original_title' => $original_title,
            'rating' => $rating,
            'summary' => $summary,
            'year' => $year,
            'originally_available_at' => $originally_available_at,
            'countries' => $countries,
            'studio' => $studio,
            'directors' => $directors,
            'producers' => $producers,
            'writers' => $writers,
            'roles' => $roles,
            'genres' => $genres,
            'collections' => $collections
        );
        savejson($movie_filename, $movie_json);
    elseif($info['metadata_type'] == 2): //TV
        $tvshow_dirname =  $year ? $file_title." (".$year.")" : $file_title;
        $tvshow_filename = $tvshow_dirname."/".$file_title;
        if(!is_dir($tvshow_dirname)) :
            mkdir($tvshow_dirname);
        endif;
        //추가정보 가져오기
        $tvshow_json = array();
        $genres = $collections = $roles = $countries = array();
        $query = "SELECT t1.tag, t2.text, t1.tag_type, t1.user_thumb_url FROM tags t1 LEFT JOIN taggings t2 ON t1.id=t2.tag_id WHERE t2.metadata_item_id=:id ORDER BY t1.tag_type, t2.`index`";
        $sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':id', $id, PDO::PARAM_STR);
        $sth->execute();
        while($tvshow_info = $sth->fetch(PDO::FETCH_ASSOC)):
            switch($tvshow_info['tag_type']):
                case "1":
                    $genres[] = $tvshow_info['tag'];
                    break;
                case "2":
                    $collections[] = $tvshow_info['tag'];
                    break;
                case "6":
                    $roles[] = array('name' => $tvshow_info['tag'], 'role' => $tvshow_info['text'], 'photo' => $tvshow_info['user_thumb_url']);
                    break;
                case "8":
                    $countries[] = $tvshow_info['tag'];
                    break;
            endswitch;
        endwhile;        
        //포스터, 아트 이미지 가져오기
        $PLEX_POSTER_DIR = $PLEX_META_DIR."/TV Shows/".$sha[0]."/".substr($sha,1).".bundle/Contents";
        if($info['user_thumb_url']):
            $postername = $tvshow_filename;
            $posterinfo = array($postername, $PLEX_POSTER_DIR, $info['user_thumb_url'], 1);
            getposter($posterinfo);
        endif;
        if($info['user_art_url']):
            $artname = $tvshow_filename;
            $artinfo = array($artname, $PLEX_POSTER_DIR, $info['user_art_url'], 1);
            getposter($artinfo);         
        endif;
        $tvshow_json = array(
            'title' => $title,
            'original_title' => $original_title,
            'rating' => $rating,
            'summary' => $summary,
            'year' => $year,
            'originally_available_at' => $originally_available_at,
            'countries' => $countries,
            'studio' => $studio,
            'roles' => $roles,
            'genres' => $genres,
            'collections' => $collections
        );
        savejson($tvshow_filename, $tvshow_json);
        //시즌 정보 가져오기
        $query_s = "SELECT id, `index`, guid, user_thumb_url,user_art_url FROM metadata_items WHERE parent_id=:id ORDER BY `index`";
        $ssth = $db->prepare($query_s, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $ssth->bindParam(':id', $id, PDO::PARAM_STR);
        $ssth->execute();

        while($season_info = $ssth->fetch(PDO::FETCH_ASSOC)):
            $season_dirname = $season_info['index'] == '0' ? $tvshow_dirname."/".$file_title." 특별편" : $tvshow_dirname."/".$file_title." 시즌 ".sprintf('%02d', $season_info['index']);
            $season_filename = $season_info['index'] == '0' ? $season_dirname."/".$file_title." 특별편" : $season_dirname."/".$file_title." 시즌 ".sprintf('%02d', $season_info['index']);
            if(!is_dir($season_dirname)) :
                mkdir($season_dirname);
            endif;
            $season_json = $episodes = array();
            if($season_info['user_thumb_url']):
                $season_postername = $season_filename;
                $season_posterinfo = array($season_postername, $PLEX_POSTER_DIR, $season_info['user_thumb_url'], 2);
                getposter($season_posterinfo);
            endif;
            if($season_info['user_art_url']):
                $season_artname = $season_filename;
                $season_artinfo = array($season_artname, $PLEX_POSTER_DIR, $season_info['user_art_url'], 2);
                getposter($season_artinfo);
            endif;
            //에피소드 정보 가져오기
            $query_e = "SELECT id, `index`, title, summary, originally_available_at, user_thumb_url, user_art_url FROM metadata_items WHERE parent_id=:parent_id ORDER BY `index`";
            $esth = $db->prepare($query_e, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $esth->bindParam(':parent_id', $season_info['id'], PDO::PARAM_STR);
            $esth->execute();
            while($episode_info = $esth->fetch(PDO::FETCH_ASSOC)):
                $directors = $writers = $producers = array();
                $query_t = "SELECT t1.tag, t2.text, t1.tag_type, t1.user_thumb_url FROM tags t1 LEFT JOIN taggings t2 ON t1.id=t2.tag_id WHERE t2.metadata_item_id=:metadata_item_id ORDER BY t2.`index`;";
                $tsth = $db->prepare($query_t, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                $tsth->bindParam(':metadata_item_id', $episode_info['id'], PDO::PARAM_STR);
                $tsth->execute();
                $episodes[] = array(
                    'name' => $episode_info['index'],
                    'title' => trim($episode_info['title']),
                    'introduceDescription' => trim(strip_tags($episode_info['summary'])),
                    'broadcastDate'=> date('Ymd', strtotime($episode_info['originally_available_at']))

                );
                if($episode_info['user_thumb_url']):
                    $episode_thumbname = $season_filename." episode_thumb ".sprintf('%02d', $episode_info['index']);
                    $episode_thumbinfo = array($episode_thumbname, $PLEX_MEDIA_DIR, $episode_info['user_thumb_url']);
                    getthumb($episode_thumbinfo);
                endif;
                if($episode_info['user_art_url']):
                    $episode_artname = $season_filename." episode_art ".sprintf('%02d', $episode_info['index']);
                    $episode_artinfo = array($episode_artname, $PLEX_MEDIA_DIR, $episode_info['user_art_url']);
                    getthumb($episode_artinfo);
                endif;

                while($etc_info = $tsth->fetch(PDO::FETCH_ASSOC)):
                    switch($etc_info['tag_type']):
                        case "4":
                            $directors[] = array('name' => $etc_info['tag'], 'photo' => $etc_info['user_thumb_url']);
                            break;
                        case "5":
                            $writers[] = array('name' => $etc_info['tag'], 'photo' => $etc_info['user_thumb_url']);
                            break;
                        case "7":
                            $producers[] = array('name' => $etc_info['tag'], 'photo' => $etc_info['user_thumb_url']);
                        break;
                    endswitch;
                endwhile;
            endwhile;
        $directors = array_unique($directors, SORT_REGULAR);
        $writers = array_unique($writers, SORT_REGULAR);
        $producers = array_unique($producers, SORT_REGULAR);
        $season_json = array(
            'directors' => $directors,
            'producers' => $producers,
            'writers' => $writers,
            'episodes' => $episodes,
        );
        savejson($season_filename, $season_json);
        endwhile;
    endif;

    // Close file db connection
    $db = null;
}
catch(PDOException $e) {
    // Print PDOException message
    echo $e->getMessage();
    echo "\n";
}

function getposter($imageinfo) {
    list($imagename, $dir, $image_url, $type) = $imageinfo;
    $ext = '';
    $image_url = str_replace('metadata://', '', $image_url);
    if($type == 1):
        list($imagetype, $agent_image) = explode("/", $image_url);
        $tmp = explode("_", $agent_image);
        $image = $dir."/".implode('_', explode('_', $agent_image, -1))."/".$imagetype."/".end($tmp);
        $imagetype = $imagetype == 'posters' ? ' poster' : " ".$imagetype;
        $imagename = $imagename.$imagetype;
    elseif($type == 2):
        list($season, $season_number, $imagetype, $agent_image) = explode("/", $image_url);
        $tmp = explode("_", $agent_image);
        $image = $dir."/".implode('_', explode('_', $agent_image, -1))."/".$season."/".$season_number."/".$imagetype."/".end($tmp);
        $imagetype = $imagetype == 'posters' ? ' poster' : " ".$imagetype;
        $imagename = $imagename.$imagetype;
    endif;
    if(file_exists($image)) :

        switch(exif_imagetype($image)):
            case IMAGETYPE_GIF:
                $ext = ".gif";
                break;
            case IMAGETYPE_JPEG:
                $ext = ".jpg";
                break;
            case IMAGETYPE_PNG:
                $ext = ".png";
                break;
        endswitch;
        if($ext):
            copy($image, $imagename.$ext);
        endif;
    endif;
}

function getthumb($imageinfo) {
    list($imagename, $dir, $image_url) = $imageinfo;
    $ext = '';
    $image_url = str_replace('media://', '', $image_url);
    $image = $dir."/".$image_url;
    if(file_exists($image)) :
        switch(exif_imagetype($image)):
            case IMAGETYPE_GIF:
                $ext = ".gif";
                break;
            case IMAGETYPE_JPEG:
                $ext = ".jpg";
                break;
            case IMAGETYPE_PNG:
                $ext = ".png";
                break;
        endswitch;
        if($ext):
            copy($image, $imagename.$ext);
        endif;
    endif;
}

function savejson($filename, $json){
    $filename = $filename.".json";
    $fp = fopen($filename, 'w+');
    fwrite($fp, json_encode($json, JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE));
    fclose($fp);
}    
?>
