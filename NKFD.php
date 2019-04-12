#!/usr/bin/php -q
<?php
/* Set internal character encoding to UTF-8 */
mb_internal_encoding("UTF-8");
//Default Time Zone Setting
@date_default_timezone_set('Asia/Seoul');
$PLEX_DIR = '/var/lib/plexmediaserver/Library/Application Support/Plex Media Server';
$PLEX_META_DIR = $PLEX_DIR."/"."Metadata";
$PLEX_MEDIA_DIR = $PLEX_DIR."/"."Media/localhost";
$PLEX_DB_DIR = $PLEX_DIR."/"."Plug-in Support/Databases";
$DB = $PLEX_DB_DIR."/"."com.plexapp.plugins.library.db";
//$DB = $PLEX_DB_DIR."/"."1.db";
try {
    $db = new PDO('sqlite:'.$DB);
    // Set errormode to exceptions
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //정보 가져오기
    $query = "SELECT id, title, title_sort FROM metadata_items WHERE parent_id is null";
    $sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute();
    while($info = $sth->fetch(PDO::FETCH_ASSOC)):
        $id = $info['id'];
        $title = $info['title'];
        $title_sort = $info['title_sort'];
        if(!Normalizer::isNormalized($title_sort, Normalizer::FORM_D)):
            $title_sortd = Normalizer::normalize($title, Normalizer::FORM_D);
            echo $id."  ".$title. "  ";
            echo $title_sortd;
            echo PHP_EOL;
            $query = "UPDATE metadata_items SET title_sort = :title_sort WHERE id = :id";
            $usth = $db->prepare($query);
            $usth->bindParam(':title_sort', $title_sortd, PDO::PARAM_STR);
            $usth->bindParam(':id', $id, PDO::PARAM_STR);
            $usth->execute();
        endif;
    endwhile;
    // Close file db connection
    $db = null;
}
catch(PDOException $e) {
    // Print PDOException message
    echo $e->getMessage();
    echo "\n";
}
?>
