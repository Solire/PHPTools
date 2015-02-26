<?php
namespace PHPTools\Libraries;

/**
 * PHPTools Dir
 *
 * @package  PHPTools
 * @author   Jonathan Sahm <contact@johnstyle.fr>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://github.com/johnstyle/PHPTools.git
 */
abstract class Dir
{
    /**
     * Attempts to create the directory specified by pathname
     *
     * @param type $pathname  The directory path
     * @param type $mode      The right mode
     * @param type $recursive Allows the creation of nested directories
     * specified in the pathname
     *
     * @return The directory path
     * @throws \Exception If unable to create the directory
     * @see \mkdir
     */
    public static function create($pathname, $mode = 0755, $recursive = false)
    {
        if (is_dir($pathname)) {
            return $pathname;
        }

        if (file_exists($pathname)) {
            throw new \Exception(
                'Can\'t create a directory [' . $pathname . '], a file already exists'
            );
        }

        $parent = pathinfo($pathname, PATHINFO_DIRNAME);
        if (!file_exists($parent) && !$recursive) {
            throw new \Exception(
                'Failed to create the directory [' . $pathname . '], because '
                . '[' . $parent . '] does not exist'
            );
        }

        if (file_exists($parent) && !is_writable($parent)) {
            throw new \Exception(
                'Failed to create a directory [' . $pathname . '], because '
                . '[' . $parent . '] is not writable'
            );
        }

        \mkdir($pathname, $mode, $recursive);

        return $pathname;
    }

    /**
     * Lists the file
     *
     * @param type $path
     * @param type $regexp
     * @param type $limit
     *
     * @return type
     */
    public static function getFiles($path, $regexp = false, $limit = false)
    {
        return self::open('is_file', $path, $regexp, $limit);
    }

    /**
     *
     *
     * @param type $path
     * @param type $regexp
     * @param type $limit
     *
     * @return type
     */
    public static function gets($path, $regexp = false, $limit = false)
    {
        return self::open('is_dir', $path, $regexp, $limit);
    }

    /**
     *
     *
     * @param type $type
     * @param type $path
     * @param type $regexp
     * @param type $limit
     *
     * @return type
     * @throws Exception
     */
    private static function open(
        $type,
        $path,
        $regexp = false,
        $limit = false
    ) {
        $ii = 0;
        $items = array();

        if (!file_exists($path)) {
            return $items;
        }

        if (!is_dir($path)) {
            throw new Exception(
                'Failed to list content of [' . $path . '] because it\'s not a '
                . 'directory'
            );
        }

        $dir = \opendir($path);

        if (!$dir) {
            throw new Exception('Failed to open directory [' . $path . ']');
        }

        $parentPathInfo = \pathinfo($path);
        while ($file = \readdir($dir)) {
            $match = false;
            if (
                   $file != '..'
                && $file != '.'
                && $type($path . '/' . $file)
                && (!$regexp || \preg_match("#" . $regexp . "#", $file, $match))
            ) {
                $pathInfo = \pathinfo($path . '/' . $file);

                $items[] = (object) array(
                    'title' => $pathInfo['filename'],
                    'name' => $file,
                    'parentname' => $parentPathInfo['filename'],
                    'dir' => $path,
                    'path' => $path . '/' . $file,
                    'match' => $match
                );

                if ($limit && $ii >= $limit) {
                    break;
                }

                $ii++;
            }
        }

        return $items;
    }

    /**
     *
     *
     * @param type $path
     * @param type $return
     *
     * @return boolean
     */
    public static function lastModifiedFile($path, $return = 'mtime')
    {
        $file   = false;
        $mtime  = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $fileinfo) {
            if ($fileinfo->isFile()) {
                if ($fileinfo->getMTime() > $mtime) {
                    $file   = $fileinfo->getFilename();
                    $mtime  = $fileinfo->getMTime();
                }
            }
        }

        switch($return) {
            case 'mtime':
                return $mtime;
                break;
            case 'file':
                return $file;
                break;
        }

        return false;
    }
}
