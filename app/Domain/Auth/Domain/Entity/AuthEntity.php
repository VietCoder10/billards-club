<?php

namespace App\Domain\Auth\Domain\Entity;

/**
 * 認証エンティティ
 * @package App\Domain\Auth\Domain\Entity
 */
class AuthEntity
{
    private string $token;
    private string $message;

    public function __construct(string $token = '', string $message = '')
    {
        $this->setToken($token);
        $this->setMessage($message);
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
