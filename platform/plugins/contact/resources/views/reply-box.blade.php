@if ($contact)
    <div
        id="reply-wrapper"
        class="mb-3"
    >
        @if ($contact->replies->isNotEmpty())
            @foreach ($contact->replies as $reply)
                <x-core::form.fieldset>
                    <p>{{ trans('plugins/contact::contact.tables.time') }}:
                        <i>{{ BaseHelper::formatDateTime($reply->created_at) }}</i></p>
                    <p>{{ trans('plugins/contact::contact.tables.content') }}:</p>
                    {!! BaseHelper::clean($reply->message) !!}
                </x-core::form.fieldset>
            @endforeach
        @else
            <div class="text-muted">{{ trans('plugins/contact::contact.no_reply') }}</div>
        @endif
    </div>

    @if (!$contact->email || !filter_var($contact->email, FILTER_VALIDATE_EMAIL))
        <x-core::alert type="warning">
            {{ trans('plugins/contact::contact.email_invalid_for_reply') }}
        </x-core::alert>
    @else
        <x-core::button
            type="button"
            class="answer-trigger-button"
        >
            {{ trans('plugins/contact::contact.reply') }}
        </x-core::button>
    @endif

    <div class="answer-wrapper mt-3">
        <input
            type="hidden"
            value="{{ $contact->id }}"
            id="input_contact_id"
        >

        <div class="mb-3">
            {!! Form::editor('message', null, ['without-buttons' => true, 'class' => 'form-control']) !!}
        </div>

        <x-core::button
            type="button"
            color="primary"
            icon="ti ti-send"
            class="answer-send-button"
            data-url="{{ route('contacts.reply', $contact->id) }}"
        >
            {{ trans('plugins/contact::contact.send') }}
        </x-core::button>
    </div>
@endif
