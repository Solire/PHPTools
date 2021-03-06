<?php

/**
 * Gestion des requêtes
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
 * Gestion des requêtes
 *
 * @package  PHPTools
 * @category Core
 * @author   Jonathan Sahm <contact@johnstyle.fr>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://github.com/johnstyle/PHPTools.git
 */
class Request
{
    public function getToken($method)
    {
        Session::start();

        return Session::getToken(Session::$id . $method);
    }

    public function hasToken()
    {
        $controller = str_replace('\\', '/', CONTROLLER);
        if (Libraries\Env::request('token') == $this->getToken($controller . PHPTOOLS_CONTROLLER_METHOD_SEPARATOR . METHOD)) {
            return true;
        }
        return false;
    }

    public function filter($key)
    {
        return Libraries\Arr::getTree(Libraries\Env::get('filters'), $key);
    }

    public static function redirect($url = false)
    {
        header('Location:' . ($url ? $url : './'));
        exit;
    }
}
