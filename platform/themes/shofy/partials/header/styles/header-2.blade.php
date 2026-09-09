<header>
    <div @class(['tp-header-area tp-header-height', 'tp-header-style-primary' => ! Theme::get('isHomePage')])>
        {!! Theme::partial('header.top', ['colorMode' => 'light', 'headerTopClass' => 'container', 'showUserMenu' => true]) !!}

        <div
            id="header-sticky"
            class="tp-header-bottom-2 tp-header-sticky" {!! Theme::partial('header.sticky-data') !!}
            style="background-color: {{ $headerMainBackgroundColor }}; color: {{ $headerMainTextColor }}"
        >
            <div class="container">
                <div class="tp-mega-menu-wrapper p-relative">
                    <div class="row align-items-center">
                        <div class="col-xl-2 col-lg-5 col-md-5 col-sm-4 col-6">
                            {!! Theme::partial('header.logo') !!}
                        </div>
                        <div class="col-xl-5 d-none d-xl-block">
                            <div class="main-menu menu-style-2">
                                <nav class="tp-main-menu-content">
                                    {!! Menu::renderMenuLocation('main-menu', ['view' => 'main-menu']) !!}
                                </nav>
                            </div>
                            @if(is_plugin_active('ecommerce'))
                                <div class="tp-category-menu-wrapper d-none">
                                    <nav class="tp-category-menu-content">
                                        {!! Theme::partial('header.categories-dropdown') !!}
                                    </nav>
                                </div>
                            @endif
                        </div>
                        @if(is_plugin_active('ecommerce'))
                            {!! Theme::partial('header.styles.header-2-search-ecommerce') !!}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
