<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\HexbatchTextException;
use App\Http\Controllers\Controller;
use App\OpenApi\App\AboutResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use Symfony\Component\HttpFoundation\Response as CodeOf;

class AppController extends Controller
{


    #[OA\Get(
        path: '/api/v1/app/about',
        operationId: 'api.app.about',
        description: 'This returns information about the app, including versions of each component',
        summary: 'Returns app information',
        security: [['bearerAuth' => []]],
        tags: ['app'],
        responses: [
            new OA\Response( response: CodeOf::HTTP_OK, description: 'Gives information about the app',content: new JsonContent(ref: AboutResponse::class))
        ]
    )]
    public function about_app() : JsonResponse
    {

        try {
            return response()->json(
                \MattyRad\OpenApi\Serializer::serialize(new AboutResponse() ), CodeOf::HTTP_OK);
        }  catch (\Exception $e) {
            $out_code = $e->getCode();
            if (!$e->getCode() || !ctype_digit($e->getCode())) { $out_code = CodeOf::HTTP_INTERNAL_SERVER_ERROR;}
            throw new HexbatchTextException($e->getMessage(),$out_code,$e);
        }
    }

}
