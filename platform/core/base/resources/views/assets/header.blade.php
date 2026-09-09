@foreach ($styles as $style)
    {!! Assets::getHtmlBuilder()->style($style['src'] . Assets::getBuildVersionFor($style['src']), $style['attributes']) !!}
@endforeach

@foreach ($headScripts as $script)
    {!! Assets::getHtmlBuilder()->script($script['src'] . Assets::getBuildVersionFor($script['src']), $script['attributes']) !!}
    @if (!empty($script['fallback']))
        <script>window.{!! $script['fallback'] !!} || document.write('<script src="{{ $script['fallbackURL'] }}"><\/script>')</script>
    @endif
@endforeach
