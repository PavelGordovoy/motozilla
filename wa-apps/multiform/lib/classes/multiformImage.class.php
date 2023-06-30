<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformImage
{

    /**
     * Get option image url
     * 
     * @param int $option_id
     * @param int $field_id
     * @param string $image - e.g, imagename.original.jpg
     * @param string $size - e.g, 50x50
     * @return string
     */
    public static function getOptionImage($option_id, $field_id, $image, $size)
    {
        $path = 'fields/' . $field_id . '/' . $option_id . '/' . str_replace('.original', $size ? '.' . $size : '', $image);
        if (waSystemConfig::systemOption('mod_rewrite')) {
            return wa()->getDataUrl($path, true, 'multiform', true);
        } else {
            if (file_exists(wa()->getDataPath($path, true, 'multiform'))) {
                return wa()->getDataUrl($path, true, 'multiform', true);
            } else {
                $path = str_replace('fields/', 'fields/thumb.php/', $path);
                return wa()->getDataUrl($path, true, 'multiform', true);
            }
        }
    }

    /**
     * Get path to option thumbs
     * 
     * @param int $option_id
     * @param int $field_id
     * @return string
     */
    public static function getOptionImagePath($option_id, $field_id)
    {
        return wa()->getDataPath('fields/' . $field_id . '/' . $option_id . '/', true, 'multiform');
    }

    /**
     * Delete all image thumbs and original image from option
     * 
     * @param int $id - option ID
     * @param int $field_id
     * @param bool $delete_dir - delete dir with image
     * @return bool
     */
    public static function deleteImage($id, $field_id, $delete_dir = false)
    {
        $path = wa()->getDataPath('fields/' . $field_id . '/' . $id . '/', true, 'multiform');
        if (!$delete_dir) {
            $files = waFiles::listdir($path);
            foreach ($files as $f) {
                waFiles::delete($path . $f, true);
            }
        } else {
            waFiles::delete($path, true);
        }
        return true;
    }

    /**
     * Generate image thumb according to sizes
     * 
     * @param string $src_image_path
     * @param string $size - E.g, 50x50
     * @param bool|int $max_size
     * @return waImage
     * @throws waException
     */
    public static function generateThumb($src_image_path, $size, $max_size = false)
    {
        $image = waImage::factory($src_image_path);
        $width = $height = null;
        $size_info = self::parseSize($size);
        $type = $size_info['type'];
        $width = $size_info['width'];
        $height = $size_info['height'];

        switch ($type) {
            case 'max':
                if (is_numeric($max_size) && $width > $max_size) {
                    return null;
                }
                $image->resize($width, $height);
                break;
            case 'crop':
                if (is_numeric($max_size) && $width > $max_size) {
                    return null;
                }
                $image->resize($width, $height, waImage::INVERSE)->crop($width, $height);
                break;
            case 'width':
                if (is_numeric($max_size) && ($width > $max_size || $height > $max_size)) {
                    return null;
                }
                $image->resize($width, $height);
                break;
            case 'height':
                if (is_numeric($max_size) && ($width > $max_size || $height > $max_size)) {
                    return null;
                }
                $image->resize($width, $height);
                break;
            case 'rectangle':
                if (is_numeric($max_size) && ($width > $max_size || $height > $max_size)) {
                    return null;
                }
                if ($width > $height) {
                    $w = $image->width;
                    $h = $image->width * $height / $width;
                } else {
                    $h = $image->height;
                    $w = $image->height * $width / $height;
                }
                $image->crop($w, $h)->resize($width, $height, waImage::INVERSE);
                break;
            default:
                throw new waException("Unknown type");
                break;
        }
        return $image;
    }

    /**
     * Parse image size
     * 
     * @param string $size
     * @return array
     */
    public static function parseSize($size)
    {
        $type = 'unknown';
        $ar_size = explode('x', $size);
        $width = !empty($ar_size[0]) ? $ar_size[0] : null;
        $height = !empty($ar_size[1]) ? $ar_size[1] : null;

        if (count($ar_size) == 1) {
            $type = 'max';
            $height = $width;
        } else {
            if ($width == $height) { // crop
                $type = 'crop';
            } else {
                if ($width && $height) { // rectange
                    $type = 'rectangle';
                } else
                if (is_null($width)) {
                    $type = 'height';
                } else
                if (is_null($height)) {
                    $type = 'width';
                }
            }
        }
        return array(
            'type' => $type,
            'width' => $width,
            'height' => $height
        );
    }

}
