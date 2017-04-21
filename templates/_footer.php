</div>
<div class="debug">
    <a href="#">отправить тех.данные</a><br>
<?=$commonClass->getMicroTime(true)?><br>
<!--user_id=--><?//=(isset(users::$user_data['id']) ? users::$user_data['id'] : 'unset')?><!--<br>-->
<!--order_id=--><?//=users::$order_id?>
</div>
<script type="text/javascript" src="/js/jquery.min.js"></script>
<script type="text/javascript" src="/js/jquery-ui.min.js"></script>
<?=(users::$edit_mode?"
<script src='/editor/tinymce.min.js'></script>
<script>
tinymce.init({
    selector: '.editor',
    height: 300,
    language: 'ru',
    directionality: 'ru',
    plugins: 'code',
    menubar: 'edit format table tools',    
//    toolbar:  'code',
    menu: {
        edit: {title: 'Edit', items: 'undo redo | cut copy paste pastetext | selectall | code'},
        insert: {title: 'Insert', items: 'link media | template hr'},
        format: {title: 'Format', items: 'bold italic underline strikethrough superscript subscript | formats | removeformat'},
        table: {title: 'Table', items: 'inserttable tableprops deletetable | cell row column'},
        tools: {title: 'Tools', items: 'spellchecker code'}
    }
});
</script>
":"")?>
<script type="text/javascript" src="/js/scripts.js"></script>
</body>
</html>