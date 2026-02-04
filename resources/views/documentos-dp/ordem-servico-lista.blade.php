@extends('adminlte::page')

@section('title', 'Ordens de Serviço do Dia')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-list text-primary mr-2"></i>
            Ordens de Serviço
        </h1>
        <p class="text-muted mt-1 mb-0">Visualize as O.S. da data selecionada</p>
    </div>
    <div>
        <a href="{{ route('documentos-dp.ordem-servico') }}" class="btn btn-outline-secondary mr-2">
            <i class="fas fa-arrow-left mr-1"></i> Voltar
        </a>
        <a href="{{ route('documentos-dp.ordem-servico.nova') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Nova O.S.
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid os-page">
    <div class="modern-card mb-3">
        <div class="card-body-modern">
            <form method="GET" action="{{ route('documentos-dp.ordem-servico.lista') }}" class="w-100">
                <div class="form-row align-items-end">
                    <div class="col-sm-12 col-md-3 mb-2">
                        <label class="font-weight-bold text-muted">Data inicial</label>
                        <input type="date" name="data_ini" value="{{ $data_ini ?? '' }}" class="form-control modern-input"/>
                    </div>
                    <div class="col-sm-12 col-md-3 mb-2">
                        <label class="font-weight-bold text-muted">Data final</label>
                        <input type="date" name="data_fim" value="{{ $data_fim ?? '' }}" class="form-control modern-input"/>
                    </div>
                    <div class="col-sm-12 col-md-4 mb-2">
                        <label class="font-weight-bold text-muted">Número da O.S.</label>
                        <input type="text" name="numero_os" value="{{ $numero_os ?? '' }}" placeholder="Ex.: OS-20250809-0001" class="form-control modern-input"/>
                    </div>
                    <div class="col-sm-12 col-md-2 mb-2 d-flex">
                        <button class="btn btn-secondary mr-2 flex-fill"><i class="fas fa-search mr-1"></i> Filtrar</button>
                        <a href="{{ route('documentos-dp.ordem-servico.lista') }}" class="btn btn-outline-secondary flex-fill">Limpar</a>
                    </div>
                </div>
                @if(!empty($data))
                    <input type="hidden" name="data" value="{{ $data }}">
                @endif
                <small class="text-muted d-block mt-2">Preencha período OU o número da O.S. (o número tem prioridade).</small>
            </form>
        </div>
    </div>

    <div class="modern-card">
        <div class="card-body-modern">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Nº O.S.</th>
                            <th>Data</th>
                            <th>Funcionário</th>
                            <th>Cidade/UF</th>
                            <th>Telefone</th>
                            <th style="width:140px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($registros as $os)
                            <tr>
                                <td>{{ $os->numero_os }}</td>
                                <td>{{ \Carbon\Carbon::parse($os->data_os)->format('d/m/Y') }}</td>
                                <td>{{ $os->funcionario }}</td>
                                <td>{{ $os->cidade }}{{ $os->estado ? ' / ' . $os->estado : '' }}</td>
                                <td>{{ $os->telefone }}</td>
                                <td class="text-nowrap">
                                    <button class="btn btn-sm btn-outline-primary mr-1" title="Ver" onclick="verOS({{ $os->id }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" title="Imprimir" onclick="imprimirOS({{ $os->id }})">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Nenhuma O.S. encontrada para a data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Visualizar OS (reutilizado da tela de Funcionários) -->
    <div class="modal fade" id="modalVerOSLista" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-file-signature mr-2"></i> Ordem de Serviço</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" id="conteudoOSLista">
            <div class="text-center text-muted py-4">Carregando...</div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            <button type="button" class="btn btn-primary" onclick="imprimirOS(window._osIdAtual)"><i class="fas fa-print mr-1"></i> Imprimir</button>
          </div>
        </div>
      </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/modern-design.css') }}">
<style>
.os-page * { transition: none !important; }
.os-page a:hover { text-decoration: none !important; color: inherit !important; }
.os-page .btn:hover, .os-page .btn:focus, .os-page .btn:active { box-shadow: none !important; filter: none !important; transform: none !important; }
.os-page table tbody tr:hover { background-color: transparent !important; }
.os-page .modern-card:hover, .os-page .card:hover { box-shadow: none !important; transform: none !important; }
@media print {
  .main-header, .main-sidebar, .main-footer, .content-header, .no-print, .btn, form { display: none !important; }
  .content-wrapper, .content { margin: 0 !important; }
}
</style>
@stop

@section('js')
<script>
async function verOS(id){
  const container = document.getElementById('conteudoOSLista');
  container.innerHTML = '<div class="text-center text-muted py-4">Carregando...</div>';
  try { $('.modal').modal('hide'); } catch(e){}
  $('.modal-backdrop').remove(); $('body').removeClass('modal-open').css('padding-right','');
  $('#modalVerOSLista').appendTo('body').modal({backdrop:'static', keyboard:true}).modal('show');
  try {
    const resp = await fetch(`/api/ordens-servico/${id}`);
    const json = await resp.json();
    if(!json.success) throw new Error(json.message || 'Erro');
    const os = json.data;
    // guardar id atual para o botão Imprimir do modal usar o layout correto
    window._osIdAtual = id;
    container.innerHTML = `
      <div class="row">
        <div class="col-md-6">
          <p><strong>Nº O.S.:</strong> ${os.numero_os}</p>
          <p><strong>Data:</strong> ${new Date(os.data_os).toLocaleDateString('pt-BR')}</p>
          <p><strong>Funcionário:</strong> ${os.funcionario || ''}</p>
          <p><strong>Descrição:</strong><br>${os.descricao || ''}</p>
        </div>
        <div class="col-md-6">
          <p><strong>Endereço:</strong> ${os.endereco || ''}</p>
          <p><strong>Cidade/UF:</strong> ${[os.cidade, os.estado].filter(Boolean).join(' / ')}</p>
          <p><strong>CEP:</strong> ${os.cep || ''}</p>
          <p><strong>Telefone:</strong> ${os.telefone || ''}</p>
          <p><strong>CPF/CNPJ:</strong> ${os.cpf_cnpj || ''}</p>
        </div>
      </div>
      ${os.observacoes ? `<hr><p><strong>Observações:</strong><br>${os.observacoes}</p>` : ''}
    `;
  } catch(e) {
    container.innerHTML = `<div class="alert alert-danger">${e.message || 'Erro ao carregar O.S.'}</div>`;
  }
}

async function imprimirOS(id){
  try {
    const resp = await fetch(`/api/ordens-servico/${id}`);
    const json = await resp.json();
    if (!json.success) throw new Error(json.message || 'Erro ao carregar O.S.');
    const os = json.data;

    const dataBR = new Date(os.data_os).toLocaleDateString('pt-BR');

    const logoUrl = window.location.origin + '/img/brs-logo.png';
    
    // Função para formatar CPF/CNPJ
    function formatarCpfCnpj(valor) {
      if (!valor) return '';
      const numeros = valor.replace(/\D/g, '');
      if (numeros.length === 11) {
        // CPF: 000.000.000-00
        return numeros.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
      } else if (numeros.length === 14) {
        // CNPJ: 00.000.000/0000-00
        return numeros.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
      }
      return valor; // Retorna original se não for CPF nem CNPJ
    }
    
    const cpfCnpjFormatado = formatarCpfCnpj(os.funcionario_cpf || os.cpf_cnpj || '');
    
    const html = `<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Ordem de Serviço - ${os.numero_os}</title>
  <style>
    @page { 
      margin: 20mm 15mm; 
      size: A4;
    }
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body { 
      font-family: 'Arial', sans-serif; 
      color: #2c3e50; 
      font-size: 9pt; 
      line-height: 1.3;
    }
    .header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 3px solid #3498db;
      padding-bottom: 15px;
      margin-bottom: 25px;
    }
    .logo {
      max-height: 60px;
      max-width: 120px;
    }
    .header-info {
      text-align: right;
    }
    .titulo {
      font-size: 20pt;
      font-weight: bold;
      color: #2c3e50;
      margin-bottom: 5px;
    }
    .numero-os {
      font-size: 12pt;
      color: #3498db;
      font-weight: bold;
    }
    .data-emissao {
      font-size: 8pt;
      color: #7f8c8d;
      margin-top: 3px;
    }
    .secao {
      margin-bottom: 20px;
    }
    .secao-titulo {
      background: #ecf0f1;
      padding: 6px 10px;
      font-weight: bold;
      font-size: 10pt;
      color: #2c3e50;
      border-left: 4px solid #3498db;
      margin-bottom: 8px;
    }
    .grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
      margin-bottom: 15px;
    }
    .campo {
      margin-bottom: 8px;
    }
    .campo-label {
      font-weight: bold;
      color: #34495e;
      font-size: 8pt;
      margin-bottom: 2px;
      display: block;
    }
    .campo-valor {
      color: #2c3e50;
      font-size: 9pt;
      min-height: 16px;
      padding: 2px 0;
    }
    .descricao-box {
      padding: 8px 0;
      min-height: 40px;
      font-size: 9pt;
      line-height: 1.4;
    }
    .observacoes-box {
      padding: 8px 0;
      min-height: 30px;
      margin-top: 8px;
      font-size: 9pt;
      line-height: 1.4;
    }
    .footer {
      position: fixed;
      bottom: 15mm;
      left: 0;
      right: 0;
      text-align: center;
      font-size: 9pt;
      color: #95a5a6;
      border-top: 1px solid #ecf0f1;
      padding-top: 8px;
    }
    .separador-assinatura {
      margin-top: 80px;
      border-top: 2px solid #2c3e50;
      padding-top: 40px;
    }
    .assinatura {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 40px;
      margin-top: 20px;
    }
    .assinatura-campo {
      text-align: center;
      border-top: 1px solid #2c3e50;
      padding-top: 8px;
      font-size: 9pt;
    }
  </style>
</head>
<body>
  <div class="header">
    <img src="${logoUrl}" alt="Logo" class="logo" />
    <div class="header-info">
      <div class="titulo">ORDEM DE SERVIÇO</div>
      <div class="numero-os">Nº ${os.numero_os || ''}</div>
      <div class="data-emissao">Emitida em: ${dataBR}</div>
    </div>
  </div>

  <div class="secao">
    <div class="secao-titulo">DADOS DO FUNCIONÁRIO</div>
    <div class="grid">
      <div class="campo">
        <span class="campo-label">Nome:</span>
        <div class="campo-valor">${os.funcionario || ''}</div>
      </div>
      <div class="campo">
        <span class="campo-label">CPF/CNPJ:</span>
        <div class="campo-valor">${cpfCnpjFormatado}</div>
      </div>
    </div>
  </div>

  <div class="secao">
    <div class="secao-titulo">TERMO DE RESPONSABILIDADE</div>
    <div style="padding: 10px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; font-size: 9pt; line-height: 1.4; text-align: justify;">
      Conforme a cláusula sexta do contrato firmado entre empregador e empregado, fica designado o colaborador(a) acima identificado(a) para realizar as atividades no local informado abaixo.
    </div>
  </div>

  <div class="secao">
    <div class="secao-titulo">LOCALIZAÇÃO</div>
    <div class="grid">
      <div class="campo">
        <span class="campo-label">Endereço:</span>
        <div class="campo-valor">${os.endereco || ''}</div>
      </div>
      <div class="campo">
        <span class="campo-label">Cidade/UF:</span>
        <div class="campo-valor">${[os.cidade, os.estado].filter(Boolean).join(' / ')}</div>
      </div>
      <div class="campo">
        <span class="campo-label">CEP:</span>
        <div class="campo-valor">${os.cep || ''}</div>
      </div>
      <div class="campo">
        <span class="campo-label">Telefone:</span>
        <div class="campo-valor">${os.telefone || ''}</div>
      </div>
    </div>
  </div>

  <div class="secao">
    <div class="secao-titulo">DESCRIÇÃO DO SERVIÇO</div>
    <div class="descricao-box">
      ${os.descricao || ''}
    </div>
    ${os.observacoes ? `
      <div class="secao-titulo" style="margin-top: 20px;">OBSERVAÇÕES</div>
      <div class="observacoes-box">
        ${os.observacoes}
      </div>
    ` : ''}
  </div>

  <div class="separador-assinatura">
    <div class="assinatura">
      <div class="assinatura-campo">
        <strong>Assinatura do Gerente</strong>
      </div>
      <div class="assinatura-campo">
        <strong>${os.funcionario || '_________________________'}</strong>
      </div>
    </div>
  </div>

  <div class="footer">
    Sistema de Gestão - Ordem de Serviço gerada automaticamente
  </div>
</body>
</html>`;

    const win = window.open('', '_blank', 'width=900,height=700');
    if (!win) { alert('Permita pop-ups para imprimir.'); return; }
    win.document.open();
    win.document.write(html);
    win.document.close();
    win.focus();
    setTimeout(function(){ try { win.print(); } finally { win.close(); } }, 150);
  } catch(e) {
    alert(e.message || 'Erro ao imprimir O.S.');
  }
}
</script>
@stop


