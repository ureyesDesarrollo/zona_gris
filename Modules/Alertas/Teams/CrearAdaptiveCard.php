<?php

namespace Modules\Alertas\Teams;

class CrearAdaptiveCard
{
    public static function crearAdaptiveCardGeneral(array $datos, array $facts): array
    {
        $titulo = $datos['titulo'] ?? '[Sin título]';
        $fecha = $datos['fecha'] ?? date('Y-m-d H:i:s');

        return [
            'type' => 'AdaptiveCard',
            'version' => '1.4',
            'body' => [
                [
                    "type" => "TextBlock",
                    "text" => $titulo,
                    "weight" => "bolder",
                    "size" => "large",
                    "color" => "attention",
                    "wrap" => true
                ],
                [
                    'type' => 'FactSet',
                    'facts' => array_map(function ($fact) {
                        return [
                            'title' => $fact['titulo'] ?? '[Sin título]',
                            'value' => $fact['valor'] ?? '-'
                        ];
                    }, $facts)
                ],
                [
                    'type' => 'TextBlock',
                    'text' => '📅 Fecha de alerta: ' . $fecha,
                    'wrap' => true,
                    'spacing' => 'Medium'
                ]
            ],
            'msteams' => [
                'width' => 'Full'
            ]
        ];
    }

    public static function crearAdaptiveCardSupervisorValidacion(array $datos, array $facts): array
{
    $fecha = $datos['fecha'] ?? date('Y-m-d H:i:s');
    $titulo = $datos['titulo'] ?? 'Datos no validados por supervisor';

    // Construir los facts dinámicamente
    $factElements = [];
    foreach ($facts as $fact) {
        $factElements[] = [
            "type" => "FactSet",
            "facts" => [
                [
                    "title" => $fact['titulo'] ?? '',
                    "value" => $fact['valor'] ?? ''
                ]
            ],
            "spacing" => "Small"
        ];
    }

    return [
        'type' => 'AdaptiveCard',
        'version' => '1.4',
        'body' => array_merge([
            [
                "type" => "TextBlock",
                "text" => $titulo,
                "weight" => "bolder",
                "size" => "large",
                "color" => "attention",
                "wrap" => true
            ],
            [
                'type' => 'TextBlock',
                'text' => '📅 Fecha de alerta: ' . $fecha,
                'wrap' => true,
                'spacing' => 'Medium'
            ]
        ], $factElements),
        'msteams' => [
            'width' => 'Full'
        ]
    ];
}
}
