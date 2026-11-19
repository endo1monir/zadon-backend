<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

/**
 * An admin as seen by the admin MCP server (Gemini and other assistants).
 *
 * Same `users` table as User, but limited to active admins and using
 * Passport's OAuth tokens instead of Sanctum's. It does not extend User
 * because the two token traits clash, and this keeps the app's customer
 * and vendor tokens untouched. A customer or vendor can never open the
 * admin MCP server, and deactivating an admin cuts their access at once.
 */
#[Table('users')]
#[Hidden(['password', 'remember_token'])]
class McpAdmin extends Authenticatable implements OAuthenticatable
{
    use HasApiTokens;

    protected static function booted(): void
    {
        static::addGlobalScope('active_admin', fn (Builder $query) => $query
            ->where('role', 'admin')
            ->where('is_active', true));
    }
}
