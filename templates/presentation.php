<h1 class="page_title"><?=pageLoader::$current_template['title']?></h1>
<br class="clear">
<div class="white_with_border">
    <?php
    $source_file = 'templates/presentation.tmp';

    if ( users::$edit_mode ) {
        if ( isset($_POST['text']) && strlen(trim($_POST['text'])) > 0 ) {
            $fh = fopen($source_file, 'wb');
            fwrite($fh, $_POST['text']);
            fclose($fh);
        }
    }

    $fh = fopen($source_file, 'rb');
    $content = fread($fh, filesize($source_file));
    fclose($fh);

    if ( users::$edit_mode ) {
        echo '<form method="post"><textarea class="editor" name="text">' . $content . '</textarea><input type="submit" class="btn green" value="сохранить"></form>';
    } else {
        echo $content;
    }

    ?>
</div>