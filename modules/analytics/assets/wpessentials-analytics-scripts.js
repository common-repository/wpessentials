($ =>
{
  $(() =>
  {
    const $bypass_admin = $('#wpessentials_analytics_bypass_administrators');
    const $bypass_loggedin = $('#wpessentials_analytics_bypass_loggedin');

    $bypass_admin.on('click', () =>
    {
      if (!$bypass_admin.is(':checked'))
      {
        $bypass_loggedin.prop('checked', false);
      }
    });

    $bypass_loggedin.on('click', () =>
    {
      if ($bypass_loggedin.is(':checked'))
      {
        $($bypass_admin).prop('checked', true);
      }
    });
  });
})(jQuery);