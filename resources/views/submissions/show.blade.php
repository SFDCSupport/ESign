<x-esign::layout
    :title="__('esign::label.submissions')"
    :document="$document"
>
    @foreach ($signer->elements as $element)
            {{ $element->submitted_at->format('') }} - {!! $element->data !!}
    @endforeach
</x-esign::layout>
