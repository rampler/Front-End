var OsmMap = new ol.layer.Tile({source: new ol.source.OSM({layer: 'osm'}), visible: false});

var actualMode = 'draw';

var TMSSource = new ol.source.XYZ({
     url: "http://otile1.mqcdn.com/tiles/1.0.0/map/{z}/{x}/{y}.jpg"

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

var editingLayer = new ol.layer.Vector({
    name: 'editingLayer',
    source: new ol.source.Vector(),
    style: addingLayer.getStyle(),
    visible: false
});

var layers = [OsmMap, tileLayer, vectorLayer, addingLayer, editingLayer];

var map = new ol.Map({
    target: 'map',
    layers: layers,
    controls: [new ol.control.Zoom, new ol.control.ScaleLine],
    view: new ol.View({
        center: ol.proj.transform([19.936754056961885, 50.06194367755023], 'EPSG:4326', 'EPSG:3857'),
        zoom: 13
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

    select_interaction = new ol.interaction.Draw({
        source: editingLayer.getSource(),
        type: ("Point"),
        style: new ol.style.Style({
            fill: new ol.style.Fill({
                color: 'rgba(255, 255, 255, 0.2)',
                width: 1
            }),
            stroke: new ol.style.Stroke({
                color: 'rgba(0, 126, 0, 0.7)',
                width: 5
            }),
            image: new ol.style.Circle({
                radius: 6,
                fill: new ol.style.Fill({
                    color: 'rgba(0, 126, 0, 0.7)'
                })
            })
        })
    });
    map.addInteraction(select_interaction);
    select_interaction.on('drawend', function(event) {
        var coordEPSG387 = getJSONcoordinates(editingLayer);
        var coordWGS84 = ol.proj.transform([coordEPSG387[0], coordEPSG387[1]],"EPSG:3857", "EPSG:4326");
        var distance = 1; //TODO in future - modify
        $.ajax({url: 'getNearestRoadSegment.php?lat='+coordWGS84[1]+'&lon='+coordWGS84[0]+'&dist='+distance}).done(function(data){
            console.log(data);
            data = JSON.parse(data);
            if(!data.error)
                FEFunctions.initializeJsonEditor(data,'edit');
            else {
                FEFunctions.showAlert(data.error, 'danger');
                clearDrawings();
            }
        });
    });
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
        FEFunctions.initializeJsonEditor(getEditorValues(getJSONcoordinates(addingLayer)),'add');
    });
}

addDrawInteraction();

function getJSONcoordinates(layer) {
    var data_type = 'GeoJSON', format = new ol.format[data_type](), data;
    try { data = format.writeFeatures(layer.getSource().getFeatures()); }
    catch (e) {
        console.log(e.name + ": " + e.message);
        return;
    }
    return JSON.parse(data).features[0].geometry.coordinates;
}

function clearDrawings() {
    addingLayer.getSource().clear();
    editingLayer.getSource().clear();
}




