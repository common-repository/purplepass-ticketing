<div class="tab-sort-wr-2">
	<table id='sort' class='sort'>
		<thead>
		<div style="background: #dbdbdb; width: 100%; padding: 15px; color: #ffffff; font-size: 22px; margin-bottom:-10px;">
			<span style="color:#666666;">LOG INFO</span>
            <img style="width: 100px; float:right;" src="<?php echo PPTEC_PLUGIN_DIR . 'img/pp-log.png'; ?>">
		</div>

		<?php
		global $wpdb;
		$results = $wpdb->get_results( "SELECT * FROM  {$wpdb->prefix}pp_logs ORDER BY `event_time` DESC LIMIT 50" );
		?>

		<tr>
			<th>#</th>
			<th>Time</th>
			<th>Action</th>
			<th>Direction</th>
			<th>Details</th>
			<th>Status</th>
		</tr>
		</thead>
		<tbody class="insert-append">
		<?php foreach( $results as $key => $item ) : ?>
		<tr>
			<td><?php echo $key + 1; ?></td>
			<td><span style="display: none;"><?php echo esc_html(strtotime( $item->event_time )); ?></span>
				<span>
					<?php
						$gmt_offset = get_option('gmt_offset');
						$timezone_offset = $gmt_offset >= 0 ? "+" . $gmt_offset : $gmt_offset;
						$time = strtotime($item->event_time);
						echo esc_html( date("F jS, Y \a\\t g:ia " . pptec_take_timezone_from_offset($timezone_offset), strtotime($timezone_offset . ' hours', $time)) );
					?>
				</span></td>

			<?php
			$item->event_action = esc_html($item->event_action);
				$status = '';
				if ( 'Create' === $item->event_action ) {
					$status = '<span class="log_blue">Create</span>';
				} else if ( 'Update' === $item->event_action ) {
					$status = '<span class="log_green">Update</span>';
				} else if ( 'Delete' === $item->event_action ) {
					$status = '<span class="log_red">Delete</span>';
				} else if ( 'Daily sync' === $item->event_action ) {
					$status = '<span class="log_orange">Daily sync</span>';
				} else {
					$status = '<span>' . $item->event_action . '</span>';
				}
			?>

			<td><?php echo $status; ?></td>
			<td><?php echo esc_html($item->event_direction); ?></td>
			<td><?php echo esc_html($item->event_details); ?></td>
			<?php
				if ( 'Failed' === $item->event_status ) {
					$ulr = PPTEC_PLUGIN_DIR . 'img/failed.png';
					$alt = 'Error';
				} else {
					$ulr = PPTEC_PLUGIN_DIR . 'img/success.png';
					$alt = 'Success';
				}
			?>
			<td class="last-log-status" style="text-align: center;"><img style="width: 20px;" alt="<?php echo esc_attr($alt); ?>" title="<?php echo esc_attr($alt); ?>" src="<?php echo esc_url($ulr); ?>"><span><?php echo esc_html($item->event_status); ?></span></td>
		</tr>
		<?php endforeach; ?>


		</tbody>
		<?php if ( !empty( $results ) ) : ?>
		<tfoot>
			<tr>
				<td colspan="6" style="text-align: center;">
					<br>
					<div class="img-log-preloader" style="width: 100%; text-align: center">
						<img src="<?php echo PPTEC_PLUGIN_DIR . '/'; ?>img/preloader.gif">
					</div>
					<br>
					<a class="setting-btn get-log-ajax button button-primary button-large" href="#">
						Load more
					</a>
					<br>
					<br>
				</td>
			</tr>
		</tfoot>
		<?php endif; ?>
	</table>
</div>

<style>
	.img-log-preloader{
		display: none;
	}
	.img-log-preloader img{
		width: 80px;
		margin: 0 auto;
	}
	html, body, div, span, applet, object, iframe,
	h1, h2, h3, h4, h5, h6, p, blockquote, pre,
	a, abbr, acronym, address, big, cite, code,
	del, dfn, em, img, ins, kbd, q, s, samp,
	small, strike, strong, sub, sup, tt, var,
	b, u, i, center,
	dl, dt, dd, ol, ul, li,
	fieldset, form, label, legend,
	table, caption, tbody, tfoot, thead, tr, th, td,
	article, aside, canvas, details, embed,
	figure, figcaption, footer, header, hgroup,
	menu, nav, output, ruby, section, summary,
	time, mark, audio, video {
		margin: 0;
		padding: 0;
		border: 0;
		font-size: 100%;
		font: inherit;
		vertical-align: baseline;
	}
	.last-log-status span{
		display: none;
	}
	/* HTML5 display-role reset for older browsers */
	article, aside, details, figcaption, figure,
	footer, header, hgroup, menu, nav, section {
		display: block;
	}

	.tab-sort-wr {
		display: block;
		max-height: 800px;
		overflow-y: scroll;
	}

	body { line-height: 1; }
	ol, ul { list-style: none;  }
	blockquote, q { quotes: none; }
	blockquote:before, blockquote:after,
	q:before, q:after { content: ''; content: none; }
	/* tables still need 'cellspacing="0"' in the markup */
	table { border-collapse: collapse; border-spacing: 0; }
	/* remember to define focus styles. Hee Haw */
	:focus { outline: 0; }
	*, *:before, *:after {
		-moz-box-sizing:border-box;
		box-sizing:border-box;
	}
	table#sort{
		font-family: 'Poppins', sans-serif;
	}
	body {
		margin:0;
		font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;
		font-size:13px;
		line-height:18px;
		color:#303030;
		background-color:#fafafa;
		-webkit-font-smoothing:antialiased;
	}
	h1,h2,h3,h4,h5 {
		font-weight:bold;
		display:block;
		margin:0 0 10px;
	}
	h1 {
		font-size:32px;
		margin:0 0 20px;
		display:block;
		font-weight:normal;
		text-shadow:0 1px 0 #fff;
	}
	h1 span.description {
		color:#6d6d6d;
	}

	.log_green{
		color:#7cb342;
	}
	.log_red{
		color:#de5c5c;
	}
	.log_blue{
		color:#2a8fe9;
	}
	.log_orange{
		color: #ff8409;
	}


	h2 { font-size:18px; line-height:24px; margin:20px 0 10px;}
	h3 { font-size:15px; }
	ul { margin:0 0 20px; }
	li {
		margin-left:30px;
		margin-bottom:3px;
	}
	ul li { list-style:disc; }
	ol li { list-style:decimal; }

	strong {
		font-weight:bold;
	}
	.notice {
		background:#ffa;
		border:1px solid #cc7;
		display:block;
		padding:10px;
		margin-bottom:10px;
	}
	.stretch {
		display:block;
		width:100%;
	}
	.pad1y {
		padding:10px 0;
	}
	.center {
		text-align:center;
	}
	.content {
		margin-top:40px;
		padding:0 0 60px;
	}
	.page a {
		color:#404040;
		font-weight:bold;
		text-decoration:none;
		border-bottom:1px solid #ddd;
	}
	.page a:hover {
		border-color:#d0d0d0;
	}
	table {
		background:#fff;
		max-width:100%;
		border-spacing:0;
		width:100%;
		margin:10px 0;
		border:1px solid #ddd;
		border-collapse:separate;
		*border-collapse:collapsed;
		-webkit-box-shadow:0 0 4px rgba(0,0,0,0.10);
		-moz-box-shadow:0 0 4px rgba(0,0,0,0.10);
		box-shadow:0 0 4px rgba(0,0,0,0.10);
	}
	table th,
	table td {
		padding:8px;
		line-height:18px;
		text-align:left;
		border-top:1px solid #ddd;
	}
	table th {
		background:#ffffff;
		background:-webkit-gradient(linear, left top, left bottom, from(#f6f6f6), to(#ffffff));
		background:-moz-linear-gradient(top, #ffffff , #ffffff);
		text-shadow:0 1px 0 #fff;
		font-weight:bold;
		vertical-align:bottom;
	}
	table td {
		vertical-align:top;
	}
	table thead:first-child tr th,
	table thead:first-child tr td {
		border-top:0;
	}
	table tbody + tbody {
		border-top:2px solid #ddd;
	}
	table th + th,
	table td + td,
	table th + td,
	table td + th {
		border-left:1px solid #ddd;
	}
	table thead:first-child tr:first-child th,
	table tbody:first-child tr:first-child th,
	table tbody:first-child tr:first-child td {
		border-top:0;
	}

	/*tablesort specific styling*/
	th.sort-header::-moz-selection { background:transparent; }
	th.sort-header::selection      { background:transparent; }
	th.sort-header { cursor:pointer; }
	table th.sort-header:after {
		content:'';
		float:right;
		margin-top:7px;
		border-width:0 4px 4px;
		border-style:solid;
		border-color:#404040 transparent;
		visibility:hidden;
	}
	table th.sort-header:hover:after {
		visibility:visible;
	}
	table th.sort-header{
		font-weight: 300;
		font-size: 16px !important;
	}
	table th.sort-up:after,
	table th.sort-down:after,
	table th.sort-down:hover:after {
		visibility:visible;
		opacity:0.4;
	}
	table th.sort-up:after {
		border-bottom:none;
		border-width:4px 4px 0;
	}

	.page .inner {
		width:960px;
		margin:0 auto;
		padding:0 20px;
	}
	.content .inner {
		width:520px;
	}
	.heading {
		margin-top:90px;
	}

	.links {
		width:480px;
		margin:50px auto 0;
	}
	.links a {
		width:50%;
		float:left;
	}
	a.button {
		background:#1F90FF;
		border:1px solid #1f4fff;
		height:40px;
		line-height:38px;
		color:#fff;
		display:inline-block;
		text-align:center;
		padding:0 10px;
		-webkit-border-radius:1px;
		border-radius:1px;
		-webkit-transition:box-shadow 150ms linear;
		-moz-transition:box-shadow 150ms linear;
		-o-transition:box-shadow 150ms linear;
		transition:box-shadow 150ms linear;
	}
	a.button:hover {
		-webkit-box-shadow:0 1px 5px rgba(0,0,0,0.25);
		box-shadow:0 1px 5px rgba(0,0,0,0.25);
		border:1px solid #1f4fff;
	}
	a.button:focus,
	a.button:active {
		background:#0081ff;
		-webkit-box-shadow:inset 0 1px 5px rgba(0,0,0,0.25);
		box-shadow:inset 0 1px 5px rgba(0,0,0,0.25);
	}

	.options {
		margin:10px 0 30px 15px;
	}
	.options h3 {
		display:block;
		padding-top:10px;
		margin-top:20px;
	}
	.options h3:first-child {
		border:none;
		margin-top:0;
	}

	pre,
	code {
		font-family:Consolas, Menlo, 'Liberation Mono', Courier, monospace;
		word-wrap:break-word;
		color:#333;
	}
	pre {
		font-size:13px;
		line-height:1.25em;
		background:#fff;
		padding:10px 15px;
		margin:10px 0;
		overflow: auto;
		-webkit-box-shadow:0 1px 3px rgba(0, 0, 0, 0.30);
		box-shadow:0 1px 3px rgba(0, 0, 0, 0.30);
	}
	code {
		font-size:12px;
		border:0;
		padding:0;
		background:#e6e6e6;
		background:rgba(0,0,0,0.08);
		box-shadow:0 0 0 2px rgba(0,0,0,0.08);
	}
	pre code {
		font-size:13px;
		line-height:1.25em;
		background:transparent;
		box-shadow:none;
		border:none;
		padding:0;
		margin:0;
	}

	@media screen {
		.com { color: #999988; }  /* a comment */
		.lit, .typ { color: #445588; } /* literal, type */
		.tag { color: navy; } /* tag */
		.atv, str { color: #dd1144; } /* attribute, string */
		.dec, .clo, .opn, .pun, .kwd { color: #333333; } /* a declaration, close bracket, open bracket, punctuation, keyword */
		.var, .atn { color: teal; } /* variable, markup attribute */
		.fun { color: #990000; } /* functione */
	}
	/* Use higher contrast and text-weight for printable form. */
	@media print, projection {
		.str { color: #060; }
		.kwd { color: #006; font-weight: bold; }
		.com { color: #600; font-style: italic; }
		.typ { color: #404; font-weight: bold; }
		.lit { color: #044; }
		.pun, .opn, .clo  { color: #440; }
		.tag { color: #006; font-weight: bold; }
		.atn { color: #404; }
		.atv { color: #060; }
	}
	/* Style */
	pre.prettyprint {
		background: white;
		font-family: Menlo, Monaco, Consolas, monospace;
		font-size: 12px;
		line-height: 1.5;
		padding: 12px 10px;
	}

	.clearfix:after {
		content: '.';
		display: block;
		height: 0;
		clear: both;
		visibility: hidden;
	}

	* html .clearfix { height: 1%; } /* IE6 */
	*:first-child + html .clearfix { min-height: 1%; } /* IE7 */
</style>
