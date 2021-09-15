<?php
declare(strict_types=1);

namespace TrayDigita\ThemeUpdater\Adapter;

use TrayDigita\ThemeUpdater\AbstractAdapter;
use function strtolower;

/**
 * @property-read string|null $token
 * @property-read string $preferred_authentication_mode
 * @property-read array<string, string|null> $auth
 */
abstract class AbstractGithubAPI extends AbstractAdapter
{
    /**
     * @var null|string token
     */
    protected $token = null;

    /**
     * @var string
     */
    protected $preferred_authentication_mode = 'token';

    /**
     * @var array<string, string|null>
     */
    protected $auth = [
        'username' => null,
        'password' => null,
    ];

    protected $github_base_api_url  = 'https://api.github.com/';

    /**
     * @param string|null $token null to disable token
     */
    public function setToken(string $token = null)
    {
        $this->token = $token;
    }

    /**
     * Set auth username & password
     *
     * @param string|null $username
     * @param string|null $password
     */
    public function setAuth(string $username = null, string $password = null)
    {
        $this->auth['username'] = $username;
        $this->auth['password'] = $password;
    }

    public function setPreferredAuthenticationMode(string $preferred)
    {
        $preferred                           = strtolower(trim($preferred));
        $this->preferred_authentication_mode = $preferred === 'token' ? 'token' : 'login';
    }

    /**
     * @return string
     */
    public function getPreferredAuthenticationMode(): string
    {
        return $this->preferred_authentication_mode;
    }

    /**
     * @return string|null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return null[]|string[]
     */
    public function getAuth(): array
    {
        return $this->auth;
    }

    /**
     * @return string
     */
    public function getGithubBaseApiUrl(): string
    {
        return $this->github_base_api_url;
    }
}
