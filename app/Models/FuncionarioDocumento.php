<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuncionarioDocumento extends Model
{
    use HasFactory;

    protected $table = 'funcionarios_documentos';
    
    protected $fillable = [
        'funcionario_id',
        'tipo_documento',
        'arquivo_nome',
        'arquivo_extensao',
        'arquivo_mime_type',
        'arquivo_tamanho',
        'arquivo_hash',
        'arquivo_conteudo',
        'arquivo_path', // NOVO: caminho no storage
        'usuario_cadastro',
        'status',
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // Relacionamento com funcionário
    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }
    
    // Relacionamento com usuário que cadastrou
    public function usuarioCadastro()
    {
        return $this->belongsTo(User::class, 'usuario_cadastro');
    }
}
