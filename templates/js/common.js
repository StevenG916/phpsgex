function flatten(m){
    var ret= [];

    m.forEach( function(a){
        ret= ret.concat(a);
    } );

    return ret;
}

function attachSearchName(selector){
    selector.keypress(function(){
        $.getJSON("?pg=Profile&act=GetSearchName&name="+$(this).val(), function(result){
            var list= $("#"+selector.attr("list"));
            list.empty();
            result.forEach( function(f){
                $("<option value='"+f+"'></option>").appendTo(list);
            } );
        });
    });
}

function multiFormSingleSubmit(frm, id){
    var data= {};
    frm.find("input[name*='["+id+"]']").each(function(){
        data[ $(this).attr("name") ]= $(this).val();
    });
    $.post(frm.attr("action"), data);
}

$(document).ready(function(){
    tinymce.init({
        selector: 'textarea.editor',
        height: 150,
        menubar: false,
        plugins: [
            'advlist autolink lists link image charmap print preview anchor textcolor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table contextmenu paste code help wordcount'
        ],
        toolbar: 'insert | undo redo |  formatselect | bold italic forecolor backcolor  | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
        content_css: [
            '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
            'includes/tinymce/skins/content.min.css']
    });
});
