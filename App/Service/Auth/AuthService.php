<?php


namespace App\Service\Auth;


use App\Service\BaseService;
use App\Service\Exception\AuthException;
use Firebase\JWT\JWT;

class AuthService extends BaseService
{
    /** @var string|null 私钥 */
    private static $privateKey;
    /** @var string|null 公钥 */
    private static $publicKey;

    /**
     * sign
     *
     * @param array $payload
     * @return string
     */
    public static function sign(array $payload): string
    {
        return JWT::encode($payload, self::getPrivateKey(), 'RS256');
    }

    /**
     * check
     *
     * @param string      $token
     * @param string|null $publicKey
     * @return array
     * @throws AuthException
     */
    public static function check(string $token, string $publicKey = null): array
    {
        try {
            $publicKey = $publicKey ?? self::getPublicKey();
            return (array)JWT::decode($token, $publicKey, ['RS256']);
        }catch (\UnexpectedValueException $e) {
            throw new AuthException($e->getMessage());
        }
    }

    /**
     * getPrivateKey
     *
     * @return string
     */
    protected static function getPrivateKey(): string
    {
        if (empty(self::$privateKey)) {
            self::$privateKey = file_get_contents(EASYSWOOLE_ROOT . '/private.key');
        }

        return self::$privateKey;
    }

    /**
     * getPublicKey
     *
     * @return string
     */
    protected static function getPublicKey(): string
    {
        if (empty(self::$publicKey)) {
            self::$publicKey = file_get_contents(EASYSWOOLE_ROOT . '/public.key');
        }

        return self::$publicKey;
    }
}