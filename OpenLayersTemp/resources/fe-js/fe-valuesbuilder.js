/**
 * Created by Mateusz on 2015-04-04.
 */

var defaultValues = {
    "id":"",
    "street":"",
    "mainLightningClass":"",
    "desc":"",
    "lampArrangement":"SINGLE_SIDED_RIGHT",
    "roadSection":[],
    "coordinates":[]
};

function getEditorValues(jsonCoordinates) {
    var coordinates=[];
    for(var i=0; i<jsonCoordinates.length; i++) {
        var tempCoord = ol.proj.transform([jsonCoordinates[i][0], jsonCoordinates[i][1]],"EPSG:3857", "EPSG:4326");
        coordinates[i] = {
            'lat': tempCoord[1],
            'lon': tempCoord[0],
            'order': i+1,
            'elev': 0,
            'group': 1
        };
    }
    var result = defaultValues;
    result.coordinates = coordinates;
    return result;
}