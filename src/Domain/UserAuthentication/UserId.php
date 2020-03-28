<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication;

final class UserId
{
    private string $id;

    private function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function fromString(string $id) : self
    {
        return new self($id);
    }

    public function toString() : string
    {
        return $this->id;
    }
}
