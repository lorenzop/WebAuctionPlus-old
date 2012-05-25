$("[id='link']").click(function() {
  $("[id='frame']").attr('src', './?page=createauction&amp;id='.((int)$itemRow['id']).'');
});
