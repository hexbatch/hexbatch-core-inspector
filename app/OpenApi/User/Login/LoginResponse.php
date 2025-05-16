<?php

namespace App\OpenApi\User\Login;

use OpenApi\Attributes as OA;

/**
 * Returns the token that is required by other api calls
 */
#[OA\Schema(schema: 'LoginResponse',title: "Login response")]
class LoginResponse
{


    #[OA\Property( title: 'Auth Token', type: 'string',format: 'password',example: '4|6u2HE63lbItRImAklzn96Axf1zUzXYYHOFotGLLR4fb81fa0')]
    public string $auth_token;


    public function __construct(string $auth_token)
    {
        $this->auth_token = $auth_token;
    }


}
