@php
    $viewTaskFilePermission = user()->permission('view_task_files');
    $deleteTaskFilePermission = user()->permission('delete_task_files');
@endphp

@forelse($reviewFiles as $file)
    @if ($viewTaskFilePermission == 'all' || ($viewTaskFilePermission == 'added' && $file->added_by == user()->id))
        <div class="position-relative">
            <x-file-card :fileName="$file->filename" :dateAdded="$file->created_at->diffForHumans()">
                @if ($file->icon == 'images')
                    <a class="view-review" data-review-file-id="{{$file->id}}"  href="javascript:;">
                        <img src="{{ $file->file_url }}">
                    </a>
                @else
                    <a class="view-review" data-review-file-id="{{$file->id}}" >
                        <i class="fa {{ $file->icon }} text-lightest"></i>
                    </a>
                @endif
                    <x-slot name="action">
                        <div class="dropdown ml-auto file-action">
                            <button class="btn btn-lg f-14 p-0 text-lightest  rounded  dropdown-toggle"
                                    type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-ellipsis-h"></i>
                            </button>

                            <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                aria-labelledby="dropdownMenuLink" tabindex="0">
                                <a class="cursor-pointer d-block text-dark-grey f-13 pt-3 px-3 edit-review" data-review-file-id="{{$file->id}}" href="javascript:;">Edit</a>
                                <!-- <a class="cursor-pointer d-block text-dark-grey f-13 pt-3 px-3 view-review" data-review-file-id="{{$file->id}}" href="javascript:;">View</a> -->
                                @if($file->canApprove())
                                    <a class="cursor-pointer d-block text-dark-grey f-13 pt-3 px-3 approve-review" data-review-file-id="{{$file->id}}" href="javascript:;">
                                        @if($file->isCreator())
                                            {{$file->approved_by_creator ? 'Unapprove' : 'Approve'}}
                                        @elseif ($file->isManager())
                                            {{$file->approved_by_manager ? 'Unapprove' : 'Approve'}}
                                        @endif
                                    </a>
                                @endif
                                <a class="cursor-pointer d-block text-dark-grey f-13 py-3 px-3 "
                                href="{{ route('task_review_files.downloadReviewFile', md5($file->id)) }}">@lang('app.download')</a>

                                @if ($deleteTaskFilePermission == 'all' || ($deleteTaskFilePermission == 'added' && $file->added_by == user()->id))
                                    <a class="cursor-pointer d-block text-dark-grey f-13 pb-3 px-3 delete-review-file"
                                    data-row-id="{{ $file->id }}" href="javascript:;">@lang('app.delete')</a>
                                @endif
                            </div>
                        </div>
                    </x-slot>
                @if($file->rejected)
                    <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-center" style="top: 0; left: 0; font-size: 30px; color: red;">
                        Rejected
                    </div>
                @endif
            </x-file-card>
            @if($file->approved_by_creator)
                <div data-toggle="tooltip" data-original-title="Approved By Creator" class="position-absolute" style="top: 0;"><i class="fas fa-check-circle" style="width: 30px; height: 30px; color: green;"></i></div>
            @else
                <div data-toggle="tooltip" data-original-title="Not Approve By Creator" class="position-absolute" style="top: 0;"><i class="fas fa-times-circle" style="width: 30px; height: 30px; color: red;"></i></div>
            @endif        
            @if($file->approved_by_manager)
                <div data-toggle="tooltip" data-original-title="Approved By Manager" class="position-absolute" style="bottom: 12px;"><i class="fas fa-check-circle" style="width: 30px; height: 30px; color: green;"></i></div>
            @else
                <div data-toggle="tooltip" data-original-title="Not Approve By Manager" class="position-absolute" style="bottom: 12px;"><i class="fas fa-times-circle" style="width: 30px; height: 30px; color: red;"></i></div>
            @endif
        </div>
    @endif
@empty
    <x-cards.no-record :message="__('messages.noFileUploaded')" icon="file"/>
@endforelse
<script>
    $('.view-review').on('click', function() {
        var reviewFileId = $(this).data('review-file-id');

        const url = "{{ route('front.public.edit-review') }}" + `?review_file_id=${encodeURIComponent(reviewFileId)}&mode=view`;

        $(MODAL_XL + ' ' + MODAL_HEADING).html('...');

        $.ajaxModal(MODAL_XL, url);
    })
    $('.edit-review').on('click', function() {
        var reviewFileId = $(this).data('review-file-id');

        const url = "{{ route('front.public.edit-review') }}" + `?review_file_id=${encodeURIComponent(reviewFileId)}&mode=edit`;

        $(MODAL_XL + ' ' + MODAL_HEADING).html('...');

        $.ajaxModal(MODAL_XL, url);
    })
    $('.approve-review').on('click', function() {
        var reviewFileId = $(this).data('review-file-id');

        const url = "{{ route('front.public.approve-review') }}";

        $.ajax({
            type: "POST",
            url : url,
            data: {
                reviewFileId : reviewFileId,
                '_token': '{{csrf_token()}}'
            },
            success: function (response) {
                window.location.reload();
            }
        })
    })
</script>