
    // Initialize the video player
    const videoPlayer = new VideoPlayer('my-video');
	videoPlayer.player.videoPlayerInstance = videoPlayer;


    // Handle adding markers from user input
    const addMarkerButton = document.getElementById('add-marker-button');
    const markerTextInput = document.getElementById('marker-text-input');
    const userInfo = JSON.parse($('#userInfo').val());

    addMarkerButton.addEventListener('click', () => {
      const currentTime = videoPlayer.player.currentTime();
      const markerText = markerTextInput.value.trim();

      if (markerText === '') {
        alert('Please enter text for the marker.');
        return;
      }

      const markerName = userInfo.name;
      const photoUrl = userInfo.image_url;

      // Create a marker object
      const marker = {
        time: currentTime,
        photoUrl: photoUrl || null, // Use photoUrl from user info
        text: markerText,
        name: markerName,
        timestamp: new Date().toISOString(),
        isEditing: false,
        userId : userInfo.id
      };

      // Add the marker
      videoPlayer.addMarker(marker);

      // Log the time and text to the console
      console.log(`Marker added at ${currentTime.toFixed(2)}s: ${markerName} - ${markerText}`);

      // Clear the input field
      markerTextInput.value = '';
    });
	
// Add a custom button to the control bar
videojs.registerComponent('DrawButton', videojs.extend(videojs.getComponent('Button'), {
  constructor: function() {
    videojs.getComponent('Button').apply(this, arguments);
    this.controlText('Draw Rectangle');
    this.addClass('vjs-draw-button');
    this.el().innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#fff" width="16" height="16">
        <rect x="3" y="3" width="18" height="18" stroke="#fff" fill="none"/>
      </svg>
    `;
  },
  handleClick: function() {
    // Access the videoPlayerInstance via the player object
    this.player().videoPlayerInstance.toggleDrawingMode();
  }
}));

// Add the button to the control bar
videoPlayer.player.getChild('controlBar').addChild('DrawButton', {}, 0);