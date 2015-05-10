/**
 * Created by Mateusz on 2015-04-04.
 */

$('[data-toggle="tooltip"]').tooltip();

var addForm = document.getElementById('addForm');
var editForm = document.getElementById('editForm');
var jsonEditor;

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

$('#deleteBtn').click(function(){
    $('#deleteModal').modal('show');
});

$('#deleteConfirmedBtn').click(function(){
    var $btn = $(this).button('loading');
    //TODO ajax do php odpowiedzialnego za usuwanie
    $btn.button('reset');
    $('#deleteModal').modal('hide');
    $('#editModal').modal('hide');
    FEFunctions.showAlert('<strong>Sukces!</strong> Pomyślnie usunięto segment drogi!','success');
});

$('#addBtn').click(function(){
    var $btn = $(this).button('loading');
    //TODO ajax do php odpowiedzialnego za dodawanie segmentu
    $btn.button('reset');
    $('#addModal').modal('hide');
    FEFunctions.showAlert('<strong>Sukces!</strong> Pomyślnie dodano segment drogi!','success');
});

$('#editBtn').click(function(){
    var $btn = $(this).button('loading');
    //TODO ajax do php odpowiedzialnego za edycję segmentu
    $btn.button('reset');
    $('#editModal').modal('hide');
    FEFunctions.showAlert('<strong>Sukces!</strong> Pomyślnie zaktualizowano segment drogi!','success');
});