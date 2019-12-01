<?php

namespace App\Infrastructure\Token;

interface TokenService
{
    /**
     * @return string
     */
    public function generateToken(): string;

    /**
     * @param string $isoDate
     * @param string $identifierToken
     * @param array $data
     * @return string
     */
    public function encodeJwt(string $isoDate, string $identifierToken, array $data): string;

    /**
     * @param string $token
     * @return array
     */
    public function decodeJwt(string $token): array;
}