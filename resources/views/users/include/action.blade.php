<td class="text-nowrap text-center">
    <button type="button" class="btn btn-sm btn-success btn-icon btn-wave js-open-user-detail" data-url="{{ route('users.show', $model) }}" title="{{ __('View') }}">
        <i class="ri-eye-line"></i>
    </button>

    @can('user edit')
        <a href="{{ route('users.edit', $model) }}" class="btn btn-sm btn-primary btn-icon btn-wave" title="{{ __('Edit') }}">
            <i class="ri-pencil-line"></i>
        </a>
    @endcan

    @can('user delete')
        <form action="{{ route('users.destroy', $model) }}" method="post" class="d-inline">
            @csrf
            @method('delete')
            <button type="button" class="btn btn-sm btn-danger btn-icon btn-wave js-delete-confirm" title="{{ __('Delete') }}">
                <i class="ri-delete-bin-line"></i>
            </button>
        </form>
    @endcan
</td>
