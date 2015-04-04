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
    controls: [new ol.control.Zoom, new ol.control.ScaleLine],
    view: new ol.View({
        center: [2000000, 6800000],
        zoom: 6
    })
});

var select_interaction,
    draw_interaction;

var $interaction_type = $('[name="interaction_type"]');
$interaction_type.on('change', function(e) {
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
}

function addDrawInteraction() {
    map.removeInteraction(select_interaction);

    draw_interaction = new ol.interaction.Draw({
        source: vector_layer.getSource(),
        type: ("LineString"),
        style: new ol.style.Style({
            fill: new ol.style.Fill({
                color: 'rgba(255, 255, 255, 0.2)',
                width: 1
            }),
            stroke: new ol.style.Stroke({
                color: 'rgba(0, 153, 255, 0.7)',
                width: 5
            }),
            image: new ol.style.Circle({
                radius: 6,
                fill: new ol.style.Fill({
                    color: 'rgba(0, 153, 255, 0.7)'
                })
            })
        })
    });
    map.addInteraction(draw_interaction);
    draw_interaction.on('drawend', function(event) {
        getJSONcoordinates();
        spawnModal();
    });
}

addDrawInteraction();

function getJSONcoordinates() {
    var data_type = 'GeoJSON', format = new ol.format[data_type](), data;
    try { data = format.writeFeatures(vector_layer.getSource().getFeatures()); }
    catch (e) {
        console.log(e.name + ": " + e.message);
        return;
    }

    console.log(data);
    return data;
}

function clearDrawings() {
    vector_layer.getSource().clear();
    if (select_interaction) {
        select_interaction.getFeatures().clear();
    }
}




