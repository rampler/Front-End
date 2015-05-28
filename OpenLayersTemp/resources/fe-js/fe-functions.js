/**
 * Created by Mateusz on 2015-05-10.
 */

JSONEditor.defaults.languages.en = {
    error_notset: "Pole jest wymagane",
    error_pattern: "Błędna wartość pola",
    error_notempty: "Pole jest wymagane"
};

var FEFunctions = {
    schema : null,
    alertModalHead : '<div class="fe-alert"><div class="alert alert-dismissible fade" role="alert" style="margin:0;"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>',
    alertModalFooter: '</div></div>',
    showAlert : function(alertMessage, alertType) {
        if(!alertType)
            alertType = 'danger';

        var $alertModal = $(FEFunctions.alertModalHead+((alertType == 'danger')?'<strong>Błąd!</strong> ':'')+alertMessage+FEFunctions.alertModalFooter);
        $alertModal.css('top',$('.fe-alert').length*50+10);
        $('[role=alert]',$alertModal).addClass('alert-'+alertType);
        $alertModal.appendTo($('body'));

        setTimeout(function(){$('[role=alert]',$alertModal).addClass('in');},100);
        setTimeout(function(){
            $('[role=alert]',$alertModal).removeClass('in');
            setTimeout(function(){
                $alertModal.remove();
                $('.fe-alert').each(function(){
                    $(this).css('top',$(this).position().top-50);
                });
            },100);
        },3000);
    },
    buildJsonEditor : function(jsonValues,action){
        FEFunctions.schema.title = (action == 'add') ? 'Nowy segment drogi' : 'Edytuj segment drogi';
        jsonEditor = new JSONEditor((action == 'add') ? addForm : editForm, {
            theme: 'bootstrap3',
            disable_edit_json: true,
            disable_properties: true,
            disable_collapse: true,
            form_name_root: "T",
            show_errors: "always",
            schema: FEFunctions.schema,
            ajax: true
        });

        jsonEditor.on('ready',function() {
            jsonEditor.setValue(jsonValues);
            if(action == 'add')
                $('#addModal').modal('show');
            else
                $('#editModal').modal('show');
        });


    },
    initializeJsonEditor : function(jsonValues,action) {
        if(action == 'add')
            addForm.innerHTML = null;
        else
            editForm.innerHTML = null;

        if(!FEFunctions.schema) {
            $.ajax("resources/schema.json")
                .done(function (data) {
                    FEFunctions.schema = data;
                    FEFunctions.buildJsonEditor(jsonValues,action);
                });
        } else
            FEFunctions.buildJsonEditor(jsonValues,action);
    },
    focusOnNextPoint : function(){
        var $containerCoordinates = $('.container-coordinates');
        var pointsTabs = $('a.list-group-item', $containerCoordinates);
        var index = 0;

        for(var i=0; i<pointsTabs.length; i++)
            if($(pointsTabs[i]).hasClass('active'))
                index = i;

        if(index != pointsTabs.length-1 && pointsTabs.length) {
            $(pointsTabs[index+1])[0].click();
            $('.container-lat input').focus();
        }
    },
    focusOnNextSection : function(){
        var $containerRoadSection = $('.container-roadSection');
        var sectionsTabs = $('a.list-group-item', $containerRoadSection);
        var index = 0;

        for (var i = 0; i < sectionsTabs.length; i++)
            if ($(sectionsTabs[i]).hasClass('active'))
                index = i;

        if (index != sectionsTabs.length - 1 && sectionsTabs.length) {
            $(sectionsTabs[index + 1])[0].click();
            $('.container-id input', $containerRoadSection).focus();
        }
    }
};