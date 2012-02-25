
function imglink(Y,data){
    alert(data);    
}

function embed_preview(Y,wwwroot){
    Y.use("json","io-base",function(){
        // noise reduction folding of form into sections
        Y.all('fieldset').addClass('qc_hidden');
        Y.all(".sectlink").on('click', function(e){
            Y.all('fieldset').addClass('qc_hidden');
            sect=(e.currentTarget.get('id'));
            Y.one('#sect'+sect).removeClass('qc_hidden');    
        });
        
        Y.one('#id_preview_url').on('click',previewer);
        Y.one('#id_prewidth').on('change',previewer);
        
        function previewer(e){
            var url = Y.one('#id_url').get('value');
            var width = Y.one('#id_prewidth').get('value');
    
            Y.on('io:complete', complete, Y);
            Y.on('io:start', start, Y);

            var uri = wwwroot + '/mod/quickcontent/embed.php?width='+width+'&url='+url;
            var request = Y.io(uri,{});
                       
        }
        
        function start(id, o) {
          Y.one('#ePreview').set('innerHTML','');
        };

        function complete(id, o) {
            var r;
            var data = eval('(' + o.responseText + ')');
            r = Y.JSON.parse(o.responseText);
            Y.one('#ePreview').set('innerHTML','');
            Y.one('#ePreview').append(r.previewCode);
            Y.one('#embedcode').set('value',r.embedCode);
        };

    })
}
