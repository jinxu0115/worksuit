<style>
    .media-element{
        max-height: 100vh;
        width: auto;
    }
    .comment{
        position : absolute;
        background-color: rgba(255, 255, 255, 0.8);
        border: none;
        padding: 5px;
        z-index: 10;
    }
    .comment textarea{
        border-radius: 10px;
        width: 300px;
        height: 100px;
        padding: 10px;
        font-size: 20px;
    }
    .submit-comment{
        right: 37px;
        top: 6px;
        border-radius: 100%;
        cursor: pointer;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .cancel-comment{
        right: 6px;
        top: 6px;
        border-radius: 100%;
        cursor: pointer;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    textarea{
        border-radius: 5px;
        border: 1px solid #c9c9c9;
    }
    .update-comment{
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
    }
    .remove-comment{
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        margin-top: 5px;
    }
    #comments_panel .row{
        border-bottom: 1px solid gray;
    }
</style>
<div class="modal-body text-center row px-10">
    <input type="hidden" id="mode" value='{{$mode}}'/>
    <input type="hidden" id="review_file" value="{{json_encode($review_file)}}"/>
    <div class="position-relative media-div col-9">
        <img src="{{ $review_file->file_url }}" class="img-fluid media-element" alt="Review Image">
        <div id="comments_in_image">
            @foreach($taskReviewComments as $comment)
                @if(!$comment->top_percentage) @continue @endif
                <div class="position-absolute" style="{{'top: ' . $comment->top_percentage .  '%; left:' . $comment->left_percentage . '%; position: absolute; background-color: rgba(255, 255, 255, 0.8); font-size: 20px; padding: 5px; z-index: 10;'}}">
                    {{$comment->comment_text}}
                </div>
            @endforeach
        </div>
        @if($mode == 'edit')
            <div class="row mt-3">
                <div class="col-9">
                    <textarea class="w-100" id="new_comment"></textarea>                
                </div>
                <div class="col-3">
                    <x-forms.button-primary class="btn-xs" id="create_comment" icon="plus">
                        Add Comment
                    </x-forms.button-primary> 
                </div>
            </div>
        @endif
    </div>
    <div id="comments_panel" class="col-3">
        <h3>Comments</h3>
        @foreach($taskReviewComments as $comment)
            <div class="row p-1">
                <div class="{{$mode == 'edit' ? 'col-10' : 'col-12'}}">
                    <textarea class="w-100">{{$comment->comment_text}}</textarea>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>{{$comment->user->name}}</div>
                        <div>{{\Carbon\Carbon::parse($comment->created_at)->format(companyOrGlobalSetting()->date_format) . ' ' . \Carbon\Carbon::parse($comment->created_at)->format(company()->time_format)}}</div>
                    </div>
                </div>
                @if($mode == 'edit')
                    <div class="col-2">
                        <x-forms.button-secondary class="btn-xs update-comment" data-comment-id="{{$comment->id}}" icon="edit">
                        </x-forms.button-secondary>
                        <x-forms.button-secondary class="btn-xs remove-comment" data-comment-id="{{$comment->id}}" icon="trash">
                        </x-forms.button-secondary>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

<div class="modal-footer d-flex align-items-center justify-content-between">
    @if($review_file->canApprove())
        <div class="d-flex">
            <button class="btn btn-danger" id="reject_review">{{$review_file->rejected ? 'Unreject' : 'Reject'}}</button>
            <button class="btn btn-success ml-2" id="approve_review">
                @if($review_file->isCreator())
                    {{$review_file->approved_by_creator ? 'Unapprove' : 'Approve'}}
                @elseif ($review_file->isManager())
                    {{$review_file->approved_by_manager ? 'Unapprove' : 'Approve'}}
                @endif
            </button>
        </div>
    @endif
    <x-forms.button-cancel data-dismiss="modal">@lang('app.close')</x-forms.button-cancel>
</div>
<script>
    $('#reject_review').click(function() {
        let review_file = JSON.parse($('#review_file').val());
        const url = "{{ route('front.public.reject-review') }}";

        $.ajax({
            type: "POST",
            url : url,
            data: {
                reviewFileId : review_file.id,
                '_token': '{{csrf_token()}}'
            },
            success: function (response) {
                window.location.reload();
            }
        })
    })
    $('#approve_review').click(function() {
        let review_file = JSON.parse($('#review_file').val());
        const url = "{{ route('front.public.approve-review') }}";

        $.ajax({
            type: "POST",
            url : url,
            data: {
                reviewFileId : review_file.id,
                '_token': '{{csrf_token()}}'
            },
            success: function (response) {
                window.location.reload();
            }
        })
    })
    if($('#mode').val() == 'edit'){
        $('#create_comment').on('click', function() {
            let review_file = JSON.parse($('#review_file').val());
            const comment = $('#new_comment').val();
            if(comment == '') return ;
            $.ajax({
                type: "POST",
                url: "{{ route('task-review-comment.store') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'mediaId' : review_file.id,
                    'commentText' : comment.trim(),
                },
                success: function (response){
                    if(response.status == 'success'){
                        $('#new_comment').val('')
                        $('#comments_panel').empty();
                        $('#comments_panel').append(response.list_view);
                        $('#comments_in_image').empty();
                        $('#comments_in_image').append(response.media_view);
                    }
                }
            })
        })
        $('.media-element').on('click', function(e) {
            let review_file = JSON.parse($('#review_file').val());

            $('.comment').remove();

            const offset = $(this).offset();
            const x = e.pageX - offset.left;
            const y = e.pageY - offset.top;
            const relativeDivX = offset.left - $('.media-div').offset().left;
            const relativeDivY = offset.top - $('.media-div').offset().top;

            // Create a comment box
            let commentHtml = `
                <div class="comment" style="top: ${y + relativeDivY}px; left: ${x + relativeDivX}px;">
                    <textarea class="comment-text" rows="2" placeholder="Enter your comment"></textarea>
                    <div class="postion-relative flex">                
                        <button class="submit-comment position-absolute btn btn-primary"><i class="fa fa-check"></i></button>
                        <button class="cancel-comment position-absolute btn btn-secondary"><i class="fa fa-times"></i></button>
                    </div>
                </div>
            `;

            $('.media-div').append(commentHtml);
            
            $('.comment textarea').focus();

            $('.cancel-comment').on('click', function() {
                $(this).closest('.comment').remove();
            })

            $('.submit-comment').last().on('click', function() {
                const comment = $('.comment').find('textarea').val();
                if (comment.trim()) {
                    const commentBox = $(this).closest('.comment');
                    const commentPosition = commentBox.position();
                    
                    commentBox.remove();
                    $.ajax({
                        type: "POST",
                        url: "{{ route('task-review-comment.store') }}",
                        data: {
                            '_token': "{{ csrf_token() }}",
                            'mediaId' : review_file.id,
                            'commentText' : comment.trim(),
                            'left_percentage' : (x + relativeDivX) * 100 / $('.media-div').width(),
                            'top_percentage' : (y + relativeDivY) * 100 / $('.media-div').height(),
                        },
                        success: function (response){
                            if(response.status == 'success'){
                                $('#comments_panel').empty();
                                $('#comments_panel').append(response.list_view);
                                $('#comments_in_image').empty();
                                $('#comments_in_image').append(response.media_view);
                            }
                        }
                    })
                } else {
                    console.log('Please enter a comment.');
                }
            });
        });
        $('.update-comment').on('click', function() {
            let commentId = $(this).attr('data-comment-id');
            let commentText = $(this).closest('.row').find('textarea').val();
            let url = `{{ route('task-review-comment.update', ':id') }}`
            url = url.replace(':id', commentId);
            $.ajax({
                type: "PUT",
                url: url,
                data: {
                    '_token': "{{ csrf_token() }}",
                    'commentText' : commentText,
                },
                success: function (response){
                    if(response.status == 'success'){
                        $('#comments_panel').empty();
                        $('#comments_panel').append(response.list_view);
                        $('#comments_in_image').empty();
                        $('#comments_in_image').append(response.media_view);
                    }
                }
            })
        })
        $('.remove-comment').on('click', function() {
            let commentId = $(this).attr('data-comment-id');
            let url = `{{ route('task-review-comment.destroy', ':id') }}`
            url = url.replace(':id', commentId);
            $.ajax({
                type: "DELETE",
                url: url,
                data: {
                    '_token': "{{ csrf_token() }}",
                },
                success: function (response){
                    if(response.status == 'success'){
                        $('#comments_panel').empty();
                        $('#comments_panel').append(response.list_view);
                        $('#comments_in_image').empty();
                        $('#comments_in_image').append(response.media_view);
                    }
                }
            })
        })
    }
</script>