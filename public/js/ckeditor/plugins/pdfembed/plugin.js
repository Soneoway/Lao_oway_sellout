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
CKEDITOR.plugins.add('pdfembed', {
    lang: 'en',
    version: 1.0,
    init: function (editor) {
        // Command
        editor.addCommand('pdfembed', new CKEDITOR.dialogCommand('pdfembedDialog'));
        // Toolbar button
        editor.ui.addButton('PdfEmbed', {
            label: editor.lang.pdfembed.button,
            command: 'pdfembed',
            toolbar: 'insert',
            icon : this.path + 'icons/pdf.png'
        });
        // Dialog window
        CKEDITOR.dialog.add('pdfembedDialog', this.path + 'dialogs/pdfembedDialog.js');
    }
});