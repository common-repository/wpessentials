($ =>
{
  $(() =>
  {
    const $debug_elem = $('#wpessentials_devmode_debug');
    const $logging_elem = $('#wpessentials_devmode_logging');
    const $savequeries_elem = $('#wpessentials_devmode_savequeries');

    $debug_elem.on('click', () =>
    {
      if (!$debug_elem.is(':checked'))
      {
        $logging_elem.prop('checked', false);
        $savequeries_elem.prop('checked', false);
      }
    });

    $logging_elem.on('click', () =>
    {
      if ($logging_elem.is(':checked'))
      {
        $debug_elem.prop('checked', true);
      }
    });
    $savequeries_elem.on('click', () =>
    {
      if ($savequeries_elem.is(':checked'))
      {
        $debug_elem.prop('checked', true);
      }
    });

  });

  window.wpessentials_clear_debug_log = (elem, ajax_url) =>
  {
    if (!window.confirm('Clear the debug log file?'))
    {
      return;
    }
    $.ajax({
      url: ajax_url,
      success: () =>
      {
        $(elem).parents('div').first().remove();
      }
    });
  };
})(jQuery);