<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Pedido - {{ $cabecalho->num_pedido }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: white;
            margin: 20px;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #3498db;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }

        .company-info h1 {
            font-size: 22px;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 5px 0;
            letter-spacing: 0.5px;
        }

        .company-info .subtitle {
            font-size: 14px;
            color: #7f8c8d;
            margin: 0;
            font-weight: 500;
        }

        .report-info {
            text-align: right;
            font-size: 11px;
            color: #5a6c7d;
        }

        .report-info .date {
            font-weight: 600;
            color: #34495e;
            margin-bottom: 8px;
        }

        .info-section {
            margin-bottom: 15px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
            font-size: 10px;
            margin-bottom: 2px;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 12px;
            color: #333;
            padding: 6px 10px;
            background-color: #f8f9fa;
            border-left: 3px solid #3498db;
            border-radius: 2px;
        }

        .prioridade {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .prioridade.baixa {
            background-color: #d5f4e6;
            color: #27ae60;
            border: 1px solid #27ae60;
        }

        .prioridade.media {
            background-color: #fff3cd;
            color: #f39c12;
            border: 1px solid #f39c12;
        }

        .prioridade.alta {
            background-color: #f8d7da;
            color: #e74c3c;
            border: 1px solid #e74c3c;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #bdc3c7;
            padding-bottom: 3px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .table th {
            background-color: #34495e;
            color: white;
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }

        .table td {
            padding: 8px 6px;
            border-bottom: 1px solid #ecf0f1;
            vertical-align: top;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .table tbody tr:hover {
            background-color: #e8f4f8;
        }

        .table .numero {
            text-align: center;
            font-weight: bold;
            color: #7f8c8d;
            width: 50px;
        }

        .table .quantidade {
            text-align: center;
            font-weight: bold;
            color: #2c3e50;
            width: 80px;
        }



        .footer {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 2px solid #bdc3c7;
            text-align: center;
            font-size: 10px;
            color: #7f8c8d;
        }

        .data-impressao {
            text-align: right;
            font-size: 10px;
            color: #7f8c8d;
            margin-bottom: 15px;
        }

        @media print {
            body {
                margin: 0;
                font-size: 11px;
            }
            
            .header-container {
                margin-bottom: 15px;
                padding-bottom: 8px;
            }
            
            .info-section,
            .section-title {
                margin-bottom: 10px;
            }
            
            .table {
                margin-bottom: 15px;
            }
            
            .footer {
                margin-top: 20px;
                padding-top: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="header-container">
        <div class="logo-section">
            <img src="/img/brs-logo.png" alt="BRS Logo" class="logo" />
            <div class="company-info">
                <h1>DETALHES DO PEDIDO</h1>
                <p class="subtitle">Sistema de Pedidos de Compras</p>
            </div>
        </div>
        <div class="report-info">
            <div class="date">Emitido em: {{ now()->format('d/m/Y H:i:s') }}</div>
            <div>Sistema SIGO - BRS Transportes</div>
        </div>
    </div>

    <div class="info-section">
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Número do Pedido</div>
                <div class="info-value">{{ $cabecalho->num_pedido }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Data de Solicitação</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($cabecalho->data_solicitacao)->format('d/m/Y H:i:s') }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Centro de Custo</div>
                <div class="info-value">{{ $cabecalho->centro_custo_nome }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Prioridade</div>
                <div class="info-value">
                    <span class="prioridade {{ $cabecalho->prioridade }}">
                        {{ ucfirst($cabecalho->prioridade) }}
                    </span>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Rota</div>
                <div class="info-value">{{ $cabecalho->rota_nome ?? '—' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Roteirização</div>
                <div class="info-value">{{ $cabecalho->roteirizacao_nome ?? '—' }}</div>
            </div>
        </div>
        
        @if($cabecalho->observacao)
        <div class="info-item">
            <div class="info-label">Observações</div>
            <div class="info-value">{{ $cabecalho->observacao }}</div>
        </div>
        @endif
    </div>

    <div class="section-title">Itens do Pedido</div>
    <table class="table">
        <thead>
            <tr>
                <th class="numero">#</th>
                <th>Produto</th>
                <th class="quantidade">Código</th>
                <th class="quantidade">Quantidade</th>
            </tr>
        </thead>
        <tbody>
            @foreach($itens as $index => $item)
            <tr>
                <td class="numero">{{ $index + 1 }}</td>
                <td>{{ $item->produto_nome }}</td>
                <td class="quantidade">{{ $item->codigo ?? '—' }}</td>
                <td class="quantidade">{{ $item->quantidade }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>



    <div class="footer">
        <p>Sistema Integrado de Gestão Operacional (SIGO) - BRS Transportes</p>
    </div>

    <script>
        // Não auto-imprime mais - a impressão é controlada pelo iframe
    </script>
</body>
</html>
