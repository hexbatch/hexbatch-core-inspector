<?php

namespace App\OpenApi\User\Password;


use OpenApi\Attributes as OA;

/**
 * Resetting a password requires an email
 */
#[OA\Schema(schema: 'PasswordResetParams',title: "Password reset parameters",example: ["email"=>"will@business.org"])]

class PasswordResetParams
{
    #[OA\Property(title: 'Email to use for link',type: 'string', maxLength: 60, minLength: 5,
        example: 'will@business.org'

    )]
    protected string $email;

}
