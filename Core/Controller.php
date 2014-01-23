<?php

/**
 * Gestion des Controlleurs
 *
 * PHP version 5
 *
 * @package  PHPTools
 * @category Core
 * @author   Jonathan Sahm <contact@johnstyle.fr>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://github.com/johnstyle/PHPTools.git
 */

namespace PHPTools;

/**
 * Gestion des Controlleurs
 *
 * @package  PHPTools
 * @category Core
 * @author   Jonathan Sahm <contact@johnstyle.fr>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://github.com/johnstyle/PHPTools.git
 */
abstract class Controller
{
   /**
    * Core
    *
    * @var Core
    */
    public $Core;

   /**
    * Model
    *
    * @var Model
    */
    public $Model;

   /**
    * Constructor
    *
    * @return void
    */
    final public function __construct ()
    {
        $this->Core = new Core();
    }

    public function __viewPreload ()
    {
    }

    public function __viewLoaded ()
    {
    }

    public function __viewCompleted ()
    {
    }
}
