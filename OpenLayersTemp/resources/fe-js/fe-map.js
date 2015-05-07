var OsmMap = new ol.layer.Tile({source: new ol.source.OSM({layer: 'osm'})});

var actualMode = 'draw';

var TMSSource = new ol.source.XYZ({
    projection: 'EPSG:900913'
    , url: "http://otile1.mqcdn.com/tiles/1.0.0/map/{z}/{x}/{y}.jpg"

});

//var TSMTEMPSource = new ol.source.XYZ({
//    url: "http://tilecache.osgeo.org/wms-c/Basic.py/"
//});

var tileLayer = new ol.layer.Tile({
    source : TMSSource
});

var vectorLayer = new ol.layer.Vector({
    visible: false
});

var addingLayer = new ol.layer.Vector({
    name: 'addingLayer',
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

var layers = [OsmMap, tileLayer, vectorLayer, addingLayer];

var map = new ol.Map({
    target: 'map',
    layers: layers,
    controls: [new ol.control.Zoom, new ol.control.ScaleLine],
    view: new ol.View({
        center: [2000000, 6800000],
        zoom: 6,
        projection: 'EPSG:900913'
    })
});

var select_interaction,
    draw_interaction;

var $interaction_type = $('[name="interaction_type"]');
$interaction_type.on('change', function(e) {
    if (this.value === 'draw') {
        actualMode = 'draw';
        addDrawInteraction();
    } else {
        actualMode = 'edit';
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
        source: addingLayer.getSource(),
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
        initializeJsonEditor(getEditorValues(getJSONcoordinates()));
    });
}

addDrawInteraction();

function getJSONcoordinates() {
    var data_type = 'GeoJSON', format = new ol.format[data_type](), data;
    try { data = format.writeFeatures(addingLayer.getSource().getFeatures()); }
    catch (e) {
        console.log(e.name + ": " + e.message);
        return;
    }
    return JSON.parse(data).features[0].geometry.coordinates;
}

function clearDrawings() {
    addingLayer.getSource().clear();
    if (select_interaction) {
        select_interaction.getFeatures().clear();
    }
}




