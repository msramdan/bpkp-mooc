@can('course view')
    <a href="{{ route('courses.show', $model) }}" class="btn btn-sm btn-success btn-icon btn-wave" title="{{ __('Detail') }}">
        <i class="ri-eye-line"></i>
    </a>
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
