<?php
namespace Admin;

use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;
return [
    'router' => [
        'routes' => [
            'usermgr' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/admin',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'index'
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'index' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/index[/][:action[/]]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ],
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'usermgr' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/user[/][:action[/]]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ],
                            'defaults' => [
                                'controller' => Controller\UsermgrController::class,
                                'action' => 'index'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => InvokableFactory::class,
            Controller\UsermgrController::class => InvokableFactory::class
        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view'
        ]
    ]
];
