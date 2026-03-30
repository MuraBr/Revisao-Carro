<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /** * Campos que podem ser preenchidos via mass assignment */
    protected $fillable = [
        'name',
        'email',
        'password',
        'data_nascimento',
        'genero',
        'role',
    ];

    /** * Campos ocultados na serialização (ex: retorno JSON) */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ── Relacionamentos ──────────────────────────────
    /** * Um usuário tem muitos veículos */
    public function veiculos()
    {
        return $this->hasMany(Veiculo::class);
    }

    // ── Scopes (filtros reutilizáveis) ────────────────
    /** * Filtrar apenas usuários com role = 'admin' * Uso: User::admins()->get() */
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    /** * Filtrar apenas clientes * Uso: User::clients()->get() */
    public function scopeClients($query)
    {
        return $query->where('role', 'client');
    }

    // ── Helpers ──────────────────────────────────────
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
