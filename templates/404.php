<?php
header($_SERVER['SERVER_PROTOCOL'] . ' 404');
?>
<div class="er404 errors"><h1>Ой, та самая ошибка 404 :(</h1>Это значит, что, несмотря на старания, мы не смогли найти страницу <b><?=trim($_SERVER['REQUEST_URI'],'/');?></b> на нашем сайте.</div>
