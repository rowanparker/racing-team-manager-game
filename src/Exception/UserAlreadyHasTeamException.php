<?php

namespace App\Exception;

use Exception;

final class UserAlreadyHasTeamException extends Exception
{
    protected $message = 'User already has a team assigned.';
}
