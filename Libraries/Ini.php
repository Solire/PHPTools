<?php
namespace PHPTools\Libraries;

/**
 * PHPTools
 *
 * @package  PHPTools
 * @author   Jonathan Sahm <contact@johnstyle.fr>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://github.com/johnstyle/PHPTools.git
 */
class Ini
{
    /**
     * Tableau des valeurs des fichiers .ini
     *
     * @var private
     */
    private $data = array();

    /**
     * Constructeur à partir d'un tableau
     *
     * @param array $default Tableau de données
     */
    public function __construct(&$default = array())
    {
        $this->data = &$default;
    }

    /**
     * Récupère une section
     *
     * @param type $args
     *
     * @return type
     */
    public function get($args = false)
    {
        return Arr::getTree($this->data, $args);
    }

    /**
     * Récupère toute les données
     *
     * @return array
     */
    public function gets()
    {
        return $this->data;
    }

    /**
     * Chargement des fichiers inis contenu dans un ou plusieur dossiers
     *
     * @param string|array $paths  Dossier(s) parent(s)
     * @param string       $regexp Expressions régulières que les fichiers
     * doivent respecter
     * @param string       $method Format de la clé pour chaque fichier ini
     * '<name>' sera remplacé
     * - si une expression régulière est définie ($regexp), par le premier couple
     * de parenthèses capturante
     * - sinon par le nom du fichier ini (sans extension)
     * exemple : si le chemin est 'aaa/bbb.ini', '<name>' sera remplacé par 'bbb'
     *
     * @return type
     */
    public function loadPath($paths, $regexp = false, $method = '<name>')
    {
        if (!$regexp) {
            $regexp = '^(.+)\.ini$';
        }

        foreach (Arr::to($paths) as $path) {
            $files = Dir::getFiles($path, $regexp);

            foreach ($files as $file) {
                $name = str_replace('-', '_', $file->match[1]);

                if ($file->parentname != $name) {
                    $name = str_replace('<name>', $name, $method);
                }

                $this->loadFile($file->path, $name);
            }
        }

        return $this->data;
    }

    /**
     * Charge un fichier ini, si un clé est défini, à la ligne défini par la clé
     *
     * @param string $filePath Chemin du fichier ini à charger
     * @param string $name     Nom de la section où insérer ce fichier
     *
     * @return array Le résultat du parse du fichier parsé
     */
    public function loadFile($filePath, $name = false)
    {
        $tab = self::parse($filePath);

        if ($name) {
            if (!isset($this->data[$name])) {
                $this->data[$name] = array();
            }

            Arr::setTree($this->data[$name], $tab);

            return $this->data[$name];
        }

        Arr::setTree($this->data, $tab);
        return $this->data;
    }

    /**
     * Parse un fichier ini
     *
     * @param string $filePath
     *
     * @return array
     */
    static public function parse($filePath)
    {
        $tab = parse_ini_file($filePath, true);

        $changes = array();
        foreach ($tab as $sectionName => $rows) {
            if (!is_array($rows)) {
                continue;
            }

            $changes[$sectionName] = array();

            foreach ($rows as $key =>  $values) {
                $changes[$sectionName][$key] = self::decodeString($key);

                if (is_array($values)) {
                    foreach ($values as $ii => $value) {
                        $values[$ii] = self::decodeString($value);
                    }
                    $tab[$sectionName][$key] = $values;
                } else {
                    $tab[$sectionName][$key] = self::decodeString($values);
                }
            }
        }

        foreach ($changes as $sectionName => $change) {
            foreach ($change as $orig => $after) {
                if ($orig !== $after) {
                    $tab[$sectionName][$after] = $tab[$sectionName][$orig];
                    unset($tab[$sectionName][$orig]);
                }
            }
        }

        return $tab;
    }

    /**
     * Pour les chaînes particulières (compatibilité .ini), on rétablit la valeur
     *
     * @param string $str Chaîne à transformer
     *
     * @return string
     */
    static protected function decodeString($str)
    {
        if (substr($str, 0, 2) == '%%'
            && substr($str, -2) == '%%'
        ) {
            /**
             * Chaîne urlencode
             */
            $str = urldecode(substr($str, 2, -2));
        } elseif (substr($str, 0, 1) == "'"
            && substr($str, -1) == "'"
        ) {
            /**
             * Chaîne protégé par simple quote
             */
            $str = substr($str, 1, -1);
        }

        return $str;
    }
}
