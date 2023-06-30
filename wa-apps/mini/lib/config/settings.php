<?php

return [
    'warnings' => [
        'title' => '',
        'control_type' => waHtmlControl::CUSTOM.' '.'mini::getWarnings',
    ],

    'enabled' => [
        'title' => 'Включить приложение',
        'description' => '',
        'control_type' => waHtmlControl::RADIOGROUP,
        'disabled' => waSystemConfig::isDebug(),
        'options' => [
            [
                'value' => '0',
                'title' => 'Выключить полностью',
                'description' => 'Приложение становится неактивно' ,
            ],
            [
                'value' => '1',
                'title' => 'Включить только для администраторов',
                'description' => 'Полезно при отладке, посетители не видят изменений',
            ],
            [
                'value' => '2',
                'title' => 'Включить для всех при первом посещении',
                'description' => 'Полезно для SEO, робот будет видеть оптимизированную версию, остальные - оригинальную',
            ],
            [
                'value' => '3',
                'title' => 'Включить для всех, всегда',
                'description' => 'Приложение работает в стандартном режиме',
            ],
        ],
        'value' => '0',
    ],

    'html' => [
        'title' => 'HTML',
        'description' => '',
        'control_type' => waHtmlControl::RADIOGROUP,
        'options' => [
            [
                'value' => '0',
                'title' => 'Не обрабатывать HTML',
                'description' => 'Сжатия итогового кода страницы не производится, она отдается как есть. При этом остальные ресурсы могут быть обработаны.',
            ],
            [
                'value' => '1',
                'title' => 'Включить сжатие HTML',
                'description' => 'Из исходного кода страницы удаляются комментарии, лишние блоки и символы. Возможность проблем после включения минимизирована, но стоит проверить работу сайта.',
            ],
        ],
        'value' => '0',
    ],

//    'ignorehtml' => array(
//        'title' => 'Игнорировать в HTML',
//        'description' => 'Если в коде страницы определенные части HTML нужно оставить на месте, то в этом поле можно указать регулярные выражения PHP, по одному на строку.',
//        'control_type' => waHtmlControl::TEXTAREA,
//    ),

    'css' => [
        'title' => 'CSS',
        'description' => '',
        'control_type' => waHtmlControl::RADIOGROUP,
        'options' => [
            [
                'value' => '0',
                'title' => 'Отключить обработку CSS',
                'description' => 'Не обрабатывать CSS-файлы на страницах вообще.',
            ],
            [
                'value' => '1',
                'title' => 'Только перенести CSS вниз',
                'description' => 'Все подключаемые внешние файлы стилей и inline-стили будут перенесены в конец кода страницы, непосредственно к тегу body.',
            ],
            [
                'value' => '2',
                'title' => 'Сжать каждый файл отдельно',
                'description' => 'Каждый подключаемый CSS-файл будет обработан компрессором, закеширован, а ссылка на странице будет заменена.',
            ],
//            array(
//                'value' => '3',
//                'title' => 'Сжать CSS в один файл',
//                'description' => 'Все CSS-файлы и inline-стили будут слиты в один файл, который будет подключен в указанном далее месте в коде страницы. Самый эффективный, но проблемный вариант.',
//            ),
        ],
        'value' => '0',
    ],

    'ignorecss' => [
        'title' => 'Игнорировать в CSS',
        'description' => 'Если какие-то стили нужно оставить на месте, то здесь можно перечислить подстроки, по которым эти стили будут найдены, часть URL или самого кода, по одной на строке.',
        'control_type' => waHtmlControl::TEXTAREA,
    ],

    'auto_css' => [
        'title' => 'Автоматически получать критический CSS',
        'description' => 'При посещении страниц администратором, если для нее еще не был получен критический CSS, то запрос будет отправлен автоматически. Работает, если включено меню администратора ниже.',
        'control_type' => waHtmlControl::CHECKBOX,
        'value' => 0,
    ],

    'defer_css' => [
        'title' => 'Откладывать исполнение CSS',
        'description' => 'Экспериментальная опция, может дать несколько баллов в PageSpeed, не рекомендуется без необходимости.',
        'control_type' => waHtmlControl::CHECKBOX,
        'value' => 0,
    ],

    'js' => [
        'title' => 'JavaScript',
        'description' => '',
        'control_type' => waHtmlControl::RADIOGROUP,
        'options' => [
            [
                'value' => '0',
                'title' => 'Отключить обработку JS',
                'description' => 'Не обрабатывать JS-файлы на страницах вообще.',
            ],
            [
                'value' => '1',
                'title' => 'Только перенести JS вниз',
                'description' => 'Тэги со скриптами будут перенесены в самый низ страницы к тегу body.',
            ],
            [
                'value' => '2',
                'title' => 'Сжать каждый файл отдельно',
                'description' => 'Каждый JS-файл, если он еще не сжат, будет минифицирован, закеширован, ссылки заменены.',
            ],
//            array(
//                'value' => '3',
//                'title' => 'Сжать JS в один файл',
//                'description' => 'Весь JS-код со страницы будет собран в один файл. Не рекомендуется использовать',
//            ),
        ],
        'value' => '0',
    ],

    'ignorejs' => [
        'title' => 'Игнорировать в JS',
        'description' => 'Если какие-то скрипты нужно оставить на месте, то здесь можно перечислить подстроки, по которым эти скрипты будут найдены, часть URL или самого кода, по одной на строке.',
        'control_type' => waHtmlControl::TEXTAREA,
    ],

    'cache_resources' => [
        'title' => 'Кэшировать внешние ресурсы JS и CSS на сервере',
        'description' => 'Стоит использовать с осторожностью.',
        'control_type' => waHtmlControl::CHECKBOX,
        'value' => 0,
    ],

    'font_swap' => [
        'title' => 'Показ текста во время загрузки веб-шрифтов',
        'description' => 'При загрузке сайта разрешать использовать временный похожий шрифт.',
        'control_type' => waHtmlControl::CHECKBOX,
        'value' => 0,
    ],

    'font_cache' => [
        'title' => 'Кэшировать внешние веб-шрифты ',
        'description' => 'Сохранять Google Fonts и подобные внешние шрифты на сервер.',
        'control_type' => waHtmlControl::CHECKBOX,
        'value' => 0,
    ],

    'preload' => [
        'title' => 'Preload',
        'description' => '',
        'control_type' => waHtmlControl::RADIOGROUP,
        'options' => [
            [
                'value' => '0',
                'title' => 'Отключить',
                'description' => 'Не будет попыток встроить ресурсы для предзагрузки',
            ],
            [
                'value' => '1',
                'title' => 'В заголовках сервера',
                'description' => 'Используется для HTTP/2 PUSH',
            ],
            [
                'value' => '2',
                'title' => 'В коде страницы',
                'description' => 'Самый простой способ',
            ],
        ],
        'value' => '0',
    ],

    'lazyimage' => [
        'title' => 'Включить ленивую загрузку изображений (lazyload)',
        'description' => 'При посещении страницы будут загружаться только изображения в области видимости.',
        'control_type' => waHtmlControl::CHECKBOX,
        'value' => 0,
    ],

    'lazy_count' => [
        'title' => 'Пропустить изображения',
        'description' => 'Укажите число изображений от начала страницы, для которых не надо включать ленивую загрузку. Оставьте пустым, если включать для всех.',
        'control_type' => waHtmlControl::INPUT,
        'value' => 0,
    ],

    'lazy_sizes' => [
        'title' => 'Передавать размеры ленивого изображения',
        'description' => 'Каждое изображение будет передаваться с проверенными размерами, может несколько замедлять работу.',
        'control_type' => waHtmlControl::CHECKBOX,
        'value' => 0,
    ],

    'menu' => [
        'title' => 'Меню администратора',
        'description' => 'Включить для администраторов меню на страницах сайта для доступа к функциям приложения.',
        'control_type' => waHtmlControl::CHECKBOX,
        'value' => 1,
    ],

    'stats' => [
        'title' => 'Занято в кэше',
        'description' => 'Если занимаемый объем постоянно и неконтролируемо растет, стоит проверить mini.log, чтобы добавить игнорируемые скрипты.',
        'control_type' => waHtmlControl::HELP,
        'value' => mini::getStats(),
    ],

    'salt' => [
        'title' => 'Соль для генерации уникальной ссылки при каждом сохранении настроек',
        'description' => '',
        'control_type' => waHtmlControl::HIDDEN,
        'value' => '123456',
    ],
];