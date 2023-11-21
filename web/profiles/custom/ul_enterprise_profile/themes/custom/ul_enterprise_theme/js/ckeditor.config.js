CKEDITOR.on('dialogDefinition', function(ev) {
var dialogName = ev.data.name;
var dialogDefinition = ev.data.definition;

if(dialogName == 'table') {
  var infoTab = dialogDefinition.getContents('info');
  infoTab.get('selHeaders')['default'] = 'row';
}
}
);
