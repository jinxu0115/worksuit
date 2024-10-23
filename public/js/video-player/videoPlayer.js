class VideoPlayer {
  constructor(videoId, startTime = 0) {
      this.player = videojs(videoId, {
          controls: true,
          autoplay: false,
          preload: "auto",
          responsive: true,
          fluid: true, // Makes the player responsive
      });
      this.markers = [];
      this.startTime = startTime;

      // Bind the onLoadedMetadata function to ensure 'this' context
      this.onLoadedMetadata = this.onLoadedMetadata.bind(this);

      // Attach the 'loadedmetadata' event listener directly
      this.player.on("loadedmetadata", this.onLoadedMetadata);

      // If metadata is already loaded, call onLoadedMetadata directly
      if (this.player.readyState() >= 1) {
          this.onLoadedMetadata();
      }
      this.isDrawing = false;
      this.drawingModeEnabled = false;
      this.rect = {};
      this.overlay = document.getElementById("drawing-overlay");

      // Bind event handlers
      this.handleMouseDown = this.handleMouseDown.bind(this);
      this.handleMouseMove = this.handleMouseMove.bind(this);
      this.handleMouseUp = this.handleMouseUp.bind(this);
      this.annotationContainer = document.createElement("div");
      this.annotationContainer.id = "annotation-container";
      this.annotationContainer.style.position = "absolute";
      this.annotationContainer.style.top = "0";
      this.annotationContainer.style.left = "0";
      this.annotationContainer.style.width = "100%";
      this.annotationContainer.style.height = "100%";
      this.annotationContainer.style.pointerEvents = "none";

      // Append to the player element
      this.player.el().appendChild(this.annotationContainer);

      // Listen to time updates
      this.player.on("timeupdate", this.updateAnnotations.bind(this));
  }

  updateAnnotations() {
      const currentTime = this.player.currentTime();
      const marker = this.markers.find(
          (m) => Math.abs(m.time - currentTime) < 0.1
      );

      // Clear existing annotations
      this.annotationContainer.innerHTML = "";
      if (marker && marker.rectangle) {
          const rectElement = document.createElement("div");
          rectElement.className = "annotation-rect";

          // No need to calculate scale factors since we use percentage
          rectElement.style.left = `${
              (JSON.parse(marker.rectangle).x / this.player.videoWidth()) * 100
          }%`;
          rectElement.style.top = `${
              (JSON.parse(marker.rectangle).y / this.player.videoHeight()) * 100
          }%`;
          rectElement.style.width = `${
              (JSON.parse(marker.rectangle).width / this.player.videoWidth()) * 100
          }%`;
          rectElement.style.height = `${
              (JSON.parse(marker.rectangle).height / this.player.videoHeight()) * 100
          }%`;

          this.annotationContainer.appendChild(rectElement);
      }
  }

  toggleDrawingMode() {
      if (this.drawingModeEnabled) {
          this.disableDrawing();
      } else {
          this.enableDrawing();
      }
  }

  enableDrawing() {
      this.drawingModeEnabled = true;
      this.overlay.style.pointerEvents = "auto";
      this.overlay.addEventListener("mousedown", this.handleMouseDown);
      document.addEventListener("mousemove", this.handleMouseMove);
      document.addEventListener("mouseup", this.handleMouseUp);

      // Optional: Update the draw button appearance
      const drawButton = this.player.controlBar.getChild("DrawButton");
      if (drawButton) {
          drawButton.addClass("active");
      }
  }

  disableDrawing() {
      this.drawingModeEnabled = false; // Update the flag
      this.overlay.style.pointerEvents = "none";
      this.overlay.removeEventListener("mousedown", this.handleMouseDown);
      document.removeEventListener("mousemove", this.handleMouseMove);
      document.removeEventListener("mouseup", this.handleMouseUp);

      // Reset drawing state
      this.isDrawing = false;
      if (
          this.currentRectElement &&
          this.overlay.contains(this.currentRectElement)
      ) {
          this.overlay.removeChild(this.currentRectElement);
          this.currentRectElement = null;
      }

      // Optional: Update the draw button appearance
      const drawButton = this.player.controlBar.getChild("DrawButton");
      if (drawButton) {
          drawButton.removeClass("active");
      }
  }

  handleMouseDown(e) {
      if (!this.drawingModeEnabled) return; // Prevent drawing if not in drawing mode
      this.isDrawing = true;
      const rect = this.overlay.getBoundingClientRect();
      this.rect.startX = e.clientX - rect.left;
      this.rect.startY = e.clientY - rect.top;

      // Create a rectangle element
      this.currentRectElement = document.createElement("div");
      this.currentRectElement.className = "drawing-rect";
      this.currentRectElement.style.left = `${this.rect.startX}px`;
      this.currentRectElement.style.top = `${this.rect.startY}px`;
      this.overlay.appendChild(this.currentRectElement);
  }

  handleMouseMove(e) {
      if (!this.isDrawing) return;
      const rect = this.overlay.getBoundingClientRect();
      const currentX = e.clientX - rect.left;
      const currentY = e.clientY - rect.top;

      const width = currentX - this.rect.startX;
      const height = currentY - this.rect.startY;

      this.currentRectElement.style.width = `${Math.abs(width)}px`;
      this.currentRectElement.style.height = `${Math.abs(height)}px`;
      this.currentRectElement.style.left = `${
          width < 0 ? currentX : this.rect.startX
      }px`;
      this.currentRectElement.style.top = `${
          height < 0 ? currentY : this.rect.startY
      }px`;
  }

  handleMouseUp(e) {
      if (!this.isDrawing) return;
      this.isDrawing = false;

      // Get the rectangle's position and dimensions relative to the video dimensions
      const videoRect = this.player.el().getBoundingClientRect();
      const overlayRect = this.overlay.getBoundingClientRect();

      const scaleX = this.player.videoWidth() / overlayRect.width;
      const scaleY = this.player.videoHeight() / overlayRect.height;

      const rectData = {
          x: parseFloat(this.currentRectElement.style.left) * scaleX,
          y: parseFloat(this.currentRectElement.style.top) * scaleY,
          width: parseFloat(this.currentRectElement.style.width) * scaleX,
          height: parseFloat(this.currentRectElement.style.height) * scaleY,
      };

      // Prompt the user for a comment
      const comment = prompt("Enter a comment for this annotation:", "");

      if (comment) {
          const currentTime = this.player.currentTime();
          const userInfo = JSON.parse($('#userInfo').val());

          // Create the marker with rectangle data
          const marker = {
              time: currentTime,
              photoUrl: userInfo.image_url || null,
              text: comment,
              name: userInfo.name,
              timestamp: new Date().toISOString(),
              isEditing: false,
              rectangle: JSON.stringify(rectData),
              userId : userInfo.id
          };

          // Add the marker
          this.addMarker(marker);
      }

      // Remove the rectangle element from the overlay
      this.overlay.removeChild(this.currentRectElement);
      this.currentRectElement = null;
      // Remove the rectangle element from the overlay
      if (
          this.currentRectElement &&
          this.overlay.contains(this.currentRectElement)
      ) {
          this.overlay.removeChild(this.currentRectElement);
      }
      this.currentRectElement = null;

      // Deactivate drawing mode after adding the marker
      this.disableDrawing();
  }
  // Handler for 'loadedmetadata' event
  onLoadedMetadata() {
      const duration = this.player.duration();

      if (!duration || duration === 0) {
          console.warn("Video duration not available yet.");
          return;
      }

      // Set the start time
      if (this.startTime > 0 && this.startTime < duration) {
          this.player.currentTime(this.startTime);
      }

      $.ajax({
        type: 'POST',
        url: getCommentsUrl,
        data: {
          fileId: $('#reviewFileId').val(),
          '_token': csrfToken
        },
        success: (response) => {
          let markers = [];
          response.map((comment) => {
            let obj = {};
            obj.time = comment.time_frame
            obj.photoUrl = comment.user.image_url
            obj.text = comment.comment_text
            obj.name = comment.user.name
            obj.timestamp = comment.created_at
            obj.isEditing = false
            obj.userId = comment.user.id
            obj.rectangle = comment.rect_data ? JSON.parse(comment.rect_data) : null;
            obj.commentId = comment.id
            markers.push(obj);
          })
          this.markers = markers;

          // Clear existing markers
          this.clearMarkers();
    
          // Create markers on the progress bar
          this.markers.forEach((marker, index) => {
              this.createMarkerElement(marker, index);
          });
    
          // Render the marker list
          this.renderMarkerList();
        }
      })
  }

  // Clear all existing marker elements
  clearMarkers() {
      // Remove marker elements from progress bar
      const markerElements = document.querySelectorAll(".custom-marker");
      markerElements.forEach((elem) => elem.remove());
  }

  // Add a marker with optional properties
  addMarker(marker) {
    $.ajax({
      type: 'POST',
      url: storeCommentsUrl,
      data: {
        fileId: $('#reviewFileId').val(),
        comment: marker,
        '_token': csrfToken
      },
      success: (response) => {
        let markers = [];
        response.map((comment) => {
          let obj = {};
          obj.time = comment.time_frame
          obj.photoUrl = comment.user.image_url
          obj.text = comment.comment_text
          obj.name = comment.user.name
          obj.timestamp = comment.created_at
          obj.isEditing = false
          obj.userId = comment.user.id
          obj.commentId = comment.id
          obj.rectangle = comment.rect_data ? JSON.parse(comment.rect_data) : null;
          markers.push(obj);
        })
        this.markers = markers;
        if (this.player.duration()) {
            this.createMarkerElement(marker, this.markers.length - 1);
        }
  
        // Update the marker list display
        this.renderMarkerList();
      }
    })

  }

  // Update a marker's text
  updateMarker(index, updatedText) {
      const marker = this.markers[index];
      $.ajax({
        type: 'PUT',
        url: updateCommentsUrl.replace(':id', marker.commentId),
        data: {
          updatedText: updatedText,
          '_token': csrfToken
        },
        success: (response) => {
          let markers = [];
          response.map((comment) => {
            let obj = {};
            obj.time = comment.time_frame
            obj.photoUrl = comment.user.image_url
            obj.text = comment.comment_text
            obj.name = comment.user.name
            obj.timestamp = comment.created_at
            obj.isEditing = false
            obj.userId = comment.user.id
            obj.commentId = comment.id
            obj.rectangle = comment.rect_data ? JSON.parse(comment.rect_data) : null;
            markers.push(obj);
          })
          this.markers = markers;
          const markerElement = document.querySelector(
            `.custom-marker[data-index='${index}']`
          );
          if (markerElement) {
              const tooltip = markerElement.querySelector(".marker-tooltip");
              if (tooltip) {
                  tooltip.innerHTML = `
                ${
                    marker.photoUrl
                        ? `<img src="${marker.photoUrl}" alt="Photo" style="width:30px;height:30px;border-radius:50%;margin-bottom:5px;">`
                        : ""
                }
                <strong>${marker.name || ""}</strong><br>
                ${marker.text || ""}<br>
                <small>${
                    marker.timestamp
                        ? new Date(marker.timestamp).toLocaleString()
                        : ""
                }</small>
              `;
              }
          }
    
          // Update the marker list display
          this.renderMarkerList();
        }
      })

  }

  // Delete a marker
  deleteMarker(index) {
      const marker = this.markers[index];
      $.ajax({
        type: 'DELETE',
        url: removeCommentsUrl.replace(':id', marker.commentId),
        data: {
          '_token': csrfToken
        },
        success: (response) => {
          let markers = [];
          response.map((comment) => {
            let obj = {};
            obj.time = comment.time_frame
            obj.photoUrl = comment.user.image_url
            obj.text = comment.comment_text
            obj.name = comment.user.name
            obj.timestamp = comment.created_at
            obj.isEditing = false
            obj.userId = comment.user.id
            obj.commentId = comment.id
            obj.rectangle = comment.rect_data ? JSON.parse(comment.rect_data) : null;
            markers.push(obj);
          })
          this.markers = markers;
          // Re-render markers to update indices and elements
          this.refreshMarkers();
    
          // Update the marker list display
          this.renderMarkerList();
        }
      })
  }

  // Refresh markers after deletion
  refreshMarkers() {
      // Remove all marker elements
      this.clearMarkers();

      // Re-create marker elements with updated indices
      this.markers.forEach((marker, index) => {
          this.createMarkerElement(marker, index);
      });
  }

  // Create marker element and append it to the progress bar
  createMarkerElement(marker, index) {

    if(marker.time == null) return ;
      const duration = this.player.duration();
      const markerPosition = (marker.time / duration) * 100;

      const markerElement = document.createElement("div");
      markerElement.className = "custom-marker";
      markerElement.style.left = markerPosition + "%";
      markerElement.setAttribute("data-index", index);

      if (marker.photoUrl) {
          const img = document.createElement("img");
          img.src = marker.photoUrl;
          img.alt = "Marker Image";
          markerElement.appendChild(img);
      } else {
          // Default background color if no image is provided
          markerElement.style.backgroundColor = "var(--primary-color)";
      }

      // Create a tooltip with name, text, and timestamp
      if (marker.name || marker.text || marker.timestamp) {
          const tooltip = document.createElement("div");
          tooltip.className = "marker-tooltip";
          tooltip.innerHTML = `
          ${
              marker.photoUrl
                  ? `<img src="${marker.photoUrl}" alt="Photo" style="width:30px;height:30px;border-radius:50%;margin-bottom:5px;">`
                  : ""
          }
          <strong>${marker.name || ""}</strong><br>
          ${marker.text || ""}<br>
          <small>${
              marker.timestamp
                  ? new Date(marker.timestamp).toLocaleString()
                  : ""
          }</small>
        `;
          markerElement.appendChild(tooltip);
      }

      const progressHolder = this.player.controlBar.progressControl.seekBar.el();
      progressHolder.appendChild(markerElement);

      // Add click event to seek to the marker's time and pause
      markerElement.addEventListener("click", (event) => {
          event.stopPropagation(); // Prevent event from bubbling up
          this.player.currentTime(marker.time);
          this.player.pause(); // Pause the video after seeking
      });
  }

  // Render the list of markers
  // Render the list of markers
  renderMarkerList() {
      const markerListContainer = document.getElementById("marker-list");
      markerListContainer.innerHTML = ""; // Clear existing list

      // Create a copy of markers array and reverse it
      const sortedMarkers = [...this.markers].reverse();

      sortedMarkers.forEach((marker, index) => {
          // Calculate the original index in the this.markers array
          const originalIndex = this.markers.length - 1 - index;

          const listItem = document.createElement("div");
          listItem.className = "marker-list-item";

          // Create the photo element
          let photoImg;
          if (marker.photoUrl) {
              photoImg = document.createElement("img");
              photoImg.src = marker.photoUrl;
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
          nameSpan.innerText = marker.name || "";
          header.appendChild(nameSpan);
          
          if(marker.time != null){
            const timeSpan = document.createElement("span");
            timeSpan.className = "marker-time";
            timeSpan.innerText = `Time: ${marker.time.toFixed(2)}s`;
  
            header.appendChild(timeSpan);            
          }

          // Timestamp span
          const timestampSpan = document.createElement("span");
          timestampSpan.className = "marker-timestamp";
          timestampSpan.innerText = `Created: ${new Date(
              marker.timestamp
          ).toLocaleString()}`;
          // Check if the marker is in editing mode
          if (marker.isEditing) {
              // --- Edit Mode ---
              const editContainer = document.createElement("div");
              editContainer.className = "edit-mode-container";

              const textInput = document.createElement("input");
              textInput.className = "marker-text-input";
              textInput.type = "text";
              textInput.value = marker.text || "";
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
                  this.updateMarker(originalIndex, updatedText);
              });

              cancelButton.addEventListener("click", () => {
                  marker.isEditing = false;
                  this.renderMarkerList();
              });

              textInput.addEventListener("keypress", (e) => {
                  if (e.key === "Enter") {
                      const updatedText = textInput.value.trim();
                      this.updateMarker(originalIndex, updatedText);
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
                  annotationIndicator.innerHTML = '<img src="/img/nfc-pen.svg" style="width: 14px; height: 20px;"/>';
                  annotationIndicator.setAttribute('data-toggle', 'tooltip');
                  annotationIndicator.setAttribute('data-original-title', 'This is an annotation');

                  header.appendChild(annotationIndicator);
              }

              const textSpan = document.createElement("p");
              textSpan.className = "marker-text";
              textSpan.innerText = marker.text;

              const buttonContainer = document.createElement("div");
              buttonContainer.className = "marker-button-container";
              if(marker.time != null) {
                const goToButton = document.createElement("button");
                goToButton.className = "go-to-marker";
                goToButton.setAttribute("title", "Go to");
                goToButton.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
          <path d="M8 5v14l11-7z"/>
        </svg>
      `;

                const goToPauseButton = document.createElement("button");
                goToPauseButton.className = "go-to-pause-marker";
                goToPauseButton.setAttribute("title", "Go to and Pause");
                goToPauseButton.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
          <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
        </svg>
      `;

                // Use marker.time for seeking operations
                goToButton.addEventListener("click", () => {
                    this.player.currentTime(marker.time);
                    this.player.play();
                });

                goToPauseButton.addEventListener("click", () => {
                    this.player.currentTime(marker.time);
                    this.player.pause();
                });
                buttonContainer.appendChild(goToButton);
                buttonContainer.appendChild(goToPauseButton);
              }

              if(JSON.parse($('#userInfo').val()).id == marker.userId){
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
                    this.renderMarkerList();
                });                
                buttonContainer.appendChild(editButton);
              }
              if(JSON.parse($('#userInfo').val()).id == marker.userId){
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
                                this.deleteMarker(originalIndex);
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
}
