<?php


namespace App\Service\Exception;


class ServiceException extends \Exception
{
    private $errorMessage;

    public function setErrorMessage(string $errorMessage) : ServiceException
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    public function getErrorMessage() : ?string
    {
        if (!empty($this->errorMessage)) {
            return $this->getErrorMessage();
        }
        return ErrorMessage::ERROR_MAP[$this->getCode()] ?? null;
    }
}