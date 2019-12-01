<?php

namespace App\Infrastructure\Token;

use App\Application\Configuration\AppConfiguration;
use App\Domain\Exception\DomainServiceException;
use Exception;
use Firebase\JWT\JWT;

class SimpleTokenService implements TokenService
{
    /**
     * {@inheritdoc}
     */
    public function generateToken(): string
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }

    /**
     * {@inheritdoc}
     */
    public function encodeJwt(string $isoDate, string $identifierToken, array $data): string
    {
        $config = AppConfiguration::getKey('security');
        $issuedAt = strtotime($isoDate);
        $expires = $issuedAt + $config['jwt_expiration'];
        $secret = base64_decode($config['jwt_secret']);
        $algorithm = $config['jwt_alg'];
        $payload = [
            'iat' => $issuedAt,
            'jti' => $identifierToken,
            'iss' => $config['server_name'],
            'exp' => $expires,
            'data' => $data
        ];
        try {
            return JWT::encode($payload, $secret, $algorithm);
        } catch (Exception $e) {
            throw new DomainServiceException(sprintf('Error encoding JWT for identifier token: %s', $identifierToken));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function decodeJwt(string $token): array
    {
        $config = AppConfiguration::getKey('security');
        $secret = base64_decode($config['jwt_secret']);
        $algorithm = $config['jwt_alg'];
        try {
            return json_decode(json_encode(JWT::decode($token, $secret, [$algorithm])), true);
        } catch (Exception $e) {
            throw new DomainServiceException(sprintf('Error decoding JWT token: %s', $token));
        }
    }
}