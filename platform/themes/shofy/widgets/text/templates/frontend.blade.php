@if ($config['name'] || $config['content'])
    <div @class([
        'col-xl-2 col-lg-3 col-md-4 col-sm-6 widget widget-text' => $sidebar === 'footer_primary_sidebar',
        'panel panel-default' => $sidebar != 'footer_primary_sidebar'
    ])>
        @if ($config['name'])
            <div class="panel-title">
                <h3>{!! BaseHelper::clean($config['name']) !!}</h3>
            </div>
        @endif

        @if ($config['content'])
            <div @class([
                'footer-sidebar-content' => $sidebar === 'footer_primary_sidebar',
                'panel-content' => $sidebar != 'footer_primary_sidebar'
            ])>
                <div>{!! BaseHelper::clean(shortcode()->compile($config['content'])) !!}</div>
            </div>
        @endif
    </div>
@endif
