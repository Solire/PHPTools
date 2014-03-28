<?php
/**
 * PHPTools
 *
 * PHP version 5
 *
 * @package  PHPTools
 * @author   Jonathan Sahm <contact@johnstyle.fr>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://github.com/johnstyle/PHPTools.git
 */

namespace PHPTools\Libraries;

abstract class Number
{
    public static function percent($val1, $val2){
        if ($val2 > 0) {
            return round(100 * $val1 / $val2, 2) . '%';
        }
        return 0 . '%';
    }
}
