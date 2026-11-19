<?php

/*
|--------------------------------------------------------------------------
| AI Settings, Prompts and Tool Descriptions
|--------------------------------------------------------------------------
|
| Everything the app tells the AI model lives in this file: which model to
| use, the instructions (system prompts) for every feature, and the
| description of every tool the chatbot can call.
|
| To change how a feature behaves, edit the wording here. The PHP code only
| reads these values and never hard-codes prompt text.
|
| After editing on a server that caches config, run: php artisan config:clear
|
*/

return [

    'provider' => env('AI_PROVIDER', 'gemini'),

    'model' => env('AI_MODEL', 'gemini-flash-lite-latest'),

    /*
    |--------------------------------------------------------------------------
    | Chatbot Limits and Rules
    |--------------------------------------------------------------------------
    |
    | max_steps:            how many tools the model may call before answering.
    | max_products:         how many products one search returns to the model.
    | max_orders:           how many past orders the model can read at once.
    | max_history:          how many previous chat messages are sent with each request.
    | confirmation_minutes: how long an action waits for the customer's "yes".
    | payment_methods:      payment method keys the chatbot may place orders with.
    |                       Card / Apple Pay need the payment screen, so they are left out.
    |
    */

    'chat' => [
        'max_steps' => 8,
        'max_products' => 5,
        'max_orders' => 5,
        'max_history' => 10,
        'confirmation_minutes' => 10,
        'payment_methods' => ['cash', 'wallet'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Prompts
    |--------------------------------------------------------------------------
    |
    | Placeholders such as :language, :name or :details are filled in by the code.
    |
    */

    'prompts' => [

        'product_descriptions' => [
            'system' => 'You write short, accurate e-commerce product descriptions (2-3 sentences each). Never invent health claims or facts that are not in the product details.',
            'user' => "Write descriptions for this product:\n:details",
            'fields' => [
                'description_ar' => 'Product description in Arabic',
                'description_en' => 'Product description in English',
            ],
        ],

        'chat' => [
            'system' => <<<'PROMPT'
                You are the shopping assistant of Zadon, an online marketplace in Saudi Arabia that sells products from pharmacies, supermarkets and other local stores.

                :account

                How to work:
                - Understand what the customer wants, then DO it with your tools. Do not tell the customer to do something you can do yourself.
                - Always use tools to get real data before answering. Never guess.
                - You may call several tools before answering, e.g. get_cart then remove_from_cart.
                - If a tool returns an error, explain it simply and suggest what to do.

                Which tool to use:
                - Offers, discounts, deals: list_offers. Products, needs or budget: search_products. What the store sells: list_categories.
                - Orders ("my last order", "where is my order"): get_my_orders. Rate an order: review_order.
                - Cart: get_cart, add_to_cart, update_cart_quantity, remove_from_cart, clear_cart ("delete my cart", "empty my cart").
                - Account: get_profile, update_profile, get_addresses, add_address, set_default_address, get_wallet, get_notifications.
                - Problems or complaints: contact_support.
                - If a cart tool says the cart holds another store's products, tell the customer adding will empty that cart and only retry with replace_cart = "yes" after they agree.

                Actions that need a "yes" first (placing an order, deleting an address):
                1. Call the action tool (place_order or delete_address). It does NOT run the action; it returns a summary.
                2. Show the summary and ask the customer to confirm.
                3. Only when the customer confirms in a NEW message ("yes", "confirm", "تمام", "أكد"), call confirm_action. Never call confirm_action in the same reply as the action tool.
                4. If the customer says no or changes their mind, call cancel_action.
                - For an order, make sure the products are in the cart first. If the customer did not choose a payment method, ask: cash on delivery or Zadon wallet.
                - Card or Apple Pay cannot be used in chat: ask the customer to check out from the cart screen.

                Things you cannot do: top up the wallet, change the phone number, upload photos, cancel an order. Tell the customer to use the app for these.

                How to answer:
                - Only mention products, prices, orders and totals returned by the tools. Never invent them.
                - Prices are in Saudi riyals (SAR / ر.س).
                - Keep answers short and friendly, and say clearly what you did. Products and orders appear as cards under your reply.
                - Reply in the same language the customer writes in. If it is unclear, reply in :language.
                - Do not give medical advice or dosages. For medicine questions, suggest asking a pharmacist.
                - Politely decline questions that are not about shopping on Zadon.
                PROMPT,

            'account_guest' => 'The customer is NOT logged in. You can show offers and products. For orders, cart, account or placing an order, ask them to log in first; those tools are not available.',

            'account_customer' => 'The customer is logged in as :name. You can read and change their cart, orders and account, and place orders for them.',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Chatbot Tools
    |--------------------------------------------------------------------------
    |
    | Each tool is a function the model can call. The description tells the
    | model WHEN to use the tool, and each parameter description tells it
    | WHAT to send. Only list_offers, search_products and list_categories are
    | given to guests; every other tool needs a logged-in customer.
    |
    */

    'tools' => [

        // Catalog (everyone)

        'list_offers' => [
            'description' => 'List products that are currently on offer (discounted), biggest discount first. Use for any question about offers, discounts, deals, sales or reduced prices.',
            'parameters' => [
                'category' => 'Optional category name to filter the offers, e.g. "vitamins" or "فيتامينات". Leave empty for all offers.',
            ],
        ],

        'search_products' => [
            'description' => 'Search available products by name. Use when the customer asks for a specific product, a type of product or a need. Returns product ids needed by add_to_cart.',
            'parameters' => [
                'query' => 'Short keyword for the product name, e.g. "honey", "عسل" or "vitamin c".',
                'max_price' => 'Optional maximum price in SAR when the customer mentions a budget. Use 0 for no limit.',
            ],
        ],

        'list_categories' => [
            'description' => 'List the product categories available in the store. Use when the customer asks what the store sells or the request is too vague to search.',
            'parameters' => [
                'parent_only' => 'Send "yes" to list only main categories, or "no" to include sub-categories.',
            ],
        ],

        // Orders and cart (logged in)

        'get_my_orders' => [
            'description' => 'Get the customer\'s orders, newest first, with order number, status, total, store and items. Use for "my last order", "where is my order", order status or order history.',
            'parameters' => [
                'limit' => 'How many orders to return, newest first. Use 1 for "my last order". Maximum 5.',
                'status' => 'Optional status filter: new, preparing, ready_for_pickup, out_for_delivery, delivered or cancelled. Leave empty for all.',
            ],
        ],

        'get_cart' => [
            'description' => 'Get the customer\'s current cart: products (with product ids), quantities, store and totals.',
        ],

        'add_to_cart' => [
            'description' => 'Add a product to the cart. A cart can only hold products from one store.',
            'parameters' => [
                'product_id' => 'The product id returned by search_products or list_offers.',
                'quantity' => 'How many to add. Default 1.',
                'replace_cart' => 'Send "yes" only after the customer agreed to empty a cart that holds another store\'s products. Otherwise leave empty.',
            ],
        ],

        'update_cart_quantity' => [
            'description' => 'Change how many of a product are in the cart.',
            'parameters' => [
                'product_id' => 'The product id from get_cart.',
                'quantity' => 'The new total quantity. 0 removes the product.',
            ],
        ],

        'remove_from_cart' => [
            'description' => 'Remove one product from the cart.',
            'parameters' => [
                'product_id' => 'The product id from get_cart.',
            ],
        ],

        'clear_cart' => [
            'description' => 'Remove everything from the cart. Use when the customer asks to delete, empty or clear their cart.',
        ],

        // Account (logged in)

        'get_profile' => [
            'description' => 'Get the customer\'s name, phone, email and city.',
        ],

        'update_profile' => [
            'description' => 'Change the customer\'s name or email. The phone number cannot be changed in chat.',
            'parameters' => [
                'name' => 'The new name. Leave empty to keep it.',
                'email' => 'The new email. Leave empty to keep it.',
            ],
        ],

        'get_addresses' => [
            'description' => 'List the customer\'s saved delivery addresses with their ids and which one is the default.',
        ],

        'add_address' => [
            'description' => 'Save a new delivery address. It becomes the default address.',
            'parameters' => [
                'full_address' => 'The full address as the customer wrote it: city, district, street, building.',
                'title' => 'Optional short label such as "home" or "work".',
            ],
        ],

        'set_default_address' => [
            'description' => 'Make one of the saved addresses the default delivery address.',
            'parameters' => [
                'address_id' => 'The address id from get_addresses.',
            ],
        ],

        'get_wallet' => [
            'description' => 'Get the Zadon wallet balance and the latest wallet transactions.',
        ],

        'get_notifications' => [
            'description' => 'Get the customer\'s latest notifications (this also marks them as read).',
        ],

        'contact_support' => [
            'description' => 'Send a message to Zadon customer support. Use for complaints, problems with an order, or anything you cannot solve.',
            'parameters' => [
                'title' => 'A short subject, e.g. "Late order ZD-261118-1852".',
                'message' => 'The customer\'s message with all useful details.',
            ],
        ],

        'review_order' => [
            'description' => 'Rate a delivered order and its store.',
            'parameters' => [
                'order_number' => 'The order number from get_my_orders, e.g. "ZD-261118-1852".',
                'rating' => 'Rating from 1 to 5.',
                'comment' => 'Optional comment in the customer\'s words.',
            ],
        ],

        // Actions that need the customer's "yes" (logged in)

        'place_order' => [
            'description' => 'Start placing an order from the current cart. Does NOT place it yet: returns a summary to show the customer; the order is placed by confirm_action after they say yes.',
            'parameters' => [
                'payment_method' => 'Payment method key: "cash" (cash on delivery) or "wallet" (Zadon wallet).',
            ],
        ],

        'delete_address' => [
            'description' => 'Start deleting a saved address. Does NOT delete it yet: returns a summary; it is deleted by confirm_action after the customer says yes.',
            'parameters' => [
                'address_id' => 'The address id from get_addresses.',
            ],
        ],

        'confirm_action' => [
            'description' => 'Run the action that is waiting for confirmation (an order or an address deletion). Only call this when the customer said yes in a new message after seeing the summary.',
        ],

        'cancel_action' => [
            'description' => 'Cancel the action that is waiting for confirmation, when the customer says no or changes their mind.',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | MCP Server
    |--------------------------------------------------------------------------
    |
    | The store as an MCP server (routes/ai.php), so outside assistants such
    | as the Gemini app can browse the catalog. It reuses the catalog tools
    | and their descriptions above; these are only the extra texts.
    |
    | path:         the server URL path, e.g. https://your-domain/mcp/zadon
    | instructions: told to the assistant when it connects.
    | language:     description of the optional language parameter every
    |               tool takes.
    |
    */

    'mcp' => [
        'path' => env('MCP_PATH', 'mcp/zadon'),

        'instructions' => <<<'TEXT'
            Zadon (زادون) is an online store in Saudi Arabia. These tools read its live catalog: current offers, product search with prices and stock, and the product categories.
            Prices are in Saudi riyals (SAR). Only answer about products from these tools; never invent products, prices or offers.
            The user can buy through the Zadon app; ordering and accounts are not available here.
            TEXT,

        'language' => 'Language of product and category names in the result: "ar" for Arabic or "en" for English. Use the language the user is writing in.',

        /*
        | The admin MCP server (needs an admin login through OAuth). Every
        | change tool works in two calls: the first only returns a summary
        | and a one-time code; the change runs when called again with it.
        */

        'admin' => [
            'path' => env('MCP_ADMIN_PATH', 'mcp/admin'),

            'max_results' => 50,

            'instructions' => <<<'TEXT'
                You are the assistant of a Zadon (زادون) super admin. Zadon is a multi-vendor online store in Saudi Arabia (pharmacies, groceries and more). These tools read the live database: overview numbers, sales reports, orders, products and stock, stores, customers and vendors, reviews and contact messages. Prices and revenue are in Saudi riyals (SAR); revenue never counts cancelled orders.
                Answer only from tool results; never invent numbers. For a period like "this month" or "last week", work out the dates and pass them as from/to (YYYY-MM-DD).
                Changes (order status, activating or deactivating products, stores, users and reviews) always take two steps: call the tool without confirmation_code to get a summary and a code, show the summary to the admin, and only call it again with that code after the admin clearly agrees in a new message. Never confirm on your own.
                TEXT,

            'common' => [
                'from' => 'Start date YYYY-MM-DD (inclusive). Default: 30 days ago.',
                'to' => 'End date YYYY-MM-DD (inclusive). Default: today.',
                'limit' => 'How many results to return (1-50). Default 20.',
                'search' => 'Optional words to search for.',
                'confirmation_code' => 'Leave empty the first time: you get a summary and a code, nothing changes. Send that code (with the same arguments) only after the admin agreed to the summary in a new message.',
            ],

            'tools' => [

                // Read

                'get_overview' => [
                    'description' => 'Store-wide numbers: customers, vendors, stores, products, orders, revenue, pending (new) orders, orders and revenue today and this month, and growth in the last 30 days compared with the 30 days before. Use first for "how is the store doing" or any general question.',
                ],

                'sales_report' => [
                    'description' => 'Sales between two dates: order count, revenue, average order value, cancelled orders, a breakdown (by day, store, payment method or status) and the best-selling products. Use for any revenue, sales, report or performance question.',
                    'parameters' => [
                        'group_by' => 'How to break the numbers down: "day", "store", "payment_method" or "status". Default "day".',
                    ],
                ],

                'search_orders' => [
                    'description' => 'Find orders, newest first, by order number, customer name or phone, status, store or date. Returns a short line per order; call get_order for the full details.',
                    'parameters' => [
                        'status' => 'Optional status: new, preparing, ready_for_pickup, out_for_delivery, delivered or cancelled.',
                        'store_id' => 'Optional store id (from list_stores).',
                    ],
                ],

                'get_order' => [
                    'description' => 'Full details of one order: customer, phone and address, store, items, totals, payment, courier, status timeline, review, and the statuses it can move to next.',
                    'parameters' => [
                        'order_number' => 'The order number, e.g. "ZD-10234".',
                    ],
                ],

                'search_products' => [
                    'description' => 'Find products from every store, active or not, with price, cost, stock, low-stock alert level and sales. Use for product, inventory, stock, low stock or best seller questions.',
                    'parameters' => [
                        'store_id' => 'Optional store id (from list_stores).',
                        'status' => '"active", "inactive" or "all". Default "all".',
                        'low_stock' => 'Send true to list only products at or below their low-stock alert level (or out of stock).',
                        'sort' => '"best_selling", "stock" (lowest first), "price" or "newest". Default "best_selling".',
                    ],
                ],

                'list_stores' => [
                    'description' => 'Stores with their owner (vendor), status, rating, number of products, orders and revenue. Use for vendor, store or branch questions and to get a store id.',
                    'parameters' => [
                        'status' => '"active", "inactive" or "all". Default "all".',
                    ],
                ],

                'search_users' => [
                    'description' => 'Find customers, vendors or admins by name, phone or email, with status, join date, number of orders, total spent and wallet balance.',
                    'parameters' => [
                        'role' => '"customer", "vendor" or "admin". Default "customer".',
                        'sort' => '"newest" or "top_spenders". Default "newest".',
                    ],
                ],

                'list_reviews' => [
                    'description' => 'Customer reviews, newest first, with rating, comment, store, order and whether they are published.',
                    'parameters' => [
                        'max_rating' => 'Optional: only reviews with this many stars or fewer, e.g. 2 for bad reviews.',
                        'store_id' => 'Optional store id.',
                    ],
                ],

                'list_contact_messages' => [
                    'description' => 'Messages customers sent to support through "contact us", newest first, with the sender\'s name and phone.',
                ],

                // Change (two steps)

                'update_order_status' => [
                    'description' => 'Move an order to a new status (e.g. preparing, out_for_delivery, delivered or cancelled). Only allowed next statuses work; the customer is notified.',
                    'parameters' => [
                        'order_number' => 'The order number.',
                        'status' => 'The new status.',
                    ],
                ],

                'set_product_active' => [
                    'description' => 'Activate or deactivate (hide) a product.',
                    'parameters' => [
                        'product_id' => 'The product id (from search_products).',
                        'active' => 'true to activate, false to deactivate.',
                    ],
                ],

                'set_store_active' => [
                    'description' => 'Activate or deactivate a store.',
                    'parameters' => [
                        'store_id' => 'The store id (from list_stores).',
                        'active' => 'true to activate, false to deactivate.',
                    ],
                ],

                'set_user_active' => [
                    'description' => 'Activate or block a customer or vendor account. Admin accounts cannot be changed here.',
                    'parameters' => [
                        'user_id' => 'The user id (from search_users).',
                        'active' => 'true to activate, false to block.',
                    ],
                ],

                'set_review_published' => [
                    'description' => 'Publish or hide a customer review.',
                    'parameters' => [
                        'review_id' => 'The review id (from list_reviews).',
                        'published' => 'true to publish, false to hide.',
                    ],
                ],

            ],
        ],
    ],

];
