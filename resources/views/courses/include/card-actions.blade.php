@can('course view')
    <a href="{{ route('courses.show', $model) }}" class="btn btn-sm btn-success btn-icon btn-wave" title="{{ __('Detail') }}">
        <i class="ri-eye-line"></i>
    </a>

    @if ($model->is_forum_open)
        <a href="{{ route('courses.show', [$model, 'tab' => 'forum']) }}"
            class="btn btn-sm btn-info btn-icon btn-wave"
            title="{{ __('Forum') }}">
            <i class="ri-chat-3-line"></i>
        </a>
    @else
        <button type="button"
            class="btn btn-sm btn-info btn-icon btn-wave"
            title="{{ __('Forum ditutup') }}"
            disabled
            aria-disabled="true">
            <i class="ri-chat-3-line"></i>
        </button>
    @endif
@endcan

@can('course delete')
    <form action="{{ route('courses.destroy', $model) }}" method="post" class="d-inline">
        @csrf
        @method('delete')
        <button type="button" class="btn btn-sm btn-danger btn-icon btn-wave js-delete-confirm" title="{{ __('Delete') }}">
            <i class="ri-delete-bin-line"></i>
        </button>
    </form>
@endcan
