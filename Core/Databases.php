<?php
/**
 *
 */

namespace PHPTools;

/**
 *
 * @uses \Doctrine\DBAL\DriverManager Manager de connection de la librairie
 * Doctrine
 */
class Databases
{
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
        return \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
    }
}

