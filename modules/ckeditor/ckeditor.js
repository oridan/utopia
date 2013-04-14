CKEDITOR.disableAutoInline = true;
CKEDITOR.config.toolbar_Basic = [
	[ 'Format', 'Styles', 'Bold', 'Italic', 'Underline', 'Strike', '-', 'NumberedList', 'BulletedList', '-', 'RemoveFormat' ]
];
CKEDITOR.config.toolbar_Advanced = [
	[ 'Format', 'Styles', 'Bold', 'Italic', 'Underline', 'Strike', '-', 'NumberedList', 'BulletedList', '-', 'RemoveFormat' ],
	{ name: 'colors', items : [ 'TextColor','BGColor' ] },
	'/',
	[ 'Link', 'Unlink', 'Anchor' ],
	{ name: 'insert', items : [ 'Image','Flash','Iframe','Table','HorizontalRule','SpecialChar' ] },
	[ 'Maximize', 'ShowBlocks' ],
	[ 'Sourcedialog' ]
];
CKEDITOR.config.toolbar = 'Advanced';
CKEDITOR.config.allowedContent = true;
CKEDITOR.config.filebrowserBrowseUrl = FILE_BROWSE_URL;
CKEDITOR.config.filebrowserWindowWidth = '800';
CKEDITOR.config.filebrowserWindowHeight = '600';

function _ckOnUpdate(info) {
	var newData = info.editor.getData();
	if (newData != info.listenerData) {
		info.editor.updateElement();
		if ($(info.editor.element.$).hasClass('uf')) {
			uf(info.editor.element.$,newData);
		}
	}
	info.editor.removeListener('blur',_ckOnUpdate);
	info.editor.on('blur',_ckOnUpdate,null,newData);
}
CKEDITOR.on('instanceReady',function(info) {
	var oldData = info.editor.getData();
	info.editor.on('blur',_ckOnUpdate,null,oldData);
});

	
function initCKE() {
	$('.ckEditReplace').each(function() {
		if ($(this).attr('name') && CKEDITOR.instances[$(this).attr('name')]) return false;
		var config = {};
		if ($(this).data('toolbar')) config.toolbar = $(this).data('toolbar');
		if ($(this).is('[contenteditable]'))
			CKEDITOR.inline(this,config);
		else
			CKEDITOR.replace(this,config);
	});
}
utopia.Initialise.add(initCKE);
