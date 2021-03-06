<?php
/**
 *
 */

namespace PHPTools;

use \Doctrine\DBAL\DriverManager;

/**
 *
 * @uses \Doctrine\DBAL\DriverManager Manager de connection de la librairie
 * Doctrine
 */
class DB
{
    /**
     * Nombre de hit au total
     *
     * @var int
     */
    static public $hits = 0;

    /**
     * Nombre de hit par session
     *
     * @var int
     */
    static public $hits_session = 0;

    /**
     * Connections enregistrées
     *
     * @var \Doctrine\DBAL\Connection[]
     */
    static private $connections;

    /**
     * Nom de la connection crée la première, donc accessible par défaut
     *
     * @var string
     */
    static private $defaultName = null;

    /**
     * Renvoi une connection en fonction de son nom
     *
     * @param string $connectionName Nom de la connection
     *
     * @return \Doctrine\DBAL\Connection
     */
    public static function get($connectionName = null)
    {
        if ($connectionName === null) {
            $connectionName = self::$defaultName;
        }

        return self::$connections[$connectionName];
    }

    /**
     * Quotes a string or an array
     *
     * @param string|array $input
     * @param int          $type
     * @param string       $connectionName
     *
     * @return string|array
     * @see \PDO::quote()
     */
    public static function quote($input, $type = null, $connectionName = null)
    {
        if (is_array($input)) {
            foreach ($input as $k => $v) {
                $input[$k] = self::quote($v, $type, $connectionName);
            }
            return $input;
        }

        return self::get($connectionName)->quote($input, $type);
    }

    /**
     *
     *
     * @param string $table
     * @param string $connectionName
     *
     * @return \Doctrine\DBAL\Schema\Column[]
     */
    public static function columns($table, $connectionName = null)
    {
        return self::get($connectionName)->getSchemaManager()->listTableColumns($table);
    }

    /**
     * Renvoi un QueryBuilder Doctrine sur une connection choisi par son nom
     *
     * @param string $connectionName Nom de la connection
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public static function createQueryBuilder($connectionName = null) {
        return self::get($connectionName)->createQueryBuilder();
    }

    /**
     * Crée une connection et la renvoie
     *
     * @param array $connectionParams
     *
     * @return \Doctrine\DBAL\Connection
     */
    public static function factory($connectionName, $connectionParams)
    {
        self::$connections[$connectionName] = self::createConnection(
            $connectionParams
        );

        if (self::$defaultName === null) {
            self::$defaultName = $connectionName;
        }

        return self::$connections[$connectionName];
    }

    /**
     * Crée une connection
     *
     * @param array $connectionParams
     *
     * @return \Doctrine\DBAL\Connection
     */
    protected static function createConnection($connectionParams)
    {
        $connection = DriverManager::getConnection($connectionParams);

        $connection
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping('enum', 'string')
        ;

        $sqlLogger = new SQLLogger();
        $configuration = $connection->getConfiguration()->setSQLLogger($sqlLogger);

        return $connection;
    }

    public static function hitPlus()
    {
        self::$hits++;
        self::$hits_session++;
    }

    public static function reset()
    {
        self::$hits_session = 0;
    }
}

