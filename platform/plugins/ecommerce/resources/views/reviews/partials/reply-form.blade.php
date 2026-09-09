<x-core::form.textarea
    name="message"
    :placeholder="trans('plugins/ecommerce::review.write_your_reply')"
    :value="old('message', $reply ? $reply->message : '')"
/>

<div class="row mt-3">
    <div class="col-md-6">
        <label class="form-label text-muted small">{{ trans('plugins/ecommerce::review.reply_date') }}</label>
        {!! Form::datePicker('created_at', old('created_at', $reply ? $reply->created_at?->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s')), ['data-options' => json_encode(['enableTime' => true, 'dateFormat' => 'Y-m-d H:i:s'])]) !!}
    </div>
</div>
