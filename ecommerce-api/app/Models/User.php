<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\Rules\In;

#[Fillable(['name', 'email', 'password', 'role_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;


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
            'role_id' => 'integer',
        ];
    }
    public function role()
    {
        return $this->belongsTo(Role::class);

        // Un user → un rôle
        // SQL : SELECT * FROM roles WHERE id = {role_id du user}
    }
    public function isAdmin(): bool
    {
        return $this->role?->name === Role::ADMIN;
        // Vérifie si le rôle du user est "admin"
        // ?-> = op. de navigation : évite l'erreur si role est null
    }
    public function isGestionnaire(): bool
    {
        return $this->role?->name === Role::GESTIONNAIRE;
    }
    public function isSecretaire(): bool
    {
        return $this->role?->name === Role::SECRETAIRE;
    }
    public function isComptable(): bool
    {
        return $this->role?->name === Role::COMPTABLE;
    }
    public function isUser(): bool
    {
        return $this->role?->name === Role::USER;
    }

    public function hasRole(array $roles): bool
    {
        return in_array($this->role?->name, $roles);
        // Vérifie si le rôle du user correspond au nom donné
    }
}
