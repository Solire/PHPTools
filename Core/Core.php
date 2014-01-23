<?php

/**
 * Core
 *
 * PHP version 5
 *
 * @package  PHPTools
 * @category Core
 * @author   Thomas <thansen@solire.fr>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://github.com/Solire/PHPTools.git
 */

namespace PHPTools;

/**
 * Core
 *
 * @package  PHPTools
 * @category Core
 * @author   Thomas <thansen@solire.fr>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://github.com/Solire/PHPTools.git
 */
class Core
{
    /**
     *
     * @var Session|\Hooks\Session
     */
    public $Session;

    /**
     *
     * @var Alert|\Hooks\Alert
     */
    public $Alert;

    /**
     *
     * @var Request|\Hooks\Request
     */
    public $Request;
}
