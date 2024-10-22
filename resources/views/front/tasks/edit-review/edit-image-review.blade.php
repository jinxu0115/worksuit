<link href="https://vjs.zencdn.net/7.20.3/video-js.css" rel="stylesheet" />
<link rel = "preconnect" href = "https://fonts.googleapis.com" > 
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ url('/css/video-player/styles.css') }}">
<div class="modal-body text-center row px-10">
    <input type="hidden" id="userInfo" value="{{json_encode(user())}}"/>
    <input type="hidden" id="reviewFileId" value="{{$review_file->id}}"/>
    <input type="hidden" id="taskReviewComments" value="{{json_encode($taskReviewComments)}}"/>
    <div class="col-9">
        <div class="video-container">
            <img src="{{ $review_file->file_url }}" class="video-js vjs-default-skin" alt="Review Image">
        </div>

        <!-- Input field and button for adding markers -->
        @if($mode == 'edit')
        <div class="marker-input-container">
            <input type="text" id="marker-text-input" placeholder="Enter marker text here..." />
            <button id="add-marker-button">Add Marker</button>
            <div id="drawing-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;"></div>

        </div>
        @endif
    </div>

    <div class="col-3">
        <!-- Marker list container -->
        <div id="marker-list" class="marker-list-container"></div>
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
    <x-forms.button-cancel id="close-video-player-modal" data-dismiss="modal">@lang('app.close')</x-forms.button-cancel>
</div>
<script>
    $('#close-video-player-modal').on('click', function() {
        window.location.reload()
    })
    let taskReviewComments = JSON.parse($('#taskReviewComments').val());
    if(taskReviewComments.length > 0){
        taskReviewComments.forEach(function(comment) {
            comment.isEditing == false;
        })
    }
    $('#add-marker-button').on('click', () => {
        const markerTextInput = document.getElementById('marker-text-input');
      const markerText = markerTextInput.value.trim();

      if (markerText === '') {
        alert('Please enter text for the marker.');
        return;
      }

    $.ajax({
        type: 'POST',
        url: "{{ route('tasks.store-image-comment') }}",
        data: {
            fileId: $('#reviewFileId').val(),
            commentText: markerText,
            '_token': "{{ csrf_token() }}"
        },
        success: function(response) {
            taskReviewComments = response;
    
            if(taskReviewComments.length > 0){
                taskReviewComments.forEach(function(comment) {
                    comment.isEditing == false;
                })
            }
            renderMarkerList()
        }
    })

      // Clear the input field
      markerTextInput.value = '';
    });
    // Update a marker's text
    function updateMarker(index, updatedText) {
        $.ajax({
            type: 'POST',
            url: "{{ route('tasks.update-image-comment') }}",
            data: {
                commentText: updatedText,
                commentId: index,
                '_token': "{{ csrf_token() }}"
            },
            success: function(response) {
                taskReviewComments = response;
        
                if(taskReviewComments.length > 0){
                    taskReviewComments.forEach(function(comment) {
                        comment.isEditing == false;
                    })
                }
                renderMarkerList()
            }
        })
    }
    function deleteMarker(index) {        
        let url = `{{ route('task-review-comment.destroy', ':id') }}`
        url = url.replace(':id', index);

        $.ajax({
            type: "DELETE",
            url: url,
            data: {
                '_token': "{{ csrf_token() }}",
            },
            success: function(response) {
                taskReviewComments = response;
        
                if(taskReviewComments.length > 0){
                    taskReviewComments.forEach(function(comment) {
                        comment.isEditing == false;
                    })
                }
                renderMarkerList()
            }
        })
    }
    function renderMarkerList() {
      const markerListContainer = document.getElementById("marker-list");
      markerListContainer.innerHTML = ""; // Clear existing list

      // Create a copy of markers array and reverse it
      const sortedMarkers = taskReviewComments;

      sortedMarkers.forEach((marker, index) => {
          // Calculate the original index in the this.markers array
          const originalIndex = marker.id;

          const listItem = document.createElement("div");
          listItem.className = "marker-list-item";

          // Create the photo element
          let photoImg;
          if (marker.user.image_url) {
              photoImg = document.createElement("img");
              photoImg.src = marker.user.image_url;
              photoImg.alt = "Photo";
              photoImg.className = "marker-list-photo";
          }

          // Create a container for the content
          const contentContainer = document.createElement("div");
          contentContainer.className = "marker-content-container";

          // Header section with name and time
          const header = document.createElement("div");
          header.className = "marker-header";

          const nameSpan = document.createElement("span");
          nameSpan.className = "marker-name";
          nameSpan.innerText = marker.user.name || "";
          header.appendChild(nameSpan);

          // Timestamp span
          const timestampSpan = document.createElement("span");
          timestampSpan.className = "marker-timestamp";
          timestampSpan.innerText = `Created: ${new Date(
              marker.created_at
          ).toLocaleString()}`;

          // Check if the marker is in editing mode
          if (marker.isEditing) {
              // --- Edit Mode ---
              const editContainer = document.createElement("div");
              editContainer.className = "edit-mode-container";

              const textInput = document.createElement("input");
              textInput.className = "marker-text-input";
              textInput.type = "text";
              textInput.value = marker.comment_text || "";
              textInput.placeholder = "Enter marker text...";

              const buttonsContainer = document.createElement("div");
              buttonsContainer.className = "edit-buttons-container";

              const saveButton = document.createElement("button");
              saveButton.className = "save-marker";
              saveButton.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                        <path d="M20.285 2l-11.285 11.567-5.286-5.011-3.714 3.716 9 8.728 15-15.285z"/>
                    </svg>
                    <span class="button-text">Update</span>
                    `;

              const cancelButton = document.createElement("button");
              cancelButton.className = "cancel-marker";
              cancelButton.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                        <path d="M24 20.188l-8.315-8.209 8.2-8.282-3.697-3.697-8.212 8.318-8.31-8.203-3.666 3.666 8.321 8.24-8.206 8.313 3.666 3.666 8.237-8.318 8.285 8.203z"/>
                    </svg>
                    <span class="button-text">Cancel</span>
                    `;

              // Use originalIndex for save/cancel operations
              saveButton.addEventListener("click", () => {
                  const updatedText = textInput.value.trim();
                  updateMarker(originalIndex, updatedText);
              });

              cancelButton.addEventListener("click", () => {
                  marker.isEditing = false;
                  renderMarkerList();
              });

              textInput.addEventListener("keypress", (e) => {
                  if (e.key === "Enter") {
                      const updatedText = textInput.value.trim();
                      updateMarker(originalIndex, updatedText);
                  }
              });

              buttonsContainer.appendChild(saveButton);
              buttonsContainer.appendChild(cancelButton);

              editContainer.appendChild(textInput);
              editContainer.appendChild(buttonsContainer);

              contentContainer.appendChild(header);
              contentContainer.appendChild(editContainer);
              contentContainer.appendChild(timestampSpan);

              listItem.classList.add("editing");
          } else {
              // --- Display Mode ---
              if (marker.rectangle) {
                  const annotationIndicator = document.createElement("span");
                  annotationIndicator.className = "annotation-indicator";
                  annotationIndicator.innerText = "🖍️ Annotation";
                  header.appendChild(annotationIndicator);
              }

              const textSpan = document.createElement("p");
              textSpan.className = "marker-text";
              textSpan.innerText = marker.comment_text;

              const buttonContainer = document.createElement("div");
              buttonContainer.className = "marker-button-container";

              if(JSON.parse($('#userInfo').val()).id == marker.user_id){
                const editButton = document.createElement("button");
                editButton.className = "edit-marker";
                editButton.setAttribute("title", "Edit");
                editButton.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                    <path d="M3 17.25V21h3.75l11-11.062-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34a1.003 1.003 0 00-1.42 0L15.13 4.96l3.75 3.75 1.83-1.67z"></path>
                </svg>
                `;

                editButton.addEventListener("click", () => {
                    marker.isEditing = true;
                    renderMarkerList();
                });                
                buttonContainer.appendChild(editButton);
              }
              if(JSON.parse($('#userInfo').val()).id == marker.user_id){
                    const deleteButton = document.createElement("button");
                    deleteButton.className = "delete-marker";
                    deleteButton.setAttribute("title", "Delete");
                    deleteButton.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                            <path d="M16 9v10H8V9h8m-1.5-6h-5l-1 1H5v2h14V4h-4.5l-1-1z"></path>
                        </svg>
                        `;

                    // Use originalIndex for delete operation
                    deleteButton.addEventListener("click", () => {
                        Swal.fire({
                            title: "Are you sure?",
                            text: "You will not be able to recover the deleted record!",
                            icon: 'warning',
                            showCancelButton: true,
                            focusCancel: true,
                            confirmButtonText: "Yes, Delete It!",
                            cancelButtonText: "Cancel",
                            customClass: {
                                confirmButton: 'btn btn-primary mr-3',
                                cancelButton: 'btn btn-secondary'
                            },
                            showClass: {
                                popup: 'swal2-noanimation',
                                backdrop: 'swal2-noanimation'
                            },
                            buttonsStyling: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                deleteMarker(originalIndex);
                            }
                        });
                    });
                    buttonContainer.appendChild(deleteButton);
                }


              contentContainer.appendChild(header);
              contentContainer.appendChild(textSpan);
              contentContainer.appendChild(buttonContainer);
              contentContainer.appendChild(timestampSpan);
          }

          if (photoImg) {
              listItem.appendChild(photoImg);
          }
          listItem.appendChild(contentContainer);
          markerListContainer.appendChild(listItem);
      });
    }
  renderMarkerList()
</script>