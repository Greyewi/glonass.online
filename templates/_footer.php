</div>
<div class="debug">
    <a href="#">отправить тех.данные</a><br>
    <?=$commonClass->getMicroTime(true)?><br>
        <!--user_id=-->
        <?//=(isset(users::$user_data['id']) ? users::$user_data['id'] : 'unset')?>
            <!--<br>-->
            <!--order_id=-->
            <?//=users::$order_id?>
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
    <!-- BEGIN JIVOSITE CODE {literal} -->
    <script type='text/javascript'>
        (function() {
            var widget_id = 'sLKpxfBeF0';
            var d = document;
            var w = window;

            function l() {
                var s = document.createElement('script');
                s.type = 'text/javascript';
                s.async = true;
                s.src = '//code.jivosite.com/script/widget/' + widget_id;
                var ss = document.getElementsByTagName('script')[0];
                ss.parentNode.insertBefore(s, ss);
            }
            if (d.readyState == 'complete') {
                l();
            } else {
                if (w.attachEvent) {
                    w.attachEvent('onload', l);
                } else {
                    w.addEventListener('load', l, false);
                }
            }
        })();

    </script>
    <!-- {/literal} END JIVOSITE CODE -->
    </body>

    </html>
