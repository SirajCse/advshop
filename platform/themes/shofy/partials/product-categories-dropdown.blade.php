@php
    $groupedCategories = ProductCategoryHelper::getProductCategoriesWithUrl()->groupBy('parent_id');

    $currentCategories = $groupedCategories->get(0);

    $enableMegaMenu = theme_option('enabled_mega_menu_in_product_categories_dropdown', 'yes') === 'yes';
@endphp

@if($currentCategories)
    @switch($style ?? 1)
        @case(5)
            <ul @class(['tp-submenu' => $hasChildren])>
                @foreach ($currentCategories as $category)
                    @php
                        $hasChildren = $groupedCategories->has($category->id);
                    @endphp

                    <li @class(['has-dropdown' => $hasChildren])>
                        <a href="{{ route('public.single', $category->url) }}" title="{{ $category->name }}">
                            {!! Theme::partial('header.categories-item', ['category' => $category]) !!}
                        </a>

                        @if($hasChildren && $currentCategories = $groupedCategories->get($category->id))
                            {!! Theme::partial('header.categories-dropdown', ['currentCategories' => $currentCategories, 'hasChildren' => $hasChildren, 'groupedCategories' => $groupedCategories]) !!}
                        @endif
                    </li>
                @endforeach
            </ul>

            @break
        @default
            <ul>
                @foreach ($currentCategories as $category)
                    @php
                        $hasChildren = $groupedCategories->has($category->id);
                        $hasMegaMenu = $enableMegaMenu && $hasChildren && $category->image;
                    @endphp

                    <li @class(['has-dropdown' => $hasChildren])>
                        <a href="{{ route('public.single', $category->url) }}" @class(['has-mega-menu' => $hasMegaMenu])>
                            {!! Theme::partial('header.categories-item', ['category' => $category]) !!}
                        </a>

                        @if($hasChildren && $currentCategories = $groupedCategories->get($category->id))
                            @php
                                $hasMegaMenu = $enableMegaMenu && $groupedCategories->has($currentCategories->first()->id) && $currentCategories->first()->image;
                            @endphp

                            <ul @class(['tp-submenu', 'mega-menu' => $hasMegaMenu])>
                                @foreach ($currentCategories as $childCategory)
                                    @php
                                        $hasChildren = $groupedCategories->has($childCategory->id);
                                        $hasMegaMenuForChild = $enableMegaMenu && $hasChildren && $childCategory->image;
                                    @endphp

                                    <li @class(['has-dropdown' => $hasChildren && ! $hasMegaMenuForChild])>
                                        <a href="{{ route('public.single', $childCategory->url) }}" @class(['mega-menu-title' => $hasMegaMenuForChild && $hasChildren])>
                                            {!! Theme::partial('header.categories-item', ['category' => $childCategory]) !!}
                                        </a>

                                        @if ($hasChildren)
                                            <ul @class(['tp-submenu' => ! $hasMegaMenu])>
                                                @foreach ($groupedCategories->get($childCategory->id) as $item)
                                                    @if($loop->first && $childCategory->image && $hasMegaMenuForChild)
                                                        <li>
                                                            <a href="{{ route('public.single', $childCategory->url) }}">
                                                                {{ RvMedia::image($childCategory->image, $childCategory->name) }}

                                                                <span class="sr-only">{{ $childCategory->name }}</span>
                                                            </a>
                                                        </li>
                                                    @endif
                                                    <li>
                                                        <a href="{{ route('public.single', $item->url) }}">
                                                            {!! Theme::partial('header.categories-item', ['category' => $item]) !!}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>

            @break
    @endswitch
@endif
