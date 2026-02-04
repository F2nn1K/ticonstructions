<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprovante de Recebimento - {{ $recebimento->ordem_numero ?? 'N/A' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .header p {
            color: #666;
            font-size: 11px;
        }
        
        .info-section {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-box {
            flex: 1;
            min-width: 200px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 12px;
        }
        
        .info-box label {
            display: block;
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        
        .info-box .value {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }
        
        .info-box .value.primary {
            color: #007bff;
        }
        
        .section-title {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            color: #333;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 8px;
            margin-bottom: 15px;
            margin-top: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table thead th {
            background: #343a40;
            color: #fff;
            padding: 10px 8px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        table tbody td {
            padding: 10px 8px;
            border-bottom: 1px solid #dee2e6;
        }
        
        table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .badge-success {
            background: #28a745;
            color: #fff;
        }
        
        .badge-secondary {
            background: #6c757d;
            color: #fff;
        }
        
        .observations {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 12px;
            margin-bottom: 20px;
        }
        
        .observations label {
            display: block;
            font-size: 10px;
            color: #856404;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .footer {
            margin-top: 40px;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
        }
        
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }
        
        .signature-box {
            text-align: center;
            width: 45%;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            padding-top: 5px;
            font-size: 11px;
        }
        
        .print-info {
            text-align: center;
            font-size: 10px;
            color: #999;
            margin-top: 30px;
        }
        
        @media print {
            body {
                padding: 10px;
            }
            
            .no-print {
                display: none;
            }
            
            .info-box {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            table thead th {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        
        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-print:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <button class="btn-print no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Imprimir
    </button>
    
    <div class="header">
        <h1>Comprovante de Recebimento de Material</h1>
        <p>Sistema de Gestão de Suprimentos - ASC Sistemas</p>
    </div>
    
    <div class="info-section">
        <div class="info-box">
            <label>Ordem de Compra</label>
            <div class="value primary">{{ $recebimento->ordem_numero ?? 'N/A' }}</div>
        </div>
        <div class="info-box">
            <label>Data do Recebimento</label>
            <div class="value">{{ \Carbon\Carbon::parse($recebimento->data_recebimento)->format('d/m/Y') }}</div>
        </div>
        <div class="info-box">
            <label>Nota Fiscal</label>
            <div class="value">{{ $recebimento->nf_numero ?? 'Não informada' }}</div>
        </div>
    </div>
    
    <div class="info-section">
        <div class="info-box" style="flex: 2;">
            <label>Fornecedor</label>
            <div class="value">{{ $recebimento->fornecedor ?? $recebimento->fornecedor_fantasia ?? 'N/A' }}</div>
            @if($recebimento->fornecedor_cnpj)
            <div style="font-size: 11px; color: #666; margin-top: 3px;">CNPJ: {{ $recebimento->fornecedor_cnpj }}</div>
            @endif
        </div>
        <div class="info-box">
            <label>Responsável pelo Recebimento</label>
            <div class="value">{{ $recebimento->responsavel_nome ?? 'N/A' }}</div>
        </div>
    </div>
    
    @if($recebimento->valor_total)
    <div class="info-section">
        <div class="info-box">
            <label>Valor Total da O.C.</label>
            <div class="value">R$ {{ number_format($recebimento->valor_total, 2, ',', '.') }}</div>
        </div>
    </div>
    @endif
    
    <div class="section-title">Itens Recebidos</div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 50%;">Descrição do Item</th>
                <th class="text-center" style="width: 15%;">Quantidade</th>
                <th class="text-center" style="width: 15%;">Unidade</th>
                <th class="text-center" style="width: 20%;">Vinculado ao Estoque</th>
            </tr>
        </thead>
        <tbody>
            @forelse($itens as $item)
            <tr>
                <td>{{ $item->descricao ?? $item->produto_nome ?? 'Item' }}</td>
                <td class="text-center">{{ $item->quantidade ?? 0 }}</td>
                <td class="text-center">{{ $item->unidade ?? 'UN' }}</td>
                <td class="text-center">
                    @if($item->produto_nome || $item->produto_id)
                    <span class="badge badge-success">Sim - {{ $item->produto_nome ?? 'Produto #' . $item->produto_id }}</span>
                    @else
                    <span class="badge badge-secondary">Não</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center" style="padding: 20px; color: #666;">
                    Nenhum item registrado para este recebimento.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($recebimento->observacoes)
    <div class="observations">
        <label>Observações</label>
        <p>{{ $recebimento->observacoes }}</p>
    </div>
    @endif
    
    <div class="footer">
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line">
                    Recebido por: {{ $recebimento->responsavel_nome ?? '____________________' }}
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    Conferido por: ____________________
                </div>
            </div>
        </div>
    </div>
    
    <div class="print-info">
        Documento gerado em {{ now()->format('d/m/Y H:i:s') }} | Recebimento #{{ $recebimento->id }}
    </div>
    
    <script>
        // Auto-print somente se abrir diretamente (não via iframe)
        // Verifica se está em um iframe - se não estiver, faz auto-print
        if (window.self === window.top) {
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                }, 300);
            }
        }
    </script>
</body>
</html>
