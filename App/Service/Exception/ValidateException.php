<?php


namespace App\Service\Exception;


class ValidateException extends ServiceException
{
    public function getErrorMessage(): ?string
    {
        return parent::getErrorMessage() ?? parent::getMessage();
    }
}