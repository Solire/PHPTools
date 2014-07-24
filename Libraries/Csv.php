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

class Csv
{
    /**
     * Chemin du fichier temporaire de travail
     *
     * @var string
     */
    protected $file;

    /**
     * Chemin du fichier final
     *
     * @var string
     */
    protected $fileMv;

    /**
     * Ressource du fichier de travail
     *
     * @var resource
     */
    protected $handle;

    /**
     *
     *
     * @var string
     */
    protected $rawHeader;

    /**
     *
     *
     * @var string
     */
    protected $rawLine;

    /**
     *
     *
     * @var string
     */
    protected $rawLines;

    /**
     *
     *
     * @var array
     */
    protected $header;

    /**
     *
     *
     * @var bool
     */
    protected $headerAdded = false;

    /**
     *
     *
     * @var array
     */
    protected $line;

    /**
     *
     *
     * @var array
     */
    protected $lines;

    /**
     *
     *
     * @var array
     */
    protected $options;

    /**
     *
     *
     * @var string
     */
    protected $separator = ';';

    /**
     *
     *
     * @var string
     */
    protected $container = '"';

    /**
     * Taille de bloc par défaut 1 Mo (1024*1024)
     *
     * @var int
     */
    protected $max_size = 1048576;

    /**
     * Constructeur
     *
     * @param array $options options de paramétrage
     */
    public function __construct ($options = array())
    {
        /** @formatter:off */
        $this->options = array_merge(array(
            'hasHeader'     => true,
            'lineStart'     => 0,
            'isArray'       => false,
            'orderby'       => false,
            'order'         => SORT_ASC,
            'limit'         => 0,
            'start'         => 0,
            'filter'        => false,
            'separator'     => $this->separator,
            'container'     => $this->container,
            'max_size'      => $this->max_size
        ), $options);
        /** @formatter:on */

        $this->separator = $this->options['separator'];
        $this->container = $this->options['container'];
        $this->max_size = $this->options['max_size'];
    }

    /**
     * Destruction de la connexion avec le fichier
     *
     * @return void
     */
    public function __destruct ()
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
    }

    /**
     * Création du fichier
     *
     * @param string chemin du fichier
     * @return object
     */
    public function create ($file)
    {
        $this->fileMv = $file;
        $this->file   = PHPTOOLS_ROOT_TMP . '/' . md5($file);
        $this->delete();
        return $this->handle('w+');
    }

    /**
     *
     *
     * @param type $file
     *
     * @return type
     */
    public function quickOpen($file)
    {
        $this->file = $file;

        if (file_exists($this->file)
            && filesize($this->file) > 0
        ) {
            $this->headerAdded = true;
        }

        $this->handle('a+');
        return $this->handle;
    }

    /**
     * Ouverture du fichier, il faudra bien utiliser la méthode close()
     *
     * @param string chemin du fichier
     *
     * @return resource
     */
    public function open($file)
    {
        $this->fileMv = $file;
        $this->file   = PHPTOOLS_ROOT_TMP . '/' . md5($file);

        if (file_exists($this->fileMv)
            && filesize($this->fileMv)
        ) {
            if ($this->options['hasHeader']) {
                $this->handle('w+');
                $handleMv = fopen($this->fileMv, 'r');

                if ($this->options['lineStart']) {
                    for ($i = 0; $i < ($this->options['lineStart'] - 1); $i++) {
                        fgetcsv($handleMv, 0, $this->separator, $this->container);
                    }
                }

                /*
                 * On récupère le header actuel du csv
                 */
                $this->header = fgetcsv($handleMv, 0, $this->separator, $this->container);
                if ($this->header) {
                    foreach ($this->header as $i => &$header) {
                        if (empty($header)) {
                            $header = 'column' . ($i + 1);
                        }
                    }
                }

                while ($l = fgetcsv($handleMv, 0, $this->separator, $this->container)) {
                    fputcsv($this->handle, $l, $this->separator, $this->container);
                }
            } else {
                rename($this->fileMv, $this->file);
                $this->handle('a+');
            }
        }

        return $this->handle;
    }

    /**
     * Ouverture du fichier
     *
     * @param $mode mode d'ouverture (cf \fopen)
     *
     * @return resource|false|null
     */
    private function handle($mode)
    {
        if (substr($mode, 1, 1) != '+') {
            \System\Notice::error('Mode d\'ouverture interdit, les fichiers doivent être ouvert en lecture et écriture, “' . $this->file . '”');
            return null;
        }

        if ($this->file) {
            if (file_exists($this->file)) {
                if (!is_readable($this->file)) {
                    \System\Notice::error('Impossible de lire le fichier “' . $this->file . '”');
                    return null;
                }

                if (!is_writable($this->file)) {
                    \System\Notice::error('Impossible d\'écrire le fichier “' . $this->file . '”');
                    return null;
                }
            }

            $this->handle = fopen($this->file, $mode);
        }
    }

    /**
     * Inutilisé actuellement
     */
    public function parseHeader()
    {
        if ($this->options['hasHeader']) {
            if ($this->options['lineStart']) {
                for ($i = 0; $i < ($this->options['lineStart'] - 1); $i++) {
                    fgetcsv($this->handle, 0, $this->separator, $this->container);
                }
            }

            $this->header = fgetcsv($this->handle, 0, $this->separator, $this->container);

            if ($this->header) {
                foreach ($this->header as $i => &$header) {
                    if (empty($header)) {
                        $header = 'column' . ($i + 1);
                    }
                }
            }
        }
    }

    /**
     * Parcours de chaque ligne du fichier
     *
     * @return boolean
     */
    public function loop ()
    {
        if (is_resource($this->handle)) {
            $feof = !feof($this->handle);
            $this->line = false;
            if ($this->rawLine = stream_get_line($this->handle, 0, "\n")) {
                return $feof;
            }
        }
        return false;
    }

    /**
     * Retourne l'entête
     *
     * @return array
     */
    public function getHeader ()
    {
        return $this->header;
    }

    /**
     * Retourne la ligne courante
     *
     * @return array
     */
    public function getLine ()
    {
        return $this->line;
    }

    /**
     * Retourne le groupe de lignes courantes
     *
     * @return array
     */
    public function getLines ()
    {
        return $this->lines;
    }

    /**
     * Retourne la ligne courante au format CSV
     *
     * @return string
     */
    public function getRawLine ()
    {
        return $this->rawLine;
    }

    /**
     * Retourne le groupe de lignes courantes au format CSV
     *
     * @return string
     */
    public function getRawLines ()
    {
        return $this->rawLines;
    }

    /**
     * Transforme la ligne CSV en objet
     *
     * @return Csv
     */
    public function toObject ()
    {
        if (is_resource($this->handle)) {
            $data = self::fromRaw($this->rawLine);
            if ($this->header) {
                foreach ($this->header as $i => $header) {
                    if ($this->options['isArray']) {
                        if (!$this->line) {
                            $this->line = array();
                        }
                        if (isset($data[$i])) {
                            $this->line[$header] = $data[$i];
                        } else {
                            $this->line[$header] = '';
                        }
                    } else {
                        if (!$this->line) {
                            $this->line = new \stdClass ();
                        }
                        if (isset($data[$i])) {
                            $this->line->{$header} = $data[$i];
                        } else {
                            $this->line->{$header} = '';
                        }
                    }
                }
            } else {
                foreach ($data as $i => $value) {
                    if ($this->options['isArray']) {
                        if (!$this->line) {
                            $this->line = array();
                        }
                        $this->line[$i] = $value;
                    } else {
                        if (!$this->line) {
                            $this->line = new \stdClass ();
                        }
                        $this->line->{$i} = $value;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Ajoute plusieurs lignes de données
     *
     * @return void
     */
    public function addLines ($items)
    {
        $this->lines = false;
        $this->rawLines = false;
        if (is_resource($this->handle)) {
            if ($items) {
                foreach ($items as $item) {
                    if ($this->addLine($item)) {
                        $this->lines[] = $this->line;
                        $this->rawLines .= self::toRaw($this->line);
                    }
                }
            }
        }
    }

    /**
     * Ajoute une ligne de données
     *
     * @return object
     */
    public function addLine ($item)
    {
        $this->line    = false;
        $this->rawLine = false;
        if (is_resource($this->handle)) {
            if ($item) {

                $item = (array)$item;

                /** Ajout des nouveaux éléments au header */
                if ($this->options['hasHeader']) {
                    $headerType = 'string';
                    if (!$this->header) {
                        $this->header = array();
                        foreach ($item as $name => $value) {
                            if (is_int($name)) {
                                $headerType = 'int';
                            }
                            if (!in_array($name, $this->header)) {
                                $this->header[] = $name;
                            }
                        }
                    } else {
                        foreach ($item as $name => $value) {
                            if (is_int($name)) {
                                $headerType = 'int';
                            } elseif (!in_array($name, $this->header)) {
                                $this->header[] = $name;
                            }
                        }
                    }

                    /** Constitution de la ligne */
                    $this->line = new \stdClass ();
                    foreach ($this->header as $i => $header) {
                        switch($headerType) {
                            case 'string' :
                                if (isset($item[$header])) {
                                    $this->line->{$header} = is_array($item[$header]) ? implode('\n', $item[$header]) : $item[$header];
                                } else {
                                    $this->line->{$header} = '';
                                }
                                break;
                            case 'int' :
                                if (isset($item[$i])) {
                                    $this->line->{$header} = is_array($item[$i]) ? implode('\n', $item[$i]) : $item[$i];
                                } else {
                                    $this->line->{$header} = '';
                                }
                                break;
                        }
                    }
                } else {
                    $this->line = $item;
                }
                $this->rawLine = self::toRaw($this->line);
                $this->append($this->rawLine);
            }
        }

        return $this->line;
    }

    public function addHeader()
    {
        if ($this->headerAdded) {
            return true;
        }

        if ($this->header) {
            fseek($this->handle, 0);

            $fileTmp   = PHPTOOLS_ROOT_TMP . '/' . PID . md5($this->file) . '-prepend';
            $handleTmp = fopen($fileTmp, 'w');

            fputcsv($handleTmp, $this->header, $this->separator, $this->container);

            while (!feof($this->handle)) {
                $bloc = fread($this->handle, $this->max_size);
                fwrite($handleTmp, $bloc);
            }

            rename($fileTmp, $this->file);
            fclose($handleTmp);
        }

        $this->headerAdded = true;
    }

    /**
     *
     *
     * @param type $str
     */
    public function append($str)
    {
        fseek($this->handle, 0, SEEK_END);
        fwrite($this->handle, $str);
    }

    /**
     * Headers du fichier CSV
     *
     * @param string $filename Nom du fichier à envoyer au navigateur
     * @return void
     */
    public static function headers($filename)
    {
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-Type: text/csv; charset: UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Pragma: public');
    }

    /**
     * Affiche le CSV
     *
     * @return void
     */
    public function display($filename)
    {
        self::headers($filename);
        readfile($this->fileMv);
    }

    /**
     * Supprime le fichier
     *
     * @return void
     */
    public function delete()
    {
        if ($this->fileMv) {
            $file = $this->fileMv;
        } else {
            $file = $this->file;
        }

        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Ferme proprement le fichier
     *
     * @return void
     */
    public function close()
    {
        if ($this->fileMv) {
            /*
             * on ajoute le header
             */
            $this->addHeader();
            rename($this->file, $this->fileMv);
        }

        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
    }

    /**
     * Converti une ligne CSV en tableau de données
     *
     * @return array
     */
    public function fromRaw($line)
    {
        $data = false;
        $line = trim($line);
        if (!empty($line)) {
            $items = str_getcsv($line, $this->separator, $this->container);
            foreach ($items as $item) {
                $data[] = $item;
            }

        }
        return $data;
    }

    /**
     * Converti un tableau de données en ligne CSV
     *
     * @return array
     */
    public function toRaw ($line, $break = "\n")
    {
        $rawLine = array();
        if ($line) {
            foreach ($line as $key => $val) {
                $val = str_replace($this->container, $this->container . $this->container, $val);
                if (strpos($val, $this->container) !== false
                    || strpos($val, $this->separator) !== false
                    || strpos($val, $break) !== false
                    || strpos($val, "\n") !== false
                    || strpos($val, "\r") !== false
                ) {
                    $val = $this->container . $val . $this->container;
                }

                $rawLine[] = $val;
            }
        }
        $rawLine = implode($this->separator, $rawLine) . $break;
        return $rawLine;
    }

    /**
     * Insertion rapide dans un fichier de log
     */
    public static function log ($file, $line)
    {
        $csv = new self();
        $csv->quickOpen($file);
        $csv->addLine($line, true);
        $csv->addHeader();
        $csv->close();
    }

    /**
     *
     *
     * @param array  $line
     * @param string $break
     *
     * @return type
     */
    public static function arrayToRaw ($line, $break = "\n")
    {
        $csv = new self();
        return $csv->toRaw($line, $break);
    }

    /**
     *
     *
     * @param array $lines
     * @param array $options
     *
     * @return array
     */
    public static function arrayFromRaw ($lines, $options = array())
    {
        $data = array();
        $lines = explode("\n", $lines);
        $csv = new self($options);
        foreach($lines as $line) {
            $data[] = $csv->fromRaw($line);
        }
        return $data;
    }
}
