/**
 * Created by Mateusz on 2015-05-10.
 */

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
            schema: FEFunctions.schema
        });
        if(jsonValues)
            jsonEditor.setValue(jsonValues);

        if(action == 'add')
            $('#addModal').modal('show');
        else
            $('#editModal').modal('show');
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
    }
};