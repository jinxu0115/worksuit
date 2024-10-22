@php
    $addTaskFilePermission = user()->permission('add_task_files');
    $viewTaskFilePermission = user()->permission('view_task_files');
    $deleteTaskFilePermission = user()->permission('delete_task_files');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">
<style>
    .file-action {
        visibility: hidden;
    }

    .file-card:hover .file-action {
        visibility: visible;
    }

</style>

<!-- TAB CONTENT START -->
<div class="tab-pane fade show active" role="tabpanel" aria-labelledby="nav-email-tab">
    @if ($addTaskFilePermission == 'all'
    || ($addTaskFilePermission == 'added' && $task->added_by == user()->id)
    || ($addTaskFilePermission == 'owned' && in_array(user()->id, $taskUsers))
    || ($addTaskFilePermission == 'both' && (in_array(user()->id, $taskUsers) || $task->added_by == user()->id))
    )
        <div class="p-20">

            <div class="row">
                <div class="col-md-12">
                    <a class="f-15 f-w-500" href="javascript:;" id="add-task-review-file"><i
                            class="icons icon-plus font-weight-bold mr-1"></i>@lang('modules.projects.uploadFile')</a>
                </div>
            </div>

            @php
                $userRoles = user_roles();
                $isAdmin = in_array('admin', $userRoles);
                $isEmployee = in_array('employee', $userRoles);
            @endphp

            @if ($task->approval_send == 1 && $task->project->need_approval_by_admin == 1 && $isEmployee && !$isAdmin && $status->slug == 'waiting_approval')
                <!-- Popup for Send Approval -->
                @include('tasks.ajax.sent-approval-modal')
            @else
                <x-form id="save-taskreview-data-form" class="d-none">

                    <input type="hidden" name="task_id" value="{{ $task->id }}">
                    <div class="row">
                        <div class="col-md-12 d-none error-block">
                            <x-alert type="danger" id="review-error"></x-alert>
                        </div>
                        <div class="col-md-12">
                            <x-forms.file-multiple fieldLabel="" fieldName="file[]" fieldId="task-review-upload-dropzone"/>
                        </div>
                        <div class="col-md-12">
                            <div class="w-100 justify-content-end d-flex mt-2">
                                <x-forms.button-cancel id="cancel-taskreviewfile" class="border-0">@lang('app.cancel')
                                </x-forms.button-cancel>
                            </div>
                        </div>
                    </div>
                </x-form>
            @endif
        </div>
    @endif

    <div class="d-flex flex-wrap p-20" id="task-review-file-list">
        @php
            $filesShowCount = 0; // This is done because if fies uploaded and not have permission to view then no record found message should be shown
        @endphp
        @forelse($task->reviewFiles as $file)
            @if ($viewTaskFilePermission == 'all' || ($viewTaskFilePermission == 'added' && $file->added_by == user()->id))
                @php
                    $filesShowCount++;
                @endphp
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

        @if ($filesShowCount == 0 && $task->files->count() > 0)
            <x-cards.no-record :message="__('messages.noFileUploaded')" icon="file"/>
        @endif
    </div>

</div>
<!-- TAB CONTENT END -->

<script>
    $(document).ready(function () {
        var add_task_files = "{{ $addTaskFilePermission }}";
        var send_approval = "{{ $task->approval_send }}";
        var admin = "{{ in_array('admin', user_roles()) }}";
        var employee = "{{ in_array('employee', user_roles()) }}";
        var needApproval = "{{ $task?->project?->need_approval_by_admin }}";
        var status = "{{ $status->slug }}";
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
        $('#add-task-review-file').click(function () {
            if (send_approval == 1 && employee == 1 && admin != 1 && needApproval == 1 && status == 'waiting_approval') {
                $('#send-approval-modal').modal('show');
                $('.modal-backdrop').css('display', 'none');
            }else{
                $(this).closest('.row').addClass('d-none');
                $('.error-block').addClass('d-none');
                $('#save-taskreview-data-form').removeClass('d-none');
            }
        });


        if (add_task_files == "all" || add_task_files == "added") {

            Dropzone.autoDiscover = false;
            taskDropzone = new Dropzone("div#task-review-upload-dropzone", {
                dictDefaultMessage: "{{ __('app.dragDrop') }}",
                url: "{{ route('task-review-files.store') }}",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                paramName: "file",
                maxFilesize: 100,
                maxFiles: DROPZONE_MAX_FILES,
                uploadMultiple: true,
                addRemoveLinks: true,
                parallelUploads: DROPZONE_MAX_FILES,
                acceptedFiles: DROPZONE_REVIEW_FILE_ALLOW,
                init: function () {
                    taskDropzone = this;
                }
            });
            taskDropzone.on('sending', function (file, xhr, formData) {
                var ids = "{{ $task->id }}";
                formData.append('task_id', ids);
                $.easyBlockUI();
            });
            taskDropzone.on('uploadprogress', function () {
                $.easyBlockUI();
            });
            taskDropzone.on('completemultiple', function (file) {
                var response = JSON.parse(file[0].xhr.response);

                if (response?.error?.message) {
                    $('.error-block').removeClass('d-none');
                    $('#review-error').html(response?.error?.message);
                }

                var taskView = response.view;
                taskDropzone.removeAllFiles();
                $.easyUnblockUI();

                $('#task-review-file-list').html(taskView);
            });
            taskDropzone.on('removedfile', function () {
                var grp = $('div#file-upload-dropzone').closest(".form-group");
                var label = $('div#file-upload-box').siblings("label");
                $(grp).removeClass("has-error");
                $(label).removeClass("is-invalid");
            });
            taskDropzone.on('error', function (file, message) {
                taskDropzone.removeFile(file);
                var grp = $('div#file-upload-dropzone').closest(".form-group");
                var label = $('div#file-upload-box').siblings("label");
                $(grp).find(".help-block").remove();
                var helpBlockContainer = $(grp);

                if (helpBlockContainer.length == 0) {
                    helpBlockContainer = $(grp);
                }

                helpBlockContainer.append('<div class="help-block invalid-feedback">' + message + '</div>');
                $(grp).addClass("has-error");
                $(label).addClass("is-invalid");

            });
        }

        $('#cancel-taskreviewfile').click(function () {
            $('#save-taskreview-data-form').addClass('d-none');
            $('#add-task-review-file').closest('.row').removeClass('d-none');
            return false;
        });
    });
</script>
