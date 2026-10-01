<?php

namespace App\Support;

class AdminMenu
{
    /**
     * @return list<array{title: string, items: list<array<string, mixed>>}>
     */
    public static function groups(): array
    {
        return [
            [
                'title' => 'Menu',
                'items' => [
                    [
                        'icon' => 'dashboard',
                        'name' => 'Dashboard',
                        'route' => 'admin.dashboard',
                    ],
                    [
                        'icon' => 'user-profile',
                        'name' => 'Users',
                        'route' => 'admin.users.index',
                    ],
                    [
                        'icon' => 'ecommerce',
                        'name' => 'Stores',
                        'route' => 'admin.stores.index',
                    ],
                    [
                        'icon' => 'tables',
                        'name' => 'Products',
                        'route' => 'admin.products.index',
                    ],
                    [
                        'icon' => 'forms',
                        'name' => 'Orders',
                        'route' => 'admin.orders.index',
                    ],
                    [
                        'icon' => 'chat',
                        'name' => 'Reviews',
                        'route' => 'admin.reviews.index',
                    ],
                    [
                        'icon' => 'email',
                        'name' => 'Notifications',
                        'route' => 'admin.notifications.index',
                    ],
                ],
            ],
            [
                'title' => 'Others',
                'items' => [
                    [
                        'icon' => 'pages',
                        'name' => 'Home Page',
                        'route' => 'admin.home-page.index',
                    ],
                    [
                        'icon' => 'authentication',
                        'name' => 'Admins',
                        'route' => 'admin.admins.index',
                    ],
                    [
                        'icon' => 'ui-elements',
                        'name' => 'Settings',
                        'route' => 'admin.settings.index',
                    ],
                    [
                        'icon' => 'support-ticket',
                        'name' => 'Contact Messages',
                        'route' => 'admin.contact-messages.index',
                    ],
                    [
                        'icon' => 'charts',
                        'name' => 'Banners',
                        'route' => 'admin.banners.index',
                    ],
                    [
                        'icon' => 'calendar',
                        'name' => 'Payment Methods',
                        'route' => 'admin.payment-methods.index',
                    ],
                    [
                        'icon' => 'task',
                        'name' => 'Categories',
                        'route' => 'admin.categories.index',
                    ],
                    [
                        'icon' => 'pages',
                        'name' => 'Cities',
                        'route' => 'admin.cities.index',
                    ],
                ],
            ],
        ];
    }
}
