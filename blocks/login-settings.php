<?php $account_info = check_if_token_exists(); ?>

<br>
<h2>Link to your Purplepass account <?php echo $account_info['message']; ?></h2>
<p class="under-login-text">Click the button below to connect the plugin with your existing Purplepass account. This account will be used for creation and management of your events.</p>

<?php if ('linked' === $account_info['account_status']) { ?>
    <a class="setting-btn button button-primary button-large disabled" href="javascript:;">
        <img src="<?php echo PPTEC_PLUGIN_DIR . '/'; ?>img/Login_icon_16.png" style="margin-bottom:0px;">
        &nbsp;Link Purplepass account
    </a>
    <a class="unlink-account" href="javascript:;">Unlink account</a>
<?php } else { ?>
    <a class="setting-btn button button-primary button-large" href="<?php echo get_site_url() . '/wp-admin/admin.php?page=purplepass'; ?>&ppauth=true">
        <img src="<?php echo PPTEC_PLUGIN_DIR . '/'; ?>img/Login_icon_16.png" style="margin-bottom:0px;">
        &nbsp;Link Purplepass account
    </a>
<?php } ?>
<br>

<script>
	localStorage.setItem("venue_map_synced", "No");
</script>
