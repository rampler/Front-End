/**
 * Created by Mateusz on 2015-05-10.
 */

var FEFunctions = {
    schema : null,
    showAlert : function(alertMessage) {
        $('#alert-message').html(alertMessage);
        $('#alertModal').modal('show');
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