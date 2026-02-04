<a href="{{ url(config('adminlte.dashboard_url', 'home')) }}" class="brand-link {{ config('adminlte.classes_brand') }}">
    @if(config('adminlte.logo_img'))
        <img src="{{ asset(config('adminlte.logo_img')) }}" alt="{{ config('adminlte.logo_img_alt', 'Logo') }}" class="{{ config('adminlte.logo_img_class', 'brand-image img-circle elevation-3') }}" style="opacity: .8; max-height: 33px;">
    @endif
    <span class="brand-text font-weight-light {{ config('adminlte.classes_brand_text') }}">
        {!! config('adminlte.logo', '<b>Admin</b>LTE') !!}
    </span>
</a>
