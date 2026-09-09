<ul {!! $options !!}>
    @foreach ($menu_nodes->loadMissing('metadata') as $key => $row)
        <li>
            <a href="{{ url($row->url) }}"
               title="{{ $row->title }}"
               @if ($row->target !== '_self') target="{{ $row->target }}" rel="noopener noreferrer" @endif
            >
                {!! $row->icon_html !!}

                {!! BaseHelper::clean($row->title) !!}
            </a>
        </li>
    @endforeach
</ul>
