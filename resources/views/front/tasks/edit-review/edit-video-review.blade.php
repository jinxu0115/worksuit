<link href="https://vjs.zencdn.net/7.20.3/video-js.css" rel="stylesheet" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ url('/css/video-player/styles.css') }}">
<style>
    .modal-dialog{
        max-width: 100% !important;
        height: 100% !important;
        margin: 0;
    }
    .modal-content{
        height: 100%;
    }
</style>

<div class="modal-body text-center px-10">
    <input type="hidden" id="userInfo" value="{{json_encode(user())}}"/>
    <input type="hidden" id="reviewFileId" value="{{$review_file->id}}"/>
    <input type="hidden" id="review_file" value="{{json_encode($review_file)}}"/>
    <div class="d-flex justify-content-end">
        <x-forms.button-cancel id="close-video-player-modal" data-dismiss="modal">@lang('app.close')</x-forms.button-cancel>
    </div>    
    <div class="row">
        <div class="col-lg-9 col-12">
            <div class="video-container">
                <video
                id="my-video"
                class="video-js vjs-default-skin"
                controls
                preload="auto">
                <source src="{{ $review_file->file_url }}" type="video/mp4" />
                <p class="vjs-no-js">
                    To view this video please enable JavaScript, and consider upgrading to a web browser that
                    <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>.
                </p>
                </video>
            </div>

            <!-- Input field and button for adding markers -->
            @if($mode == 'edit')
                <div class="marker-input-container">
                    <input type="text" id="marker-text-input" placeholder="Enter marker text here..." />
                    <label for="is-comment-only">
                        <input type="checkbox" id="is-comment-only" />
                        Comment Only
                    </label>
                    <button id="add-marker-button">Add Marker</button>
                    <div id="drawing-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;"></div>

                </div>
            @endif
        </div>
        <div class="col-lg-3 col-12">
            <!-- Marker list container -->
            <div id="marker-list" class="marker-list-container"></div>
        </div>
    </div>
</div>

<div class="modal-footer d-flex align-items-center justify-content-start">
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
</div>
  <!-- Video.js JavaScript -->
  <script src="/js/video-player/video.js"></script> 

  <script src="/js/video-player/markerStorage.js"></script>
  <script src="/js/video-player/videoPlayer.js"></script>
  <script src="/js/video-player/main.js"></script>
  <script>
    var csrfToken = "{{ csrf_token() }}";
    var getCommentsUrl = "{{ route('tasks.get_comments') }}";
    var storeCommentsUrl = "{{ route('task-review-comment.store') }}";
    $('#close-video-player-modal').on('click', function() {
        window.location.reload()
    })
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
  </script>

