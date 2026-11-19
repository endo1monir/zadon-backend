<?php

namespace App\Mcp\Tools\Admin;

use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class SearchUsersTool extends AdminTool
{
    protected function key(): string
    {
        return 'search_users';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description($this->parameter('search')),
            'role' => $schema->string()->enum(['customer', 'vendor', 'admin'])->description($this->parameter('role')),
            'sort' => $schema->string()->enum(['newest', 'top_spenders'])->description($this->parameter('sort')),
            'limit' => $schema->integer()->description($this->parameter('limit')),
        ];
    }

    public function handle(Request $request): Response
    {
        $role = in_array($request->get('role'), ['customer', 'vendor', 'admin'], true) ? $request->get('role') : 'customer';

        $query = User::query()
            ->where('role', $role)
            ->with('wallet:id,user_id,balance')
            ->withCount('orders')
            ->withSum(['orders as spent' => fn ($query) => $query->where('status', '!=', 'cancelled')], 'total')
            ->when(filled($request->get('search')), function ($query) use ($request): void {
                $term = '%'.trim((string) $request->get('search')).'%';

                $query->where(fn ($query) => $query->where('name', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('email', 'like', $term));
            });

        $request->get('sort') === 'top_spenders'
            ? $query->orderByDesc('spent')
            : $query->latest('id');

        $total = (clone $query)->count();

        return $this->json([
            'role' => $role,
            'found' => $total,
            'users' => $query->limit($this->limit($request))->get()->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'active' => (bool) $user->is_active,
                'joined_at' => $user->created_at?->toDateString(),
                'orders' => $user->orders_count,
                'total_spent_sar' => $this->money($user->spent),
                'wallet_balance_sar' => $user->wallet ? $this->money($user->wallet->balance) : null,
            ])->all(),
        ]);
    }
}
