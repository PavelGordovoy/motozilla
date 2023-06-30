$files = array(
    dirname(__FILE__) . '/../../../lib/config/data/plugins/files_dropbox_uk_UA.po',  
);

foreach ($files as $file) {
    try {
        waFiles::delete($file, true);
    } catch (Exception $e) {

    }
}