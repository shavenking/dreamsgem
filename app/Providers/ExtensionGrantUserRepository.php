<?php

namespace App\Providers;

use GuzzleHttp\Client;
use Laravel\Passport\Bridge\User;
use RuntimeException;
use Illuminate\Hashing\HashManager;
use Illuminate\Contracts\Hashing\Hasher;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

class ExtensionGrantUserRepository implements UserRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getUserEntityByUserCredentials($username, $password, $grantType, ClientEntityInterface $clientEntity)
    {
        $provider = config('auth.guards.api.provider');

        if (is_null($model = config('auth.providers.'.$provider.'.model'))) {
            throw new RuntimeException('Unable to determine authentication model from configuration.');
        }

        if (method_exists($model, 'findForPassport')) {
            $user = (new $model)->findForPassport($username);
        } else {
            $user = (new $model)->where('email', $username)->first();
        }

        if (! $user) {
            return;
        } elseif (method_exists($user, 'validateForPassportPasswordGrant')) {
            if (! $user->validateForPassportPasswordGrant($password)) {
                return;
            }
        }

        $response = app(Client::class)->get(env('APP_AUTH_ENDPOINT'), [
            'headers' => [
                'E-Mail' => $username,
                'Passcode' => $password
            ],
            'http_errors' => false,
        ]);

        if ($response->getStatusCode() !== \Illuminate\Http\Response::HTTP_OK) {
            return;
        }

        return new User($user->getAuthIdentifier());
    }
}
