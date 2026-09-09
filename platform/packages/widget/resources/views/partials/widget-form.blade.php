<form method="post" id="widget-add-form">
    <input name="id" type="hidden" value="{{ $widget->getId() }}">
    {!! $widget->form($sidebarId, $position) !!}
</form>
