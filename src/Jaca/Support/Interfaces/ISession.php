<?php
namespace Jaca\Support\Interfaces;

/**
 * Interface ISession
 *
 * Defines a contract for session management operations.
 *
 * @package Jaca\Support
 */
interface ISession
{
    /**
     * Stores a value in the session under the given key.
     *
     * @param string $key   The key to associate with the value.
     * @param mixed  $value The value to store.
     *
     * @return void
     */
    public function set(string $key, mixed $value): void;

    /**
     * Retrieves a value from the session by key.
     *
     * @param string $key The key of the value to retrieve.
     *
     * @return mixed The value stored under the key, or null if not set.
     */
    public function get(string $key): mixed;

    /**
     * Checks whether a given key exists in the session.
     *
     * @param string $key The key to check.
     *
     * @return bool True if the key exists, false otherwise.
     */
    public function check(string $key): bool;

    /**
     * Removes a specific key from the session.
     *
     * @param string $key The key to remove.
     *
     * @return void
     */
    public function remove(string $key): void;

    /**
     * Clears the session data and optionally destroys the session.
     *
     * @return void
     */
    public function destroy(): void;

    /**
     * Retorna um array com todos os itens da sessão cujo nome da chave contém a substring fornecida.
     *
     * @param string $keyPart Substring que deve estar presente nas chaves dos dados na sessão.
     * @return array Array associativo com as chaves e valores que correspondem ao filtro.
     */
    public function getByPart(string $keyPart): array;
}
