/**
 * Created by Mateusz on 2015-04-04.
 */

$('[data-toggle="tooltip"]').tooltip();

var addForm = document.getElementById('addForm');
var jsonEditor;
var schema;

function initializeJsonEditor(jsonValues) {
    addForm.innerHTML = null;
    if(!schema) {
        $.ajax("resources/schema.json")
            .done(function (data) {
                schema = data;
                buildJsonEditor(jsonValues);
            });
    }
    else
        buildJsonEditor();
}

function buildJsonEditor(jsonValues){
    jsonEditor = new JSONEditor(addForm, {
        theme: 'bootstrap3',
        disable_edit_json: true,
        disable_properties: true,
        disable_collapse: true,
        form_name_root: "T",
        schema: schema
    });
    if(jsonValues)
        jsonEditor.setValue(jsonValues);
    $('#addModal').modal('show');
}

/** Action on modal hiding **/
$('#addModal').on('hidden.bs.modal', function (e) {
    clearDrawings();
});

/** Esc key reset drawing**/
$(document).on('keyup', function (event) {
    if (event.keyCode == 27) {
        console.log(draw_interaction.getProperties());
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