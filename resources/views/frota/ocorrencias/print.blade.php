<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ocorrência #{{ $ocorrencia->id }} - Impressão</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #333;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .logo img {
            max-height: 60px;
            max-width: 200px;
        }
        
        .doc-info {
            text-align: right;
        }
        
        .doc-info h1 {
            font-size: 18pt;
            margin-bottom: 5px;
        }
        
        .doc-info p {
            font-size: 10pt;
            color: #666;
        }
        
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .section-title {
            background: #f5f5f5;
            padding: 8px 12px;
            font-weight: bold;
            border-left: 4px solid #ffc107;
            margin-bottom: 10px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .info-item {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .info-item label {
            display: block;
            font-weight: bold;
            color: #555;
            font-size: 10pt;
            margin-bottom: 3px;
        }
        
        .info-item .value {
            color: #000;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 11pt;
        }
        
        .status-novo {
            background: #dc3545;
            color: white;
        }
        
        .status-em_andamento {
            background: #17a2b8;
            color: white;
        }
        
        .status-resolvido {
            background: #28a745;
            color: white;
        }
        
        .description-box {
            border: 1px solid #ddd;
            padding: 12px;
            border-radius: 4px;
            background: #fafafa;
            min-height: 80px;
            white-space: pre-wrap;
        }
        
        .photos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }
        
        .photo-item {
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
            page-break-inside: avoid;
        }
        
        .photo-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }
        
        .photo-caption {
            padding: 5px;
            text-align: center;
            background: #f5f5f5;
            font-size: 9pt;
            color: #666;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #ddd;
            text-align: center;
            font-size: 9pt;
            color: #666;
        }
        
        @media print {
            body {
                padding: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            .container {
                max-width: 100%;
            }
            
            @page {
                size: A4;
                margin: 15mm;
            }
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .print-button:hover {
            background: #0056b3;
        }
    </style>
    <script>
        // Auto-print quando chamado via iframe
        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('autoprint') === '1') {
                // Aguarda um pouco para garantir que imagens carregaram
                setTimeout(() => {
                    window.print();
                }, 800);
            }
        });
    </script>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Imprimir
    </button>
    
    <div class="container">
        <!-- Cabeçalho -->
        <div class="header">
            <div class="logo">
                <img src="{{ asset('img/brs-logo.png') }}" alt="Logo">
            </div>
            <div class="doc-info">
                <h1>Ocorrência #{{ $ocorrencia->id }}</h1>
                <p>Impresso em: {{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>
        
        <!-- Informações do Veículo -->
        <div class="section">
            <div class="section-title">Informações do Veículo</div>
            <div class="info-grid">
                <div class="info-item">
                    <label>Veículo</label>
                    <div class="value">
                        @if($ocorrencia->veiculo)
                            {{ $ocorrencia->veiculo->placa }} - {{ $ocorrencia->veiculo->marca }} {{ $ocorrencia->veiculo->modelo }}
                        @else
                            Não informado
                        @endif
                    </div>
                </div>
                <div class="info-item">
                    <label>Motorista</label>
                    <div class="value">{{ $ocorrencia->motorista_nome ?? 'Não informado' }}</div>
                </div>
            </div>
        </div>
        
        <!-- Informações da Ocorrência -->
        <div class="section">
            <div class="section-title">Detalhes da Ocorrência</div>
            <div class="info-grid">
                <div class="info-item">
                    <label>{{ __('Data') }}</label>
                    <div class="value">{{ $ocorrencia->data ? $ocorrencia->data->format('d/m/Y') : '-' }}</div>
                </div>
                <div class="info-item">
                    <label>Hora</label>
                    <div class="value">{{ $ocorrencia->hora ?? '-' }}</div>
                </div>
                <div class="info-item">
                    <label>{{ __('Status') }}</label>
                    <div class="value">
                        @php
                            $status = strtolower($ocorrencia->status ?? 'novo');
                            $statusClass = 'status-' . str_replace(' ', '_', $status);
                            $statusText = $status === 'novo' ? 'Novo' : ($status === 'em_andamento' ? 'Em Andamento' : ($status === 'resolvido' ? 'Resolvido' : ucfirst($status)));
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                    </div>
                </div>
                <div class="info-item">
                    <label>Registrado em</label>
                    <div class="value">{{ $ocorrencia->created_at ? $ocorrencia->created_at->format('d/m/Y H:i') : '-' }}</div>
                </div>
            </div>
        </div>
        
        <!-- Descrição -->
        <div class="section">
            <div class="section-title">Descrição do Problema</div>
            <div class="description-box">{{ $ocorrencia->descricao ?? 'Nenhuma descrição fornecida.' }}</div>
        </div>
        
        <!-- Sugestão (se houver) -->
        @if($ocorrencia->sugestao)
        <div class="section">
            <div class="section-title">Sugestão de Solução</div>
            <div class="description-box">{{ $ocorrencia->sugestao }}</div>
        </div>
        @endif
        
        <!-- Fotos -->
        @if($fotos && count($fotos) > 0)
        <div class="section">
            <div class="section-title">Fotos da Ocorrência ({{ count($fotos) }})</div>
            <div class="photos-grid">
                @foreach($fotos as $foto)
                <div class="photo-item">
                    <img src="{{ $foto['url'] }}" alt="Foto {{ $foto['idx'] }}">
                    <div class="photo-caption">Foto {{ $foto['idx'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        
        <!-- Rodapé -->
        <div class="footer">
            <p>Este documento é um registro oficial de ocorrência da frota.</p>
            <p>Gerado automaticamente pelo Sistema de Gestão.</p>
        </div>
    </div>
</body>
</html>

