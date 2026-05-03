<style>
:root { --rdo-gold: var(--ti-gold, #C9A84C); }

.card-rdo { border: 1px solid #e0e0e0; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,.06); }

.rdo-section-title {
    font-size: .7rem; text-transform: uppercase; font-weight: 800;
    letter-spacing: .1em; color: var(--rdo-gold);
    border-bottom: 2px solid var(--rdo-gold);
    padding-bottom: 4px; margin-bottom: 12px; display: block;
}
.lbl-sec { font-size: .78rem; font-weight: 700; color: #555; margin-bottom: 2px; }

/* Clima por turno */
.turno-box { background: #fafafa; border: 1px solid #eee; border-radius: 8px; padding: 10px 12px; }
.turno-label { font-size: .75rem; font-weight: 800; text-transform: uppercase; letter-spacing: .07em; color: #888; }
.clima-turno-btns { display: flex; gap: 4px; }
.btn-clima {
    background: none; border: 2px solid transparent; border-radius: 8px;
    font-size: 1.25rem; padding: 2px 5px; cursor: pointer; transition: .15s;
    line-height: 1;
}
.btn-clima.ativo { border-color: var(--rdo-gold); background: rgba(201,168,76,.12); }
.btn-clima:hover  { border-color: #ccc; }

/* Tabelas RDO */
.rdo-table thead th {
    background: #f5f5f5; font-size: .72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em; color: #666;
    padding: 6px 8px; white-space: nowrap;
}
.rdo-table td { padding: 4px 6px; vertical-align: middle; }
.rdo-table .form-control-sm { font-size: .82rem; }

/* Fotos */
.foto-preview { display: flex; flex-wrap: wrap; gap: 8px; }
.foto-item-preview { position: relative; }
.foto-item-preview img { width: 80px; height: 80px; object-fit: cover; border-radius: 6px; border: 2px solid #ddd; }
.btn-rm-foto {
    position: absolute; top: -6px; right: -6px;
    background: #e53935; color: #fff; border: none; border-radius: 50%;
    width: 18px; height: 18px; font-size: .55rem;
    display: flex; align-items: center; justify-content: center; cursor: pointer; padding: 0;
}

/* Show/visualização */
.rdo-show-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
.rdo-show-table th {
    background: #f5f5f5; padding: 7px 10px; font-size: .7rem;
    text-transform: uppercase; letter-spacing: .06em; font-weight: 700; color: #666;
    border: 1px solid #e0e0e0;
}
.rdo-show-table td { padding: 7px 10px; border: 1px solid #e8e8e8; vertical-align: middle; }
.rdo-show-table tbody tr:hover { background: #fafafa; }

.status-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 5px; }

/* Header RDO (show) */
.rdo-header { border-bottom: 2px solid var(--rdo-gold); padding-bottom: 12px; margin-bottom: 16px; }
.rdo-num { font-size: 1.1rem; font-weight: 800; color: #333; }
.rdo-meta-item { font-size: .82rem; color: #555; }

.foto-grid-show { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px,1fr)); gap: 8px; margin-top: 8px; }
.foto-grid-show a img { width: 100%; height: 110px; object-fit: cover; border-radius: 7px; border: 2px solid #eee; transition: .2s; }
.foto-grid-show a:hover img { border-color: var(--rdo-gold); }

.pasta-titulo { font-size: .8rem; font-weight: 700; color: #666; margin-bottom: 6px; }
</style>
