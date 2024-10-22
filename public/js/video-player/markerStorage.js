// MarkerStorage class to handle marker storage
class MarkerStorage {
  constructor(storageKey = 'videoMarkers') {
    this.storageKey = storageKey;
  }

  // Load markers from localStorage
  loadMarkers() {
    $.ajax({
      type: 'POST',
      url: getCommentsUrl,
      data: {
        fileId: $('#reviewFileId').val(),
        '_token': csrfToken
      },
      success: function(response) {
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
          markers.push(obj);
        })
        console.log(markers);
        return markers;
      }
    })
  }

  // Save markers to localStorage
  saveMarkers(markers) {
    $.ajax({
      type: 'POST',
      url: storeCommentsUrl,
      data: {
        fileId: $('#reviewFileId').val(),
        markers: markers,
        '_token': csrfToken
      },
      success: function(response) {
        console.log(response)
      }
    })
  }
}