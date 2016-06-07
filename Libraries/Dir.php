<?php

namespace PHPTools\Libraries;

use Exception;

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
     * @throws Exception If unable to create the directory
     * @see mkdir
     */
    public static function create($pathname, $mode = 0755, $recursive = false)
    {
        if (strlen($pathname) === 0) {
            throw new Exception(
                'Can\'t create a directory with an empty path'
            );
        }

        if (is_dir($pathname)) {
            return $pathname;
        }

        if (file_exists($pathname)) {
            throw new Exception(sprintf(
                'Can\'t create a directory [%s], a file already exists',
                $pathname
            ));
        }

        $parent = pathinfo($pathname, PATHINFO_DIRNAME);
        if (!file_exists($parent) && !$recursive) {
            throw new Exception(sprintf(
                'Failed to create the directory [%s], because [%s] does not exist',
                $pathname,
                $parent
            ));
        }

        if (file_exists($parent) && !is_writable($parent)) {
            throw new Exception(sprintf(
                'Failed to create a directory [%s], because [%s] is not writable',
                $pathname,
                $parent
            ));
        }

        if (!mkdir($pathname, $mode, $recursive)) {
            throw new Exception(sprintf(
                'Failed to create a directory [%s]',
                $pathname
            ));
        }

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
     * @param string $path
     * @param string $return
     *
     * @return mixed
     */
    public static function lastModifiedFile($path, $return = 'mtime')
    {
        $file  = null;
        $mtime = 0;
        $filesInfo = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path)
        );

        foreach ($filesInfo as $fileInfo) {
            if ($fileInfo->isFile()
                && $fileInfo->getMTime() > $mtime
            ) {
                $file  = $fileInfo->getFilename();
                $mtime = $fileInfo->getMTime();
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

        return null;
    }
}
