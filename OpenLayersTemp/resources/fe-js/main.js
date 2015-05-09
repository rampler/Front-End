/**
 * Created by Mateusz on 2015-04-04.
 */

$('[data-toggle="tooltip"]').tooltip();

var addForm = document.getElementById('addForm');
var editForm = document.getElementById('editForm');
var jsonEditor;
var schema;

function initializeJsonEditor(jsonValues,action) {
    if(action == 'add')
        addForm.innerHTML = null;
    else
        editForm.innerHTML = null;

    if(!schema) {
        $.ajax("resources/schema.json")
            .done(function (data) {
                schema = data;
                buildJsonEditor(jsonValues,action);
            });
    } else
        buildJsonEditor(jsonValues,action);
}

function buildJsonEditor(jsonValues,action){
    schema.title = (action == 'add') ? 'Nowy segment drogi' : 'Edytuj segment drogi';
    jsonEditor = new JSONEditor((action == 'add') ? addForm : editForm, {
        theme: 'bootstrap3',
        disable_edit_json: true,
        disable_properties: true,
        disable_collapse: true,
        form_name_root: "T",
        schema: schema
    });
    if(jsonValues)
        jsonEditor.setValue(jsonValues);

    if(action == 'add')
        $('#addModal').modal('show');
    else
        $('#editModal').modal('show');
}

/** Action on modal hiding **/
$('#addModal').on('hidden.bs.modal', clearDrawings);
$('#editModal').on('hidden.bs.modal', clearDrawings);

/** Esc key reset drawing**/
$(document).on('keyup', function (event) {
    if (event.keyCode == 27 && actualMode == 'draw') {
        map.removeInteraction(draw_interaction);
        map.addInteraction(draw_interaction);
    }
});

$('#osm-checkbox').change(function () {
    $checkbox = $('#osm-checkbox');
    layers[0].setVisible($checkbox.is(':checked'));
    if ($checkbox.is(':checked'))
        $('#osm-checkbox_label').attr('title', "Wyłącz OSM").tooltip('fixTitle').tooltip('show');
    else
        $('#osm-checkbox_label').attr('title', "Włącz OSM").tooltip('fixTitle').tooltip('show');
});

$('#tilesLayerTab').click(function(){
    layers[1].setVisible(true);
    layers[2].setVisible(false);
});

$('#vectorsLayerTab').click(function(){
    layers[1].setVisible(false);
    layers[2].setVisible(true);
});