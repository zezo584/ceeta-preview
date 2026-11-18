/* CEETA — Training Gallery picker (Academy page meta box) */
jQuery(function ($) {
  var frame, $ids = $('#ceeta-gallery-ids'), $prev = $('#ceeta-gallery-preview');
  function render(list) {
    $prev.empty();
    list.forEach(function (a) {
      var u = (a.sizes && a.sizes.thumbnail) ? a.sizes.thumbnail.url : a.url;
      $prev.append('<li><img src="' + u + '" width="80" height="80" style="object-fit:cover;border-radius:8px" alt=""></li>');
    });
  }
  $('#ceeta-gallery-pick').on('click', function (e) {
    e.preventDefault();
    var current = ($ids.val() || '').split(',').filter(Boolean);
    frame = wp.media({ title: 'Training Gallery', button: { text: 'Use these photos' }, library: { type: 'image' }, multiple: 'add' });
    frame.on('open', function () {
      var sel = frame.state().get('selection');
      current.forEach(function (id) { var att = wp.media.attachment(id); att.fetch(); sel.add(att); });
    });
    frame.on('select', function () {
      var list = frame.state().get('selection').toJSON();
      $ids.val(list.map(function (a) { return a.id; }).join(','));
      render(list);
    });
    frame.open();
  });
  $('#ceeta-gallery-clear').on('click', function (e) { e.preventDefault(); $ids.val(''); $prev.empty(); });
});
