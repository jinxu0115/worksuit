@foreach($taskReviewComments as $comment)
    @if(!$comment->media_width) @continue @endif
    <div class="position-absolute" style="{{'top: ' . $comment->position_top * 100/$comment->media_height .  '%; left:' . $comment->position_left * 100/$comment->media_width . '%; position: absolute; background-color: rgba(255, 255, 255, 0.8); font-size: 20px; padding: 5px; z-index: 10;'}}">
        {{$comment->comment_text}}
    </div>
@endforeach