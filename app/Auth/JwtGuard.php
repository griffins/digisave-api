<?php

namespace App\Auth;


use App\User;
use Firebase\JWT\JWT;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;

class JwtGuard implements Guard
{

    protected $provider;
    protected $currentUser;
    protected $token;

    /**
     * JwtGuard constructor.
     * @param $provider
     */
    public function __construct($provider)
    {
        $this->provider = $provider;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        return $this->authenticateViaBearer();
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        return !$this->check();
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        return $this->currentUser;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|null
     */
    public function id()
    {
        return $this->currentUser->id;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array $credentials
     * @return bool
     */

    public function validate(array $credentials = [])
    {
        $user = $this->provider->retrieveByCredentials($credentials);
        return !is_null($user) && $this->provider->validateCredentials($user, $credentials);
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array $credentials
     * @return bool
     */

    public function attempt(array $credentials = [])
    {
        $user = $this->provider->retrieveByCredentials($credentials);
        if (!is_null($user) && $this->provider->validateCredentials($user, $credentials)) {
            $this->currentUser = $user;
            return true;
        }
        return false;
    }

    public function bearerToken()
    {
        $bearer = request()->hasHeader('authorization') ? request()->header('authorization') : '';
        $bearer = explode(' ', $bearer);

        if (count($bearer) == 2 && strtolower($bearer[0]) == 'bearer') {
            $bearer = $bearer[1];
        } else {
            $bearer = null;
        }
        return coalesce(request('token'), $bearer);
    }

    public function decodeToken($token)
    {
        try {
            return JWT::decode($token, $this->getPublicKey(), ['RS256']);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getToken($lifeTime = 0)
    {
        if ($lifeTime == 0) {
            $lifeTime = (config('session.lifetime') * 60);
        }

        $token = [
            "iss" => url('/'),
            "aud" => url('/'),
            "exp" => time() + $lifeTime,
            "iat" => time(),
            "nbf" => time(),
            "sub" => $this->currentUser->id
        ];

        $token = array_merge($token, $this->currentUser->claims());

        return JWT::encode($token, $this->getPrivateKey(), 'RS256');
    }

    protected function authenticateViaBearer()
    {
        $token = $this->decodeToken($this->bearerToken());
        if ($token) {
            if ($this->isValidToken($token)) {
                $user = $this->provider->retrieveById($token->sub);
                if ($user) {
                    $this->token = $token;
                    $this->currentUser = $user;
                    return true;
                }
            }
        } else if (config('auth.driver') == null) {
            $user = User::query()->first();
            $this->currentUser = $user;
            return true;
        }
        return false;
    }

    public function isValidToken($token)
    {
        //todo check for revoked tokens, for now am just checking expiry dates.
        return time() <= $token->exp && time() >= $token->nbf && $token->aud == url('/');
        //todo develop a proper workaround for tests. time() >= $token->nbf is a temporary fix for tests.
    }

    /**
     * Set the current user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @return void
     */
    public function setUser(Authenticatable $user)
    {
        $this->currentUser = $user;
    }

    protected function getPublicKey()
    {
        $path = sprintf("%s/key.pub", config('app.key_path'));
        return @file_get_contents($path);
    }

    protected function getPrivateKey()
    {
        $path = sprintf("%s/key", config('app.key_path'));
        return @file_get_contents($path);
    }
}