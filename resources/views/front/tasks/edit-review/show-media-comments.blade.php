@foreach($taskReviewComments as $comment)
    @if(!$comment->top_percentage) @continue @endif
    <div class="position-absolute" style="{{'top: ' . $comment->top_percentage .  '%; left:' . $comment->left_percentage . '%; position: absolute; background-color: rgba(255, 255, 255, 0.8); font-size: 20px; padding: 5px; z-index: 10;'}}">
        {{$comment->comment_text}}
    </div>
@endforeach