<?php
namespace Jaca\Support;

use Jaca\Support\Interfaces\ISession;

/**
 * Class SessionManager
 *
 * Provides a singleton interface for managing session data in a structured way.
 * Wraps native PHP session handling with a scoped session namespace for storing values.
 *
 * @package Jaca\Support
 */
class SessionManager implements ISession
{
    /**
     * Holds the singleton instance.
     *
     * @var SessionManager|null
     */
    private static ?ISession $instance;

    /**
     * Key used to namespace session data in $_SESSION.
     */
    private const SESSION_KEY = 'jaca_session';

    /**
     * Private constructor to enforce singleton usage.
     * Starts the session if not already active.
     */
    private function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * Returns the singleton instance of the SessionManager.
     *
     * @return SessionManager
     */
    public static function getInstance(): SessionManager
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Prevents cloning of the singleton instance.
     *
     * @throws \Exception Always thrown to prevent cloning.
     */
    private function __clone()
    {
        throw new \Exception('Cloning SessionManager is not allowed.');
    }

    /**
     * Stores a value in the session under the given key.
     *
     * @param string $key   The key to store the value under.
     * @param mixed  $value The value to store.
     *
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $_SESSION[self::SESSION_KEY][$key] = $value;
    }

    /**
     * Retrieves a value from the session by key.
     *
     * @param string $key The key of the value to retrieve.
     *
     * @return mixed|null The stored value, or null if not set.
     */
    public function get(string $key): mixed
    {
        return $_SESSION[self::SESSION_KEY][$key] ?? null;
    }

    /**
     * Checks if a given key exists in the session.
     *
     * @param string $key The key to check for existence.
     *
     * @return bool True if the key exists, false otherwise.
     */
    public function check(string $key): bool
    {
        return isset($_SESSION[self::SESSION_KEY][$key]);
    }

    /**
     * Removes a specific key from the session.
     *
     * @param string $key The key to remove.
     *
     * @return void
     */
    public function remove(string $key): void
    {
        unset($_SESSION[self::SESSION_KEY][$key]);
    }

    /**
     * Destroys the session and clears all stored values under the namespace.
     *
     * @return void
     */
    public function destroy(): void
    {
        if (isset($_SESSION[self::SESSION_KEY])) {
            unset($_SESSION[self::SESSION_KEY]);
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * Retorna um array com todos os itens da sessão cujo nome da chave contém a substring fornecida.
     *
     * @param string $keyPart Substring que deve estar presente nas chaves dos dados na sessão.
     * @return array Array associativo com as chaves e valores que correspondem ao filtro.
     */
    public function getByPart(string $keyPart): array
    {
        // Pega todos os dados do namespace da sessão, ou um array vazio se não existir
        $all = $_SESSION[self::SESSION_KEY] ?? [];

        // Array para armazenar os pares chave/valor que correspondem ao filtro
        $result = [];

        // Percorre todos os itens da sessão
        foreach ($all as $key => $value) {
            // Verifica se a substring $keyPart está presente na chave atual
            if (strpos($key, $keyPart) !== false) {
                // Se sim, adiciona essa chave e valor ao resultado
                $result[$key] = $value;
            }
        }

        // Retorna o array filtrado com as chaves que contêm a substring
        return $result;
    }
}
