<?php

namespace App\Services;

class ApplicationCreationUnavailableException extends \RuntimeException
{
    public function __construct(public readonly PpdbAvailability $availability)
    {
        parent::__construct($availability->creationBlockedMessage());
    }
}
