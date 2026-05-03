<a href="{{ url(config('adminlte.dashboard_url', 'home')) }}" class="brand-link {{ config('adminlte.classes_brand') }}" style="padding:10px 14px !important; display:flex !important; align-items:center !important; min-height:70px !important; overflow:visible !important;">
    @if(config('adminlte.logo_img'))
        <img src="{{ asset(config('adminlte.logo_img')) }}"
             alt="{{ config('adminlte.logo_img_alt', 'Logo') }}"
             style="display:block !important; height:50px !important; width:auto !important; max-width:160px !important; min-height:50px !important; margin:0 10px 0 0 !important; float:none !important; opacity:1 !important; object-fit:contain !important;">
    @endif
    <span class="brand-text font-weight-light {{ config('adminlte.classes_brand_text') }}">
        {!! config('adminlte.logo', '<b>Admin</b>LTE') !!}
    </span>
</a>
