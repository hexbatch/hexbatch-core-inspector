<?php

namespace App\OpenApi\User\Login;


use OpenApi\Attributes as OA;

/**
 * Login requires a username and password
 */
#[OA\Schema(schema: 'LoginParams', title: "Login Data", required: ['username','password'],
    example: ["username"=>"will","password"=>"past-pass-power",'device_token'=>"c2pIsrDb4v6cZS6u4uNYhx1v3sBibXci1_zI_BCb"])  ]

class LoginParams
{
    #[OA\Property(title: 'User name',type: 'string', maxLength: 30, minLength: 3,
        example: 'dobby_1987'

    )]
    protected string $username;

    #[OA\Property(  title: 'Password',type: 'string', format: 'password',
                    example: 'beans_r_88good'
    )]
    protected string $password;



}
