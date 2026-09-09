@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row row-cards mb-4">
        <x-core::stat-widget.item
            :label="trans('plugins/blog::reports.total_posts')"
            :value="number_format($totalPosts)"
            icon="ti ti-file-text"
            color="primary"
            column="col-sm-6 col-lg-3"
        />
        <x-core::stat-widget.item
            :label="trans('plugins/blog::reports.total_views')"
            :value="number_format($totalViews)"
            icon="ti ti-eye"
            color="green"
            column="col-sm-6 col-lg-3"
        />
        <x-core::stat-widget.item
            :label="trans('plugins/blog::reports.total_categories')"
            :value="number_format($totalCategories)"
            icon="ti ti-folder"
            color="yellow"
            column="col-sm-6 col-lg-3"
        />
        <x-core::stat-widget.item
            :label="trans('plugins/blog::reports.total_tags')"
            :value="number_format($totalTags)"
            icon="ti ti-tag"
            color="cyan"
            column="col-sm-6 col-lg-3"
        />
    </div>

    <div class="row row-cards mb-4">
        <x-core::stat-widget.item
            :label="trans('plugins/blog::reports.published')"
            :value="number_format($publishedPosts)"
            icon="ti ti-check"
            color="green"
            column="col-sm-6 col-lg-3"
        />
        <x-core::stat-widget.item
            :label="trans('plugins/blog::reports.draft')"
            :value="number_format($draftPosts)"
            icon="ti ti-file"
            color="yellow"
            column="col-sm-6 col-lg-3"
        />
        <x-core::stat-widget.item
            :label="trans('plugins/blog::reports.pending')"
            :value="number_format($pendingPosts)"
            icon="ti ti-clock"
            color="cyan"
            column="col-sm-6 col-lg-3"
        />
    </div>

    <div class="row row-cards mb-4">
        <div class="col-md-12">
            <x-core::card>
                <x-core::card.header>
                    <x-core::card.title>{{ trans('plugins/blog::reports.posts_per_category') }}</x-core::card.title>
                </x-core::card.header>
                <x-core::card.body class="p-0">
                    @if ($postsPerCategory->isNotEmpty())
                        <div class="table-responsive">
                            <x-core::table>
                                <x-core::table.header>
                                    <x-core::table.header.cell>#</x-core::table.header.cell>
                                    <x-core::table.header.cell>{{ trans('core/base::tables.name') }}</x-core::table.header.cell>
                                    <x-core::table.header.cell class="text-end">{{ trans('plugins/blog::reports.posts_count') }}</x-core::table.header.cell>
                                </x-core::table.header>
                                <x-core::table.body>
                                    @foreach ($postsPerCategory as $category)
                                        <x-core::table.body.row>
                                            <x-core::table.body.cell>{{ $loop->iteration }}</x-core::table.body.cell>
                                            <x-core::table.body.cell>
                                                <a href="{{ route('categories.edit', $category->id) }}">{{ $category->name }}</a>
                                            </x-core::table.body.cell>
                                            <x-core::table.body.cell class="text-end">{{ number_format($category->posts_count) }}</x-core::table.body.cell>
                                        </x-core::table.body.row>
                                    @endforeach
                                </x-core::table.body>
                            </x-core::table>
                        </div>
                    @else
                        <x-core::empty-state :title="trans('plugins/blog::reports.no_data')" />
                    @endif
                </x-core::card.body>
            </x-core::card>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-md-6">
            <x-core::card>
                <x-core::card.header>
                    <x-core::card.title>{{ trans('plugins/blog::reports.top_viewed_posts') }}</x-core::card.title>
                </x-core::card.header>
                <x-core::card.body class="p-0">
                    @if ($topViewedPosts->isNotEmpty())
                        <div class="table-responsive">
                            <x-core::table>
                                <x-core::table.header>
                                    <x-core::table.header.cell>#</x-core::table.header.cell>
                                    <x-core::table.header.cell>{{ trans('core/base::tables.name') }}</x-core::table.header.cell>
                                    <x-core::table.header.cell class="text-end">{{ trans('plugins/blog::posts.views') }}</x-core::table.header.cell>
                                </x-core::table.header>
                                <x-core::table.body>
                                    @foreach ($topViewedPosts as $post)
                                        <x-core::table.body.row>
                                            <x-core::table.body.cell>{{ $loop->iteration }}</x-core::table.body.cell>
                                            <x-core::table.body.cell>
                                                <a href="{{ route('posts.edit', $post->id) }}">{{ Str::limit($post->name, 50) }}</a>
                                                @if ($post->categories->isNotEmpty())
                                                    <div class="small text-muted">
                                                        {{ $post->categories->pluck('name')->join(', ') }}
                                                    </div>
                                                @endif
                                            </x-core::table.body.cell>
                                            <x-core::table.body.cell class="text-end">
                                                <strong>{{ number_format($post->views) }}</strong>
                                            </x-core::table.body.cell>
                                        </x-core::table.body.row>
                                    @endforeach
                                </x-core::table.body>
                            </x-core::table>
                        </div>
                    @else
                        <x-core::empty-state :title="trans('plugins/blog::reports.no_data')" />
                    @endif
                </x-core::card.body>
            </x-core::card>
        </div>

        <div class="col-md-6">
            <x-core::card>
                <x-core::card.header>
                    <x-core::card.title>{{ trans('plugins/blog::reports.recent_posts') }}</x-core::card.title>
                </x-core::card.header>
                <x-core::card.body class="p-0">
                    @if ($recentPosts->isNotEmpty())
                        <div class="table-responsive">
                            <x-core::table>
                                <x-core::table.header>
                                    <x-core::table.header.cell>#</x-core::table.header.cell>
                                    <x-core::table.header.cell>{{ trans('core/base::tables.name') }}</x-core::table.header.cell>
                                    <x-core::table.header.cell class="text-end">{{ trans('core/base::tables.created_at') }}</x-core::table.header.cell>
                                </x-core::table.header>
                                <x-core::table.body>
                                    @foreach ($recentPosts as $post)
                                        <x-core::table.body.row>
                                            <x-core::table.body.cell>{{ $loop->iteration }}</x-core::table.body.cell>
                                            <x-core::table.body.cell>
                                                <a href="{{ route('posts.edit', $post->id) }}">{{ Str::limit($post->name, 50) }}</a>
                                                @if ($post->categories->isNotEmpty())
                                                    <div class="small text-muted">
                                                        {{ $post->categories->pluck('name')->join(', ') }}
                                                    </div>
                                                @endif
                                            </x-core::table.body.cell>
                                            <x-core::table.body.cell class="text-end text-nowrap">
                                                {{ BaseHelper::formatDate($post->created_at) }}
                                            </x-core::table.body.cell>
                                        </x-core::table.body.row>
                                    @endforeach
                                </x-core::table.body>
                            </x-core::table>
                        </div>
                    @else
                        <x-core::empty-state :title="trans('plugins/blog::reports.no_data')" />
                    @endif
                </x-core::card.body>
            </x-core::card>
        </div>
    </div>

@endsection
