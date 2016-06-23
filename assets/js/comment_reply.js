$(function(){

	// status message
	var changeStatus = function(msg, $context){
		if (!msg || !msg.length){
			$context.parent().find('.status').hide();
		} else {
			$context.parent().find('.status').html(msg);
		}
	}

	// setup handler for reply to comments
	$('.comment_reply_form').on('click', 'form .submit', function(){

		var $formHolder = $(this).closest('.comment_reply_form');

		$form = $(this).closest('form');

		changeStatus('Submitting comment...', $formHolder);
		var url = $form.attr('action');
		var formData = $form.serializeArray();

		$.post(url, formData).done(function(html) {
			$formHolder.html(html);
			$formHolder.find('form').remove();

		}).fail(function(data) {
			changeStatus('', $formHolder);
			$formHolder.prepend(data.responseText);
			return false;
		});

		return false;
	});

	// reply link toggle
	$('.comments').on('click', '.comment_reply', function(e){
		e.preventDefault();

		var $this = $(this);
		var $formHolder = $this.closest('.comment').find('.comment_reply_form');

		if ($formHolder.data('visible')){

			$formHolder.slideUp('fast');
			$(this).html('Reply')
				.removeClass('reply-close')
				.addClass('reply-open');
			$formHolder.data('visible', false)

		} else {
			var url = $(this).attr('href');
			var insertElem = $this.next();

			$formHolder.hide();
			$formHolder.parent().append('<div class="status"></div>');
			changeStatus('Loading form...', $formHolder);
			
			$.get(url, function(html){
				
					changeStatus('', $formHolder);
				
					$formHolder.html(html).show().slideUp(0).slideDown('fast');
					$this.html('Close')
						.addClass('reply-close')
						.removeClass('reply-open');
					$formHolder.data('visible', true)
				}
			);
			
		}
	});

	
})