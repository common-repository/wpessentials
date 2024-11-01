($ =>
{
  $(() =>
  {
    const $toggle_input =
      $('div.wpessentials-settings-page table.form-table tr.wpessentials-toggle input[type=checkbox]');

    const $input_rows =
      $('div.wpessentials-settings-page table.form-table tr:not(.wpessentials-toggle):not(.wpessentials-toggle-ignore)');

    if (!$toggle_input.prop('checked'))
    {
      $input_rows.css('opacity', '.6');
    }

    $toggle_input.on('change', () =>
    {
      if ($toggle_input.prop('checked'))
      {
        $input_rows.css('opacity', '1');
      } else
      {
        $input_rows.css('opacity', '.6');
      }
    });
  });
})(jQuery);