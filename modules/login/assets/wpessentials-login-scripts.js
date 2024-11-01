/**
 * Login logo.
 * 
 * @link https://codex.wordpress.org/Javascript_Reference/wp.media
 * @link https://wordpress.stackexchange.com/questions/235406/how-do-i-select-an-image-from-media-library-in-my-plugin
 * @link https://dev.to/kelin1003/utilising-wordpress-media-library-for-uploading-files-2b01
 */

(($, wpMedia) =>
{
  $(() =>
  {
    // Set all variables to be used in scope.
    let mediaLibrary;
    const
      $imgContainer = $('#wpessentials_login_logo_img'),
      $imgIdInput = $('#wpessentials_login_logo_id'),
      $addImgLink = $('#wpessentials_login_logo_add_btn, #wpessentials_login_logo_img'),
      $delImgLink = $('#wpessentials_login_logo_rem_link');


    /**
     * ADD IMAGE LINK ..
     */
    $addImgLink.on('click', event =>
    {
      event.preventDefault();

      // If the media frame already exists, reopen it.
      if (mediaLibrary)
      {
        mediaLibrary.open();
        return;
      }

      // Create a new media frame.
      mediaLibrary = wpMedia({
        frame: 'select',
        title: 'Select or Upload Media',
        button: {
          text: 'Use this image'
        },
        library: {
          type: 'image',
        },
        multiple: false,
      });

      // When an image is selected in the media frame ..
      mediaLibrary.on('select', () =>
      {
        // Get media attachment details from the frame state.
        let attachment = mediaLibrary.state().get('selection').first().toJSON();

        // Send the attachment URL to our custom image input field.
        $imgContainer.attr('src', attachment.url);

        // Unhide the remove image link.
        $delImgLink.show();

        // Send the attachment id to our hidden input.
        $imgIdInput.val(attachment.id);
      });

      // On open, get the attachment ID from the hidden input and
      // select the appropiate images in the media manager.
      mediaLibrary.on('open', () =>
      {
        // Get the selection from the frame.
        let selection = mediaLibrary.state().get('selection');

        // Get the attachment ID from the hidden input.
        let id = $imgIdInput.val();

        // Get the corresponding attachment in the frame.
        // eslint-disable-next-line no-undef
        let attachment = wpMedia.attachment(id);

        // Fetch ..
        attachment.fetch();

        // Add the attachment to the selection.
        selection.add(attachment ? [attachment] : []);
      });

      // Finally, open the modal on click.
      mediaLibrary.open();
    });


    /**
     * DELETE IMAGE LINK ..
     */
    $delImgLink.on('click', event =>
    {
      event.preventDefault();

      // Clear out the preview image.
      // eslint-disable-next-line no-undef
      $imgContainer.attr('src', wpessentials_default_login_logo);

      // Hide the delete image link.
      $delImgLink.hide();

      // Delete the image id from the hidden input.
      $imgIdInput.val('');
    });
  });

})(jQuery, window.wp.media);