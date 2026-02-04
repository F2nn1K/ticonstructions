<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'login',
        'empresa',
        'password',
        'active',
        'profile_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function temPermissao($permissao)
    {
        // Verifica pelo perfil principal (profile_id)
        if ($this->profile_id) {
            $temPermissao = DB::table('profile_permissions')
                ->join('permissions', 'permissions.id', '=', 'profile_permissions.permission_id')
                ->where('profile_permissions.profile_id', $this->profile_id)
                ->where(function($query) use ($permissao) {
                    $query->where('permissions.name', $permissao)
                          ->orWhere('permissions.code', $permissao);
                })
                ->exists();

            if ($temPermissao) {
                return true;
            }
        }

        // Não verificamos perfis adicionais pois não temos a tabela user_profiles
        return false;
    }

    public function hasAnyPermission($permissions)
    {
        if (!is_array($permissions)) {
            $permissions = explode(',', $permissions);
        }

        foreach ($permissions as $permission) {
            if ($this->temPermissao(trim($permission))) {
                return true;
            }
        }

        return false;
    }

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($user) {
            try {
                // Cache específico por usuário em vez de flush global
                cache()->forget("user_permissions_{$user->id}");
                cache()->forget("user_profile_{$user->id}");
            } catch (\Exception $e) {
                \Log::warning('Erro ao limpar cache na atualização do usuário: ' . $e->getMessage());
            }
        });

        static::saved(function ($user) {
            try {
                // Cache específico por usuário em vez de flush global
                cache()->forget("user_permissions_{$user->id}");
                cache()->forget("user_profile_{$user->id}");
            } catch (\Exception $e) {
                \Log::warning('Erro ao limpar cache ao salvar usuário: ' . $e->getMessage());
            }
        });
    }

    /**
     * Obtém os problemas de RH criados por este usuário.
     */
    public function problemas()
    {
        return $this->hasMany(RHProblema::class, 'usuario_id');
    }

    /**
     * Obtém os problemas de RH onde este usuário é o responsável.
     */
    public function problemasComoResponsavel()
    {
        return $this->hasMany(RHProblema::class, 'responsavel_id');
    }

    /**
     * Obtém os problemas de RH onde este usuário é o responsável (alias para compatibilidade).
     */
    public function responsavelProblemas()
    {
        return $this->hasMany(RHProblema::class, 'responsavel_id');
    }
}
