<?php

namespace App\Providers;

use App\QRCode;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Bridge\User;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use RuntimeException;

class QRCodeGrantUserRepository implements UserRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getUserEntityByUserCredentials($id, $password, $grantType, ClientEntityInterface $clientEntity)
    {
        $qrCode = QRCode::find($id);

        if (!$qrCode) {
            return;
        }

        if (!Hash::check($password, $qrCode->password)) {
            return;
        }

        $provider = config('auth.guards.api.provider');

        if (is_null($model = config('auth.providers.'.$provider.'.model'))) {
            throw new RuntimeException('Unable to determine authentication model from configuration.');
        }

        $user = (new $model)->find($qrCode->user_id);

        if (!$user) {
            return;
        }

        return new User($user->getAuthIdentifier());
    }
}
