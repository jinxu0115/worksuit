<style>
    .media-element{
        width: 100%;
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
        right: 10px;
        top: 10px;
        border-radius: 100%;
        cursor: pointer;
        padding: 5px;
    }
    #comments_panel textarea{
        border-radius: 10px;
    }
</style>
<div class="modal-body text-center">
    <input type="hidden" id="mode" value='{{$mode}}'/>
    <input type="hidden" id="review_file" value="{{json_encode($review_file)}}"/>
    <div class="position-relative media-div">
        <img src="{{ $review_file->file_url }}" class="img-fluid media-element" alt="Review Image">
        <div id="comments_in_image">
            @foreach($taskReviewComments as $comment)
                <div class="position-absolute" style="{{'top: ' . $comment->position_top * 100/$comment->media_height .  '%; left:' . $comment->position_left * 100/$comment->media_width . '%; position: absolute; background-color: rgba(255, 255, 255, 0.8); font-size: 20px; padding: 5px; z-index: 10;'}}">
                    {{$comment->comment_text}}
                </div>
            @endforeach
        </div>
    </div>
    @if($mode == 'edit')
        <div id="comments_panel">
            @foreach($taskReviewComments as $comment)
                <div class="row mt-3">
                    <div class="col-8">
                        <textarea class="w-100 h-100">{{$comment->comment_text}}</textarea>
                    </div>
                    <div class="col-4 d-flex justify-content-center">
                        <x-forms.button-secondary class="btn-xs update-comment mr-2" data-comment-id="{{$comment->id}}" icon="edit">
                            Update
                        </x-forms.button-secondary>
                        <x-forms.button-secondary class="btn-xs remove-comment" data-comment-id="{{$comment->id}}" icon="trash">
                            Remove
                        </x-forms.button-secondary>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="modal-footer">
  <x-forms.button-cancel data-dismiss="modal">@lang('app.close')</x-forms.button-cancel>
</div>
<script>
    if($('#mode').val() == 'edit'){
        $('.media-element').on('click', function(e) {
            let review_file = JSON.parse($('#review_file').val());

            $('.comment').remove();

            const offset = $(this).offset();
            const x = e.pageX - offset.left;
            const y = e.pageY - offset.top;

            // Create a comment box
            let commentHtml = `
                <div class="comment" style="top: ${y}px; left: ${x}px;">
                    <textarea class="comment-text" rows="2" placeholder="Enter your comment"></textarea>
                    <div class="postion-relative">                
                        <button class="submit-comment position-absolute"><i class="fa fa-check mr-1"></i></button>
                    </div>
                </div>
            `;

            $('.media-div').append(commentHtml);

            $('.submit-comment').last().on('click', function() {
                const comment = $('.comment').find('textarea').val();
                if (comment.trim()) {
                    const commentBox = $(this).closest('.comment');
                    const commentPosition = commentBox.position();
                    let divHtml = `
                        <div style="top: ${commentPosition.top}px; left: ${commentPosition.left}px; position: absolute; background-color: rgba(255, 255, 255, 0.8); font-size: 20px; padding: 5px; z-index: 10;">
                            ${comment}
                        </div>
                    `;
                    $('#comments_in_image').append(divHtml);
                    
                    commentBox.remove();
                    $.ajax({
                        type: "POST",
                        url: "{{ route('task-review-comment.store') }}",
                        data: {
                            '_token': "{{ csrf_token() }}",
                            'mediaId' : review_file.id,
                            'commentText' : comment.trim(),
                            'mediaWidth' : $('.media-element').width(),
                            'mediaHeight' : $('.media-element').height(),
                            'positionTop' : commentPosition.top,
                            'positionLeft' : commentPosition.left
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