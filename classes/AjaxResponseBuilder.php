<?php

namespace PrestaShop\Module\AutoUpgrade;

use Symfony\Component\HttpFoundation\JsonResponse;

class AjaxResponseBuilder
{
    /**
     * @param array{newRoute?:string, addScript?:string} $options
     */
    public static function hydrationResponse(string $parentToUpdate, string $newContent, ?array $options = []): JsonResponse
    {
        $arrayToReturn = [
            'hydration' => true,
            'parent_to_update' => $parentToUpdate,
            'new_content' => $newContent,
        ];

        if ($options['newRoute']) {
            $arrayToReturn['new_route'] = $options['newRoute'];
        }

        if ($options['addScript']) {
            $arrayToReturn['add_script'] = $options['addScript'];
        }

        return new JsonResponse($arrayToReturn);
    }

    public static function nextRouteResponse(string $nextRoute): JsonResponse
    {
        return new JsonResponse([
            'next_route' => $nextRoute,
        ]);
    }

    public static function errorResponse(string $error, ?int $errorNumber): JsonResponse
    {
        return new JsonResponse([
            'error' => $error,
        ], $errorNumber ?? 400);
    }
}
