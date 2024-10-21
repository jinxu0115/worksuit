@foreach($taskReviewComments as $comment)
    @if($comment->time_frame)
        <img src="{{$comment->user->image_url}}" class="comment-marker" data-time="{{$comment->time_frame}}" style="{{ 'left: ' . $comment->time_frame * 100 / $review_file->duration . '%;'}}"/>
    @endif
@endforeach