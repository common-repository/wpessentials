($ =>
{
  $(() =>
  {
    $('#wpessentials_user_public_names_table td.user_login').each((i, td_user_login) =>
    {
      let $td_user_login = $(td_user_login);
      let user_login = $td_user_login.text().toLowerCase();
      $td_user_login.nextAll('td.user_public').filter(function ()
      {
        let $this = $(this);
        return (
          $this.text().toLowerCase() == user_login ||
          $this.find('input').val().toLowerCase() == user_login
        );
      }).addClass('wpessentials_links_tr_err');
    });
  });

  window.wpessentials_generate_secret_keys = ajax_url =>
  {
    if (!window.confirm('Regenerate secret keys?\nYou will need to log in again.'))
    {
      return;
    }
    $.ajax({
      url: ajax_url,
      success: () =>
      {
        location.reload();
      }
    });
  };

  window.wpessentials_release_lockout = (elem, ajax_url, lockout_id) =>
  {
    elem = $(elem);
    if (!window.confirm('Release lockout with ID ' + lockout_id + '?'))
    {
      return;
    }
    $.ajax({
      url: ajax_url,
      method: 'POST',
      data: {
        lockout_id: lockout_id,
      },
      dataType: 'text',
      success: data =>
      {
        if (data === 'true')
        {
          elem.parents('tr').first().remove();
        }
      }
    });
  };

  window.wpessentials_print_user_public_names_table = (data) =>
  {
    let $div = $('div#wpessentials_user_public_names_div');
    let $table = $('table#wpessentials_user_public_names_table');
    if (!data.length)
    {
      return $div.hide();
    }
    $div.show();
    let $tr_head = $('<tr/>');
    $tr_head
      .append('<th>Username</th>')
      .append('<th>Full name</th>')
      .append('<th>Nicename</th>')
      .append('<th>Display name</th>')
      .append('<th>Roles</th>');
    $table.append($tr_head);
    data.forEach(user =>
    {
      let $tr = $('<tr/>');
      let $td_username = $('<td/>').text(user.username);
      let $td_fullname = $('<td/>').text(user.fullname);
      let $td_nicename = $('<td/>').text(user.nicename);
      let $td_displayname = $('<td/>');
      let $td_roles = $('<td/>').text(user.roles);
      if (user.nicename.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toUpperCase() == user.username.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toUpperCase())
      {
        $td_nicename.addClass('wpessentials_links_tr_err');
      }
      if (user.displayname.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toUpperCase() == user.username.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toUpperCase())
      {
        $td_displayname.addClass('wpessentials_links_tr_err');
      }
      let $input = $('<input/>');
      $input
        .addClass('user_displayname')
        .attr('type', 'text')
        .attr('name', 'userid_' + user.ID)
        .val(user.displayname);
      $td_displayname.append($input);
      $tr.append(
        $td_username,
        $td_fullname,
        $td_nicename,
        $td_displayname,
        $td_roles
      );
      $table.append($tr);
    });
  };

  window.wpessentials_update_user_public_names = (elem, ajax_url) =>
  {
    let $loader = $(elem).next('span');
    $loader.html('<span></span>'); // activate wpessentials-loader
    let $table = $('table#wpessentials_user_public_names_table');
    let post_data = [];
    $table.find('input.user_displayname').each((i, $input) =>
    {
      $input = $($input);
      if (!/^userid_\d+$/.test($input.attr('name')))
      {
        return true;
      }
      post_data.push({
        ID: $input.attr('name').match(/\d+$/)[0],
        name: $input.val(),
      });
    });
    $.ajax({
      url: ajax_url,
      method: 'POST',
      data: {
        users: post_data
      },
      // contentType: 'application/json; charset=UTF-8',
      dataType: 'json',
      success: data =>
      {
        $table.empty();
        window.wpessentials_print_user_public_names_table(data);
      },
      complete: () =>
      {
        $loader.html('&#10003;'); // deactivate wpessentials-loader
      }
    });
  };
})(jQuery);