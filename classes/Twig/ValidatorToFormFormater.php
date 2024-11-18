<?php

namespace PrestaShop\Module\AutoUpgrade\Twig;

abstract class ValidatorToFormFormater
{
    /**
     * @param array<array{message:string, target?:string}> $errors
     *
     * @return array<'global'|string, string>
     */
    public static function format($errors): array
    {
        return array_column(
            array_map(function ($error) {
                return [
                    'key' => $error['target'] ?? 'global',
                    'value' => $error['message'],
                ];
            }, $errors),
            'value',
            'key'
        );
    }
}
