<?php
return array(
   'export_books'=>'*/10 * * * * /usr/bin/php -q '.wa()->getConfig()->getPath('root').DIRECTORY_SEPARATOR.'cli.php contacts sendpulseExport',
);
