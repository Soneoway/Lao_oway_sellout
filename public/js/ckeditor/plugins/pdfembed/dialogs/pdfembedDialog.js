/*
 *   Plugin developed by CTRL+N.
 *
 *   LICENCE: GPL, LGPL, MPL
 *   NON-COMMERCIAL PLUGIN.
 *
 *   Website: https://www.ctrplusn.net/
 *   Facebook: https://www.facebook.com/ctrlplusn.net/
 *
 */
CKEDITOR.dialog.add('pdfembedDialog', function (editor) {
    return {
        title: editor.lang.pdfembed.title,
        minWidth: 400,
        minHeight: 80,
        contents: [
            {
                id: 'tab-basic',
                label: 'Basic Settings',
                elements: [
                    {
                        type: 'html',
                        html: '<p>' + editor.lang.pdfembed.onlytxt + '</p>'
                    },
                    {
                        type: 'text',
                        id: 'url_pdf',
                        label: 'URL (ex: https://drive.google.com/file/d/1adlKA2cmGZgyoJndC5OkCAIxcj1DyQ0v/view)',
                        validate: CKEDITOR.dialog.validate.notEmpty(editor.lang.pdfembed.validatetxt)
                    },
                    {
                        type: 'hbox',
                        widths: ['50%', '50%'],
                        children: [
                            {
                                type: 'text',
                                id: 'txtWidth',
                                width: '100%',
                                label: editor.lang.pdfembed.txtWidth
                            },
                            {
                                type: 'text',
                                id: 'txtHeight',
                                width: '100%',
                                label: editor.lang.pdfembed.txtHeight
                            }]
                    }
                ]
            }
        ],
        onOk: function () {
            var
                dialog = this,
                div_container = new CKEDITOR.dom.element('div');

            var width = dialog.getValueOf('tab-basic', 'txtWidth');
            var height = dialog.getValueOf('tab-basic', 'txtHeight');

            // Auto-detect
            var url = detect(dialog.getValueOf('tab-basic', 'url_pdf'));
            // Create iframe with specific url
            if (url.length > 1) {
                var iframe = new CKEDITOR.dom.element.createFromHtml('<iframe frameborder="0" width="' + width + '" height="' + height + '" src="' + url + '" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>');
                div_container.append(iframe);
                editor.insertElement(div_container);
            }
        }
    };
});

// Detect platform and return pdf ID
function detect(url) {
    var embed_url = '';
    // drive google pdf url
    if (url.indexOf('drive.google') > 0) {
        id = getId(url, "d/", 2).replace('/view', '');
        return embed_url = 'https://drive.google.com/file/d/' + id + '/preview';
    } else {
        return embed_url = 'https://drive.google.com/viewerng/viewer?embedded=true&url=' + url;
    }

    return embed_url;
}

// Return video ID from URL
function getId(url, string = "/", index = 1) {
    return url.substring(url.lastIndexOf(string) + index, url.length);
}