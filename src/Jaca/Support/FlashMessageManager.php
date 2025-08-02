<?php
namespace Jaca\Support;

use Jaca\Support\SessionManager;

/**
 * Class FlashMessageManager
 *
 * Provides a way to store temporary session data (flash messages) that are
 * available for the next request only and are then automatically removed.
 *
 * This is useful for passing status messages (like success or error notices)
 * between redirects.
 *
 * @package Jaca\Support
 */
class FlashMessageManager
{
    /**
     * The session manager instance.
     *
     * @var SessionManager
     */
    private SessionManager $session;

    /**
     * Key used to namespace session data in $_SESSION.
     */
    private const SESSION_KEY = '_flash';

    /**
     * FlashMessageManager constructor.
     *
     * @param SessionManager $session The session manager to use for storing flash messages.
     */
    public function __construct(SessionManager $session)
    {
        $this->session = $session;
    }

    /**
     * Sets a flash message value for the given key.
     *
     * This data will be available for the next request only.
     *
     * @param string $key   The key of the flash message.
     * @param mixed  $value The value to store temporarily.
     *
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $this->session->set(self::SESSION_KEY . '.' . $key, $value);
    }

    /**
     * Retrieves a flash message by key and removes it from the session.
     *
     * If the key does not exist, returns null.
     *
     * @param string $key The key of the flash message to retrieve.
     *
     * @return mixed|null The value of the flash message, or null if not set.
     */
    public function get(string $key): mixed
    {
        $value = $this->session->get(self::SESSION_KEY . '.' . $key);
        $this->session->remove(self::SESSION_KEY . '.' . $key);
        return $value;
    }

    /**
     * Retrieves all flash messages from the session and removes them.
     *
     * This method returns an associative array where keys are message types (e.g. 'success', 'error')
     * and values are the messages stored under each type.
     * After calling this method, all flash messages are cleared from the session.
     *
     * @return array<string, mixed> An associative array of all flash messages.
     */
    public function getAll(): array
    {
        $flashData = $this->session->getByPart(self::SESSION_KEY) ?? [];

        foreach ($flashData as $key => $val) {
            $this->session->remove($key);
        }

        return $flashData;
    }
}
