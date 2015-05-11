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

$('#tilesLayerTab').click(function () {
    layers[1].setVisible(true);
    layers[2].setVisible(false);
});

$('#vectorsLayerTab').click(function () {
    layers[1].setVisible(false);
    layers[2].setVisible(true);
});

$('#deleteBtn').click(function () {
    $('#deleteModal').modal('show');
});

$('#deleteConfirmedBtn').click(function () {
    var $btn = $(this).button('loading');
    $.ajax({
        url: 'deleteRoadSegment.php',
        type: 'POST',
        data: {
            roadSegmentId: jsonEditor.getValue().id
        }
    }).done(function (data) {
        data = JSON.parse(data);
        $btn.button('reset');
        $('#deleteModal').modal('hide');
        $('#editModal').modal('hide');
        FEFunctions.showAlert(data.message, data.type);
    });
});

$('#addBtn').click(function () {
    if (!(jsonEditor.validate().length)) {
        var $btn = $(this).button('loading');
        $.ajax({
            url: 'addRoadSegment.php',
            type: 'POST',
            data: {
                json: jsonEditor.getValue()
            }
        }).done(function (data) {
            data = JSON.parse(data);
            $btn.button('reset');
            $('#addModal').modal('hide');
            FEFunctions.showAlert(data.message, data.type);
        });
    }
    else
        FEFunctions.showAlert('Nie wszystkie wymagane pola są poprawne!', 'danger');
});

$('#saveBtn').click(function () {
    if (!(jsonEditor.validate().length)) {
        var $btn = $(this).button('loading');
        $.ajax({
            url: 'saveRoadSegment.php',
            type: 'POST',
            data: {
                json: jsonEditor.getValue()
            }
        }).done(function (data) {
            data = JSON.parse(data);
            $btn.button('reset');
            $('#editModal').modal('hide');
            FEFunctions.showAlert(data.message, data.type);
        });
    }
    else
        FEFunctions.showAlert('Nie wszystkie wymagane pola są poprawne!', 'danger');
});