/**
 * Created by Mateusz on 2015-04-04.
 */

$('[data-toggle="tooltip"]').tooltip();

function spawnModal() {
    $('#addModal').modal('show');
}

/** Action on modal hiding **/
$('#addModal').on('hidden.bs.modal', function (e) {
    clearDrawings();
});

/** Delete key reset drawing**/
$(document).on('keyup', function (event) {
    if (event.keyCode == 46) {
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