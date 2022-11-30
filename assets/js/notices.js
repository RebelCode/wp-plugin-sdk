jQuery(function ($) {
  const L10N = {
    noticeId: "%s",
    ajaxUrl: "%s",
    nonce: "%s",
    action: "%s",
  }

  $('#' + L10N.noticeId + ' .notice-dismiss').on('click', function (e) {
    const notice = $(this).parent().attr('data-notice')
    const nonce = L10N.nonce
    const action = L10N.action

    $.ajax({
      url: L10N.ajaxUrl,
      method: 'POST',
      data: {action, nonce, notice},
    })

    e.preventDefault()
    e.stopPropagation()
  })
})
