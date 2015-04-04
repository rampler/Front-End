/**
 * Created by Mateusz on 2015-04-04.
 */

var addForm = document.getElementById('addForm');
JSONEditor.defaults.languages.es = {
    error_notset: "propiedad debe existir"
};
JSONEditor.defaults.language = "es";

console.log(JSONEditor.defaults);
var jsonEditor = new JSONEditor(addForm, {
    theme: 'bootstrap3',
    disable_edit_json: true,
    disable_properties: true,
    disable_collapse: true,
    form_name_root: "T",
    schema: {
        type: "object",
        title: "Nowy segment drogi",
        properties: {
            id: {
                "type": "string",
                "title": "ID"
            },
            street: {
                "type": "string",
                "title": "Nazwa ulicy"
            },
            desc: {
                "type": "string",
                "title": "Opis"
            },
            mainLightningClass: {
                "type": "string",
                "title": "Klasa oświetleniowa"
            },
            roadSection: {
                "type": "array",
                "title": "Sekcje drogi",
                "format" : "tabs",
                "items": {
                    "type": "object",
                    "properties" : {
                        "id" : {
                            type: "string"
                        }
                    }
                }
            }
        }
    }
});