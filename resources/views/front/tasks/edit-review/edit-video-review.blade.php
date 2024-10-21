<style>
    #video_element{
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
    #comments_panel textarea{
        border-radius: 10px;
    }
    #total_time_bar{
        height: 10px;
        border-radius: 10px;
        background: #c3bfbf;
        position: relative;
        cursor: pointer;
        user-select: none;
    }
    #video_time_bar{
        height: 10px;
        border-radius: 10px;
        background: #515050;
        position: absolute;
        top: 0;
        left: 0;
    }
    .video-control-button{
        width: 30px;
        height: 30px;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
    }
    #short_comments img{
        width: 30px;
        height: 30px;
        position: absolute;
        top: -30px;
        cursor: pointer;
    }
</style>
<div class="modal-body text-center">
    <input type="hidden" id="review_file" value="{{json_encode($review_file)}}"/>
    <input type="hidden" id="taskReviewComments" value="{{json_encode($taskReviewComments)}}"/>
    <input type="hidden" id="mode" value='{{$mode}}'/>
    <div class="position-relative media-div">
        <div class="media-element" >
            <video id="video_element">
                <source src="{{ $review_file->file_url }}" type="video/{{ $extension }}">
                Your browser does not support the video tag.
            </video>
        </div>
        <div class="d-flex align-items-center">
            <div class="mr-2 rounded-circle border video-control-button">
                <i class="fa fa-stop"></i>
            </div>
            <div class="w-100" id="total_time_bar">
                <div class="w-100" id="short_comments">
                    @foreach($taskReviewComments as $comment)
                        <img src="/img/marker.png" class="comment-marker" data-time="{{$comment->time_frame}}" style="{{ 'left: ' . $comment->time_frame * 100 / $review_file->duration . '%;'}}"/>
                    @endforeach
                </div>
                <div id="video_time_bar"></div>
            </div>
        </div>
        <div id="comments_in_image">
            
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
    $(document).ready(function () {
        video = document.getElementById('video_element');

        video.addEventListener('loadedmetadata', () => {
            duration = video.duration;
            video.play();
        });

        video.addEventListener('ended', () => {
            $('.video-control-button').empty();
            $('.video-control-button').append('<i class="fa fa-play"></i>')
        });

        $('.comment-marker').on('click', function() {
            video.currentTime = $(this).attr('data-time');
            video.pause();
            $('.video-control-button').empty();
            $('.video-control-button').append('<i class="fa fa-play"></i>')
        })

        $('#total_time_bar').on('click', function(e) {
            const offset = $(this).offset();
            const left = e.pageX - offset.left;
            const total_width = $(this).width();
            $('#video_time_bar').width(left * 100 / total_width + '%');
            let current_time = left * duration / total_width;

            video.currentTime = current_time.toString();
        })

        $('.video-control-button').on('click', function() {
            if (video.paused) {
                video.play();
                $(this).empty();
                $(this).append('<i class="fa fa-stop"></i>')
            } else {
                video.pause();
                $(this).empty();
                $(this).append('<i class="fa fa-play"></i>')
            }
        })
        
        $('#video_element').on('timeupdate', function() {
            const currentTime = video.currentTime;
            $('#video_time_bar').width(currentTime * 100 /duration + '%');

            let taskReviewComments = JSON.parse($('#taskReviewComments').val());
            let comments = taskReviewComments.filter((comment) => {
                return comment.time_frame - 1 < currentTime && comment.time_frame + 1 > currentTime;
            })
            let html = '';
            comments.map((comment) => {
                html += `<div class="position-absolute" style="top : ${comment.position_top * 100/comment.media_height}%; left: ${comment.position_left * 100/comment.media_width}%; position: absolute; background-color: rgba(255, 255, 255, 0.8); font-size: 20px; padding: 5px; z-index: 10;">
                            ${comment.comment_text}
                        </div>`
            })
            $('#comments_in_image').empty();
            $('#comments_in_image').append(html);
        })
        if($('#mode').val() == 'edit'){
            $('.media-element').on('click', function(e) {
                $('#video_element').get(0).pause();
                $('.video-control-button').empty();
                $('.video-control-button').append('<i class="fa fa-play"></i>')
                let review_file = JSON.parse($('#review_file').val());

                $('.comment').remove();

                const offset = $(this).offset();
                const x = e.pageX - offset.left;
                const y = e.pageY - offset.top;

                // Create a comment box
                let commentHtml = `
                    <div class="comment" style="top: ${y}px; left: ${x}px; background: transparent;">
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
                                'positionLeft' : commentPosition.left,
                                'timeFrame' : $('#video_element').get(0).currentTime
                            },
                            success: function (response){
                                if(response.status == 'success'){
                                    $('#video_element').get(0).play();
                                    $('.video-control-button').empty();
                                    $('.video-control-button').append('<i class="fa fa-stop"></i>')
                                    $('#comments_panel').empty();
                                    $('#comments_panel').append(response.list_view);
                                    $('#short_comments').empty();
                                    $('#short_comments').append(response.comment_marker_view);
                                    $('.comment-marker').on('click', function() {
                                        video.currentTime = $(this).attr('data-time');
                                        video.pause();
                                        $('.video-control-button').empty();
                                        $('.video-control-button').append('<i class="fa fa-play"></i>')
                                    })
                                    $('#taskReviewComments').val(JSON.stringify(response.taskReviewComments));
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
        }
    })
</script>