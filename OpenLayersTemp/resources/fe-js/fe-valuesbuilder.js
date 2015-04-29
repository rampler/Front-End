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
        coordinates[i] = {
            'lat': jsonCoordinates[i][0],
            'lon': jsonCoordinates[i][1],
            'order': i+1,
            'elev': 0,
            'group': 0
        };
    }
    var result = defaultValues;
    result.coordinates = coordinates;
    return result;
}