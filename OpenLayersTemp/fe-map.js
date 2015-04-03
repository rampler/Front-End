var OsmMap = new ol.layer.Tile({source: new ol.source.MapQuest({layer: 'osm'})});

var vector_layer = new ol.layer.Vector({
    name: 'my_vectorlayer',
    source: new ol.source.Vector(),
    style: new ol.style.Style({
        fill: new ol.style.Fill({
            color: 'rgba(255, 255, 255, 0.2)'
        }),
        stroke: new ol.style.Stroke({
            color: 'rgba(0, 123, 225, 1)',
            width: 5
        }),
        image: new ol.style.Circle({
            radius: 7,
            fill: new ol.style.Fill({
                color: 'rgba(0, 153, 255, 1)'
            })
        })
    })
});

var layers = [OsmMap, vector_layer];

var map = new ol.Map({
    target: 'map',
    layers: layers,
    view: new ol.View({
        center: [2000000, 6800000],
        zoom: 6
    })
});

var select_interaction,
    draw_interaction,
    modify_interaction;

var $interaction_type = $('[name="interaction_type"]');
$interaction_type.on('click', function(e) {
    if (this.value === 'draw') {
        addDrawInteraction();
    } else {
        addModifyInteraction();
    }
});

function addModifyInteraction() {
    map.removeInteraction(draw_interaction);
    select_interaction = new ol.interaction.Select({
        layers: function(vector_layer) {
            return vector_layer.get('name') === 'my_vectorlayer';
        }
    });
    map.addInteraction(select_interaction);

    var selected_features = select_interaction.getFeatures();
    selected_features.on('add', function(event) {
        var feature = event.element;
        feature.on('change', saveData);
        $(document).on('keyup', function(event) {
            if (event.keyCode == 46) {
                selected_features.forEach(function(selected_feature) {
                    var selected_feature_id = selected_feature.getId();
                    selected_features.remove(selected_feature);
                    var vectorlayer_features = vector_layer.getSource().getFeatures();
                    vectorlayer_features.forEach(function(source_feature) {
                        var source_feature_id = source_feature.getId();
                        if (source_feature_id === selected_feature_id) {
                            vector_layer.getSource().removeFeature(source_feature);
                            saveData();
                        }
                    });
                });
                $(document).off('keyup');
            }
        });
    });
    //modify_interaction = new ol.interaction.Modify({
    //    features: selected_features,
    //    deleteCondition: function(event) {
    //        return ol.events.condition.shiftKeyOnly(event) &&
    //            ol.events.condition.singleClick(event);
    //    }
    //});
    //map.addInteraction(modify_interaction);
}

function addDrawInteraction() {
    map.removeInteraction(select_interaction);
    map.removeInteraction(modify_interaction);

    draw_interaction = new ol.interaction.Draw({
        source: vector_layer.getSource(),
        type: /** @type {ol.geom.GeometryType} */ ("LineString")
    });
    map.addInteraction(draw_interaction);
    draw_interaction.on('drawend', function(event) {
        var id = uid();
        event.feature.setId(id);
        saveData();
    });
}

addDrawInteraction();

function saveData() {
    var data_type = 'GeoJSON', format = new ol.format[data_type](), data;
    try { data = format.writeFeatures(vector_layer.getSource().getFeatures()); }
    catch (e) {
        console.log(e.name + ": " + e.message);
        return;
    }

    console.log(data);
}

$("#delete").click(function() {
    vector_layer.getSource().clear();
    if (select_interaction) {
        select_interaction.getFeatures().clear();
    }
    $('#data').val('');
});

function uid(){
    var id = 0;
    return function() {
        if (arguments[0] === 0) {
            id = 0;
        }
        return id++;
    }
}

$('#osm-checkbox').change(function(){
    layers[0].setVisible($('#osm-checkbox').is(':checked'));
});