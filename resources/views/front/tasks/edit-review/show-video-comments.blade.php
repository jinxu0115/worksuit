<h3>Comments</h3>
@foreach($taskReviewComments as $comment)
    <div class="row p-1">
        <div class="col-10">
            <textarea class="w-100">{{$comment->comment_text}}</textarea>
            <div class="d-flex justify-content-between align-items-center">
                <div>{{$comment->user->name}}</div>
                <div>{{\Carbon\Carbon::parse($comment->created_at)->format(companyOrGlobalSetting()->date_format) . ' ' . \Carbon\Carbon::parse($comment->created_at)->format(company()->time_format)}}</div>
            </div>
        </div>
        <div class="col-2">
            <x-forms.button-secondary class="btn-xs update-comment" data-comment-id="{{$comment->id}}" icon="edit">
            </x-forms.button-secondary>
            <x-forms.button-secondary class="btn-xs remove-comment" data-comment-id="{{$comment->id}}" icon="trash">
            </x-forms.button-secondary>
        </div>
    </div>
@endforeach

<script>
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
                    $('#taskReviewComments').val(JSON.stringify(response.taskReviewComments));
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
                    $('#short_comments').empty();
                    $('#short_comments').append(response.comment_marker_view);
                    $('#taskReviewComments').val(JSON.stringify(response.taskReviewComments));
                    
                    $('.comment-marker').on('click', function() {
                        video.currentTime = $(this).attr('data-time');
                        video.pause();
                        $('.video-control-button').empty();
                        $('.video-control-button').append('<i class="fa fa-play"></i>')
                    })
                }
            }
        })
    }) 
</script>