<?php
//Clean vendors
$files = [
    'lib/actions/blog/mylangBlog.action.php',
    'lib/actions/site/mylangSite.action.php',
    'templates/actions/blog/Blog.html',
    'templates/actions/site/Site.html',
];

$path = wa()->getAppPath('', 'mylang');
foreach ($files as $file) {
    waFiles::delete($path.'/'.$file, true);
}
