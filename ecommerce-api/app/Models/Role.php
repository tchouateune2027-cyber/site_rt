<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    // =====================================================
    // CONSTANTES
    // Evite les fautes de frappe quand on écrit les rôles
    //
    // Au lieu d'écrire : 'admin' (risque de taper 'adnim')
    // On écrit : Role::ADMIN  (sûr car c'est une constante)
    // =====================================================
    const USER         = 'user';
    const ADMIN        = 'admin';
    const GESTIONNAIRE = 'gestionnaire';
    const SECRETAIRE   = 'secretaire';
    const COMPTABLE    = 'comptable';

    // =====================================================
    // RELATION
    // Un rôle appartient à plusieurs utilisateurs
    // =====================================================
    public function users()
    {
        return $this->hasMany(User::class);
        // Un rôle → plusieurs users
        // SQL : SELECT * FROM users WHERE role_id = {id du rôle}
    }
}
