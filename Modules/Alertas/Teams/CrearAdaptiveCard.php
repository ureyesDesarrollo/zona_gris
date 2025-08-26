<?php

namespace Modules\Alertas\Teams;

class CrearAdaptiveCard {
    public static function crearAdaptiveCard(array $datos, array $facts): array {
    return [
        'type' => 'AdaptiveCard',
        'version' => '1.4',
        'body' => [
            [
                "type" => "TextBlock",
                "text" => $datos['titulo'],
                "weight" => "bolder",
                "size" => "large",
                "color" => "attention",
                "wrap" => true
            ],
            [
                'type' => 'FactSet',
                'facts' => array_map(function ($fact) {
                    return [
                        'title' => $fact['titulo'],
                        'value' => $fact['valor']
                    ];
                }, $facts)
            ],
            [
                'type' => 'TextBlock',
                'text' => '📅 Fecha de alerta: ' . ($datos['fecha'] ?? date('Y-m-d H:i:s')),
                'wrap' => true,
                'spacing' => 'Medium'
            ]
        ],
        'msteams' => [
            'width' => 'Full'
        ]
    ];
    }
}