<style>
  .control-buttons {
    display: flex;
    justify-content: space-between;
    width: 100%;
    max-width: 840px;
    padding: 100px 0 0 0;
  }
  .save-publish-btn-event,
  .cancel-btn-event {
    position: relative;
  }
  .img-pp-post-preloader,
  .img-pp-post-preloader-cancel {
    position: absolute;
    bottom: 45px;
    left: 0;
    display: none;
    align-items: center;
    width: 100%;
  }
  .img-pp-post-preloader.show,
  .img-pp-post-preloader-cancel.show {
    display: flex;
  }
  .img-pp-post-preloader img,
  .img-pp-post-preloader-cancel img {
    width: 80px;
  }
</style>

<section class="control-buttons">
  <div class="save-publish-btn-event">
    <a href="#" class="setting-btn button button-primary button-large">Publish event</a>
    <div class="img-pp-post-preloader">
      <img src="<?php echo PPTEC_PLUGIN_DIR . '/'; ?>img/preloader.gif">
      <span>Please wait...</span>
    </div>
  </div>

  <?php if ( isset( $_GET['post'] ) && isset( $_GET['action'] ) ) : ?>
    <?php
      $status = get_post_status( $_GET['post'] );
      $post_type = get_post_type( $post->ID );
    ?>
    <?php if ( 'edit' === $_GET['action'] && 'tribe_events' === $post_type && 'canceled' !== $status ) : ?>
      <div class="cancel-btn-event">
        <a href="#" class="setting-btn button button-primary button-large">Cancel event</a>
        <div class="img-pp-post-preloader-cancel">
          <img src="<?php echo PPTEC_PLUGIN_DIR . '/'; ?>img/preloader.gif">
          <span>Please wait...</span>
        </div>
      </div>

      <script>
        jQuery(document).ready(function ($) {
          // on click custom publish/update button, trigger cick event on WP button
          $(".cancel-btn-event a").on("click", function (e) {
            e.preventDefault();
            var answer = confirm('Mark as "Cancelled"\n\n CONFIRMATION - THIS CANNOT BE UNDONE. \n\n' +
              'If you mark this event as "Cancelled", all orders will be refunded. Are you sure you want to do this? \n\n' +
              'NOTE: Service fees are non-refundable as per the terms of service.');
            if (answer === true) {
              $(".img-pp-post-preloader-cancel").addClass("show");
              let post_data = window.location.search;
              post_data = post_data.replace('?post=', '');
              post_data = post_data.replace('&action=edit', '');
              let all_data = {
                'action' : 'pptec_cancel_event',
                'post_id' : post_data
              };
              $.ajax({
                url: ajaxurl,
                data: all_data,
                type: 'POST',
                dataType: 'json',
                success: function (json) {
                  // $(".img-pp-post-preloader-cancel").hide();
                  $(".img-pp-post-preloader-cancel").removeClass("show");

                  if (!json.success) {
                      alert(json.message);
                  } else {
                      window.location.reload();
                  }
                }
              });
            }
          });
        });
      </script>
    <?php endif; ?>
  <?php endif; ?>
</section>

<script>
	jQuery(document).ready(function ($) {
		// on click custom publish/update button, trigger cick event on WP button
		$(".save-publish-btn-event a").on("click", function (e) {
			e.preventDefault();
			$('.save-repeater').trigger('click');
			setTimeout(function () {
				$("#publishing-action #publish").trigger("click");
			}, 500);
		});
	});
</script>

<script>
	function getCookie(cname) {
		var name = cname + "=";
		var decodedCookie = decodeURIComponent(document.cookie);
		var ca = decodedCookie.split(';');
		for(var i = 0; i <ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == ' ') {
				c = c.substring(1);
			}
			if (c.indexOf(name) == 0) {
				return c.substring(name.length, c.length);
			}
		}
		return "";
	}
</script>