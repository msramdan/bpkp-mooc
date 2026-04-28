<td class="text-nowrap text-center">
    <button type="button" class="btn btn-sm btn-success btn-icon btn-wave js-open-learning-category-detail"
        data-url="{{ route('learning-categories.show', $model) }}" title="{{ __('View') }}">
        <i class="ri-eye-line"></i>
    </button>

    @can('learning category edit')
        <button type="button" class="btn btn-sm btn-primary btn-icon btn-wave js-open-learning-category-form"
            data-mode="edit" data-update-url="{{ route('learning-categories.update', $model) }}"
            data-name="{{ $model->name }}" data-id="{{ $model->id }}" title="{{ __('Edit') }}">
            <i class="ri-pencil-line"></i>
        </button>
    @endcan

    @can('learning category delete')
        <form action="{{ route('learning-categories.destroy', $model) }}" method="post" class="d-inline">
            @csrf
            @method('delete')
            <button type="button" class="btn btn-sm btn-danger btn-icon btn-wave js-delete-confirm" title="{{ __('Delete') }}">
                <i class="ri-delete-bin-line"></i>
            </button>
        </form>
    @endcan
</td>
