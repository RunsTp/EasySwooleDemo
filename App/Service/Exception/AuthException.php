<?php


namespace App\Service\Exception;


class AuthException extends ServiceException
{
    public function getErrorMessage(): ?string
    {
        return parent::getErrorMessage() ?? parent::getMessage();
    }
}