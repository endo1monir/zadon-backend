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
                'title' => 'admin.nav.groups.menu',
                'items' => [
                    ['icon' => 'dashboard', 'key' => 'dashboard', 'route' => 'admin.dashboard'],
                    ['icon' => 'authentication', 'key' => 'admins', 'route' => 'admin.admins.index'],
                    ['icon' => 'user-profile', 'key' => 'users', 'route' => 'admin.users.index'],
                    ['icon' => 'ecommerce', 'key' => 'stores', 'route' => 'admin.stores.index'],
                    ['icon' => 'authentication', 'key' => 'vendors', 'route' => 'admin.vendors.index'],
                    ['icon' => 'tables', 'key' => 'products', 'route' => 'admin.products.index'],
                    ['icon' => 'forms', 'key' => 'orders', 'route' => 'admin.orders.index'],
                    ['icon' => 'chat', 'key' => 'reviews', 'route' => 'admin.reviews.index'],
                    ['icon' => 'email', 'key' => 'notifications', 'route' => 'admin.notifications.index'],
                ],
            ],
            [
                'title' => 'admin.nav.groups.others',
                'items' => [
                    ['icon' => 'pages', 'key' => 'home_page', 'route' => 'admin.home-page.index'],
                    ['icon' => 'ui-elements', 'key' => 'settings', 'route' => 'admin.settings.index'],
                    ['icon' => 'support-ticket', 'key' => 'contact_messages', 'route' => 'admin.contact-messages.index'],
                    ['icon' => 'charts', 'key' => 'banners', 'route' => 'admin.banners.index'],
                    ['icon' => 'calendar', 'key' => 'payment_methods', 'route' => 'admin.payment-methods.index'],
                    ['icon' => 'task', 'key' => 'categories', 'route' => 'admin.categories.index'],
                    ['icon' => 'chat', 'key' => 'socials', 'route' => 'admin.socials.index'],
                    ['icon' => 'pages', 'key' => 'cities', 'route' => 'admin.cities.index'],
                ],
            ],
        ];
    }
}
