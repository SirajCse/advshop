<?php

return [
    'name' => 'Anuncios',

    'enums' => [
        'announce_placement' => [
            'top' => 'Arriba',
            'bottom' => 'Fijo en la parte inferior',
            'popup' => 'Ventana emergente',
            'theme' => 'Integrado en el tema',
        ],

        'text_alignment' => [
            'start' => 'Inicio',
            'center' => 'Centro',
        ],
    ],

    'validation' => [
        'font_size' => 'El tamaño de fuente debe ser un valor válido de tamaño de fuente CSS.',
        'text_color' => 'El color del texto debe ser un valor hexadecimal de color válido.',
    ],

    'create' => 'Crear nuevo anuncio',
    'add_new' => 'Agregar nuevo',
    'settings' => [
        'name' => 'Anuncio',
        'description' => 'Administrar configuración de anuncios',
    ],

    'background_color' => 'Color de fondo',
    'font_size' => 'Tamaño de fuente',
    'font_size_help' => 'Dejar vacío para usar el valor predeterminado. Ejemplo: 1rem, 1em, 12px, ...',
    'text_color' => 'Color del texto',
    'start_date' => 'Fecha de inicio',
    'end_date' => 'Fecha de finalización',
    'has_action' => 'Tiene acción',
    'action_label' => 'Etiqueta de acción',
    'action_url' => 'URL de acción',
    'action_open_new_tab' => 'Abrir en nueva pestaña',
    'dismissible_label' => 'Permitir al usuario cerrar el anuncio',
    'placement' => 'Ubicación',
    'text_alignment' => 'Alineación del texto',
    'is_active' => 'Está activo',
    'max_width' => 'Ancho máximo',
    'max_width_help' => 'Dejar vacío para usar el valor predeterminado. Ejemplo: 100%, 500px, ...',
    'max_width_unit' => 'Unidad de ancho máximo',
    'font_size_unit' => 'Unidad de tamaño de fuente',
    'autoplay_label' => 'Reproducción automática',
    'autoplay_delay_label' => 'Retraso de reproducción automática',
    'autoplay_delay_help' => 'El retraso entre cada anuncio en milisegundos. Dejar vacío para usar el valor predeterminado (5000).',
    'lazy_loading' => 'Carga diferida',
    'lazy_loading_description' => 'Habilite esta opción para mejorar la velocidad de carga de la página',
    'hide_on_mobile' => 'Ocultar en móvil',
    'dismiss' => 'Cerrar',

    // Placeholders and help texts
    'name_placeholder' => 'Ingrese el nombre del anuncio',
    'name_help' => 'Nombre solo para referencia interna, no visible para los usuarios',
    'content_placeholder' => 'Ingrese su mensaje de anuncio aquí...',
    'content_help' => 'El mensaje que se mostrará a los usuarios. Admite formato HTML.',
    'start_date_placeholder' => 'Seleccione fecha y hora de inicio',
    'start_date_help' => 'El anuncio será visible desde esta fecha. Dejar vacío para comenzar de inmediato.',
    'end_date_placeholder' => 'Seleccione fecha y hora de finalización',
    'end_date_help' => 'El anuncio se ocultará después de esta fecha. Dejar vacío para sin vencimiento.',
    'has_action_help' => 'Agregue un botón de llamada a la acción a su anuncio',
    'action_label_placeholder' => 'ej., Más información, Comprar ahora',
    'action_label_help' => 'Texto que se muestra en el botón de acción',
    'action_url_placeholder' => 'https://ejemplo.com/pagina',
    'action_url_help' => 'URL a la que se redirigirá a los usuarios al hacer clic en el botón de acción',
    'action_open_new_tab_help' => 'Abrir el enlace de acción en una nueva pestaña del navegador',
    'is_active_help' => 'Habilitar o deshabilitar este anuncio sin eliminarlo',
];
