var OsmMap = new ol.layer.Tile({
    source: new ol.source.MapQuest({layer: 'osm'})
});

var map = new ol.Map({
    target: 'map',
    layers: [OsmMap],
    view: new ol.View({
        center: [2000000, 6800000],
        zoom: 6
    })
});

var featureOverlay = new ol.FeatureOverlay({
    style: new ol.style.Style({
        fill: new ol.style.Fill({
            color: 'rgba(255, 255, 255, 0.2)'
        }),
        stroke: new ol.style.Stroke({
            color: '#ff0000',
            width: 4
        }),
        image: new ol.style.Circle({
            radius: 7,
            fill: new ol.style.Fill({
                color: '#ff0000'
            })
        })
    })
});
featureOverlay.setMap(map);

//var modify = new ol.interaction.Modify({
//  features: featureOverlay.getFeatures(),
//  // the SHIFT key must be pressed to delete vertices, so
//  // that new vertices can be drawn at the same position
//  // of existing vertices
//  deleteCondition: function(event) {
//    return ol.events.condition.shiftKeyOnly(event) &&
//        ol.events.condition.singleClick(event);
//  }
//});
//map.addInteraction(modify);

var draw; // global so we can remove it later
function addInteraction() {
    draw = new ol.interaction.Draw({
        features: featureOverlay.getFeatures(),
        type: "LineString"
    });
    map.addInteraction(draw);
}

addInteraction();
