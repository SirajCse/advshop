@if ($config['image'])
    <div class="col-md-6">
        <div class="tp-footer-payment text-md-end">
            <p>
                @if (($url = $config['url']) && $url !== '#')
                    <a href="{{ $url }}">
                        {{ RvMedia::image($config['image'], 'footer image') }}
                    </a>
                @else
                    {{ RvMedia::image($config['image'], 'footer image') }}
                @endif
            </p>
        </div>
    </div>
@endif
